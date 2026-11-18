<?php

namespace App\Services\HCGA;

use App\PerformancePipAction;
use App\PerformancePipCheckin;
use App\PerformancePipPlan;
use App\PerformancePipTraining;
use App\PerformanceReview;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerformancePipService
{
    /**
     * Generate unique nomor PIP Plan
     */
    public function generatePipNo(): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "PIP/{$year}/{$month}/";
        $last = DB::table('performance_pip_plans')/*  */
            ->where('pip_no', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('pip_no');

        if ($last) {
            $seq = (int) substr($last, strrpos($last, '/') + 1);
            $nextSeq = str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }

    /**
     * Buat dokumen PIP dari hasil evaluasi review berkinerja rendah (Grade D / Skor < 60)
     */
    public function createFromLowPerformanceReview(PerformanceReview $review, int $creatorId = 1): PerformancePipPlan
    {
        return DB::transaction(function () use ($review, $creatorId) {
            $startDate = Carbon::now()->toDateString();
            $durationDays = 60; // Default program 60 hari
            $endDate = Carbon::now()->addDays($durationDays)->toDateString();

            $pip = PerformancePipPlan::create([
                'pip_no'                    => $this->generatePipNo(),
                'review_id'                 => $review->id,
                'user_id'                   => $review->user_id,
                'mentor_id'                 => $review->manager_id ?? $review->user_id,
                'duration_days'             => $durationDays,
                'start_date'                => $startDate,
                'end_date'                  => $endDate,
                'performance_issue_summary' => "Hasil evaluasi siklus {$review->cycle->title} memperoleh nilai {$review->final_score} (Grade {$review->rating_grade}). Catatan: " . ($review->improvement_notes ?? 'Perlu peningkatan kinerja pada indikator target KPI dan kompetensi kerja.'),
                'expected_outcome'          => 'Meningkatkan pemenuhan target kerja minimal standar passing grade, kedisiplinan dan kapabilitas operasional sesuai uraian jabatan.',
                'outcome_status'            => 'ONGOING',
                'status'                    => 'ACTIVE',
                'created_by'                => $creatorId,
                'updated_by'                => $creatorId,
            ]);

            // Buat default aksi korektif
            PerformancePipAction::create([
                'pip_id'            => $pip->id,
                'action_title'      => 'Penyelarasan target mingguan dan review berkala bersama atasan/mentor',
                'due_date'          => $endDate,
                'expected_standard' => 'Pencapaian milestone mingguan mencapai minimal 80%',
                'status'            => 'IN_PROGRESS',
            ]);

            return $pip;
        });
    }

    /**
     * Buat dokumen PIP secara manual oleh HRGA / Atasan
     */
    public function createManualPip(array $data, int $creatorId = 1): PerformancePipPlan
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $startDate = Carbon::parse($data['start_date'])->toDateString();
            $durationDays = (int) ($data['duration_days'] ?? 60);
            $endDate = !empty($data['end_date'])
                ? Carbon::parse($data['end_date'])->toDateString()
                : Carbon::parse($startDate)->addDays($durationDays)->toDateString();

            $pip = PerformancePipPlan::create([
                'pip_no'                    => $this->generatePipNo(),
                'review_id'                 => $data['review_id'] ?? null,
                'user_id'                   => $data['user_id'],
                'mentor_id'                 => $data['mentor_id'] ?? $creatorId,
                'duration_days'             => $durationDays,
                'start_date'                => $startDate,
                'end_date'                  => $endDate,
                'performance_issue_summary' => $data['performance_issue_summary'],
                'expected_outcome'          => $data['expected_outcome'] ?? ($data['expected_outcomes'] ?? ''),
                'outcome_status'            => 'ONGOING',
                'status'                    => 'ACTIVE',
                'created_by'                => $creatorId,
                'updated_by'                => $creatorId,
            ]);

            if (!empty($data['action_title'])) {
                PerformancePipAction::create([
                    'pip_id'            => $pip->id,
                    'action_title'      => $data['action_title'],
                    'expected_standard' => $data['expected_standard'] ?? null,
                    'due_date'          => $endDate,
                    'status'            => 'PENDING',
                ]);
            }

            return $pip;
        });
    }

    /**
     * Tambah Action Item pada rencana PIP
     */
    public function addActionItem(PerformancePipPlan $pip, array $data): PerformancePipAction
    {
        return PerformancePipAction::create([
            'pip_id'            => $pip->id,
            'action_title'      => $data['action_title'],
            'expected_standard' => $data['expected_standard'] ?? null,
            'due_date'          => $data['due_date'],
            'status'            => $data['status'] ?? 'PENDING',
            'notes'             => $data['notes'] ?? null,
        ]);
    }

    /**
     * Perbarui status Action Item pada rencana PIP
     */
    public function updateActionStatus(PerformancePipAction $action, string $status): PerformancePipAction
    {
        $action->update(['status' => $status]);
        return $action;
    }

    /**
     * Catat sesi check-in mingguan mentoring PIP
     */
    public function recordWeeklyCheckin(PerformancePipPlan $pip, array $data, int $creatorId = 1): PerformancePipCheckin
    {
        return DB::transaction(function () use ($pip, $data, $creatorId) {
            $lastWeek = PerformancePipCheckin::where('pip_id', $pip->id)->max('week_number') ?? 0;
            $weekNumber = $data['week_number'] ?? ($lastWeek + 1);

            $checkin = PerformancePipCheckin::create([
                'pip_id'           => $pip->id,
                'week_number'      => $weekNumber,
                'checkin_date'     => $data['checkin_date'] ?? Carbon::now()->toDateString(),
                'discussion_notes' => $data['discussion_notes'],
                'action_agreed'    => $data['action_agreed'],
                'progress_rating'  => $data['progress_rating'] ?? 'ON_TRACK',
                'created_by'       => $creatorId,
            ]);

            return $checkin;
        });
    }

    /**
     * Menugaskan materi pelatihan ke rencana PIP
     */
    public function assignTraining(PerformancePipPlan $pip, array $data): PerformancePipTraining
    {
        return DB::transaction(function () use ($pip, $data) {
            return PerformancePipTraining::create([
                'pip_id'         => $pip->id,
                'training_id'    => $data['training_id'] ?? null,
                'training_title' => $data['training_title'],
                'status'         => $data['status'] ?? 'ASSIGNED',
                'notes'          => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Evaluasi akhir dokumen PIP
     */
    public function evaluatePip(PerformancePipPlan $pip, array $data, int $userId = 1): PerformancePipPlan
    {
        return DB::transaction(function () use ($pip, $data, $userId) {
            $pip->final_score = (float) $data['final_score'];
            $pip->outcome_status = $data['outcome_status']; // PASSED, EXTENDED, FAILED
            $pip->final_notes = $data['evaluation_notes'] ?? ($data['final_notes'] ?? null);
            $pip->status = 'COMPLETED';
            $pip->updated_by = $userId;

            if ($pip->outcome_status === 'FAILED') {
                $pip->hr_followup_action = $data['hr_followup_action'] ?? 'SANCTION';
                $pip->hr_followup_notes = $data['hr_followup_notes'] ?? $pip->final_notes;
            }

            $pip->save();

            // Jika EXTENDED, buat otomatis dokumen PIP perpanjangan
            if ($pip->outcome_status === 'EXTENDED' && !$pip->extendedPips()->exists()) {
                $extendDays = max(7, (int) ($data['extend_days'] ?? 30));
                $newStart = $pip->end_date
                    ? Carbon::parse($pip->end_date)->addDay()->toDateString()
                    : Carbon::now()->toDateString();
                $newEnd = Carbon::parse($newStart)->addDays($extendDays)->toDateString();

                $extendedPip = PerformancePipPlan::create([
                    'pip_no'                    => $this->generatePipNo(),
                    'review_id'                 => $pip->review_id,
                    'parent_pip_id'             => $pip->id,
                    'user_id'                   => $pip->user_id,
                    'mentor_id'                 => $pip->mentor_id,
                    'duration_days'             => $extendDays,
                    'start_date'                => $newStart,
                    'end_date'                  => $newEnd,
                    'performance_issue_summary' => "Perpanjangan dari {$pip->pip_no}: " . $pip->performance_issue_summary,
                    'expected_outcome'          => $pip->expected_outcome,
                    'outcome_status'            => 'ONGOING',
                    'status'                    => 'ACTIVE',
                    'created_by'                => $userId,
                    'updated_by'                => $userId,
                ]);

                // Salin action items yang belum ACHIEVED
                foreach ($pip->actions()->where('status', '!=', 'ACHIEVED')->get() as $oldAct) {
                    PerformancePipAction::create([
                        'pip_id'            => $extendedPip->id,
                        'action_title'      => $oldAct->action_title,
                        'expected_standard' => $oldAct->expected_standard,
                        'due_date'          => $newEnd,
                        'status'            => 'IN_PROGRESS',
                    ]);
                }
            }

            return $pip;
        });
    }
}

