<?php

namespace App\Services\GA;

use App\Services\Approval\ApprovalWorkflowService;
use App\Services\HCGA\AssetLifecycleService;
use App\Services\HCGA\AssetTicketingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service terpadu modul IT Support & Helpdesk:
 * - Tiket Bantuan IT (IT_SUPPORT)
 * - Gangguan Sistem (IT_INCIDENT)
 * - Pengajuan Perangkat IT (IT_DEVICE_REQUEST -> Approval Manager -> asset_list / PR)
 * - Action_Type: PREVENTIVE / CORRECTIVE
 * - Tracking Stepper, Root Cause, Knowledge Base sederhana, User Feedback, Monitoring & Backup Logs
 */
class ItTicketService
{
    /** @var TicketService */
    protected $tickets;

    /** @var ApprovalWorkflowService */
    protected $workflow;

    /** @var GaNotificationService */
    protected $notifications;

    /** @var AssetLifecycleService */
    protected $lifecycle;

    /** @var AssetTicketingService */
    protected $assetTicketing;

    public function __construct(
        TicketService $tickets,
        ApprovalWorkflowService $workflow,
        GaNotificationService $notifications,
        AssetLifecycleService $lifecycle,
        AssetTicketingService $assetTicketing
    ) {
        $this->tickets = $tickets;
        $this->workflow = $workflow;
        $this->notifications = $notifications;
        $this->lifecycle = $lifecycle;
        $this->assetTicketing = $assetTicketing;
    }

    public function create(array $data, $user, array $files = [])
    {
        $workflowMode = strtoupper(trim((string) ($data['Workflow_Mode'] ?? TicketService::MODE_IT_SUPPORT)));
        $validModes = [
            TicketService::MODE_IT_SUPPORT,
            TicketService::MODE_IT_INCIDENT,
            TicketService::MODE_IT_DEVICE_REQUEST,
        ];
        if (!in_array($workflowMode, $validModes, true)) {
            throw new InvalidArgumentException('Tipe kebutuhan IT tidak valid.');
        }

        $actionType = strtoupper(trim((string) ($data['Action_Type'] ?? TicketService::ACTION_CORRECTIVE)));
        if (!in_array($actionType, [TicketService::ACTION_PREVENTIVE, TicketService::ACTION_CORRECTIVE], true)) {
            throw new InvalidArgumentException('Action Type harus PREVENTIVE atau CORRECTIVE.');
        }

        $payload = $data;
        $payload['Domain_Type'] = TicketService::DOMAIN_IT;
        $payload['Workflow_Mode'] = $workflowMode;
        $payload['Action_Type'] = $actionType;
        $payload['Source'] = strtoupper(trim((string) ($data['Source'] ?? 'FORM')));

        if (empty($payload['Category_ID'])) {
            $payload['Category_ID'] = $this->resolveDefaultItCategoryId($workflowMode, $actionType);
        }

        if ($workflowMode === TicketService::MODE_IT_DEVICE_REQUEST) {
            if (empty($payload['Device_Spec'])) {
                $payload['Device_Spec'] = trim((string) ($payload['Description'] ?? ''));
            }
            $payload['Skip_Auto_Assign'] = true;
            $payload['Suppress_Create_Notification'] = true;
        }

        $ticket = $this->tickets->create($payload, $user);

        if (!empty($files)) {
            $this->tickets->attachFiles((int) $ticket->ID, $files, (string) $user->username);
        }

        if ($workflowMode === TicketService::MODE_IT_DEVICE_REQUEST) {
            $this->submitDeviceRequestApproval((int) $ticket->ID, $ticket, $user);
        } else {
            // Pastikan tiket bantuan/gangguan yang baru dibuat memiliki tim IT yang ternotifikasi
            if (empty($ticket->Assignee_Team_ID) && empty($ticket->Assignee_User_Id)) {
                $defaultTeamId = $this->resolveDefaultItTeamId($workflowMode);
                if ($defaultTeamId) {
                    $this->tickets->assignRecord(
                        (int) $ticket->ID,
                        'TEAM',
                        $defaultTeamId,
                        null,
                        null,
                        (string) $user->username,
                        'Routing otomatis antrean IT Support.',
                        Carbon::now(),
                        true
                    );
                }
            }
        }

        return $this->tickets->find((int) $ticket->ID);
    }

