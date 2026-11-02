<?php

namespace App\Services\Approval;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApprovalCandidateResolver
{
    /**
     * Resolve the positions and users that must act for one policy step.
     *
     * Returned shape:
     * [
     *   'positions' => [
     *      [
     *        'Position' => object,
     *        'Candidates' => [['User_Id'=>1,'Type'=>'HOLDER','Delegation_ID'=>null]],
     *        'Fallback' => null|'PARENT'|'HIGHER_LEVEL',
     *        'Action' => 'TASK'|'AUTO_APPROVE'|'HOLD'|'BLOCK',
     *        'Reason' => null|'NO_CANDIDATE'|'NO_HIGHER_LEVEL'|'VACANT_POSITION',
     *      ],
     *   ],
     *   'Action' => 'TASK'|'AUTO_APPROVE'|'HOLD'|'BLOCK',
     *   'Reason' => null|string,
     * ]
     */
    public function resolve($policy, $step, array $context, int $requesterId = null): array
    {
        $positions = $this->positionsForStep($step, $context);
        $fallbackType = null;

        if (empty($positions)) {
            $positions = $this->higherLevelPositions((int) $step->Level_Number, $context);
            $fallbackType = 'HIGHER_LEVEL';
        }

        if (empty($positions)) {
            $action = $this->noPositionAction($policy);
            $reason = $action === 'HOLD' || $action === 'BLOCK'
                ? 'NO_HIGHER_LEVEL'
                : 'NO_HIGHER_LEVEL';

            return [
                'positions' => [],
                'Action' => $action,
                'Reason' => $reason,
            ];
        }

        $items = [];
        foreach ($positions as $position) {
            $candidates = $this->candidatesForPosition($policy, $position, $context);
            $usedFallback = $fallbackType;

            if (empty($candidates)) {
                $fallback = $this->fallbackForPosition($policy, $position, $context);
                if (!empty($fallback)) {
                    $position = $fallback['Position'];
                    $candidates = $fallback['Candidates'];
                    $usedFallback = $fallback['Type'];
                }
            }

            if (empty($candidates)) {
                $action = $this->vacancyAction($policy);
                $items[] = [
                    'Position' => $position,
                    'Candidates' => [],
                    'Fallback' => $usedFallback,
                    'Action' => $action,
                    'Reason' => 'NO_CANDIDATE',
                ];
                continue;
            }

            $items[] = [
                'Position' => $position,
                'Candidates' => $candidates,
                'Fallback' => $usedFallback,
                'Action' => 'TASK',
                'Reason' => null,
            ];
        }

        return [
            'positions' => $items,
            'Action' => 'TASK',
            'Reason' => null,
        ];
    }

    public function positionsForStep($step, array $context): array
    {
        if (!empty($step->Position_ID)) {
            $position = $this->positionById((int) $step->Position_ID, $context);
            return $position ? [$position] : [];
        }

        if (!empty($step->Position_Code)) {
            $position = $this->positionByCode((string) $step->Position_Code, $context);
            return $position ? [$position] : [];
        }

        $level = (int) $step->Level_Number;
        if ($level < 1) {
            return [];
        }

        $query = DB::table('master_position')
            ->where('Level', $level)
            ->where(function ($sub) {
                $sub->whereNull('Status')->orWhere('Status', 'ACTIVE');
            });

        $this->applyEffectiveScope($query);
        $this->applyPositionScope($query, $context);

        $department = !empty($step->Department)
            ? (string) $step->Department
            : (string) ($context['Department'] ?? '');

        if ($department !== '') {
            $query->where('Department', $department);
        }

        return $query->orderBy('Level', 'ASC')
            ->orderBy('ID', 'ASC')
            ->get()
            ->all();
    }

