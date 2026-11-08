<?php

namespace App\Services\HCGA;

use App\PerformanceCompetency;
use App\PerformanceGoal;
use App\PerformancePipPlan;
use App\PerformanceReview;
use App\PerformanceReviewCompetencyScore;
use App\PerformanceReviewCycle;
use App\PerformanceReviewFeedback;
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
     * Inisiasi Dokumen Review Karyawan dari Siklus & Goal yang sudah disetujui (Approved)
     */
    public function initiateReviewForEmployee(PerformanceReviewCycle $cycle, User $employee, ?User $manager = null): PerformanceReview
    {
        return DB::transaction(function () use ($cycle, $employee, $manager) {
            $goal = PerformanceGoal::where('period_id', $cycle->period_id)
                ->where('user_id', $employee->id)
                ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
                ->orderBy('id', 'desc')
                ->first();

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

            return $review;
        });
    }

    /**
     * Submit Review dari Penilai (Termasuk Breakdown Competency Matrix)
     */
    public function submitFeedback(PerformanceReview $review, User $reviewer, array $data): PerformanceReviewFeedback
    {
        return DB::transaction(function () use ($review, $reviewer, $data) {
            $isSelf = ($reviewer->id == $review->user_id);
            $type = $isSelf ? 'SELF' : 'MANAGER';

            $feedback = PerformanceReviewFeedback::firstOrNew([
                'review_id'   => $review->id,
                'reviewer_id' => $reviewer->id,
            ]);

            $feedback->reviewer_type = $type;

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

            // CEK OTOMATIS: Jika Skor < 60 atau Rating Grade D, inisiasi draft PIP Plan
            if ($review->final_score < 60.0 || $review->rating_grade === 'D') {
                $existingPip = PerformancePipPlan::where('review_id', $review->id)->exists();
                if (!$existingPip) {
                    $this->pipService->createFromLowPerformanceReview($review, $userId);
                }
            }

            return $review;
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
