<?php

namespace App\Services\HCGA;

use App\PerformanceDeptPic;
use App\PerformanceGoal;
use App\PerformanceGoalItem;
use App\PerformanceGoalProgress;
use App\PerformancePeriod;
use App\Services\Approval\ApprovalWorkflowService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceKpiService
{
    const REQUEST_TYPE = 'PERFORMANCE_KPI';

    protected $workflowService;

    public function __construct(ApprovalWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Generate unique nomor sheet goal
     */
    public function generateGoalNo(string $periodCode = 'PM'): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "GOAL/{$year}/{$month}/";
        $last = DB::table('performance_goals')
            ->where('goal_no', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('goal_no');

        if ($last) {
            $seq = (int) substr($last, strrpos($last, '/') + 1);
            $nextSeq = str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }

    /**
     * Simpan / Buat Goal Sheet baru dengan validasi bobot KPI
     */
    public function createGoal(array $headerData, array $items, int $creatorId): PerformanceGoal
    {
        return DB::transaction(function () use ($headerData, $items, $creatorId) {
            $period = PerformancePeriod::findOrFail($headerData['period_id']);

            if (!empty($headerData['position_id'])) {
                $pos = DB::table('master_position')->where('ID', (int) $headerData['position_id'])->first();
                if (!$pos) {
                    throw new \InvalidArgumentException('Posisi jabatan tidak ditemukan di master_position.');
                }

                $exists = PerformanceGoal::where('period_id', $period->id)
                    ->where('position_id', $pos->ID)
                    ->where('scope_type', 'POSITION')
                    ->where('status', '!=', 'CANCELLED')
                    ->exists();

                if ($exists) {
                    throw new \InvalidArgumentException("Template KPI untuk posisi {$pos->Position} ({$pos->Department}) pada periode {$period->period_code} sudah ada.");
                }

                $goal = PerformanceGoal::create([
                    'goal_no'          => $this->generateGoalNo($period->period_code),
                    'scope_type'       => 'POSITION',
                    'period_id'        => $period->id,
                    'user_id'          => null,
                    'emp_id'           => null,
                    'department'       => $pos->Department,
                    'position_id'      => $pos->ID,
                    'position'         => $pos->Initial ?: $pos->Position,
                    'goal_type'        => $headerData['goal_type'] ?? 'HYBRID',
                    'level'            => $headerData['level'] ?? 'TEAM',
                    'total_kpi_weight' => 0,
                    'status'           => 'DRAFT',
                    'notes'            => $headerData['notes'] ?? null,
                    'created_by'       => $creatorId,
                    'updated_by'       => $creatorId,
                ]);
            } else {
                $user = User::findOrFail($headerData['user_id']);
                $goal = PerformanceGoal::create([
                    'goal_no'          => $this->generateGoalNo($period->period_code),
                    'scope_type'       => 'INDIVIDUAL',
                    'period_id'        => $period->id,
                    'user_id'          => $user->id,
                    'emp_id'           => $user->Emp_ID,
                    'department'       => $user->Department,
                    'position'         => $user->Position,
                    'goal_type'        => $headerData['goal_type'] ?? 'HYBRID',
                    'level'            => $headerData['level'] ?? 'INDIVIDUAL',
                    'total_kpi_weight' => 0,
                    'status'           => 'DRAFT',
                    'notes'            => $headerData['notes'] ?? null,
                    'created_by'       => $creatorId,
                    'updated_by'       => $creatorId,
                ]);
            }

            $this->syncGoalItems($goal, $items);
            return $goal->fresh(['items', 'period', 'user']);
        });
    }

    /**
     * Update Goal Sheet
     */
    public function updateGoal(PerformanceGoal $goal, array $headerData, array $items, int $updaterId): PerformanceGoal
    {
        if (!in_array($goal->status, ['DRAFT', 'REJECTED'])) {
            throw new \InvalidArgumentException("Target KPI dengan status [{$goal->status}] sudah terkunci untuk tahun berjalan dan tidak dapat diubah.");
        }

        return DB::transaction(function () use ($goal, $headerData, $items, $updaterId) {
            $goal->update([
                'goal_type'  => $headerData['goal_type'] ?? $goal->goal_type,
                'level'      => $headerData['level'] ?? $goal->level,
                'notes'      => $headerData['notes'] ?? $goal->notes,
                'updated_by' => $updaterId,
            ]);

            $this->syncGoalItems($goal, $items);
            return $goal->fresh(['items', 'period', 'user']);
        });
    }

    /**
     * Sinkronisasi item target KPI / OKR
     */
    protected function syncGoalItems(PerformanceGoal $goal, array $items): void
    {
        // Hapus item lama jika update
        PerformanceGoalItem::where('goal_id', $goal->id)->delete();

        $totalKpiWeight = 0;
        $sortOrder = 1;

        // Map temporer untuk parent objective jika item berupa OKR key result
        $objectiveMap = [];

        foreach ($items as $item) {
            $type = strtoupper($item['item_type'] ?? 'KPI');
            $weight = ($type === 'KPI') ? (float) ($item['weight_percent'] ?? 0) : 0;
            $targetVal = (float) ($item['target_value'] ?? 100);

            $parentId = null;
            if ($type === 'KEY_RESULT' && !empty($item['objective_key']) && isset($objectiveMap[$item['objective_key']])) {
                $parentId = $objectiveMap[$item['objective_key']];
            }

            $createdItem = PerformanceGoalItem::create([
                'goal_id'          => $goal->id,
                'parent_item_id'   => $parentId,
                'item_type'        => $type,
                'title'            => $item['title'] ?? '',
                'description'      => $item['description'] ?? null,
                'kpi_unit'         => $item['kpi_unit'] ?? '%',
                'target_value'     => $targetVal,
                'actual_value'     => 0,
                'weight_percent'   => $weight,
                'progress_percent' => 0,
                'score'            => 0,
                'scoring_type'     => $item['scoring_type'] ?? 'HIGHER_BETTER',
                'due_date'         => !empty($item['due_date']) ? Carbon::parse($item['due_date']) : null,
                'sort_order'       => $sortOrder++,
            ]);

            if ($type === 'OBJECTIVE' && !empty($item['temp_key'])) {
                $objectiveMap[$item['temp_key']] = $createdItem->id;
            }

            $totalKpiWeight += $weight;
        }

        $goal->update(['total_kpi_weight' => round($totalKpiWeight, 2)]);
    }

    /**
     * Submit penetapan target ke Dynamic Approval Workflow Engine
     */
    public function submitToApproval(PerformanceGoal $goal, User $requester): PerformanceGoal
    {
        if (!in_array($goal->status, ['DRAFT', 'REJECTED'])) {
            throw new \InvalidArgumentException("Target dengan status [{$goal->status}] tidak dapat disubmit ke approval.");
        }

        // Validasi bobot jika tipe hybrid/kpi dan terdapat item KPI
        $kpiItemsCount = $goal->items()->where('item_type', 'KPI')->count();
        if ($kpiItemsCount > 0 && abs($goal->total_kpi_weight - 100.00) > 0.01) {
            throw new \InvalidArgumentException("Total bobot KPI harus tepat 100% (saat ini: {$goal->total_kpi_weight}%).");
        }

        return DB::transaction(function () use ($goal, $requester) {
            $owner = $goal->user ?: $requester;
            $targetDept = $goal->department ?: ($owner->Department ?? 'HCGA');
            $context = [
                'request_type' => self::REQUEST_TYPE,
                'location'     => $owner->Location ?? 'GEU-HO',
                'company_id'   => $owner->Company_ID ?? 'GEU',
                'department'   => $targetDept,
                'Department'   => $targetDept,
                'amount'       => 0,
            ];

            $targetLabel = $goal->scope_type === 'POSITION'
                ? "Posisi {$goal->position} ({$targetDept})"
                : ($owner->Name ?? '-');
            $desc = "Persetujuan Target KPI: {$goal->goal_no} ({$targetLabel})";
            $approvalReq = $this->workflowService->submit([
                'Request_No'  => $goal->goal_no,
                'Description' => $desc,
                'Map_To'      => $goal->id,
            ], $context, (int) $requester->id);

            $reqId = $approvalReq ? ($approvalReq->ID ?? $approvalReq->id ?? null) : null;
            if (!$reqId) {
                throw new \RuntimeException('Gagal membuat permintaan approval pada Approval Workflow Engine.');
            }

            $reqStatus = strtoupper((string) ($approvalReq->Status ?? ''));
            $goalStatus = ($reqStatus === 'APPROVED') ? 'APPROVED' : 'WAITING_APPROVAL';

            $goal->update([
                'status'              => $goalStatus,
                'approval_request_id' => $reqId,
                'rejection_reason'    => null,
                'updated_by'          => $requester->id,
            ]);

            return $goal->fresh(['items', 'period', 'user']);
        });
    }

    /**
     * Cek apakah user memiliki akses penuh HCGA / Administrator untuk modul Performance.
     */
    public function isHcgaOrAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('ADM') || $user->hasRole('Super Admin'))) {
            return true;
        }

        if (($user->Position ?? '') === 'Administrator') {
            return true;
        }

        if (strtoupper((string) ($user->Department ?? '')) === 'HCGA') {
            return true;
        }

        if (function_exists('can_menu') && can_menu('SI01039', 'approve')) {
            return true;
        }

        return false;
    }

    /**
     * Ambil daftar user_id bawahan langsung & tidak langsung berdasarkan organization_structure.
     */
    public function getSubordinateUserIds(User $manager): array
    {
        if (empty($manager->Emp_ID)) {
            return [];
        }

        $managerNodeIds = DB::table('organization_structure')
            ->where('NIK', $manager->Emp_ID)
            ->pluck('ID')
            ->all();

        if (empty($managerNodeIds)) {
            return [];
        }

        // Traverse hierarki ke bawah (maksimal 6 level untuk menghindari cycle)
        $allSubNiks = [];
        $frontier = $managerNodeIds;
        $visited = $managerNodeIds;

        for ($depth = 0; $depth < 6 && !empty($frontier); $depth++) {
            $children = DB::table('organization_structure')
                ->whereIn('Reports_To_Position_ID', $frontier)
                ->select('ID', 'NIK')
                ->get();

            $nextFrontier = [];
            foreach ($children as $child) {
                if (!empty($child->NIK)) {
                    $allSubNiks[] = $child->NIK;
                }
                if (!in_array($child->ID, $visited, true)) {
                    $visited[] = $child->ID;
                    $nextFrontier[] = $child->ID;
                }
            }
            $frontier = $nextFrontier;
        }

        $allSubNiks = array_values(array_unique(array_filter($allSubNiks)));
        if (empty($allSubNiks)) {
            return [];
        }

        return User::whereIn('Emp_ID', $allSubNiks)
            ->where('Status', 'ACTIVE')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
    }

    /**
     * Daftar user_id yang boleh dilihat/dikelola oleh user saat ini.
     * Return null berarti akses penuh (HCGA/Admin).
     */
    public function getAccessibleEmployeeIds(?User $user): ?array
    {
        if (!$user) {
            return [];
        }

        if ($this->isHcgaOrAdmin($user)) {
            return null;
        }

        $subIds = $this->getSubordinateUserIds($user);
        $ids = array_values(array_unique(array_merge([(int) $user->id], $subIds)));

        return $ids;
    }

    /**
     * Daftar departemen di mana user ditunjuk sebagai PIC Departemen.
     */
    public function getPicDepartments(?User $user): array
    {
        if (!$user) {
            return [];
        }

        return PerformanceDeptPic::where('user_id', $user->id)
            ->pluck('department')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Cek apakah user adalah PIC Departemen (opsional: untuk departemen tertentu).
     */
    public function isDeptPic(?User $user, ?string $department = null): bool
    {
        $depts = $this->getPicDepartments($user);
        if (empty($depts)) {
            return false;
        }
        if ($department === null) {
            return true;
        }
        foreach ($depts as $d) {
            if (strcasecmp(trim($d), trim($department)) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Cek apakah user boleh membuat / mengelola KPI Planning (hanya HCGA/Admin atau PIC Departemen).
     * Karyawan biasa non-PIC bersifat strictly read-only.
     */
    public function canUserCreateGoal(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->isHcgaOrAdmin($user) || $this->isDeptPic($user);
    }

    /**
     * Cek apakah user boleh mengedit / mengajukan KPI tertentu.
     */
    public function canUserManageGoal(?User $user, PerformanceGoal $goal): bool
    {
        if (!$user) {
            return false;
        }
        if ($this->isHcgaOrAdmin($user)) {
            return true;
        }
        return $this->isDeptPic($user, $goal->department);
    }

    /**
     * Cek apakah user boleh melihat KPI tertentu (HCGA, PIC Dept, pemegang posisi saat ini, atau punya histori review).
     */
    public function canUserViewGoal(?User $user, PerformanceGoal $goal): bool
    {
        if (!$user) {
            return false;
        }
        if ($this->canUserManageGoal($user, $goal)) {
            return true;
        }
        if ($goal->user_id && (int) $goal->user_id === (int) $user->id) {
            return true;
        }
        if ($goal->scope_type === 'POSITION' && $goal->status === 'APPROVED') {
            $matchesPosition = strcasecmp((string) $user->Department, (string) $goal->department) === 0
                && strcasecmp((string) $user->Position, (string) $goal->position) === 0;
            if ($matchesPosition) {
                return true;
            }
        }

        return DB::table('performance_reviews')
            ->where('user_id', $user->id)
            ->where('goal_id', $goal->id)
            ->exists();
    }

    /**
     * Catat Check-in / Pembaruan Progres Target
     */
    public function recordProgress(PerformanceGoalItem $item, array $data, int $userId): PerformanceGoalProgress
    {
        $goal = $item->goal;
        if ($goal && !in_array($goal->status, ['APPROVED', 'EVALUATION'])) {
            throw new \InvalidArgumentException("Realisasi hanya dapat dicatat untuk KPI yang sudah berstatus APPROVED (status saat ini: {$goal->status}).");
        }

        return DB::transaction(function () use ($item, $data, $userId) {
            $newValue = (float) $data['new_value'];
            $previousValue = (float) $item->actual_value;

            // Hitung persentase pencapaian
            $target = (float) $item->target_value;
            $progressPercent = 0;
            if ($target > 0) {
                if ($item->scoring_type === 'LOWER_BETTER') {
                    $progressPercent = $newValue <= $target ? 100 : max(0, round(($target / max($newValue, 0.01)) * 100, 2));
                } else {
                    $progressPercent = round(($newValue / $target) * 100, 2);
                }
            }

            $progress = PerformanceGoalProgress::create([
                'goal_item_id'     => $item->id,
                'check_in_date'    => $data['check_in_date'] ?? date('Y-m-d'),
                'previous_value'   => $previousValue,
                'new_value'        => $newValue,
                'progress_percent' => $progressPercent,
                'comment'          => $data['comment'] ?? null,
                'attachment_path'  => $data['attachment_path'] ?? null,
                'created_by'       => $userId,
            ]);

            // Update aktual & progres pada item
            $item->update([
                'actual_value'     => $newValue,
                'progress_percent' => $progressPercent,
                'score'            => round(($progressPercent * ($item->weight_percent > 0 ? $item->weight_percent : 100)) / 100, 2),
            ]);

            // Recalculate keseluruhan goal
            $this->recalculateGoalOverall($item->goal);

            return $progress;
        });
    }

    /**
     * Hitung ulang agregasi progress & skor keseluruhan goal sheet
     */
    public function recalculateGoalOverall(PerformanceGoal $goal): void
    {
        $items = $goal->items()->get();
        if ($items->isEmpty()) {
            return;
        }

        $totalWeightedProgress = 0;
        $totalWeight = 0;
        $sumSimpleProgress = 0;

        foreach ($items as $it) {
            if ($it->item_type === 'KPI' && $it->weight_percent > 0) {
                $totalWeightedProgress += ($it->progress_percent * $it->weight_percent);
                $totalWeight += $it->weight_percent;
            } else {
                $sumSimpleProgress += $it->progress_percent;
            }
        }

        $overallProgress = 0;
        if ($totalWeight > 0) {
            $overallProgress = round($totalWeightedProgress / $totalWeight, 2);
        } elseif ($items->count() > 0) {
            $overallProgress = round($sumSimpleProgress / $items->count(), 2);
        }

        $goal->update([
            'overall_progress_percent' => $overallProgress,
            'final_score'              => $overallProgress,
        ]);
    }
}