    public function candidatesForPosition($policy, $position, array $context): array
    {
        $candidates = [];
        $today = Carbon::now()->toDateString();

        // 1. Sumber utama: employment (data kepegawaian) + users (akun login).
        //    Karyawan baru cukup diinput sekali di employment; approver langsung
        //    dikenali tanpa menambah baris position_assignment manual.
        foreach ($this->employmentHolders($position, $context) as $userId) {
            $this->addCandidate($candidates, (int) $userId, 'HOLDER', null);
        }

        // 2. Assignment PRIMARY/SECONDARY dipakai bila employment belum terisi
        //    (data lama atau masa transisi).
        if (empty($candidates)) {
            foreach ($this->assignmentHolders($position, $context, ['PRIMARY', 'SECONDARY']) as $userId) {
                $this->addCandidate($candidates, (int) $userId, 'HOLDER', null);
            }
        }

        // 3. Pelaksana tugas (ACTING) selalu ikut sebagai kandidat tambahan.
        foreach ($this->assignmentHolders($position, $context, ['ACTING']) as $userId) {
            $this->addCandidate($candidates, (int) $userId, 'ACTING', null);
        }

        // 4. Bagan organisasi (organization_structure) sebagai sumber cadangan.
        if (empty($candidates)) {
            foreach ($this->organizationHolders($position, $context) as $userId) {
                $this->addCandidate($candidates, (int) $userId, 'HOLDER', null);
            }
        }

        // 5. Legacy: sebagian akun lama hanya mengisi users.Position.
        if (empty($candidates)) {
            $legacy = DB::table('users')
                ->where('Status', 'ACTIVE')
                ->where(function ($query) use ($position) {
                    $query->where('Position', $position->Initial)
                        ->orWhere('Position', $position->Position);
                });

            if (!empty($context['Location'])) {
                $legacy->where('Location', $context['Location']);
            }
            if (!empty($context['Company_ID'])) {
                $legacy->where('Company_ID', $context['Company_ID']);
            }

            foreach ($legacy->pluck('id')->all() as $userId) {
                $this->addCandidate($candidates, (int) $userId, 'HOLDER', null);
            }
        }

        if ((int) ($policy->Allow_Delegation ?? 1) === 1 && !empty($candidates)) {
            foreach (array_keys($candidates) as $delegatorId) {
                $delegations = DB::table('approval_delegation')
                    ->where('Delegator_Id', $delegatorId)
                    ->where('Status', 'ACTIVE')
                    ->where('Location', $context['Location'])
                    ->where('Company_ID', $context['Company_ID'])
                    ->where(function ($query) use ($position) {
                        $query->whereNull('Position_ID')->orWhere('Position_ID', (int) $position->ID);
                    })
                    ->where(function ($query) use ($context) {
                        $query->whereNull('Request_Type')->orWhere('Request_Type', $context['Request_Type']);
                    })
                    ->where(function ($query) use ($today) {
                        $query->whereNull('Start_Date')->orWhere('Start_Date', '<=', $today);
                    })
                    ->where(function ($query) use ($today) {
                        $query->whereNull('End_Date')->orWhere('End_Date', '>=', $today);
                    })
                    ->get();

                foreach ($delegations as $delegation) {
                    $delegateActive = DB::table('users')
                        ->where('id', (int) $delegation->Delegate_Id)
                        ->where('Status', 'ACTIVE')
                        ->exists();

                    if ($delegateActive) {
                        $this->addCandidate(
                            $candidates,
                            (int) $delegation->Delegate_Id,
                            'DELEGATE',
                            (int) $delegation->ID
                        );
                    }
                }
            }
        }

        return array_values($candidates);
    }

