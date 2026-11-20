<?php

namespace App\Services\HCGA;

use App\PerformancePeriod;
use App\PerformanceReview;
use App\PerformanceReviewCycle;
use App\PerformanceSetting;
use App\Services\NotificationService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProbationEvaluationService
{
    const TOPIC = 'performance_probation';

    protected $reviewService;
    protected $notificationService;

    public function __construct(
        PerformanceReviewService $reviewService = null,
        NotificationService $notificationService = null
    ) {
        $this->reviewService = $reviewService ?: app(PerformanceReviewService::class);
        $this->notificationService = $notificationService ?: app(NotificationService::class);
    }

    /**
     * Jalankan pengecekan harian karyawan dalam masa probation:
     * - H-14 (atau sesuai setting): Auto-draft review Probation + Notifikasi ke Atasan Langsung
     * - H-7: Reminder ke Atasan Langsung jika belum dinilai
     * - H-3: Eskalasi ke HCGA jika belum dinilai
     */
    public function processDailyProbationReminders(?Carbon $today = null): array
    {
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $reminderDaysRaw = PerformanceSetting::getValue('probation_reminder_days', '14,7');
        $reminderDays = array_map('intval', array_filter(explode(',', $reminderDaysRaw)));
        rsort($reminderDays);
        $firstDay = $reminderDays[0] ?? 14;
        $secondDay = $reminderDays[1] ?? 7;
        $escalationDay = (int) PerformanceSetting::getValue('probation_escalation_days', 3);

        $maxWindowEnd = $today->copy()->addDays(max($firstDay, $secondDay, $escalationDay))->toDateString();

        $employees = DB::table('employment as e')
            ->join('users as u', 'u.Emp_ID', '=', 'e.NIK')
            ->where('e.Status', 'ACTIVE')
            ->where('u.Status', 'ACTIVE')
            ->whereNotNull('e.probation_end_date')
            ->where('e.probation_end_date', '!=', '0000-00-00')
            ->where('e.probation_end_date', '>=', $today->toDateString())
            ->where('e.probation_end_date', '<=', $maxWindowEnd)
            ->select('e.NIK', 'e.probation_end_date', 'e.Department', 'e.Position', 'u.id as user_id')
            ->get();

        $stats = [
            'drafts_created' => 0,
            'h14_sent'       => 0,
            'h7_sent'        => 0,
            'h3_escalated'   => 0,
        ];

        if ($employees->isEmpty()) {
            return $stats;
        }

        $cycle = $this->getOrCreateActiveProbationCycle($today);

        foreach ($employees as $empRow) {
            $employee = User::find($empRow->user_id);
            if (!$employee) {
                continue;
            }

            $probEnd = Carbon::parse($empRow->probation_end_date)->startOfDay();
            $daysLeft = (int) $today->diffInDays($probEnd, false);

            $review = PerformanceReview::where('user_id', $employee->id)
                ->where('probation_end_date', $probEnd->toDateString())
                ->whereHas('cycle', function ($q) {
                    $q->where('review_type', 'PROBATION');
                })
                ->first();

            $manager = $this->reviewService->resolveDirectManagerForUser($employee);

            // 1. H-14: Auto-create draft review + notifikasi pertama ke atasan langsung
            if (!$review && $daysLeft <= $firstDay) {
                $review = $this->reviewService->initiateReviewForEmployee(
                    $cycle,
                    $employee,
                    $manager,
                    $probEnd->toDateString()
                );
                $stats['drafts_created']++;
            }

            if (!$review) {
                continue;
            }

            // Pastikan manager_id terisi jika baru di-resolve
            if (!$review->manager_id && $manager) {
                $review->manager_id = $manager->id;
                $review->save();
            }

            $isCompleted = in_array($review->status, ['CALIBRATION', 'FINALIZED'], true);
            if ($isCompleted) {
                continue;
            }

            // Kirim notifikasi H-14
            if ($daysLeft <= $firstDay && empty($review->reminder_h14_sent_at)) {
                if ($manager) {
                    $this->sendManagerNotification(
                        $manager,
                        $employee,
                        $review,
                        $probEnd,
                        $daysLeft,
                        'Pengingat Penilaian Masa Probation'
                    );
                }
                $review->reminder_h14_sent_at = Carbon::now();
                $review->save();
                $stats['h14_sent']++;
            }

            // 2. H-7: Reminder kedua ke Atasan Langsung
            if ($daysLeft <= $secondDay && empty($review->reminder_h7_sent_at)) {
                if ($manager) {
                    $this->sendManagerNotification(
                        $manager,
                        $employee,
                        $review,
                        $probEnd,
                        $daysLeft,
                        'Reminder H-' . max(0, $daysLeft) . ' Penilaian Probation'
                    );
                }
                $review->reminder_h7_sent_at = Carbon::now();
                $review->save();
                $stats['h7_sent']++;
            }

            // 3. H-3: Eskalasi ke HCGA jika Atasan Langsung belum menilai
            if ($daysLeft <= $escalationDay && empty($review->escalation_h3_sent_at)) {
                $this->sendHcgaEscalation($employee, $manager, $review, $probEnd, $daysLeft);
                $review->escalation_h3_sent_at = Carbon::now();
                $review->save();
                $stats['h3_escalated']++;
            }
        }

        return $stats;
    }

    /**
     * Ambil atau buat Siklus Probation aktif untuk tahun berjalan
     */
    public function getOrCreateActiveProbationCycle(Carbon $today): PerformanceReviewCycle
    {
        $year = $today->year;
        $cycleCode = "PROB-{$year}";

        $existing = PerformanceReviewCycle::where('review_type', 'PROBATION')
            ->where('status', 'ONGOING')
            ->orderBy('id', 'desc')
            ->first();

        if ($existing) {
            return $existing;
        }

        $period = PerformancePeriod::where('status', 'ACTIVE')->orderBy('id', 'desc')->first();
        if (!$period) {
            $period = PerformancePeriod::firstOrCreate(
                ['period_code' => "PM-{$year}"],
                [
                    'name'       => "Performance Period {$year}",
                    'start_date' => "{$year}-01-01",
                    'end_date'   => "{$year}-12-31",
                    'status'     => 'ACTIVE',
                ]
            );
        }

        return PerformanceReviewCycle::firstOrCreate(
            ['cycle_code' => $cycleCode],
            [
                'title'                 => "Evaluasi Masa Percobaan (Probation) {$year}",
                'period_id'             => $period->id,
                'review_type'           => 'PROBATION',
                'enable_self_review'    => false,
                'enable_manager_review' => true,
                'enable_peer_review'    => false,
                'weight_kpi'            => 70.00,
                'weight_competency'     => 30.00,
                'start_date'            => "{$year}-01-01",
                'end_date'              => "{$year}-12-31",
                'status'                => 'ONGOING',
                'description'           => 'Siklus otomatis evaluasi karyawan masa percobaan / kontrak (FR/HCGA/14-00).',
            ]
        );
    }

    protected function sendManagerNotification(
        User $manager,
        User $employee,
        PerformanceReview $review,
        Carbon $probEnd,
        int $daysLeft,
        string $titlePrefix
    ): void {
        $title = "{$titlePrefix}: {$employee->Name}";
        $content = "Masa probation karyawan {$employee->Name} ({$employee->Position} - {$employee->Department}) "
            . "berakhir pada {$probEnd->format('d M Y')} (H-{$daysLeft}). "
            . "Silakan lakukan penilaian pada lembar evaluasi {$review->review_no}.";

        $metadata = json_encode([
            'review_id'   => $review->id,
            'review_no'   => $review->review_no,
            'employee_id' => $employee->id,
            'url'         => url("hrd-performance-review-detail/{$review->id}"),
        ]);

        try {
            $this->notificationService->makeNotification(
                $title,
                $content,
                self::TOPIC,
                null,
                (string) $manager->id,
                'SENT',
                $metadata
            );
        } catch (\Exception $e) {
            Log::warning("Gagal mengirim notifikasi probation ke manager {$manager->id}: " . $e->getMessage());
        }
    }

    protected function sendHcgaEscalation(
        User $employee,
        ?User $manager,
        PerformanceReview $review,
        Carbon $probEnd,
        int $daysLeft
    ): void {
        $hcgaUsers = User::where('Department', 'HCGA')
            ->where('Status', 'ACTIVE')
            ->pluck('id')
            ->all();

        $mgrName = $manager ? $manager->Name : 'Belum terpetakan';
        $title = "Eskalasi H-{$daysLeft} Probation Belum Dinilai: {$employee->Name}";
        $content = "Evaluasi probation {$employee->Name} ({$review->review_no}) berakhir {$probEnd->format('d M Y')} "
            . "dan belum dinilai oleh atasan langsung ({$mgrName}).";

        $metadata = json_encode([
            'review_id'   => $review->id,
            'review_no'   => $review->review_no,
            'employee_id' => $employee->id,
            'url'         => url("hrd-performance-review-detail/{$review->id}"),
        ]);

        foreach ($hcgaUsers as $uid) {
            try {
                $this->notificationService->makeNotification(
                    $title,
                    $content,
                    self::TOPIC . '_escalation',
                    null,
                    (string) $uid,
                    'SENT',
                    $metadata
                );
            } catch (\Exception $e) {
                Log::warning("Gagal mengirim eskalasi probation ke HCGA {$uid}: " . $e->getMessage());
            }
        }
    }
}
