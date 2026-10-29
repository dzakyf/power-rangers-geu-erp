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
        $today = Carbon::now()->toDateString();
        $candidates = [];

        $assignments = DB::table('position_assignment as pa')
            ->join('users as u', 'pa.User_Id', '=', 'u.id')
            ->where('pa.Position_ID', (int) $position->ID)
            ->where('pa.Status', 'ACTIVE')
            ->where('u.Status', 'ACTIVE')
            ->where(function ($query) use ($today) {
                $query->whereNull('pa.Start_Date')->orWhere('pa.Start_Date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('pa.End_Date')->orWhere('pa.End_Date', '>=', $today);
            });

        if (!empty($context['Location'])) {
            $assignments->where('pa.Location', $context['Location']);
        }
        if (!empty($context['Company_ID'])) {
            $assignments->where('pa.Company_ID', $context['Company_ID']);
        }

        foreach ($assignments->select('u.id', 'pa.Assignment_Type')->get() as $row) {
            $type = strtoupper((string) $row->Assignment_Type) === 'ACTING' ? 'ACTING' : 'HOLDER';
            $this->addCandidate($candidates, (int) $row->id, $type, null);
        }

        // Legacy fallback: many existing users only have users.Position.
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
        if (empty($position->ID)) {
            return null;
        }

        try {
            $row = DB::table('organization_structure as o')
                ->join('organization_structure as p', 'p.ID', '=', 'o.Reports_To_Position_ID')
                ->where('o.Position_ID', (int) $position->ID)
                ->whereNotNull('p.Position_ID')
                ->select('p.Position_ID')
                ->first();
        } catch (\Throwable $e) {
            // Bagan belum dimigrasi / tabel belum siap: fallback level tetap jalan.
            return null;
        }

        if (!$row) {
            return null;
        }

        return $this->positionById((int) $row->Position_ID, $context);
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