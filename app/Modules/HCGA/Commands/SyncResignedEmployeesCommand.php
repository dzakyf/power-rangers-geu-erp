<?php

namespace App\Modules\HCGA\Commands;

use App\Services\HCGA\ExitClearanceService;
use Illuminate\Console\Command;

class SyncResignedEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exit-clearance:sync-resigned {--clearance_id= : ID Exit Clearance spesifik}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi status karyawan menjadi RESIGNED di tabel employment, users, dan struktur organisasi setelah tanggal pengunduran diri terpenuhi';

    /**
     * Execute the console command.
     *
     * @param ExitClearanceService $service
     * @return int
     */
    public function handle(ExitClearanceService $service)
    {
        $clearanceId = $this->option('clearance_id') ? (int) $this->option('clearance_id') : null;

        $this->info('Memulai pengecekan status pengunduran diri karyawan...');

        try {
            $synced = $service->syncEmploymentResignationStatus($clearanceId);
            $count = count($synced);

            if ($count > 0) {
                $this->info("Berhasil memperbarui status {$count} karyawan menjadi RESIGNED:");
                foreach ($synced as $item) {
                    $this->line("- User ID: {$item['user_id']} | NIK: {$item['nik']} | Efektif: {$item['effective_date']}");
                }
            } else {
                $this->info('Tidak ada karyawan yang perlu diperbarui statusnya hari ini.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Gagal melakukan sinkronisasi status karyawan resign: ' . $e->getMessage());
            return 1;
        }
    }
}

