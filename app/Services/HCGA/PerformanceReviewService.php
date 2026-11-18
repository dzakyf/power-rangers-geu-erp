<?php

namespace App\Services\HCGA;

use App\PerformanceCompetency;
use App\PerformanceGoal;
use App\PerformancePipPlan;
use App\PerformanceReview;
use App\PerformanceReviewCompetencyScore;
use App\PerformanceReviewCycle;
use App\PerformanceReviewFeedback;
use App\PerformanceReviewKpiItem;
use App\PerformanceSetting;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerformanceReviewService
{
    protected $pipService;

    public function __construct(PerformancePipService $pipService = null)
    {
        $this->pipService = $pipService ?: app(PerformancePipService::class);
    }

    /**
     * Generate unique nomor review
     */
    public function generateReviewNo(): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "REV/{$year}/{$month}/";
        $last = DB::table('performance_reviews')
            ->where('review_no', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('review_no');

        if ($last) {
            $seq = (int) substr($last, strrpos($last, '/') + 1);
            $nextSeq = str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }

    /**
     * Cari Goal KPI aktif untuk karyawan pada periode tertentu:
     * prioritas template KPI Posisi saat ini (mendukung mutasi tengah tahun), fallback ke KPI Individu.
     */
    public function resolveActiveGoalForEmployee(User $employee, int $periodId): ?PerformanceGoal
    {
        if (!empty($employee->Position)) {
            $posQuery = DB::table('master_position')
                ->where(function ($q) use ($employee) {
                    $q->where('Initial', $employee->Position)
                      ->orWhere('Position', $employee->Position);
                });

            if (!empty($employee->Department)) {
                $posQuery->where('Department', $employee->Department);
            }

            $pos = $posQuery->first();
            if ($pos) {
                $posGoal = PerformanceGoal::where('period_id', $periodId)
                    ->where('scope_type', 'POSITION')
                    ->where('position_id', $pos->ID)
                    ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
                    ->orderBy('id', 'desc')
                    ->first();

                if ($posGoal) {
                    return $posGoal;
                }
            }
        }

        if (!empty($employee->Department) && !empty($employee->Position)) {
            $byText = PerformanceGoal::where('period_id', $periodId)
                ->where('scope_type', 'POSITION')
                ->where('department', $employee->Department)
                ->where('position', $employee->Position)
                ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
                ->orderBy('id', 'desc')
                ->first();

            if ($byText) {
                return $byText;
            }
        }

        return PerformanceGoal::where('period_id', $periodId)
            ->where('user_id', $employee->id)
            ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Inisiasi Dokumen Review Karyawan dari Siklus & Goal yang sudah disetujui (Approved)
     */
    public function initiateReviewForEmployee(PerformanceReviewCycle $cycle, User $employee, ?User $manager = null): PerformanceReview
    {
        return DB::transaction(function () use ($cycle, $employee, $manager) {
            $goal = $this->resolveActiveGoalForEmployee($employee, (int) $cycle->period_id);

            $kpiScore = $goal ? (float) $goal->overall_progress_percent : 0;

            $review = PerformanceReview::create([
                'review_no'        => $this->generateReviewNo(),
                'cycle_id'         => $cycle->id,
                'user_id'          => $employee->id,
                'goal_id'          => $goal ? $goal->id : null,
                'manager_id'       => $manager ? $manager->id : null,
                'kpi_score'        => $kpiScore,
                'competency_score' => 0,
                'status'           => $cycle->enable_self_review ? 'SELF_REVIEW' : 'MANAGER_REVIEW',
            ]);

            if ($cycle->enable_self_review) {
                PerformanceReviewFeedback::create([
                    'review_id'     => $review->id,
                    'reviewer_id'   => $employee->id,
                    'reviewer_type' => 'SELF',
                    'score'         => 0,
                ]);
            }

            if ($manager && $cycle->enable_manager_review) {
                PerformanceReviewFeedback::create([
                    'review_id'     => $review->id,
                    'reviewer_id'   => $manager->id,
                    'reviewer_type' => 'MANAGER',
                    'score'         => 0,
                ]);
            }

            if ($goal) {
                $this->snapshotGoalItemsForReview($review, $goal);
            }

            return $review;
        });
    }

    /**
     * Salin item KPI/OKR dari Goal Sheet ke snapshot per dokumen Review 6 bulanan.
     */
    public function snapshotGoalItemsForReview(PerformanceReview $review, PerformanceGoal $goal): void
    {
        $items = $goal->items()->orderBy('sort_order')->get();
        foreach ($items as $it) {
            PerformanceReviewKpiItem::firstOrCreate(
                [
                    'review_id'    => $review->id,
                    'goal_item_id' => $it->id,
                ],
                [
                    'item_type'        => $it->item_type,
                    'title'            => $it->title,
                    'kpi_unit'         => $it->kpi_unit,
                    'target_value'     => $it->target_value,
                    'actual_value'     => 0,
                    'weight_percent'   => $it->weight_percent,
                    'progress_percent' => 0,
                    'score'            => 0,
                    'scoring_type'     => $it->scoring_type ?? 'HIGHER_BETTER',
                ]
            );
        }
    }

    /**
     * Submit Review dari Penilai (Termasuk Breakdown Competency Matrix)
     */
    public function submitFeedback(PerformanceReview $review, User $reviewer, array $data): PerformanceReviewFeedback
    {
        return DB::transaction(function () use ($review, $reviewer, $data) {
            $isSelf = ($reviewer->id == $review->user_id);
            $type = $isSelf ? 'SELF' : 'MANAGER';

            if ($isSelf) {
                if (!in_array($review->status, ['SELF_REVIEW', 'SCHEDULED'], true)) {
                    throw new \InvalidArgumentException('Tahap Self Review sudah selesai atau tidak aktif.');
                }
            } else {
                $isAssignedManager = ($review->manager_id && (int) $review->manager_id === (int) $reviewer->id);
                $isHcga = app(PerformanceKpiService::class)->isHcgaOrAdmin($reviewer);
                if (!$isAssignedManager && !$isHcga) {
                    throw new \InvalidArgumentException('Hanya Atasan Penilai atau HCGA yang dapat mengisi Manager Review.');
                }
                if ($review->status === 'FINALIZED') {
                    throw new \InvalidArgumentException('Review yang sudah FINALIZED tidak dapat diubah.');
                }
            }

            $feedback = PerformanceReviewFeedback::firstOrNew([
                'review_id'   => $review->id,
                'reviewer_id' => $reviewer->id,
            ]);

            $feedback->reviewer_type = $type;
            if (!$feedback->exists) {
                $feedback->score = 0;
                $feedback->save();
            }

            // Jika ada rincian nilai competency matrix
            $competencyScores = $data['competencies'] ?? [];
            $avgCompetencyScore = null;

            if (!empty($competencyScores) && is_array($competencyScores)) {
                PerformanceReviewCompetencyScore::where('feedback_id', $feedback->id)->delete();
                $totalComp = 0;
                $countComp = 0;

                foreach ($competencyScores as $compId => $compVal) {
                    $scoreVal = (int) $compVal;
                    if ($scoreVal >= 1 && $scoreVal <= 5) {
                        PerformanceReviewCompetencyScore::create([
                            'feedback_id'   => $feedback->id,
                            'competency_id' => $compId,
                            'score'         => $scoreVal,
                        ]);
                        // Konversi skala 1-5 ke skala 100
                        $totalComp += ($scoreVal * 20);
                        $countComp++;
                    }
                }

                if ($countComp > 0) {
                    $avgCompetencyScore = round($totalComp / $countComp, 2);
                }
            }

            $finalFeedbackScore = $avgCompetencyScore !== null ? $avgCompetencyScore : (float) ($data['score'] ?? 0);
            $feedback->score = $finalFeedbackScore;
            $feedback->feedback = $data['feedback'] ?? null;
            $feedback->is_submitted = true;
            $feedback->submitted_at = Carbon::now();
            $feedback->save();

            // Simpan detail breakdown ke tabel utama review
            if ($isSelf) {
                $review->self_score = $feedback->score;
                $review->self_notes = $data['feedback'] ?? null;
                if ($review->status === 'SELF_REVIEW') {
                    $review->status = 'MANAGER_REVIEW';
                }
            } else {
                $review->manager_score = $feedback->score;
                $review->competency_score = $feedback->score;
                $review->manager_notes = $data['feedback'] ?? null;
                $review->strength_notes = $data['strength_notes'] ?? $review->strength_notes;
                $review->improvement_notes = $data['improvement_notes'] ?? $review->improvement_notes;
                $review->status = 'CALIBRATION';
            }

            // Hitung komposisi skor
            $cycle = $review->cycle;
            if ($cycle) {
                $kpiPart = ($review->kpi_score * ($cycle->weight_kpi / 100));
                $compScore = $review->manager_score ?: $review->self_score ?: 0;
                $compPart = ($compScore * ($cycle->weight_competency / 100));
                $review->final_score = round($kpiPart + $compPart, 2);
                $review->rating_grade = $this->determineGrade($review->final_score);
            }

            $review->save();

            return $feedback;
        });
    }

    /**
     * Finalisasi Review / Kalibrasi Selesai (Otomatis Buat PIP jika Grade D / Skor < 60)
     */
    public function finalizeReview(PerformanceReview $review, float $calibratedScore = null, string $notes = null, int $userId = 1): PerformanceReview
    {
        return DB::transaction(function () use ($review, $calibratedScore, $notes, $userId) {
            if ($calibratedScore !== null) {
                $review->final_score = $calibratedScore;
                $review->rating_grade = $this->determineGrade($calibratedScore);
            }

            $review->status = 'FINALIZED';
            if ($notes) {
                $review->manager_notes = ($review->manager_notes ? $review->manager_notes . "\n" : '') . "Catatan Kalibrasi: " . $notes;
            }
            $review->save();

            // CEK OTOMATIS: Jika Skor < Threshold (default 60) atau Rating Grade D, inisiasi draft PIP Plan
            $threshold = ($review->cycle && $review->cycle->pip_threshold_score !== null)
                ? (float) $review->cycle->pip_threshold_score
                : (float) PerformanceSetting::getValue('default_pip_threshold_score', 60);

            if ($review->final_score < $threshold || $review->rating_grade === 'D') {
                $existingPip = PerformancePipPlan::where('review_id', $review->id)->exists();
                if (!$existingPip) {
                    $this->pipService->createFromLowPerformanceReview($review, $userId);
                }
            }

            return $review;
        });
    }

    /**
     * Resolve atasan langsung karyawan melalui organization_structure (Reports_To_Position_ID)
     * dengan fallback ke Manager Departemen pada master_position / users.
     */
    public function resolveDirectManagerForUser(User $employee): ?User
    {
        if (!empty($employee->Emp_ID)) {
            $node = DB::table('organization_structure')
                ->where('NIK', $employee->Emp_ID)
                ->whereNotNull('Reports_To_Position_ID')
                ->first();

            if ($node) {
                $parentNode = DB::table('organization_structure')
                    ->where('ID', (int) $node->Reports_To_Position_ID)
                    ->first();

                if ($parentNode && !empty($parentNode->NIK)) {
                    $mgr = User::where('Emp_ID', $parentNode->NIK)->where('Status', 'ACTIVE')->first();
                    if ($mgr && (int) $mgr->id !== (int) $employee->id) {
                        return $mgr;
                    }
                }
            }
        }

        if (!empty($employee->Position)) {
            $empPosQuery = DB::table('master_position')
                ->where(function ($q) use ($employee) {
                    $q->where('Initial', $employee->Position)
                      ->orWhere('Position', $employee->Position);
                });
            if (!empty($employee->Department)) {
                $empPosQuery->where('Department', $employee->Department);
            }
            $empPos = $empPosQuery->first();

            if ($empPos && !empty($empPos->Parent_Position_ID)) {
                $parentPos = DB::table('master_position')->where('ID', (int) $empPos->Parent_Position_ID)->first();
                if ($parentPos) {
                    $mgr = User::where(function ($q) use ($parentPos) {
                            $q->where('Position', $parentPos->Initial)->orWhere('Position', $parentPos->Position);
                        })
                        ->where('Status', 'ACTIVE')
                        ->where('id', '!=', $employee->id)
                        ->first();
                    if ($mgr) {
                        return $mgr;
                    }
                }
            }
        }

        if (!empty($employee->Department)) {
            $mgrPos = DB::table('master_position')
                ->where('Department', $employee->Department)
                ->where('Level', 3)
                ->pluck('Initial')
                ->filter()
                ->all();

            if (!empty($mgrPos)) {
                $mgr = User::whereIn('Position', $mgrPos)
                    ->where('Department', $employee->Department)
                    ->where('Status', 'ACTIVE')
                    ->where('id', '!=', $employee->id)
                    ->first();
                if ($mgr) {
                    return $mgr;
                }
            }
        }

        return null;
    }

    /**
     * Perbarui realisasi item KPI langsung dari lembar Review 6 bulanan & hitung ulang skor KPI.
     */
    public function updateKpiRealizationOnReview(PerformanceReview $review, int $goalItemId, array $data, int $userId): PerformanceReview
    {
        if ($review->status === 'FINALIZED') {
            throw new \InvalidArgumentException('Review yang sudah FINALIZED tidak dapat diubah.');
        }

        $actor = User::find($userId);
        $isAssignedManager = ($review->manager_id && (int) $review->manager_id === $userId);
        $isHcga = app(PerformanceKpiService::class)->isHcgaOrAdmin($actor);
        if (!$isAssignedManager && !$isHcga) {
            throw new \InvalidArgumentException('Hanya Atasan Langsung atau HCGA yang dapat mengisi realisasi KPI evaluasi.');
        }

        if (!$review->goal_id) {
            throw new \InvalidArgumentException('Lembar review ini tidak tertaut ke Goal KPI.');
        }

        $item = \App\PerformanceGoalItem::where('id', $goalItemId)
            ->where('goal_id', $review->goal_id)
            ->firstOrFail();

        return DB::transaction(function () use ($review, $item, $data, $userId) {
            $goal = PerformanceGoal::findOrFail($review->goal_id);
            if ($review->kpiItems()->count() === 0) {
                $this->snapshotGoalItemsForReview($review, $goal);
            }

            $snapshot = PerformanceReviewKpiItem::where('review_id', $review->id)
                ->where('goal_item_id', $item->id)
                ->firstOrFail();

            $newValue = (float) $data['new_value'];
            $target = (float) $snapshot->target_value;
            $progressPercent = 0;
            if ($target > 0) {
                if ($snapshot->scoring_type === 'LOWER_BETTER') {
                    $progressPercent = $newValue <= $target ? 100 : max(0, round(($target / max($newValue, 0.01)) * 100, 2));
                } else {
                    $progressPercent = round(($newValue / $target) * 100, 2);
                }
            }

            $itemScore = round(($progressPercent * ($snapshot->weight_percent > 0 ? $snapshot->weight_percent : 100)) / 100, 2);
            $snapshot->update([
                'actual_value'     => $newValue,
                'progress_percent' => $progressPercent,
                'score'            => $itemScore,
                'comment'          => $data['comment'] ?? $snapshot->comment,
                'updated_by'       => $userId,
            ]);

            // Catat juga check-in log di Goal Item
            app(PerformanceKpiService::class)->recordProgress($item, $data, $userId);

            // Hitung ulang kpi_score dari snapshot milik review ini (independen antar semester)
            $snapshots = $review->kpiItems()->get();
            $totalWeighted = 0;
            $totalWeight = 0;
            $sumSimple = 0;
            foreach ($snapshots as $sn) {
                if ($sn->item_type === 'KPI' && $sn->weight_percent > 0) {
                    $totalWeighted += ($sn->progress_percent * $sn->weight_percent);
                    $totalWeight += $sn->weight_percent;
                } else {
                    $sumSimple += $sn->progress_percent;
                }
            }

            $reviewKpiScore = 0;
            if ($totalWeight > 0) {
                $reviewKpiScore = round($totalWeighted / $totalWeight, 2);
            } elseif ($snapshots->count() > 0) {
                $reviewKpiScore = round($sumSimple / $snapshots->count(), 2);
            }

            $review->kpi_score = $reviewKpiScore;

            $cycle = $review->cycle;
            if ($cycle) {
                $kpiPart = ($review->kpi_score * ($cycle->weight_kpi / 100));
                $compScore = $review->manager_score ?: $review->self_score ?: 0;
                $compPart = ($compScore * ($cycle->weight_competency / 100));
                $review->final_score = round($kpiPart + $compPart, 2);
                $review->rating_grade = $this->determineGrade($review->final_score);
            }

            $review->save();

            return $review->fresh(['goal.items', 'kpiItems', 'cycle']);
        });
    }

    /**
     * Konversi Skor Angka ke Grade Rating (A, B+, B, C, D)
     */
    public function determineGrade(float $score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B+';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        return 'D';
    }
}
