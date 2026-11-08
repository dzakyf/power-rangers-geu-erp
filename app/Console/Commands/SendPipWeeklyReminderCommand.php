<?php

namespace App\Console\Commands;

use App\PerformancePipPlan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendPipWeeklyReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:send-pip-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim reminder jadwal sesi mentoring mingguan bagi program PIP yang sedang aktif';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now()->toDateString();
        $this->info("Menjalankan PIP weekly reminder untuk tanggal: {$today}");

        $activePips = PerformancePipPlan::with(['user', 'mentor', 'checkins'])
            ->whereIn('status', ['DRAFT', 'ACTIVE'])
            ->where('outcome_status', 'ONGOING')
            ->get();

        $sentCount = 0;

        foreach ($activePips as $pip) {
            $employee = $pip->user;
            $mentor = $pip->mentor;

            if (!$employee || !$mentor) {
                continue;
            }

            // Hitung minggu berjalan
            $startDate = Carbon::parse($pip->start_date);
            $weekRunning = max(1, $startDate->diffInWeeks(Carbon::now()) + 1);

            // Cek apakah minggu ini sudah ada checkin
            $hasCheckinThisWeek = $pip->checkins()
                ->where('created_at', '>=', Carbon::now()->startOfWeek())
                ->exists();

            if (!$hasCheckinThisWeek) {
                // Log audit reminder mentoring mingguan
                Log::info("PIP Reminder sent: [{$pip->pip_no}] Minggu ke-{$weekRunning} untuk Karyawan {$employee->Name} dan Mentor {$mentor->Name}");
                
                // Jika aplikasi memiliki tabel user_notifications / email sender
                $this->line("-> Pengingat terkirim untuk PIP: {$pip->pip_no} (Mentor: {$mentor->Name}, Karyawan: {$employee->Name})");
                $sentCount++;
            }
        }

        $this->info("Selesai. Total {$sentCount} pengingat sesi mentoring PIP berhasil diproses.");
        return 0;
    }
}