    public function syncApprovalStatus(int $approvalRequestId)
    {
        $detail = DB::table('ga_ticket')->where('Dept_Approval_Request_ID', $approvalRequestId)->first();
        if (!$detail) {
            $mapTo = DB::table('approval_request')->where('ID', $approvalRequestId)->value('Map_To');
            if ($mapTo) {
                $detail = DB::table('ga_ticket')->where('Ticket_ID', (int) $mapTo)->first();
            }
        }
        if (!$detail || strtoupper((string) $detail->Domain_Type) !== TicketService::DOMAIN_IT) {
            return null;
        }
        if ((int) $detail->Dept_Approval_Request_ID !== $approvalRequestId) {
            return $this->tickets->find((int) $detail->Ticket_ID);
        }

        $ticket = DB::table('ticket')->where('ID', (int) $detail->Ticket_ID)->first();
        $request = DB::table('approval_request')->where('ID', $approvalRequestId)->first();
        if (!$ticket || !$request) {
            return null;
        }

        $currentStatus = strtoupper((string) $ticket->Ticket_Status);
        if ($currentStatus !== TicketService::STATUS_WAITING_MANAGER_APPROVAL) {
            return $this->tickets->find((int) $ticket->ID);
        }

        $reqStatus = strtoupper((string) $request->Status);
        $now = Carbon::now();

        if ($reqStatus === 'APPROVED') {
            DB::transaction(function () use ($ticket, $now) {
                DB::table('ticket')->where('ID', (int) $ticket->ID)->update([
                    'Ticket_Status' => TicketService::STATUS_OPEN,
                ]);
                DB::table('ga_ticket')->where('Ticket_ID', (int) $ticket->ID)->update([
                    'Updated_By' => 'system',
                    'Updated_At' => $now,
                ]);
                $this->tickets->recordHistory(
                    (int) $ticket->ID,
                    TicketService::STATUS_WAITING_MANAGER_APPROVAL,
                    TicketService::STATUS_OPEN,
                    'Disetujui Manager, masuk ke antrean IT Support.',
                    'system',
                    $now
                );

                $teamId = $this->resolveDefaultItTeamId(TicketService::MODE_IT_DEVICE_REQUEST);
                if ($teamId) {
                    $this->tickets->assignRecord(
                        (int) $ticket->ID,
                        'TEAM',
                        $teamId,
                        null,
                        null,
                        'system',
                        'Routing otomatis ke IT Support setelah approval Manager.',
                        $now,
                        true
                    );
                }
            });

            $fresh = $this->tickets->find((int) $ticket->ID);
            if ($fresh && !empty($fresh->Reporter_User_Id)) {
                $this->notifications->notifyUser(
                    (int) $fresh->Reporter_User_Id,
                    'Pengajuan Perangkat IT disetujui: ' . $fresh->Ticket_No,
                    'Pengajuan perangkat "' . $fresh->Ticket_Subject . '" telah disetujui Manager dan masuk antrean IT.',
                    'it_ticket',
                    ['Ticket_ID' => (int) $fresh->ID, 'Ticket_No' => $fresh->Ticket_No]
                );
            }

            return $fresh;
        }

        if (in_array($reqStatus, ['REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            $reason = $this->latestApprovalNote($approvalRequestId) ?: 'Ditolak oleh Manager.';
            DB::transaction(function () use ($ticket, $reason, $now) {
                DB::table('ticket')->where('ID', (int) $ticket->ID)->update([
                    'Ticket_Status' => TicketService::STATUS_REJECTED,
                ]);
                DB::table('ga_ticket')->where('Ticket_ID', (int) $ticket->ID)->update([
                    'Rejection_Reason' => mb_substr($reason, 0, 2000),
                    'Updated_By'       => 'system',
                    'Updated_At'       => $now,
                ]);
                $this->tickets->recordHistory(
                    (int) $ticket->ID,
                    TicketService::STATUS_WAITING_MANAGER_APPROVAL,
                    TicketService::STATUS_REJECTED,
                    'Pengajuan ditolak Manager: ' . $reason,
                    'system',
                    $now
                );
            });

            $fresh = $this->tickets->find((int) $ticket->ID);
            if ($fresh && !empty($fresh->Reporter_User_Id)) {
                $this->notifications->notifyUser(
                    (int) $fresh->Reporter_User_Id,
                    'Pengajuan Perangkat IT ditolak: ' . $fresh->Ticket_No,
                    'Pengajuan perangkat "' . $fresh->Ticket_Subject . '" ditolak: ' . $reason,
                    'it_ticket',
                    ['Ticket_ID' => (int) $fresh->ID, 'Ticket_No' => $fresh->Ticket_No]
                );
            }

            return $fresh;
        }

        return $this->tickets->find((int) $ticket->ID);
    }

    public function allowedTransitions(string $workflowMode, string $status): array
    {
        $workflowMode = strtoupper(trim($workflowMode));
        $status = strtoupper(trim($status));

        if ($workflowMode === TicketService::MODE_IT_INCIDENT) {
            $map = [
                TicketService::STATUS_OPEN => [TicketService::STATUS_VALIDATED, TicketService::STATUS_REJECTED],
                TicketService::STATUS_VALIDATED => [TicketService::STATUS_ROOT_CAUSE_ANALYSIS, TicketService::STATUS_ON_HOLD, TicketService::STATUS_REJECTED],
                TicketService::STATUS_ROOT_CAUSE_ANALYSIS => [TicketService::STATUS_RECOVERY, TicketService::STATUS_ON_HOLD],
                TicketService::STATUS_RECOVERY => [TicketService::STATUS_TESTING, TicketService::STATUS_ON_HOLD],
                TicketService::STATUS_TESTING => [TicketService::STATUS_SYSTEM_NORMAL, TicketService::STATUS_RECOVERY],
                TicketService::STATUS_SYSTEM_NORMAL => [TicketService::STATUS_CLOSED, TicketService::STATUS_REOPENED],
                TicketService::STATUS_ON_HOLD => [TicketService::STATUS_VALIDATED, TicketService::STATUS_ROOT_CAUSE_ANALYSIS, TicketService::STATUS_RECOVERY, TicketService::STATUS_TESTING],
                TicketService::STATUS_CLOSED => [TicketService::STATUS_REOPENED],
                TicketService::STATUS_REJECTED => [TicketService::STATUS_REOPENED],
                TicketService::STATUS_REOPENED => [TicketService::STATUS_VALIDATED, TicketService::STATUS_ROOT_CAUSE_ANALYSIS, TicketService::STATUS_RECOVERY],
            ];
            return $map[$status] ?? [];
        }

        if ($workflowMode === TicketService::MODE_IT_DEVICE_REQUEST) {
            $map = [
                TicketService::STATUS_WAITING_MANAGER_APPROVAL => [TicketService::STATUS_REJECTED],
                TicketService::STATUS_OPEN => [TicketService::STATUS_DEVICE_PREPARATION, TicketService::STATUS_ON_HOLD, TicketService::STATUS_REJECTED],
                TicketService::STATUS_DEVICE_PREPARATION => [TicketService::STATUS_ASSIGNED_TO_USER, TicketService::STATUS_ON_HOLD, TicketService::STATUS_REJECTED],
                TicketService::STATUS_ON_HOLD => [TicketService::STATUS_DEVICE_PREPARATION, TicketService::STATUS_ASSIGNED_TO_USER, TicketService::STATUS_REJECTED],
                TicketService::STATUS_ASSIGNED_TO_USER => [TicketService::STATUS_HANDOVER, TicketService::STATUS_DEVICE_PREPARATION],
                TicketService::STATUS_HANDOVER => [TicketService::STATUS_USER_CONFIRMED, TicketService::STATUS_CLOSED, TicketService::STATUS_REOPENED],
                TicketService::STATUS_USER_CONFIRMED => [TicketService::STATUS_CLOSED],
                TicketService::STATUS_CLOSED => [TicketService::STATUS_REOPENED],
                TicketService::STATUS_REOPENED => [TicketService::STATUS_DEVICE_PREPARATION, TicketService::STATUS_ASSIGNED_TO_USER],
            ];
            return $map[$status] ?? [];
        }

        // Default: IT_SUPPORT
        $map = [
            TicketService::STATUS_OPEN => [TicketService::STATUS_IN_PROGRESS, TicketService::STATUS_ON_HOLD, TicketService::STATUS_REJECTED],
            TicketService::STATUS_IN_PROGRESS => [TicketService::STATUS_TROUBLESHOOTING, TicketService::STATUS_ESCALATED, TicketService::STATUS_RESOLVED, TicketService::STATUS_ON_HOLD],
            TicketService::STATUS_TROUBLESHOOTING => [TicketService::STATUS_RESOLVED, TicketService::STATUS_ESCALATED, TicketService::STATUS_ON_HOLD],
            TicketService::STATUS_ESCALATED => [TicketService::STATUS_IN_PROGRESS, TicketService::STATUS_TROUBLESHOOTING, TicketService::STATUS_RESOLVED, TicketService::STATUS_ON_HOLD],
            TicketService::STATUS_ON_HOLD => [TicketService::STATUS_IN_PROGRESS, TicketService::STATUS_TROUBLESHOOTING, TicketService::STATUS_ESCALATED, TicketService::STATUS_RESOLVED],
            TicketService::STATUS_RESOLVED => [TicketService::STATUS_USER_CONFIRMED, TicketService::STATUS_CLOSED, TicketService::STATUS_REOPENED],
            TicketService::STATUS_USER_CONFIRMED => [TicketService::STATUS_CLOSED, TicketService::STATUS_REOPENED],
            TicketService::STATUS_CLOSED => [TicketService::STATUS_REOPENED],
            TicketService::STATUS_REJECTED => [TicketService::STATUS_REOPENED],
            TicketService::STATUS_REOPENED => [TicketService::STATUS_IN_PROGRESS, TicketService::STATUS_TROUBLESHOOTING, TicketService::STATUS_RESOLVED],
        ];

        return $map[$status] ?? [];
    }

    public function transition(int $ticketId, string $toStatus, array $data, $user)
    {
        $toStatus = strtoupper(trim($toStatus));

        return DB::transaction(function () use ($ticketId, $toStatus, $data, $user) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            if (!$ticket || (string) $ticket->Module !== 'GA') {
                throw new InvalidArgumentException('Tiket IT tidak ditemukan.');
            }
            $detail = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first();
            if (!$detail || strtoupper((string) $detail->Domain_Type) !== TicketService::DOMAIN_IT) {
                throw new InvalidArgumentException('Tiket ini bukan tiket IT Support.');
            }

            $mode = strtoupper((string) ($detail->Workflow_Mode ?? TicketService::MODE_IT_SUPPORT));
            $from = strtoupper((string) $ticket->Ticket_Status);
            $allowed = $this->allowedTransitions($mode, $from);
            if (!in_array($toStatus, $allowed, true)) {
                throw new InvalidArgumentException('Transisi status IT ' . $from . ' ke ' . $toStatus . ' tidak diizinkan.');
            }

            $note = trim((string) ($data['note'] ?? $data['Note'] ?? ''));
            $diagnosis = trim((string) ($data['Diagnosis_Note'] ?? $note));
            $rootCause = trim((string) ($data['Root_Cause'] ?? $note));
            $recovery = trim((string) ($data['Recovery_Note'] ?? $note));
            $testingNote = trim((string) ($data['Testing_Note'] ?? $note));

            if ($toStatus === TicketService::STATUS_TROUBLESHOOTING && $diagnosis === '') {
                throw new InvalidArgumentException('Catatan diagnosa masalah wajib diisi.');
            }
            if ($toStatus === TicketService::STATUS_ROOT_CAUSE_ANALYSIS && $rootCause === '') {
                throw new InvalidArgumentException('Analisis Root Cause wajib diisi.');
            }
            if ($toStatus === TicketService::STATUS_RECOVERY && $recovery === '') {
                throw new InvalidArgumentException('Catatan perbaikan / recovery wajib diisi.');
            }
            if ($toStatus === TicketService::STATUS_TESTING && $testingNote === '') {
                throw new InvalidArgumentException('Catatan pengujian sistem wajib diisi.');
            }
            if ($toStatus === TicketService::STATUS_RESOLVED && $note === '' && trim((string) ($data['Kb_Solution'] ?? '')) === '') {
                throw new InvalidArgumentException('Catatan penyelesaian masalah wajib diisi.');
            }
            if ($toStatus === TicketService::STATUS_REJECTED && $note === '') {
                throw new InvalidArgumentException('Alasan penolakan wajib diisi.');
            }
            if ($toStatus === TicketService::STATUS_CLOSED && $from === TicketService::STATUS_RESOLVED && empty($detail->User_Confirmed_At) && $note === '') {
                throw new InvalidArgumentException('Catatan penutupan wajib diisi jika menutup tiket sebelum konfirmasi user.');
            }

            $now = Carbon::now();
            $headerUpdates = ['Ticket_Status' => $toStatus];
            if (in_array($toStatus, [TicketService::STATUS_RESOLVED, TicketService::STATUS_SYSTEM_NORMAL], true)) {
                $summaryNote = $note !== '' ? $note : trim((string) ($data['Kb_Solution'] ?? $testingNote));
                $headerUpdates['Completion_Note'] = mb_substr($summaryNote, 0, 150);
            }
            DB::table('ticket')->where('ID', $ticketId)->update($headerUpdates);

            $gaUpdates = [
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ];

            if (empty($detail->First_Response_At) && (int) $detail->Reporter_User_Id !== (int) $user->id) {
                $gaUpdates['First_Response_At'] = $now;
            }
            if ($toStatus === TicketService::STATUS_TROUBLESHOOTING) {
                $gaUpdates['Diagnosis_Note'] = $diagnosis;
            }
            if ($toStatus === TicketService::STATUS_ROOT_CAUSE_ANALYSIS) {
                $gaUpdates['Root_Cause'] = $rootCause;
            }
            if ($toStatus === TicketService::STATUS_RECOVERY) {
                if ($from === TicketService::STATUS_TESTING) {
                    $gaUpdates['Testing_Status'] = 'NOT_NORMAL';
                    $gaUpdates['Testing_Note'] = $testingNote !== '' ? $testingNote : $recovery;
                    $gaUpdates['Testing_At'] = $now;
                }
                $gaUpdates['Recovery_Note'] = $recovery;
            }
            if ($toStatus === TicketService::STATUS_TESTING) {
                $gaUpdates['Testing_Status'] = 'PENDING';
                $gaUpdates['Testing_Note'] = $testingNote;
                $gaUpdates['Testing_At'] = $now;
            }
            if ($toStatus === TicketService::STATUS_SYSTEM_NORMAL) {
                $gaUpdates['Testing_Status'] = 'NORMAL';
                $gaUpdates['Testing_Note'] = $testingNote !== '' ? $testingNote : 'Sistem normal setelah pengujian.';
                $gaUpdates['Testing_At'] = $now;
                $gaUpdates['Resolved_At'] = $now;
            }
            if ($toStatus === TicketService::STATUS_RESOLVED) {
                $gaUpdates['Resolved_At'] = $now;
            }
            if ($toStatus === TicketService::STATUS_CLOSED) {
                $gaUpdates['Closed_At'] = $now;
            }
            if ($toStatus === TicketService::STATUS_REJECTED) {
                $gaUpdates['Rejection_Reason'] = mb_substr($note, 0, 2000);
            }
            if ($toStatus === TicketService::STATUS_ON_HOLD) {
                $gaUpdates['On_Hold_At'] = $now;
            }
            if ($toStatus === TicketService::STATUS_REOPENED) {
                $gaUpdates['Reopen_Count'] = DB::raw('Reopen_Count + 1');
                $gaUpdates['Resolved_At'] = null;
                $gaUpdates['Closed_At'] = null;
                $gaUpdates['On_Hold_At'] = null;
            }
            if ($from === TicketService::STATUS_ON_HOLD && $toStatus !== TicketService::STATUS_ON_HOLD && !empty($detail->On_Hold_At)) {
                $holdMinutes = max(0, Carbon::parse($detail->On_Hold_At)->diffInMinutes($now));
                $gaUpdates['Total_Hold_Minutes'] = (int) ($detail->Total_Hold_Minutes ?? 0) + $holdMinutes;
                $gaUpdates['On_Hold_At'] = null;
                if ($holdMinutes > 0) {
                    if (!empty($detail->Response_Due_At)) {
                        $gaUpdates['Response_Due_At'] = Carbon::parse($detail->Response_Due_At)->addMinutes($holdMinutes);
                    }
                    if (!empty($detail->Resolve_Due_At)) {
                        $gaUpdates['Resolve_Due_At'] = Carbon::parse($detail->Resolve_Due_At)->addMinutes($holdMinutes);
                    }
                }
            }

            if (!empty($data['Kb_Flag'])) {
                $gaUpdates['Kb_Flag'] = 1;
                $gaUpdates['Kb_Title'] = mb_substr(trim((string) ($data['Kb_Title'] ?? $ticket->Ticket_Subject)), 0, 200);
                $gaUpdates['Kb_Solution'] = trim((string) ($data['Kb_Solution'] ?? ($note !== '' ? $note : $recovery)));
            }
            if (array_key_exists('Monitoring_Note', $data) && trim((string) $data['Monitoring_Note']) !== '') {
                $gaUpdates['Monitoring_Note'] = trim((string) $data['Monitoring_Note']);
            }
            if (!empty($data['Backup_Verified'])) {
                $gaUpdates['Backup_Verified'] = 1;
                $gaUpdates['Backup_Verified_At'] = $now;
                $gaUpdates['Backup_Note'] = trim((string) ($data['Backup_Note'] ?? 'Backup terverifikasi sebelum penutupan.'));
            } elseif (array_key_exists('Backup_Note', $data) && trim((string) $data['Backup_Note']) !== '') {
                $gaUpdates['Backup_Note'] = trim((string) $data['Backup_Note']);
            }

            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update($gaUpdates);

            $historyNote = $note;
            if ($historyNote === '') {
                if ($toStatus === TicketService::STATUS_TROUBLESHOOTING) {
                    $historyNote = $diagnosis;
                } elseif ($toStatus === TicketService::STATUS_ROOT_CAUSE_ANALYSIS) {
                    $historyNote = 'Root Cause: ' . $rootCause;
                } elseif ($toStatus === TicketService::STATUS_RECOVERY) {
                    $historyNote = ($from === TicketService::STATUS_TESTING ? '[Perbaikan Ulang] ' : '') . $recovery;
                } elseif ($toStatus === TicketService::STATUS_TESTING) {
                    $historyNote = $testingNote;
                } elseif ($toStatus === TicketService::STATUS_SYSTEM_NORMAL) {
                    $historyNote = 'Sistem normal: ' . $gaUpdates['Testing_Note'];
                }
            }
            $this->tickets->recordHistory($ticketId, $from, $toStatus, $historyNote !== '' ? $historyNote : null, (string) $user->username, $now);

            $fresh = $this->tickets->find($ticketId);
            if ($fresh && !empty($fresh->Reporter_User_Id)) {
                $this->notifications->notifyUser(
                    (int) $fresh->Reporter_User_Id,
                    'Update tiket IT ' . $fresh->Ticket_No . ': ' . $toStatus,
                    'Tiket "' . $fresh->Ticket_Subject . '" berstatus ' . $this->tickets->statusLabel($toStatus) . ($historyNote !== '' ? '. Catatan: ' . $historyNote : '.'),
                    'it_ticket',
                    ['Ticket_ID' => $ticketId, 'Ticket_No' => $fresh->Ticket_No, 'Status' => $toStatus]
                );
            }

            return $fresh;
        });
    }

    public function escalate(int $ticketId, array $data, $user)
    {
        $escalationNote = trim((string) ($data['Escalation_Note'] ?? $data['note'] ?? ''));
        if ($escalationNote === '') {
            throw new InvalidArgumentException('Alasan / catatan eskalasi wajib diisi.');
        }

        $ticket = $this->tickets->find($ticketId);
        if (!$ticket || strtoupper((string) ($ticket->Domain_Type ?? '')) !== TicketService::DOMAIN_IT) {
            throw new InvalidArgumentException('Tiket IT tidak ditemukan.');
        }
        if (in_array(strtoupper((string) $ticket->Ticket_Status), [TicketService::STATUS_CLOSED, TicketService::STATUS_REJECTED], true)) {
            throw new InvalidArgumentException('Tiket yang sudah CLOSED/REJECTED tidak dapat dieskalasi.');
        }

        return DB::transaction(function () use ($ticketId, $ticket, $data, $user, $escalationNote) {
            $now = Carbon::now();
            $from = strtoupper((string) $ticket->Ticket_Status);
            $spawnedId = null;
            $spawnedNo = null;

            if (!empty($data['Spawn_Incident'])) {
                $incident = $this->create([
                    'Ticket_Subject'        => '[Eskalasi] ' . $ticket->Ticket_Subject,
                    'Description'           => "Eskalasi dari tiket {$ticket->Ticket_No}: {$escalationNote}\n\nDeskripsi awal: " . ($ticket->Ga_Description ?: $ticket->Description),
                    'Workflow_Mode'         => TicketService::MODE_IT_INCIDENT,
                    'Action_Type'           => TicketService::ACTION_CORRECTIVE,
                    'Priority'              => in_array(strtoupper((string) $ticket->Priority), ['HIGH', 'URGENT'], true) ? strtoupper((string) $ticket->Priority) : 'HIGH',
                    'Location'              => $ticket->Location,
                    'Company_ID'            => $ticket->Company_ID,
                    'Department'            => $ticket->Ga_Department ?: $ticket->Department,
                    'Cost_Center'           => $ticket->Cost_Center,
                    'On_Behalf_Of_User_Id'  => $ticket->Reporter_User_Id,
                ], $user);
                $spawnedId = (int) $incident->ID;
                $spawnedNo = (string) $incident->Ticket_No;
            }

            DB::table('ticket')->where('ID', $ticketId)->update([
                'Ticket_Status' => TicketService::STATUS_ESCALATED,
            ]);
            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                'Escalated_Flag'         => 1,
                'Escalation_Note'        => $escalationNote,
                'Escalated_To_Ticket_ID' => $spawnedId ?: $ticket->Escalated_To_Ticket_ID,
                'Updated_By'             => (string) $user->username,
                'Updated_At'             => $now,
            ]);

            $histNote = 'Eskalasi tiket: ' . $escalationNote . ($spawnedNo ? ' (Gangguan Sistem: ' . $spawnedNo . ')' : '');
            $this->tickets->recordHistory($ticketId, $from, TicketService::STATUS_ESCALATED, $histNote, (string) $user->username, $now);

            if (!empty($data['Assignee_User_Id'])) {
                $this->tickets->assignRecord($ticketId, 'USER', null, (int) $data['Assignee_User_Id'], null, (string) $user->username, $histNote, $now, true);
            } elseif (!empty($data['Assignee_Team_ID'])) {
                $this->tickets->assignRecord($ticketId, 'TEAM', (int) $data['Assignee_Team_ID'], null, null, (string) $user->username, $histNote, $now, true);
            }

            return $this->tickets->find($ticketId);
        });
    }

    public function submitUserFeedback(int $ticketId, array $data, $user)
    {
        $ticket = $this->tickets->find($ticketId);
        if (!$ticket || strtoupper((string) ($ticket->Domain_Type ?? '')) !== TicketService::DOMAIN_IT) {
            throw new InvalidArgumentException('Tiket IT tidak ditemukan.');
        }

        $status = strtoupper((string) $ticket->Ticket_Status);
        if (!in_array($status, [TicketService::STATUS_RESOLVED, TicketService::STATUS_HANDOVER], true)) {
            throw new InvalidArgumentException('Konfirmasi user hanya tersedia saat tiket berstatus RESOLVED atau HANDOVER.');
        }

        $isAdmin = strtoupper((string) ($user->Priv_ID ?? '')) === 'ADM'
            || strtolower((string) ($user->username ?? '')) === 'admin';
        $isReporter = ((int) ($ticket->Reporter_User_Id ?? 0) === (int) $user->id)
            || ((string) ($ticket->Created_By ?? '') === (string) $user->username);
        if (!$isReporter && !$isAdmin) {
            throw new InvalidArgumentException('Hanya pelapor tiket (atau Admin) yang dapat memberikan konfirmasi & feedback.');
        }

        $isResolved = filter_var($data['Is_Resolved'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $feedback = trim((string) ($data['User_Feedback'] ?? ''));
        $rating = isset($data['User_Rating']) && $data['User_Rating'] !== '' ? (int) $data['User_Rating'] : null;

        if ($isResolved && ($rating === null || $rating < 1 || $rating > 5)) {
            throw new InvalidArgumentException('Rating kepuasan (1-5) wajib dipilih saat mengonfirmasi selesai.');
        }
        if (!$isResolved && $feedback === '') {
            throw new InvalidArgumentException('Catatan feedback wajib diisi jika masalah belum selesai.');
        }

        return DB::transaction(function () use ($ticketId, $ticket, $status, $isResolved, $rating, $feedback, $user) {
            $now = Carbon::now();

            if ($isResolved) {
                DB::table('ticket')->where('ID', $ticketId)->update([
                    'Ticket_Status' => TicketService::STATUS_USER_CONFIRMED,
                ]);
                DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                    'User_Confirmed_At' => $now,
                    'User_Confirmed_By' => (string) $user->username,
                    'User_Rating'       => $rating,
                    'User_Feedback'     => $feedback !== '' ? $feedback : null,
                    'Updated_By'        => (string) $user->username,
                    'Updated_At'        => $now,
                ]);
                $this->tickets->recordHistory(
                    $ticketId,
                    $status,
                    TicketService::STATUS_USER_CONFIRMED,
                    'User mengonfirmasi selesai (Rating: ' . $rating . '/5)' . ($feedback !== '' ? ' - ' . $feedback : ''),
                    (string) $user->username,
                    $now
                );
            } else {
                DB::table('ticket')->where('ID', $ticketId)->update([
                    'Ticket_Status' => TicketService::STATUS_REOPENED,
                ]);
                DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                    'User_Feedback' => $feedback,
                    'Resolved_At'   => null,
                    'Reopen_Count'  => DB::raw('Reopen_Count + 1'),
                    'Updated_By'    => (string) $user->username,
                    'Updated_At'    => $now,
                ]);
                $this->tickets->recordHistory(
                    $ticketId,
                    $status,
                    TicketService::STATUS_REOPENED,
                    'User menyatakan belum selesai (Reopen): ' . $feedback,
                    (string) $user->username,
                    $now
                );
            }

            $fresh = $this->tickets->find($ticketId);
            if ($fresh) {
                $targetUserId = !empty($fresh->Assignee_User_Id) ? (int) $fresh->Assignee_User_Id : null;
                $msgTitle = $isResolved
                    ? 'User konfirmasi tiket IT ' . $fresh->Ticket_No . ' (' . $rating . '/5)'
                    : 'Tiket IT ' . $fresh->Ticket_No . ' dibuka ulang oleh user';
                $msgBody = $isResolved
                    ? 'Pelapor mengonfirmasi tiket selesai. Siap ditutup oleh IT Support.'
                    : 'Pelapor melaporkan masalah belum selesai: ' . $feedback;

                if ($targetUserId) {
                    $this->notifications->notifyUser($targetUserId, $msgTitle, $msgBody, 'it_ticket', ['Ticket_ID' => $ticketId]);
                } elseif (!empty($fresh->Assignee_Team_ID)) {
                    $this->notifications->notifyTeam((int) $fresh->Assignee_Team_ID, $msgTitle, $msgBody, 'it_ticket', ['Ticket_ID' => $ticketId]);
                }
            }

            return $fresh;
        });
    }

    public function handoverDevice(int $ticketId, array $data, $user)
    {
        $ticket = $this->tickets->find($ticketId);
        if (!$ticket || strtoupper((string) ($ticket->Domain_Type ?? '')) !== TicketService::DOMAIN_IT) {
            throw new InvalidArgumentException('Tiket IT tidak ditemukan.');
        }
        if (strtoupper((string) ($ticket->Workflow_Mode ?? '')) !== TicketService::MODE_IT_DEVICE_REQUEST) {
            throw new InvalidArgumentException('Serah terima perangkat hanya untuk tiket Pengajuan Perangkat IT.');
        }

        $action = strtolower(trim((string) ($data['action'] ?? 'handover')));
        $assetNo = trim((string) ($data['Device_Asset_No'] ?? $ticket->Device_Asset_No ?? ''));
        if ($assetNo === '') {
            throw new InvalidArgumentException('Asset No perangkat dari master asset_list wajib dipilih.');
        }

        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset) {
            throw new InvalidArgumentException('Aset ' . $assetNo . ' tidak ditemukan di master asset_list.');
        }
        if (strtoupper((string) ($asset->Status ?? '')) === 'DISPOSED') {
            throw new InvalidArgumentException('Aset ' . $assetNo . ' berstatus DISPOSED dan tidak dapat digunakan.');
        }

        $targetUserId = !empty($data['Device_Assigned_User_Id'])
            ? (int) $data['Device_Assigned_User_Id']
            : (int) ($ticket->Device_Assigned_User_Id ?: $ticket->Reporter_User_Id);
        $targetUser = $targetUserId ? DB::table('users')->where('id', $targetUserId)->first() : null;
        $recipientName = $targetUser ? ($targetUser->Name ?: $targetUser->username) : ($ticket->Reporter_Name ?: $ticket->Reporter);
        $handoverNote = trim((string) ($data['Handover_Note'] ?? $data['note'] ?? ''));

        $from = strtoupper((string) $ticket->Ticket_Status);
        if ($action === 'assign') {
            if (!in_array($from, [TicketService::STATUS_OPEN, TicketService::STATUS_DEVICE_PREPARATION, TicketService::STATUS_ON_HOLD, TicketService::STATUS_REOPENED], true)) {
                throw new InvalidArgumentException('Tiket berstatus ' . $from . ' tidak dapat di-assign ke user.');
            }

            return DB::transaction(function () use ($ticketId, $from, $assetNo, $targetUserId, $recipientName, $handoverNote, $user) {
                $now = Carbon::now();
                DB::table('ticket')->where('ID', $ticketId)->update([
                    'Ticket_Status' => TicketService::STATUS_ASSIGNED_TO_USER,
                ]);
                DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                    'Device_Asset_No'         => mb_substr($assetNo, 0, 50),
                    'Device_Assigned_User_Id' => $targetUserId ?: null,
                    'Handover_Note'           => $handoverNote !== '' ? $handoverNote : null,
                    'Updated_By'              => (string) $user->username,
                    'Updated_At'              => $now,
                ]);
                $this->tickets->recordHistory(
                    $ticketId,
                    $from,
                    TicketService::STATUS_ASSIGNED_TO_USER,
                    'Perangkat ' . $assetNo . ' dialokasikan ke ' . $recipientName . ($handoverNote !== '' ? ' (' . $handoverNote . ')' : ''),
                    (string) $user->username,
                    $now
                );

                return $this->tickets->find($ticketId);
            });
        }

        if (!in_array($from, [TicketService::STATUS_DEVICE_PREPARATION, TicketService::STATUS_ASSIGNED_TO_USER, TicketService::STATUS_OPEN], true)) {
            throw new InvalidArgumentException('Serah terima hanya dapat dilakukan dari status OPEN / DEVICE_PREPARATION / ASSIGNED_TO_USER.');
        }

        return DB::transaction(function () use ($ticketId, $ticket, $from, $assetNo, $targetUserId, $targetUser, $recipientName, $handoverNote, $user) {
            $now = Carbon::now();
            $noteFinal = $handoverNote !== '' ? $handoverNote : ('Serah terima perangkat IT melalui tiket ' . $ticket->Ticket_No);

            $this->lifecycle->recordUserTransfer(
                $assetNo,
                null,
                (string) $recipientName,
                'IT_DEVICE_REQUEST',
                (string) $ticket->Ticket_No,
                (string) $user->username,
                $noteFinal,
                $now->toDateString()
            );

            $newDept = $targetUser && !empty($targetUser->Department) ? $targetUser->Department : ($ticket->Ga_Department ?: $ticket->Department);
            $newLoc = $targetUser && !empty($targetUser->Location) ? $targetUser->Location : $ticket->Location;
            $this->lifecycle->updateLocation($assetNo, $newLoc, $newDept, null, (string) $user->username);

            DB::table('ticket')->where('ID', $ticketId)->update([
                'Ticket_Status'   => TicketService::STATUS_HANDOVER,
                'Completion_Note' => mb_substr($noteFinal, 0, 150),
            ]);
            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                'Device_Asset_No'         => mb_substr($assetNo, 0, 50),
                'Device_Assigned_User_Id' => $targetUserId ?: null,
                'Handover_Note'           => $noteFinal,
                'Handover_At'             => $now,
                'Resolved_At'             => $now,
                'Updated_By'              => (string) $user->username,
                'Updated_At'              => $now,
            ]);

            $this->tickets->recordHistory(
                $ticketId,
                $from,
                TicketService::STATUS_HANDOVER,
                'Serah terima perangkat ' . $assetNo . ' kepada ' . $recipientName . ' selesai.',
                (string) $user->username,
                $now
            );

            if ($targetUserId) {
                $this->notifications->notifyUser(
                    $targetUserId,
                    'Serah Terima Perangkat IT: ' . $ticket->Ticket_No,
                    'Perangkat ' . $assetNo . ' telah diserahterimakan kepada Anda. Silakan konfirmasi pada detail tiket.',
                    'it_ticket',
                    ['Ticket_ID' => $ticketId, 'Asset_No' => $assetNo]
                );
            }

            return $this->tickets->find($ticketId);
        });
    }

    public function escalateDeviceToPr(int $ticketId, array $data, $user): array
    {
        $ticket = $this->tickets->find($ticketId);
        if (!$ticket || strtoupper((string) ($ticket->Domain_Type ?? '')) !== TicketService::DOMAIN_IT) {
            throw new InvalidArgumentException('Tiket IT tidak ditemukan.');
        }
        if (strtoupper((string) ($ticket->Workflow_Mode ?? '')) !== TicketService::MODE_IT_DEVICE_REQUEST) {
            throw new InvalidArgumentException('Eskalasi PR hanya berlaku untuk tiket Pengajuan Perangkat IT.');
        }
        if (!empty($ticket->PR_No)) {
            throw new InvalidArgumentException('Tiket ini sudah memiliki Purchase Request: ' . $ticket->PR_No);
        }

        $itemName = trim((string) ($data['Item_Name'] ?? $ticket->Ticket_Subject));
        $pr = $this->assetTicketing->createProcurementRequest([
            'Asset_Request_Type' => 'NEW',
            'Item_Name'          => $itemName,
            'Qty'                => max(1, (float) ($data['Qty'] ?? 1)),
            'Est_Price'          => max(0, (float) ($data['Est_Price'] ?? 0)),
            'Unit'               => trim((string) ($data['Unit'] ?? 'UNIT')),
            'Location'           => (string) ($ticket->Location ?: $user->Location ?: 'GEU-HO'),
            'Specification'      => (string) ($ticket->Device_Spec ?: $ticket->Ga_Description ?: ''),
            'Remark'             => 'Pengadaan perangkat IT dari Tiket ' . $ticket->Ticket_No . ': ' . $itemName,
        ], $user);

        $now = Carbon::now();
        $from = strtoupper((string) $ticket->Ticket_Status);
        DB::transaction(function () use ($ticketId, $from, $pr, $user, $now) {
            DB::table('ticket')->where('ID', $ticketId)->update([
                'Ticket_Status' => TicketService::STATUS_ON_HOLD,
            ]);
            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                'PR_No'      => $pr['pr_no'],
                'On_Hold_At' => $now,
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ]);
            $this->tickets->recordHistory(
                $ticketId,
                $from,
                TicketService::STATUS_ON_HOLD,
                'Stok kosong — diajukan Purchase Request ' . $pr['pr_no'],
                (string) $user->username,
                $now
            );
        });

        return $pr;
    }

    public function searchKnowledgeBase(string $keyword = '', int $limit = 30)
    {
        $keyword = trim($keyword);

        $query = DB::table('ticket as t')
            ->join('ga_ticket as g', 'g.Ticket_ID', '=', 't.ID')
            ->leftJoin('ga_ticket_category as c', 'c.ID', '=', 'g.Category_ID')
            ->where('t.Module', 'GA')
            ->where('g.Domain_Type', TicketService::DOMAIN_IT)
            ->where('g.Kb_Flag', 1)
            ->whereIn('t.Ticket_Status', [
                TicketService::STATUS_RESOLVED,
                TicketService::STATUS_USER_CONFIRMED,
                TicketService::STATUS_SYSTEM_NORMAL,
                TicketService::STATUS_CLOSED,
            ]);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('g.Kb_Title', 'like', '%' . $keyword . '%')
                    ->orWhere('t.Ticket_Subject', 'like', '%' . $keyword . '%')
                    ->orWhere('g.Kb_Solution', 'like', '%' . $keyword . '%')
                    ->orWhere('g.Root_Cause', 'like', '%' . $keyword . '%')
                    ->orWhere('g.Diagnosis_Note', 'like', '%' . $keyword . '%');
            });
        }

        return $query->select([
            't.ID',
            't.Ticket_No',
            't.Ticket_Subject',
            't.Ticket_Status',
            'g.Workflow_Mode',
            'g.Action_Type',
            'g.Kb_Title',
            'g.Kb_Solution',
            'g.Root_Cause',
            'g.Diagnosis_Note',
            'c.Name as Category_Name',
            't.Created_Date',
            'g.Resolved_At',
        ])
            ->orderBy('t.ID', 'desc')
            ->limit(max(1, min(100, $limit)))
            ->get();
    }

    public function dashboardSummary($user, array $filters = []): array
    {
        $base = DB::table('ticket as t')
            ->join('ga_ticket as g', 'g.Ticket_ID', '=', 't.ID')
            ->where('t.Module', 'GA')
            ->where('g.Domain_Type', TicketService::DOMAIN_IT);

        if (!empty($filters['location'])) {
            $base->where('t.Location', $filters['location']);
        }
        if (!empty($filters['company_id'])) {
            $base->where('t.Company_ID', $filters['company_id']);
        }

        $rows = $base->select('t.Ticket_Status', 'g.Workflow_Mode', 'g.Action_Type')->get();

        $summary = [
            'total'                   => $rows->count(),
            'total_active'            => 0,
            'open_count'              => 0,
            'in_progress_count'       => 0,
            'incident_active'         => 0,
            'waiting_feedback'        => 0,
            'device_pending_approval' => 0,
            'closed_count'            => 0,
            'preventive_count'        => 0,
            'corrective_count'        => 0,
        ];

        $closedSet = [TicketService::STATUS_CLOSED, TicketService::STATUS_REJECTED];
        $progressSet = [
            TicketService::STATUS_IN_PROGRESS,
            TicketService::STATUS_TROUBLESHOOTING,
            TicketService::STATUS_ESCALATED,
            TicketService::STATUS_VALIDATED,
            TicketService::STATUS_ROOT_CAUSE_ANALYSIS,
            TicketService::STATUS_RECOVERY,
            TicketService::STATUS_TESTING,
            TicketService::STATUS_DEVICE_PREPARATION,
            TicketService::STATUS_ASSIGNED_TO_USER,
        ];

        foreach ($rows as $row) {
            $st = strtoupper((string) $row->Ticket_Status);
            $mode = strtoupper((string) $row->Workflow_Mode);
            $action = strtoupper((string) $row->Action_Type);

            if ($action === TicketService::ACTION_PREVENTIVE) {
                $summary['preventive_count']++;
            } else {
                $summary['corrective_count']++;
            }

            if (!in_array($st, $closedSet, true)) {
                $summary['total_active']++;
            } else {
                $summary['closed_count']++;
            }

            if ($st === TicketService::STATUS_OPEN) {
                $summary['open_count']++;
            }
            if (in_array($st, $progressSet, true)) {
                $summary['in_progress_count']++;
            }
            if ($mode === TicketService::MODE_IT_INCIDENT && !in_array($st, $closedSet, true)) {
                $summary['incident_active']++;
            }
            if (in_array($st, [TicketService::STATUS_RESOLVED, TicketService::STATUS_HANDOVER], true)) {
                $summary['waiting_feedback']++;
            }
            if ($mode === TicketService::MODE_IT_DEVICE_REQUEST && $st === TicketService::STATUS_WAITING_MANAGER_APPROVAL) {
                $summary['device_pending_approval']++;
            }
        }

        return $summary;
    }

    public function getApprovalTimeline($ticket): ?array
    {
        if (!$ticket || empty($ticket->Dept_Approval_Request_ID)) {
            return null;
        }

        $reqId = (int) $ticket->Dept_Approval_Request_ID;
        $request = DB::table('approval_request')->where('ID', $reqId)->first();
        if (!$request) {
            return null;
        }

        $tasks = DB::table('approval_request_task')
            ->where('Request_ID', $reqId)
            ->orderBy('Stage_Order', 'asc')
            ->orderBy('ID', 'asc')
            ->get();

        $logs = DB::table('approval_log as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.Approver_Id')
            ->where('l.Request_Id', $reqId)
            ->select('l.*', 'u.Name as Actor_Name', 'u.username as Actor_Username')
            ->orderBy('l.ID', 'asc')
            ->get();

        $policy = json_decode((string) ($request->Policy_Snapshot ?? '{}'), true) ?: [];

        return [
            'request' => $request,
            'tasks'   => $tasks,
            'logs'    => $logs,
            'steps'   => $policy['Steps'] ?? ($policy['steps'] ?? []),
        ];
    }

    protected function submitDeviceRequestApproval(int $ticketId, $ticket, $user): void
    {
        $requesterId = (int) ($ticket->Reporter_User_Id ?: $user->id);
        $context = [
            'Request_Type' => 'IT_DEVICE_REQUEST',
            'Location'     => trim((string) ($ticket->Location ?: $user->Location ?: 'GEU-HO')),
            'Company_ID'   => trim((string) ($ticket->Company_ID ?: $user->Company_ID ?: 'GEU')),
            'Department'   => trim((string) ($ticket->Ga_Department ?: $ticket->Department ?: $user->Department ?: 'ICT')),
            'Amount'       => 0,
        ];

        $approvalReq = $this->workflow->submit([
            'Request_No'  => $ticket->Ticket_No,
            'Description' => 'Pengajuan Perangkat IT ' . $ticket->Ticket_No . ' - ' . $ticket->Ticket_Subject,
        ], $context, $requesterId);

        DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
            'Map_To' => $ticketId,
        ]);

        $now = Carbon::now();
        DB::table('ticket')->where('ID', $ticketId)->update([
            'Ticket_Status' => TicketService::STATUS_WAITING_MANAGER_APPROVAL,
        ]);
        DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
            'Dept_Approval_Request_ID' => (int) $approvalReq->ID,
            'Updated_By'               => (string) $user->username,
            'Updated_At'               => $now,
        ]);

        $this->tickets->recordHistory(
            $ticketId,
            TicketService::STATUS_OPEN,
            TicketService::STATUS_WAITING_MANAGER_APPROVAL,
            'Pengajuan Perangkat IT dikirim ke Approval Manager (' . $approvalReq->Request_No . ').',
            (string) $user->username,
            $now
        );

        if (strtoupper((string) $approvalReq->Status) === 'APPROVED') {
            $this->syncApprovalStatus((int) $approvalReq->ID);
        }
    }

    protected function resolveDefaultItCategoryId(string $workflowMode, string $actionType): ?int
    {
        $code = 'IT-HELP';
        if ($actionType === TicketService::ACTION_PREVENTIVE) {
            $code = 'IT-PREV';
        } elseif ($workflowMode === TicketService::MODE_IT_INCIDENT) {
            $code = 'IT-SYS';
        } elseif ($workflowMode === TicketService::MODE_IT_DEVICE_REQUEST) {
            $code = 'IT-DEV';
        }

        $id = DB::table('ga_ticket_category')
            ->where('Code', $code)
            ->where('Status', 'ACTIVE')
            ->value('ID');

        return $id ? (int) $id : null;
    }

    protected function resolveDefaultItTeamId(string $workflowMode): ?int
    {
        $code = $workflowMode === TicketService::MODE_IT_INCIDENT ? 'IT-INFRA' : 'IT-SUPPORT';
        $id = DB::table('ga_team')->where('Code', $code)->where('Status', 'ACTIVE')->value('ID');
        if ($id) {
            return (int) $id;
        }
        $fallback = DB::table('ga_team')->where('Code', 'like', 'IT-%')->where('Status', 'ACTIVE')->value('ID');
        return $fallback ? (int) $fallback : null;
    }

    protected function latestApprovalNote(int $approvalRequestId): ?string
    {
        $humanNote = DB::table('approval_log')
            ->where('Request_Id', $approvalRequestId)
            ->whereIn('Action', ['REJECT', 'REJECTED'])
            ->whereNotNull('Notes')
            ->where('Notes', '!=', '')
            ->orderBy('ID', 'desc')
            ->value('Notes');

        if ($humanNote && trim((string) $humanNote) !== '') {
            return trim((string) $humanNote);
        }

        $note = DB::table('approval_log')
            ->where('Request_Id', $approvalRequestId)
            ->whereIn('Action', ['REJECT', 'REJECTED', 'RETURNED'])
            ->whereNotNull('Notes')
            ->where('Notes', '!=', '')
            ->orderBy('ID', 'desc')
            ->value('Notes');

        return $note ? trim((string) $note) : null;
    }
}
