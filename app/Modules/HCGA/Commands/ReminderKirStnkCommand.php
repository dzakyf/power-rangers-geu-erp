<?php

namespace App\Modules\HCGA\Commands;

use App\Services\HCGA\VehicleDocumentReminderService;
use Illuminate\Console\Command;

class ReminderKirStnkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:send-document-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi In-App & Email reminder jatuh tempo STNK (Tahunan & 5 Tahunan) serta KEUR/KIR kendaraan.';

    /** @var VehicleDocumentReminderService */
    protected $reminderService;

    public function __construct(VehicleDocumentReminderService $reminderService)
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
        $inAppSent = $this->reminderService->syncInAppReminders(false);
        $emailSent = $this->reminderService->sendEmailReminders();

        $this->info("Vehicle document reminders processed. In-App: {$inAppSent}, Email: {$emailSent}.");

        return 0;
    }
}
