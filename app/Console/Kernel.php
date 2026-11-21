<?php

namespace App\Console;

use App\Support\Modules;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        if (Modules::isEnabled('hcga')) {
            $schedule->command('permits:send-expiry-reminders')->dailyAt('07:00');
            $schedule->command('vehicle:send-document-reminders')->dailyAt('07:30');
            $schedule->command('roster:generate-schedules --days=30')->dailyAt('01:00');
            $schedule->command('performance:send-pip-reminders')->weeklyOn(1, '08:00'); // Setiap Senin Pukul 08:00
            $schedule->command('performance:send-probation-reminders')->dailyAt('07:45');
            $schedule->command('sanctions:auto-expire')->dailyAt('00:01');
            $schedule->command('exit-clearance:sync-resigned')->dailyAt('00:05');
        }

        $schedule->command('approval:check-sla')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        $moduleDirs = [
            'bp' => 'BusinessPartner',
            'finance' => 'Finance',
            'cns' => 'CNS',
            'hcga' => 'HCGA',
        ];

        foreach ($moduleDirs as $module => $dir) {
            $path = app_path("Modules/{$dir}/Commands");

            if (Modules::isEnabled($module) && is_dir($path)) {
                $this->load($path);
            }
        }

        require base_path('routes/console.php');
    }
}
