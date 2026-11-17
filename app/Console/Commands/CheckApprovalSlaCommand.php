<?php

namespace App\Console\Commands;

use App\Services\Approval\ApprovalNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CheckApprovalSlaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:check-sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek task approval yang melewati batas SLA, tandai Escalated_At, dan kirim reminder.';

    /**
     * @var ApprovalNotificationService
     */
    protected $notifications;

    public function __construct(ApprovalNotificationService $notifications)
    {
        parent::__construct();
        $this->notifications = $notifications;
    }

    public function handle()
    {
        $now = Carbon::now();

        $overdueTasks = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
            ->whereIn('r.Status', ['IN_PROGRESS', 'BLOCKED'])
            ->whereNotNull('t.Due_At')
            ->where('t.Due_At', '<=', $now)
            ->select('t.*', 'r.Request_No', 'r.Request_Type', 'r.Description', 'r.Location', 'r.Company_ID', 'r.Department', 'r.Requester_Id')
            ->get();

        $escalatedCount = 0;
        $remindedCount = 0;

        foreach ($overdueTasks as $task) {
            $snapshot = json_decode((string) ($task->Snapshot ?? ''), true);
            $intervalHours = (int) ($snapshot['Step']['Escalation_Interval_Hours'] ?? 24);
            if ($intervalHours < 1) {
                $intervalHours = 24;
            }

            $isFirstEscalation = empty($task->Escalated_At);
            $shouldRemind = $isFirstEscalation || (
                !empty($task->Last_Reminder_At) &&
                Carbon::parse($task->Last_Reminder_At)->lte($now->copy()->subHours($intervalHours))
            );

            if (!$shouldRemind) {
                continue;
            }

            $candidates = DB::table('approval_task_candidate')
                ->where('Task_ID', (int) $task->ID)
                ->where('Status', 'ACTIVE')
                ->get()
                ->map(function ($c) {
                    return [
                        'User_Id'        => (int) $c->User_Id,
                        'Candidate_Type' => $c->Candidate_Type,
                        'Delegation_ID'  => $c->Delegation_ID,
                    ];
                })
                ->all();

            $requestObj = (object) [
                'ID'           => (int) $task->Request_ID,
                'Request_No'   => $task->Request_No,
                'Request_Type' => $task->Request_Type,
                'Description'  => '[OVERDUE SLA] ' . ($task->Description ?? ''),
                'Location'     => $task->Location,
                'Company_ID'   => $task->Company_ID,
                'Department'   => $task->Department,
                'Requester_Id' => $task->Requester_Id,
            ];

            if (!empty($candidates)) {
                $this->notifications->notifyTaskCandidates($task, $requestObj, $candidates);
            }

            $update = [
                'Last_Reminder_At' => $now,
                'Reminder_Count'   => ((int) ($task->Reminder_Count ?? 0)) + 1,
                'Updated_At'       => $now,
            ];

            if ($isFirstEscalation) {
                $update['Escalated_At'] = $now;
                $escalatedCount++;
            } else {
                $remindedCount++;
            }

            DB::table('approval_request_task')->where('ID', (int) $task->ID)->update($update);

            DB::table('approval_log')->insert([
                'Request_Id'   => (int) $task->Request_ID,
                'Approver_Id'  => 0,
                'Action'       => $isFirstEscalation ? 'SLA_ESCALATED' : 'SLA_REMINDER_SENT',
                'Level_Number' => (int) $task->Level_Number,
                'Stage_Order'  => (int) $task->Stage_Order,
                'Task_ID'      => (int) $task->ID,
                'Position_ID'  => (int) $task->Position_ID,
                'Notes'        => $isFirstEscalation
                    ? 'Task melewati batas SLA (' . $task->Due_At . ') dan ditandai untuk eskalasi.'
                    : 'Reminder ulang SLA ke-' . $update['Reminder_Count'] . ' dikirim.',
                'Metadata'     => json_encode(['Due_At' => $task->Due_At, 'Interval_Hours' => $intervalHours]),
                'Action_Date'  => $now,
            ]);
        }

        $this->info("SLA check selesai: {$escalatedCount} task baru dieskalasi, {$remindedCount} reminder ulang dikirim.");
        return 0;
    }
}
