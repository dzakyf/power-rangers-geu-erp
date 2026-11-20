<?php

namespace App\Services\HCGA;

use App\PerformanceCompetency;
use App\PerformanceCompetencyVersion;
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
     * Mengembalikan array ['primary' => ?PerformanceGoal, 'addons' => Collection<PerformanceGoal>]
     * Mendukung KPI Posisi + KPI Tambahan (Add-on), serta kategori PROBATION.
     */
    public function resolveGoalsForEmployee(User $employee, int $periodId, string $reviewType = 'REGULAR'): array
    {
        $isProbation = (strtoupper($reviewType) === 'PROBATION');
        $posGoal = null;
        $addons = collect();

        $posId = null;
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
                $posId = (int) $pos->ID;
            }
        }

        if ($isProbation) {
            // 1a. Cari Template KPI Probation Posisi
            if ($posId) {
                $posGoal = PerformanceGoal::where('scope_type', 'POSITION')
                    ->where('goal_category', 'PROBATION')
                    ->where('position_id', $posId)
                    ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
                    ->orderBy('id', 'desc')
                    ->first();
            }

            // 1b. Cari KPI Probation Individu (bisa sebagai utama atau add-on)
            $probIndGoals = PerformanceGoal::where('user_id', $employee->id)
                ->where('goal_category', 'PROBATION')
                ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
                ->orderBy('id', 'desc')
                ->get();

            if (!$posGoal && $probIndGoals->isNotEmpty()) {
                $posGoal = $probIndGoals->first();
                $addons = $probIndGoals->slice(1)->values();
            } else {
                $addons = $probIndGoals;
            }

            if ($posGoal || $addons->isNotEmpty()) {
                return ['primary' => $posGoal, 'addons' => $addons];
            }
        }

        // 2. KPI Reguler: Template Posisi (prioritas) + KPI Tambahan (is_addon = 1)
        if ($posId) {
            $posGoal = PerformanceGoal::where('period_id', $periodId)
                ->where('scope_type', 'POSITION')
                ->where('goal_category', 'REGULAR')
                ->where('position_id', $posId)
                ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$posGoal && !empty($employee->Department) && !empty($employee->Position)) {
            $posGoal = PerformanceGoal::where('period_id', $periodId)
                ->where('scope_type', 'POSITION')
                ->where('goal_category', 'REGULAR')
                ->where('department', $employee->Department)
                ->where('position', $employee->Position)
                ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
                ->orderBy('id', 'desc')
                ->first();
        }

        $indGoals = PerformanceGoal::where('period_id', $periodId)
            ->where('user_id', $employee->id)
            ->where('goal_category', 'REGULAR')
            ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
            ->orderBy('id', 'desc')
            ->get();

        if ($posGoal) {
            $addons = $indGoals->filter(function ($g) {
                return (bool) $g->is_addon || $g->scope_type === 'INDIVIDUAL';
            })->values();
        } elseif ($indGoals->isNotEmpty()) {
            $posGoal = $indGoals->first();
            $addons = $indGoals->slice(1)->values();
        }

        return ['primary' => $posGoal, 'addons' => $addons];
    }

    /**
     * Backward-compatible helper untuk mengambil 1 Goal utama karyawan.
     */
    public function resolveActiveGoalForEmployee(User $employee, int $periodId): ?PerformanceGoal
    {
        $resolved = $this->resolveGoalsForEmployee($employee, $periodId);
        return $resolved['primary'];
    }

    /**
     * Inisiasi Dokumen Review Karyawan dari Siklus & Goal yang sudah disetujui (Approved)
     */
    public function initiateReviewForEmployee(PerformanceReviewCycle $cycle, User $employee, ?User $manager = null, ?string $probationEndDate = null): PerformanceReview
    {
        return DB::transaction(function () use ($cycle, $employee, $manager, $probationEndDate) {
            $resolved = $this->resolveGoalsForEmployee($employee, (int) $cycle->period_id, (string) $cycle->review_type);
            $primaryGoal = $resolved['primary'];
            $addonGoals = $resolved['addons'];

            $activeVersion = PerformanceCompetencyVersion::getActivePublished();
            $kpiScore = $primaryGoal ? (float) $primaryGoal->overall_progress_percent : 0;

            $review = PerformanceReview::create([
                'review_no'             => $this->generateReviewNo(),
                'cycle_id'              => $cycle->id,
                'user_id'               => $employee->id,
                'goal_id'               => $primaryGoal ? $primaryGoal->id : null,
                'manager_id'            => $manager ? $manager->id : null,
                'competency_version_id' => $activeVersion ? $activeVersion->id : null,
                'probation_end_date'    => $probationEndDate,
                'kpi_score'             => $kpiScore,
                'competency_score'      => 0,
                'status'                => $cycle->enable_self_review ? 'SELF_REVIEW' : 'MANAGER_REVIEW',
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

            if ($primaryGoal) {
                $primaryScope = ($primaryGoal->goal_category === 'PROBATION')
                    ? ($primaryGoal->scope_type === 'POSITION' ? 'PROBATION_POSITION' : 'PROBATION_INDIVIDUAL')
                    : ($primaryGoal->scope_type === 'POSITION' ? 'POSITION' : 'INDIVIDUAL');
                $this->snapshotGoalItemsForReview($review, $primaryGoal, $primaryScope);
            }

            foreach ($addonGoals as $addon) {
                $addonScope = ($addon->goal_category === 'PROBATION') ? 'PROBATION_INDIVIDUAL' : 'INDIVIDUAL_ADDON';
                $this->snapshotGoalItemsForReview($review, $addon, $addonScope);
            }

            return $review;
        });
    }

    /**
     * Salin item KPI/OKR dari Goal Sheet ke snapshot per dokumen Review.
     */
    public function snapshotGoalItemsForReview(PerformanceReview $review, PerformanceGoal $goal, string $sourceScope = 'POSITION'): void
    {
        $items = $goal->items()->orderBy('sort_order')->get();
        foreach ($items as $it) {
            PerformanceReviewKpiItem::firstOrCreate(
                [
                    'review_id'    => $review->id,
                    'goal_item_id' => $it->id,
                ],
                [
                    'source_scope'     => $sourceScope,
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

            // Ambil konfigurasi skala penilaian dari versi kompetensi yang terpasang pada review (atau versi aktif)
            $scaleConfig = $this->getScaleConfigForReview($review);
            $scaleMin = (int) $scaleConfig['min'];
            $scaleMax = (int) $scaleConfig['max'];

            // Jika ada rincian nilai competency matrix / soft skill
            $competencyScores = $data['competencies'] ?? [];
            $competencyComments = $data['competency_comments'] ?? [];
            $avgCompetencyScore = null;

            if (!empty($competencyScores) && is_array($competencyScores)) {
                PerformanceReviewCompetencyScore::where('feedback_id', $feedback->id)->delete();
                $totalScore = 0;
                $countComp = 0;

                foreach ($competencyScores as $compId => $compVal) {
                    $scoreVal = (int) $compVal;
                    if ($scoreVal >= $scaleMin && $scoreVal <= $scaleMax) {
                        PerformanceReviewCompetencyScore::create([
                            'feedback_id'   => $feedback->id,
                            'competency_id' => $compId,
                            'score'         => $scoreVal,
                            'max_scale'     => $scaleMax,
                            'comment'       => $competencyComments[$compId] ?? null,
                        ]);
                        $totalScore += $scoreVal;
                        $countComp++;
                    }
                }

                if ($countComp > 0 && $scaleMax > 0) {
                    $avgCompetencyScore = round(($totalScore / ($countComp * $scaleMax)) * 100, 2);
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

                if ($cycle->review_type === 'PROBATION') {
                    $review->recommendation = !empty($data['recommendation'])
                        ? $data['recommendation']
                        : $this->determineProbationRecommendation((float) $review->final_score);
                    if (isset($data['conclusion_notes'])) {
                        $review->conclusion_notes = $data['conclusion_notes'];
                    }
                }
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
        $kpiService = app(PerformanceKpiService::class);
        $empDept = $review->user ? $review->user->Department : null;
        $isPic = $kpiService->isDeptPic($actor, $empDept);
        $isHcga = $kpiService->isHcgaOrAdmin($actor);
        if (!$isPic && !$isHcga) {
            throw new \InvalidArgumentException('Hanya PIC yang ditetapkan atau HCGA yang dapat mengisi target & realisasi KPI. Atasan hanya melakukan penilaian.');
        }

        $snapshot = PerformanceReviewKpiItem::where('review_id', $review->id)
            ->where('goal_item_id', $goalItemId)
            ->first();

        if (!$snapshot && !$review->goal_id) {
            throw new \InvalidArgumentException('Item KPI tidak ditemukan pada lembar review ini.');
        }

        $item = \App\PerformanceGoalItem::where('id', $goalItemId)->firstOrFail();

        return DB::transaction(function () use ($review, $item, $data, $userId) {
            if ($review->kpiItems()->count() === 0 && $review->goal_id) {
                $goal = PerformanceGoal::findOrFail($review->goal_id);
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

    /**
     * Konversi Skor Akhir Probation ke Rekomendasi Kesimpulan (FR/HCGA/14-00)
     */
    public function determineProbationRecommendation(float $score): string
    {
        if ($score <= 50) {
            return 'NOT_RECOMMENDED';
        }
        if ($score <= 75) {
            return 'EXTEND_CONTRACT';
        }
        return 'PROMOTE_PERMANENT';
    }

    /**
     * Ambil konfigurasi skala penilaian untuk dokumen review tertentu
     */
    public function getScaleConfigForReview(?PerformanceReview $review = null): array
    {
        $version = null;
        if ($review && $review->competency_version_id) {
            $version = PerformanceCompetencyVersion::find($review->competency_version_id);
        }
        if (!$version) {
            $version = PerformanceCompetencyVersion::getActivePublished();
        }

        if ($version) {
            return [
                'version_id'    => $version->id,
                'version_no'    => $version->version_no,
                'version_label' => $version->version_label,
                'min'           => (int) $version->scale_min,
                'max'           => (int) $version->scale_max,
                'labels'        => $version->scale_labels ?: [],
            ];
        }

        return [
            'version_id'    => null,
            'version_no'    => 1,
            'version_label' => 'Default',
            'min'           => (int) PerformanceSetting::getValue('competency_scale_min', 1),
            'max'           => (int) PerformanceSetting::getValue('competency_scale_max', 5),
            'labels'        => [],
        ];
    }
}
