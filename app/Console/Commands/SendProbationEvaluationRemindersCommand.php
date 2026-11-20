<?php

namespace App\Console\Commands;

use App\Services\HCGA\ProbationEvaluationService;
use Illuminate\Console\Command;

class SendProbationEvaluationRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:send-probation-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate draft evaluasi probation dan kirim notifikasi H-14/H-7 ke atasan langsung serta eskalasi H-3 ke HCGA';

    /**
     * Execute the console command.
     *
     * @param ProbationEvaluationService $service
     * @return int
     */
    public function handle(ProbationEvaluationService $service)
    {
        $stats = $service->processDailyProbationReminders();

        $this->info(sprintf(
            'Selesai. Draft baru: %d | H-14: %d | H-7: %d | Eskalasi H-3: %d',
            $stats['drafts_created'],
            $stats['h14_sent'],
            $stats['h7_sent'],
            $stats['h3_escalated']
        ));

        return 0;
    }
}