    /**
     * Pegawai aktif di employment yang menduduki master_position ini.
     * Relasi: employment.Position -> master_position.Initial (kode posisi),
     *         employment.NIK      -> users.Emp_ID (akun login).
     */
    protected function employmentHolders($position, array $context): array
    {
        if (empty($position->Initial) && empty($position->Position)) {
            return [];
        }

        $today = Carbon::now()->toDateString();

        $query = DB::table('employment as e')
            ->join('users as u', function ($join) {
                $join->on('u.Emp_ID', '=', 'e.NIK')
                    ->orOn('u.username', '=', 'e.NIK');
            })
            ->where('u.Status', 'ACTIVE')
            ->where(function ($sub) {
                $sub->whereNull('e.Status')->orWhere('e.Status', 'ACTIVE');
            })
            ->where(function ($sub) use ($today) {
                $sub->whereNull('e.End_Date')
                    ->orWhere('e.End_Date', '')
                    ->orWhere('e.End_Date', '0000-00-00')
                    ->orWhere('e.End_Date', '>=', $today);
            })
            ->where(function ($sub) use ($position) {
                $this->applyCodeMatch($sub, 'e.Position', $position);
            });

        if (!empty($position->Department)) {
            $query->where(function ($sub) use ($position) {
                $sub->whereNull('e.Department')
                    ->orWhere('e.Department', '')
                    ->orWhere('e.Department', $position->Department);
            });
        }

        if (!empty($context['Location'])) {
            $query->where(function ($sub) use ($context) {
                $sub->where('e.Location', $context['Location'])
                    ->orWhere('u.Location', $context['Location']);
            });
        }
        if (!empty($context['Company_ID'])) {
            $query->where(function ($sub) use ($context) {
                $sub->where('u.Company_ID', $context['Company_ID'])
                    ->orWhereNull('u.Company_ID')
                    ->orWhere('u.Company_ID', '');
            });
        }

        return $query->distinct()->pluck('u.id')->all();
    }

