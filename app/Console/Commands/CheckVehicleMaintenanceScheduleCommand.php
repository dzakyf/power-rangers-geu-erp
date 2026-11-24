<?php

namespace App\Console\Commands;

use App\Services\HCGA\VehiclePmReminderService;
use Illuminate\Console\Command;

class CheckVehicleMaintenanceScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:check-maintenance-schedules {--force : Abaikan log reminder sebelumnya dan kirim ulang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluasi jadwal PM kendaraan (Tanggal & KM) dan kirim reminder In-App & Email secara idempoten.';

    /** @var VehiclePmReminderService */
    protected $reminderService;

    public function __construct(VehiclePmReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $force = (bool) $this->option('force');
        $result = $this->reminderService->processDailyReminders($force);

        $this->info(sprintf(
            'Vehicle PM schedule check done. Evaluated: %d, KM synced: %d, In-App: %d, Email: %d.',
            $result['evaluated'],
            $result['synced_km'],
            $result['in_app'],
            $result['email']
        ));

        return 0;
    }
}
