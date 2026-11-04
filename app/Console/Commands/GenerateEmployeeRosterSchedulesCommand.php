<?php

namespace App\Console\Commands;

use App\Models\HCGA\EmployeeRosterAssignment;
use App\Services\HCGA\RosterScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateEmployeeRosterSchedulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roster:generate-schedules 
                            {--days=30 : Jumlah hari ke depan yang akan di-generate} 
                            {--user= : User ID spesifik yang ingin di-generate (opsional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomasi generate jadwal kerja harian (employee_schedules) dari penugasan pola roster aktif.';

    protected $rosterScheduleService;

    public function __construct(RosterScheduleService $rosterScheduleService)
    {
        parent::__construct();
        $this->rosterScheduleService = $rosterScheduleService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $daysAhead = (int) $this->option('days');
        $specificUser = $this->option('user');

        $startDate = Carbon::today()->toDateString();
        $endDate = Carbon::today()->addDays($daysAhead)->toDateString();

        $this->info("Memulai pembuatan jadwal roster dari {$startDate} s/d {$endDate} ({$daysAhead} hari ke depan)...");

        $query = EmployeeRosterAssignment::where('status', 'ACTIVE')
            ->where('start_date', '<=', $endDate)
            ->where(function ($sub) use ($startDate) {
                $sub->whereNull('end_date')
                    ->orWhere('end_date', '>=', $startDate);
            });

        if (!empty($specificUser)) {
            $query->where('user_id', $specificUser);
        }

        $activeUsers = $query->pluck('user_id')->unique();

        if ($activeUsers->isEmpty()) {
            $this->warn("Tidak ditemukan karyawan dengan penugasan roster aktif.");
            return 0;
        }

        $totalGenerated = 0;
        $bar = $this->output->createProgressBar($activeUsers->count());
        $bar->start();

        foreach ($activeUsers as $userId) {
            try {
                $res = $this->rosterScheduleService->generateEmployeeSchedule($userId, $startDate, $endDate, 'COMMAND_ROSTER_SYNC');
                $totalGenerated += $res['generated_count'] ?? 0;
            } catch (\Exception $e) {
                $this->error("Gagal generate jadwal untuk user {$userId}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->line("");
        $this->info("Sukses! Total {$totalGenerated} jadwal hari kerja berhasil diperbarui untuk {$activeUsers->count()} karyawan.");

        return 0;
    }
}

