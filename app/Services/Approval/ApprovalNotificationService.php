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
    protected $mailService;

    public function __construct(
        NotificationService $notifications,
        ApprovalAuthorizationService $authorization,
        ApprovalMailService $mailService
    ) {
        $this->notifications = $notifications;
        $this->authorization = $authorization;
        $this->mailService = $mailService;
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

        // Kirimkan notifikasi Email Real-Time modern ke semua kandidat approver
        try {
            $this->mailService->sendTaskNotification($task, $request, $candidates);
        } catch (\Throwable $mailEx) {
            Log::warning('ApprovalNotificationService email candidate error: ' . $mailEx->getMessage());
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

        try {
            $this->mailService->sendStatusDecisionNotification($request, 'RETURNED', $notes);
        } catch (\Throwable $mailEx) {
            Log::warning('ApprovalNotificationService email returned error: ' . $mailEx->getMessage());
        }
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

        try {
            $this->mailService->sendStatusDecisionNotification($request, 'REJECTED', $reason);
        } catch (\Throwable $mailEx) {
            Log::warning('ApprovalNotificationService email rejected error: ' . $mailEx->getMessage());
        }
    }

    public function notifyApproved($request, int $requesterId, string $reason = ''): void
    {
        $this->safeNotify(
            'Approval Approved #' . ($request->Request_No ?: $request->ID),
            'Selamat, pengajuan Anda telah disetujui sepenuhnya.',
            'APPROVAL_APPROVED',
            $requesterId,
            [
                'Request_ID' => (int) $request->ID,
                'Revision_No' => (int) $request->Revision_No,
                'Reason' => $reason,
            ]
        );

        try {
            $this->mailService->sendStatusDecisionNotification($request, 'APPROVED', $reason);
        } catch (\Throwable $mailEx) {
            Log::warning('ApprovalNotificationService email approved error: ' . $mailEx->getMessage());
        }
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

        $position = DB::table('master_position')
            ->where('ID', (int) $task->Position_ID)
            ->first();

        // 1. Pemegang posisi dari data kepegawaian (employment) + akun users.
        if ($position && (!empty($position->Initial) || !empty($position->Position))) {
            $employment = DB::table('employment as e')
                ->join('users as u', function ($join) {
                    $join->on('u.Emp_ID', '=', 'e.NIK')
                        ->orOn('u.username', '=', 'e.NIK');
                })
                ->where('u.Status', 'ACTIVE')
                ->where(function ($sub) use ($request) {
                    $sub->where('e.Location', $request->Location)
                        ->orWhere('u.Location', $request->Location);
                })
                ->where(function ($sub) use ($request) {
                    $sub->where('u.Company_ID', $request->Company_ID)
                        ->orWhereNull('u.Company_ID')
                        ->orWhere('u.Company_ID', '');
                })
                ->where(function ($sub) {
                    $sub->whereNull('e.Status')->orWhere('e.Status', 'ACTIVE');
                })
                ->where(function ($sub) use ($today) {
                    $sub->whereNull('e.End_Date')
                        ->orWhere('e.End_Date', '')
                        ->orWhere('e.End_Date', '0000-00-00')
                        ->orWhere('e.End_Date', '>=', $today);
                })
                ->where(function ($sub) use ($position) {
                    $hasCondition = false;

                    if (!empty($position->Initial)) {
                        $sub->where('e.Position', $position->Initial);
                        $hasCondition = true;
                    }
                    if (!empty($position->Position)) {
                        if ($hasCondition) {
                            $sub->orWhere('e.Position', $position->Position);
                        } else {
                            $sub->where('e.Position', $position->Position);
                        }
                    }
                });

            if (!empty($position->Department)) {
                $employment->where(function ($sub) use ($position) {
                    $sub->whereNull('e.Department')
                        ->orWhere('e.Department', '')
                        ->orWhere('e.Department', $position->Department);
                });
            }

            $recipients = array_merge($recipients, $employment->distinct()->pluck('u.id')->all());
        }

        // 2. Assignment aktif (PRIMARY/ACTING) pada posisi dan level yang sama.
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

        $recipients = array_merge($recipients, $assignments);

        // 3. Bagan organisasi (organization_structure) sebagai cadangan.
        $org = DB::table('organization_structure as o')
            ->join('users as u', function ($join) {
                $join->on('u.Emp_ID', '=', 'o.NIK')
                    ->orOn('u.username', '=', 'o.NIK');
            })
            ->where('u.Status', 'ACTIVE')
            ->where('u.Location', $request->Location)
            ->where(function ($sub) use ($request) {
                $sub->where('u.Company_ID', $request->Company_ID)
                    ->orWhereNull('u.Company_ID')
                    ->orWhere('u.Company_ID', '');
            })
            ->whereNotNull('o.NIK')
            ->where('o.NIK', '<>', '')
            ->where(function ($sub) use ($task) {
                $sub->where('o.Position_ID', (int) $task->Position_ID);

                if (!empty($task->Position_Code)) {
                    $sub->orWhere('o.Position', $task->Position_Code);
                }
            })
            ->pluck('u.id')
            ->all();

        $recipients = array_merge($recipients, $org);

        // 4. Legacy: sebagian akun lama hanya mengisi users.Position.
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