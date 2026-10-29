<?php

namespace App\Services\Approval;

use App\Services\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalNotificationService
{
    protected $notifications;
    protected $authorization;

    public function __construct(NotificationService $notifications, ApprovalAuthorizationService $authorization)
    {
        $this->notifications = $notifications;
        $this->authorization = $authorization;
    }

    public function notifyTaskCandidates($task, $request, array $candidates): void
    {
        foreach ($candidates as $candidate) {
            $metadata = [
                'Task_ID' => (int) $task->ID,
                'Request_ID' => (int) $request->ID,
                'Stage_Order' => (int) $task->Stage_Order,
                'Position_ID' => (int) $task->Position_ID,
            ];

            $this->safeNotify(
                'Approval Task #' . $task->ID,
                'Anda ditunjuk sebagai approver untuk posisi ' . ($task->Position_Name ?: $task->Position_Code)
                    . ' level ' . $task->Level_Number . ' pada request ' . ($request->Request_No ?: $request->ID) . '.',
                'APPROVAL_TASK',
                (int) $candidate['User_Id'],
                $metadata
            );

            DB::table('approval_task_candidate')
                ->where('Task_ID', (int) $task->ID)
                ->where('User_Id', (int) $candidate['User_Id'])
                ->where('Candidate_Type', $candidate['Type'])
                ->update(['Notified_At' => Carbon::now()]);
        }
    }

    public function notifyHeld($task, $request, string $reason): array
    {
        $recipients = $this->heldRecipients($task, $request);

        if (empty($recipients)) {
            DB::table('approval_held_notification')->insert([
                'Task_ID' => (int) $task->ID,
                'User_Id' => null,
                'Channel' => 'SYSTEM',
                'Notified_At' => Carbon::now(),
                'Metadata' => json_encode([
                    'Reason' => $reason,
                    'Note' => 'NO_RECIPIENT',
                    'Request_ID' => (int) $request->ID,
                ]),
            ]);

            Log::warning('Approval HELD tanpa penerima', [
                'Task_ID' => $task->ID,
                'Request_ID' => $request->ID,
                'Reason' => $reason,
            ]);

            return [];
        }

        foreach ($recipients as $userId) {
            DB::table('approval_held_notification')->insert([
                'Task_ID' => (int) $task->ID,
                'User_Id' => (int) $userId,
                'Channel' => 'SYSTEM',
                'Notified_At' => Carbon::now(),
                'Metadata' => json_encode([
                    'Reason' => $reason,
                    'Request_ID' => (int) $request->ID,
                    'Position_ID' => (int) $task->Position_ID,
                    'Level_Number' => (int) $task->Level_Number,
                ]),
            ]);

            $this->safeNotify(
                'Approval HELD #' . $task->ID,
                'Task approval untuk posisi ' . ($task->Position_Name ?: $task->Position_Code)
                    . ' level ' . $task->Level_Number . ' sedang HELD. Mohon segera ditindaklanjuti (SLA ASAP).',
                'APPROVAL_HELD',
                (int) $userId,
                [
                    'Task_ID' => (int) $task->ID,
                    'Request_ID' => (int) $request->ID,
                    'Reason' => $reason,
                ]
            );
        }

        return $recipients;
    }

    public function notifyReturned($request, int $requesterId, string $notes = ''): void
    {
        $this->safeNotify(
            'Approval Returned #' . ($request->Request_No ?: $request->ID),
            'Request Anda dikembalikan untuk diperbaiki. ' . $notes,
            'APPROVAL_RETURNED',
            $requesterId,
            [
                'Request_ID' => (int) $request->ID,
                'Revision_No' => (int) $request->Revision_No,
            ]
        );
    }

    public function notifyRejected($request, int $requesterId, string $reason = ''): void
    {
        $this->safeNotify(
            'Approval Rejected #' . ($request->Request_No ?: $request->ID),
            'Request Anda ditolak. ' . $reason,
            'APPROVAL_REJECTED',
            $requesterId,
            [
                'Request_ID' => (int) $request->ID,
                'Revision_No' => (int) $request->Revision_No,
                'Reason' => $reason,
            ]
        );
    }

    public function notifyAutoApproved($task, $request, string $reason): void
    {
        $this->safeNotify(
            'Auto Approved #' . ($request->Request_No ?: $request->ID),
            'Task ' . ($task->Position_Name ?: $task->Position_Code)
                . ' level ' . $task->Level_Number . ' otomatis disetujui. Alasan: ' . $reason . '.',
            'APPROVAL_AUTO',
            (int) $request->Requester_Id,
            [
                'Task_ID' => (int) $task->ID,
                'Request_ID' => (int) $request->ID,
                'Reason' => $reason,
            ]
        );
    }

    /**
     * HELD recipients must be active holders of the exact same position and
     * level, in the same Location/Company. If the position is vacant there is
     * no such holder, so we fall back to the wildcard admin escalation queue.
     */
    public function heldRecipients($task, $request): array
    {
        $today = Carbon::now()->toDateString();
        $recipients = [];

        $assignments = DB::table('position_assignment as pa')
            ->join('master_position as mp', 'pa.Position_ID', '=', 'mp.ID')
            ->join('users as u', 'pa.User_Id', '=', 'u.id')
            ->where('pa.Position_ID', (int) $task->Position_ID)
            ->where('mp.Level', (int) $task->Level_Number)
            ->where('pa.Status', 'ACTIVE')
            ->where('u.Status', 'ACTIVE')
            ->where('pa.Location', $request->Location)
            ->where('pa.Company_ID', $request->Company_ID)
            ->where(function ($query) use ($today) {
                $query->whereNull('pa.Start_Date')->orWhere('pa.Start_Date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('pa.End_Date')->orWhere('pa.End_Date', '>=', $today);
            })
            ->pluck('u.id')
            ->all();

        if (!empty($assignments)) {
            $recipients = array_merge($recipients, $assignments);
        }

        if (!empty($task->Position_Code)) {
            $legacy = DB::table('users')
                ->where('Status', 'ACTIVE')
                ->where('Position', $task->Position_Code)
                ->where('Location', $request->Location)
                ->where('Company_ID', $request->Company_ID)
                ->pluck('id')
                ->all();

            if (!empty($legacy)) {
                $recipients = array_merge($recipients, $legacy);
            }
        }

        $recipients = array_values(array_unique(array_map('intval', $recipients)));

        if (empty($recipients)) {
            $recipients = $this->authorization->wildcardAdminUserIds();
        }

        return $recipients;
    }

    private function safeNotify(string $title, string $content, string $type, int $recipientId, array $metadata): void
    {
        try {
            $this->notifications->makeNotification(
                $title,
                $content,
                $type,
                null,
                $recipientId,
                'SENT',
                json_encode($metadata)
            );
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengirim notifikasi approval', [
                'message' => $exception->getMessage(),
                'recipient' => $recipientId,
                'title' => $title,
            ]);
        }
    }
}