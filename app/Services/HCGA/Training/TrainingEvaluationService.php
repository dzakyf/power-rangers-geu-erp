<?php

namespace App\Services\HCGA\Training;

use App\Models\HCGA\Training\HcgaTraining;
use App\Models\HCGA\Training\HcgaTrainingFeedback;
use App\Models\HCGA\Training\HcgaTrainingParticipant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainingEvaluationService
{
    public const TRAINER_ASPECTS = [
        'Penguasaan Materi',
        'Kemampuan Komunikasi & Penyampaian',
        'Interaksi & Menjawab Pertanyaan',
        'Manajemen Waktu',
    ];

    public const MATERIAL_ASPECTS = [
        'Kesesuaian Materi dengan Kebutuhan Kerja',
        'Kejelasan Struktur & Modul',
        'Kualitas Bahan / Slide / Studi Kasus',
        'Manfaat & Aplikabilitas',
    ];

    /**
     * Hitung skor akhir dari Post-Test (60%) dan Praktik (40%), atau 100% jika hanya salah satu terisi.
     */
    public function calculateFinalScore(?float $post, ?float $practice): ?float
    {
        if ($post !== null && $practice !== null) {
            return round(($post * 0.6) + ($practice * 0.4), 2);
        }
        if ($post !== null) {
            return round($post, 2);
        }
        if ($practice !== null) {
            return round($practice, 2);
        }

        return null;
    }

    /**
     * Simpan penilaian hasil training karyawan & hitung status lulus terhadap passing grade.
     */
    public function saveParticipantScores(int $trainingId, array $scoresInput, $user): array
    {
        $training = HcgaTraining::findOrFail($trainingId);
        $passingGrade = (float) ($training->passing_grade ?: 75.00);
        $evaluator = $user->username ?? 'system';
        $updated = [];

        DB::transaction(function () use ($trainingId, $scoresInput, $passingGrade, $evaluator, &$updated) {
            foreach ($scoresInput as $row) {
                $participant = HcgaTrainingParticipant::where('training_id', $trainingId)
                    ->where('id', (int) ($row['participant_id'] ?? 0))
                    ->first();

                if (!$participant) {
                    continue;
                }

                $attendance = $row['attendance_status'] ?? $participant->attendance_status;
                $pre = isset($row['pre_test_score']) && $row['pre_test_score'] !== '' ? (float) $row['pre_test_score'] : null;
                $post = isset($row['post_test_score']) && $row['post_test_score'] !== '' ? (float) $row['post_test_score'] : null;
                $practice = isset($row['practice_score']) && $row['practice_score'] !== '' ? (float) $row['practice_score'] : null;

                // Selalu hitung ulang skor akhir dari komponen nilai agar perubahan nilai tidak tertimpa skor lama
                $final = $this->calculateFinalScore($post, $practice);

                $isPassed = null;
                if ($attendance === 'ABSENT') {
                    $isPassed = false;
                } elseif ($final !== null) {
                    $isPassed = $final >= $passingGrade;
                }

                $participant->attendance_status = $attendance;
                $participant->pre_test_score = $pre;
                $participant->post_test_score = $post;
                $participant->practice_score = $practice;
                $participant->final_score = $final;
                $participant->is_passed = $isPassed;
                $participant->evaluation_notes = $row['evaluation_notes'] ?? $participant->evaluation_notes;
                $participant->evaluated_by = $evaluator;
                $participant->evaluated_at = Carbon::now();
                $participant->save();

                $updated[] = $participant;
            }
        });

        return $updated;
    }

    /**
     * Simpan form evaluasi Pemateri & Materi dari peserta.
     */
    public function submitFeedback(int $trainingId, int $participantId, array $trainerRatings, array $materialRatings, ?string $comments = null): void
    {
        $participant = HcgaTrainingParticipant::where('training_id', $trainingId)
            ->where('id', $participantId)
            ->firstOrFail();

        DB::transaction(function () use ($trainingId, $participant, $trainerRatings, $materialRatings, $comments) {
            HcgaTrainingFeedback::where('training_id', $trainingId)
                ->where('participant_id', $participant->id)
                ->delete();

            foreach ($trainerRatings as $aspect => $rating) {
                $val = max(1, min(5, (int) $rating));
                HcgaTrainingFeedback::create([
                    'training_id' => $trainingId,
                    'participant_id' => $participant->id,
                    'feedback_type' => 'TRAINER',
                    'aspect_name' => (string) $aspect,
                    'rating' => $val,
                    'comments' => $comments,
                ]);
            }

            foreach ($materialRatings as $aspect => $rating) {
                $val = max(1, min(5, (int) $rating));
                HcgaTrainingFeedback::create([
                    'training_id' => $trainingId,
                    'participant_id' => $participant->id,
                    'feedback_type' => 'MATERIAL',
                    'aspect_name' => (string) $aspect,
                    'rating' => $val,
                    'comments' => $comments,
                ]);
            }

            $participant->feedback_submitted_at = Carbon::now();
            $participant->save();
        });
    }

    /**
     * Agregasi Summary Training: jumlah peserta, hadir, lulus, tidak lulus, rata-rata skor, dan rating pemateri/materi.
     */
    public function getTrainingSummary(int $trainingId): array
    {
        $training = HcgaTraining::with(['participants.certificate', 'participants.coachings', 'curriculums'])
            ->findOrFail($trainingId);

        $participants = $training->participants;
        $totalParticipants = $participants->count();
        $attendedCount = $participants->where('attendance_status', 'ATTENDED')->count();
        $passedCount = $participants->filter(function ($p) {
            return $p->is_passed === true;
        })->count();
        $failedCount = $participants->filter(function ($p) {
            return $p->is_passed === false;
        })->count();
        $pendingEvalCount = $participants->filter(function ($p) {
            return $p->is_passed === null;
        })->count();

        $scored = $participants->whereNotNull('final_score');
        $avgFinalScore = $scored->count() > 0 ? round($scored->avg('final_score'), 2) : 0;
        $avgPreScore = $participants->whereNotNull('pre_test_score')->count() > 0
            ? round($participants->whereNotNull('pre_test_score')->avg('pre_test_score'), 2)
            : 0;
        $avgPostScore = $participants->whereNotNull('post_test_score')->count() > 0
            ? round($participants->whereNotNull('post_test_score')->avg('post_test_score'), 2)
            : 0;

        $trainerAvg = HcgaTrainingFeedback::where('training_id', $trainingId)
            ->where('feedback_type', 'TRAINER')
            ->avg('rating');
        $materialAvg = HcgaTrainingFeedback::where('training_id', $trainingId)
            ->where('feedback_type', 'MATERIAL')
            ->avg('rating');

        return [
            'training' => $training,
            'total_participants' => $totalParticipants,
            'attended_count' => $attendedCount,
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'pending_eval_count' => $pendingEvalCount,
            'avg_pre_score' => $avgPreScore,
            'avg_post_score' => $avgPostScore,
            'avg_final_score' => $avgFinalScore,
            'trainer_avg_rating' => $trainerAvg ? round((float) $trainerAvg, 2) : 0,
            'material_avg_rating' => $materialAvg ? round((float) $materialAvg, 2) : 0,
            'feedback_respondents' => $participants->whereNotNull('feedback_submitted_at')->count(),
        ];
    }
}
