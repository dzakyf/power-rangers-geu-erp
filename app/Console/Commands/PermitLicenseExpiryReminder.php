<?php

namespace App\Console\Commands;

use App\Services\PermitLicenseMailService;
use App\Services\PermitLicenseReminderService;
use Illuminate\Console\Command;

class PermitLicenseExpiryReminder extends Command
{
    protected $signature = 'permits:send-expiry-reminders';
    protected $description = 'Create in-app notifications and send email reminders for permits/licenses nearing expiry.';

    protected $reminderService;
    protected $mailService;

    public function __construct(PermitLicenseReminderService $reminderService, PermitLicenseMailService $mailService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
        $this->mailService = $mailService;
    }

    public function handle()
    {
        $count = $this->reminderService->syncExpiryNotifications(true);
        $retry = $this->mailService->retryFailedEmails();
        $this->info('Permit/license reminder sync complete. ' . $count . ' permit(s) notified, ' . $retry . ' email(s) retried.');

        return 0;
    }
}