    /**
     * Assignment aktif pada position_assignment (override manual / acting).
     */
    protected function assignmentHolders($position, array $context, array $types): array
    {
        $today = Carbon::now()->toDateString();

        $query = DB::table('position_assignment as pa')
            ->join('users as u', 'pa.User_Id', '=', 'u.id')
            ->where('pa.Position_ID', (int) $position->ID)
            ->where('pa.Status', 'ACTIVE')
            ->where('u.Status', 'ACTIVE')
            ->where(function ($sub) use ($types) {
                $sub->whereIn('pa.Assignment_Type', $types);
                if (in_array('PRIMARY', $types, true)) {
                    $sub->orWhereNull('pa.Assignment_Type')
                        ->orWhere('pa.Assignment_Type', '');
                }
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('pa.Start_Date')->orWhere('pa.Start_Date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('pa.End_Date')->orWhere('pa.End_Date', '>=', $today);
            });

        if (!empty($context['Location'])) {
            $query->where('pa.Location', $context['Location']);
        }
        if (!empty($context['Company_ID'])) {
            $query->where('pa.Company_ID', $context['Company_ID']);
        }

        return $query->distinct()->pluck('u.id')->all();
    }

    /**
     * Pemegang posisi menurut bagan organisasi (organization_structure).
     * NIK pada bagan adalah kunci ke users.Emp_ID.
     */
    protected function organizationHolders($position, array $context): array
    {
        $query = DB::table('organization_structure as o')
            ->join('users as u', function ($join) {
                $join->on('u.Emp_ID', '=', 'o.NIK')
                    ->orOn('u.username', '=', 'o.NIK');
            })
            ->where('u.Status', 'ACTIVE')
            ->whereNotNull('o.NIK')
            ->where('o.NIK', '<>', '')
            ->where(function ($sub) use ($position) {
                $this->applyPositionNodeMatch($sub, $position);
            });

        if (!empty($position->Department)) {
            $query->where(function ($sub) use ($position) {
                $sub->whereNull('o.Department')
                    ->orWhere('o.Department', '')
                    ->orWhere('o.Department', $position->Department);
            });
        }
        if (!empty($context['Location'])) {
            $query->where('u.Location', $context['Location']);
        }
        if (!empty($context['Company_ID'])) {
            $query->where('u.Company_ID', $context['Company_ID']);
        }

        return $query->distinct()->pluck('u.id')->all();
    }

    /**
     * Cocokkan kode posisi (Initial) atau nama posisi pada sebuah kolom.
     */
    protected function applyCodeMatch($sub, $column, $position): void
    {
        $hasCondition = false;

        if (!empty($position->Initial)) {
            $sub->where($column, $position->Initial);
            $hasCondition = true;
        }

        if (!empty($position->Position)) {
            if ($hasCondition) {
                $sub->orWhere($column, $position->Position);
            } else {
                $sub->where($column, $position->Position);
            }
        }
    }

    /**
     * Cocokkan node bagan lewat Position_ID, kode Initial, atau nama posisi.
     */
    protected function applyPositionNodeMatch($sub, $position): void
    {
        $hasCondition = false;

        if (!empty($position->ID)) {
            $sub->where('o.Position_ID', (int) $position->ID);
            $hasCondition = true;
        }

        if (!empty($position->Initial)) {
            if ($hasCondition) {
                $sub->orWhere('o.Position', $position->Initial);
            } else {
                $sub->where('o.Position', $position->Initial);
                $hasCondition = true;
            }
        }

        if (!empty($position->Position)) {
            if ($hasCondition) {
                $sub->orWhere('o.Position', $position->Position);
            } else {
                $sub->where('o.Position', $position->Position);
            }
        }
    }

    public function fallbackForPosition($policy, $position, array $context, int $depth = 0): ?array
    {
        if ($depth >= 4) {
            return null;
        }

        $parent = null;
        if (!empty($position->Parent_Position_ID)) {
            $parent = $this->positionById((int) $position->Parent_Position_ID, $context);
        }

        // Bagan organisasi adalah sumber hierarki kedua bila
        // master_position.Parent_Position_ID belum diisi.
        if (!$parent) {
            $parent = $this->organizationParent($position, $context);
        }

        if (!$parent) {
            $higher = $this->higherLevelPositions((int) $position->Level, $context, (int) $position->ID);
            $parent = $higher[0] ?? null;
        }

        if (!$parent) {
            return null;
        }

        $candidates = $this->candidatesForPosition($policy, $parent, $context);
        if (!empty($candidates)) {
            return [
                'Position' => $parent,
                'Candidates' => $candidates,
                'Type' => 'PARENT',
            ];
        }

        $nested = $this->fallbackForPosition($policy, $parent, $context, $depth + 1);
        if ($nested) {
            return $nested;
        }

        foreach ($this->higherLevelPositions((int) $parent->Level, $context, (int) $parent->ID) as $higher) {
            $candidates = $this->candidatesForPosition($policy, $higher, $context);
            if (!empty($candidates)) {
                return [
                    'Position' => $higher,
                    'Candidates' => $candidates,
                    'Type' => 'HIGHER_LEVEL',
                ];
            }
        }

        return null;
    }

    protected function organizationParent($position, array $context)
    {
        if (empty($position->ID) && empty($position->Initial) && empty($position->Position)) {
            return null;
        }

        try {
            $node = DB::table('organization_structure')
                ->where(function ($sub) use ($position) {
                    $hasCondition = false;

                    if (!empty($position->ID)) {
                        $sub->where('Position_ID', (int) $position->ID);
                        $hasCondition = true;
                    }
                    if (!empty($position->Initial)) {
                        if ($hasCondition) {
                            $sub->orWhere('Position', $position->Initial);
                        } else {
                            $sub->where('Position', $position->Initial);
                            $hasCondition = true;
                        }
                    }
                    if (!empty($position->Position)) {
                        if ($hasCondition) {
                            $sub->orWhere('Position', $position->Position);
                        } else {
                            $sub->where('Position', $position->Position);
                        }
                    }
                })
                ->whereNotNull('Reports_To_Position_ID')
                ->orderBy('ID')
                ->first();

            if (!$node) {
                return null;
            }

            $parentNode = DB::table('organization_structure')
                ->where('ID', (int) $node->Reports_To_Position_ID)
                ->first();

            if (!$parentNode) {
                return null;
            }

            if (!empty($parentNode->Position_ID)) {
                return $this->positionById((int) $parentNode->Position_ID, $context);
            }

            if (empty($parentNode->Position)) {
                return null;
            }

            $parent = DB::table('master_position')
                ->where(function ($sub) use ($parentNode) {
                    $sub->where('Initial', $parentNode->Position)
                        ->orWhere('Position', $parentNode->Position);
                })
                ->where(function ($sub) {
                    $sub->whereNull('Status')->orWhere('Status', 'ACTIVE');
                });

            $this->applyPositionScope($parent, $context);

            if (!empty($parentNode->Department)) {
                $parent->where(function ($sub) use ($parentNode) {
                    $sub->whereNull('Department')
                        ->orWhere('Department', '')
                        ->orWhere('Department', $parentNode->Department);
                });
            }

            return $parent->orderBy('ID')->first();
        } catch (\Throwable $e) {
            // Bagan belum dimigrasi / tabel belum siap: fallback level tetap jalan.
            return null;
        }
    }

    public function higherLevelPositions(int $level, array $context, int $excludeId = null): array
    {
        if ($level <= 1) {
            return [];
        }

        $run = function ($withDepartment) use ($level, $context, $excludeId) {
            $query = DB::table('master_position')
                ->where('Level', '<', $level)
                ->where(function ($sub) {
                    $sub->whereNull('Status')->orWhere('Status', 'ACTIVE');
                });

            $this->applyEffectiveScope($query);
            $this->applyPositionScope($query, $context);

            if ($withDepartment && !empty($context['Department'])) {
                $query->where('Department', $context['Department']);
            }
            if ($excludeId) {
                $query->where('ID', '!=', $excludeId);
            }

            return $query->orderBy('Level', 'DESC')
                ->orderBy('ID', 'ASC')
                ->get()
                ->all();
        };

        $positions = $run(true);
        if (empty($positions) && !empty($context['Department'])) {
            $positions = $run(false);
        }

        return $positions;
    }

    public function positionById(int $id, array $context = null)
    {
        $query = DB::table('master_position')
            ->where('ID', $id)
            ->where(function ($sub) {
                $sub->whereNull('Status')->orWhere('Status', 'ACTIVE');
            });

        $this->applyEffectiveScope($query);
        if ($context !== null) {
            $this->applyPositionScope($query, $context);
        }

        return $query->first();
    }

    public function positionByCode(string $code, array $context)
    {
        $query = DB::table('master_position')
            ->where('Initial', $code)
            ->where(function ($sub) {
                $sub->whereNull('Status')->orWhere('Status', 'ACTIVE');
            });

        $this->applyPositionScope($query, $context);

        return $query->first();
    }

    private function addCandidate(array &$candidates, int $userId, string $type, $delegationId): void
    {
        $priority = ['HOLDER' => 30, 'ACTING' => 20, 'DELEGATE' => 10];

        if (!isset($candidates[$userId]) || $priority[$type] > $priority[$candidates[$userId]['Type']]) {
            $candidates[$userId] = [
                'User_Id' => $userId,
                'Type' => $type,
                'Delegation_ID' => $delegationId,
            ];
        }
    }

    private function vacancyAction($policy): string
    {
        $onVacancy = strtoupper((string) ($policy->On_Vacancy ?? 'FALLBACK'));

        if ($onVacancy === 'HOLD') {
            return 'HOLD';
        }
        if ($onVacancy === 'BLOCK') {
            return 'BLOCK';
        }

        return $this->noHigherLevelAction($policy);
    }

    private function noPositionAction($policy): string
    {
        $onVacancy = strtoupper((string) ($policy->On_Vacancy ?? 'FALLBACK'));
        if ($onVacancy === 'HOLD') {
            return 'HOLD';
        }
        if ($onVacancy === 'BLOCK') {
            return 'BLOCK';
        }

        return $this->noHigherLevelAction($policy);
    }

    private function noHigherLevelAction($policy): string
    {
        $action = strtoupper((string) ($policy->On_No_Higher_Level ?? 'AUTO_APPROVE'));

        if (!in_array($action, ['AUTO_APPROVE', 'HOLD', 'BLOCK'], true)) {
            return 'AUTO_APPROVE';
        }

        return $action;
    }

    private function applyEffectiveScope($query): void
    {
        $today = Carbon::now()->toDateString();

        $query->where(function ($sub) use ($today) {
            $sub->whereNull('Effective_From')->orWhere('Effective_From', '<=', $today);
        })->where(function ($sub) use ($today) {
            $sub->whereNull('Effective_To')->orWhere('Effective_To', '>=', $today);
        });
    }

    private function applyPositionScope($query, array $context): void
    {
        // Approval data is isolated by Location and Company. Legacy positions
        // with NULL scope are backfilled before they can participate.
        if (!empty($context['Location'])) {
            $query->where('Location', $context['Location']);
        }
        if (!empty($context['Company_ID'])) {
            $query->where('Company_ID', $context['Company_ID']);
        }
    }
}