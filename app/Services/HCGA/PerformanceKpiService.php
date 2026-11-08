<?php

namespace App\Services\HCGA;

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
            $user = User::findOrFail($headerData['user_id']);
            $period = PerformancePeriod::findOrFail($headerData['period_id']);

            $goal = PerformanceGoal::create([
                'goal_no'           => $this->generateGoalNo($period->period_code),
                'period_id'         => $period->id,
                'user_id'           => $user->id,
                'emp_id'            => $user->Emp_ID,
                'department'        => $user->Department,
                'position'          => $user->Position,
                'goal_type'         => $headerData['goal_type'] ?? 'HYBRID',
                'level'             => $headerData['level'] ?? 'INDIVIDUAL',
                'total_kpi_weight'  => 0,
                'status'            => 'DRAFT',
                'notes'             => $headerData['notes'] ?? null,
                'created_by'        => $creatorId,
                'updated_by'        => $creatorId,
            ]);

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
            throw new \InvalidArgumentException("Target dengan status [{$goal->status}] tidak dapat diedit.");
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
            $context = [
                'request_type' => self::REQUEST_TYPE,
                'location'     => $requester->Location ?? 'GEU-HO',
                'company_id'   => $requester->Company_ID ?? 'GEU',
                'department'   => $requester->Department ?? 'HCGA',
                'amount'       => 0,
            ];

            $desc = "Persetujuan Target KPI & OKR: {$goal->goal_no} ({$requester->Name})";
            $approvalReq = null;

            try {
                $approvalReq = $this->workflowService->submit([
                    'Request_No'  => $goal->goal_no,
                    'Description' => $desc,
                ], $context, (int) $requester->id);
            } catch (\Throwable $e) {
                Log::warning('ApprovalWorkflow engine submit fallback: ' . $e->getMessage());
            }

            $goal->update([
                'status'              => 'WAITING_APPROVAL',
                'approval_request_id' => $approvalReq ? $approvalReq->id : null,
                'updated_by'          => $requester->id,
            ]);

            return $goal->fresh(['items', 'period', 'user']);
        });
    }

    /**
     * Catat Check-in / Pembaruan Progres Target
     */
    public function recordProgress(PerformanceGoalItem $item, array $data, int $userId): PerformanceGoalProgress
    {
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

