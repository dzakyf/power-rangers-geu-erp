<?php

namespace App\Services\GA;

use App\Services\Approval\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service alur 2-fase approval GA Order Pekerjaan (WORK_ORDER_VOUCHER):
 * - Fase 1: GA_TICKET_DEPT_HEAD (Approval Department Head Pemohon -> GA_VERIFICATION)
 * - GA Verification: GA finalisasi Verified_Amount & vendor
 * - Fase 2: GA_TICKET_BUDGET (<3jt HCGA Mgr, 3-5jt +FAT Mgr +VP FAT, >5jt +BOD)
 * - Reject Loop Fase 2: kembali ke GA_VERIFICATION tanpa mengulang Dept Head
 */
class GaTicketApprovalService
{
    /** @var TicketService */
    protected $tickets;

    /** @var ApprovalWorkflowService */
    protected $workflow;

    /** @var GaNotificationService */
    protected $notifications;

    public function __construct(
        TicketService $tickets,
        ApprovalWorkflowService $workflow,
        GaNotificationService $notifications
    ) {
        $this->tickets = $tickets;
        $this->workflow = $workflow;
        $this->notifications = $notifications;
    }

    public function submitDeptHeadApproval(int $ticketId, $user)
    {
        return $this->submitDeptHeadInternal($ticketId, $user, null, 1, false);
    }

    public function resubmitDeptHead(int $ticketId, array $data, $user)
    {
        $ticket = $this->tickets->find($ticketId);
        if (!$ticket || strtoupper((string) ($ticket->Workflow_Mode ?? '')) !== TicketService::MODE_WORK_ORDER_VOUCHER) {
            throw new InvalidArgumentException('Tiket GA Order Pekerjaan tidak ditemukan.');
        }

        $status = strtoupper((string) $ticket->Ticket_Status);
        if (!in_array($status, [TicketService::STATUS_REJECTED, TicketService::STATUS_OPEN], true)) {
            throw new InvalidArgumentException('Pengajuan ulang ke Dept Head hanya dapat dilakukan jika tiket ditolak atau masih Open.');
        }
        if (!empty($ticket->Budget_Approval_Request_ID) && $status !== TicketService::STATUS_OPEN) {
            throw new InvalidArgumentException('Tiket sudah melewati fase Dept Head.');
        }

        $oldReqId = !empty($ticket->Dept_Approval_Request_ID) ? (int) $ticket->Dept_Approval_Request_ID : null;
        $revisionNo = 1;
        if ($oldReqId) {
            $oldReq = DB::table('approval_request')->where('ID', $oldReqId)->first();
            $revisionNo = $oldReq ? ((int) $oldReq->Revision_No + 1) : 2;
        }

        $now = Carbon::now();
        DB::transaction(function () use ($ticketId, $data, $user, $now) {
            $headerUpdates = [];
            if (!empty($data['Ticket_Subject'])) {
                $headerUpdates['Ticket_Subject'] = mb_substr(trim((string) $data['Ticket_Subject']), 0, 250);
            }
            if (!empty($data['Description'])) {
                $headerUpdates['Description'] = trim((string) $data['Description']);
            }
            if (!empty($headerUpdates)) {
                DB::table('ticket')->where('ID', $ticketId)->update($headerUpdates);
            }

            $gaUpdates = [
                'Rejection_Reason' => null,
                'Updated_By'       => (string) $user->username,
                'Updated_At'       => $now,
            ];
            if (!empty($data['Description'])) {
                $gaUpdates['Description'] = trim((string) $data['Description']);
            }
            if (array_key_exists('Estimated_Amount', $data)) {
                $gaUpdates['Estimated_Amount'] = max(0, round((float) $data['Estimated_Amount'], 2));
            }
            if (array_key_exists('Preferred_Vendor_ID', $data)) {
                $gaUpdates['Preferred_Vendor_ID'] = !empty($data['Preferred_Vendor_ID']) ? (int) $data['Preferred_Vendor_ID'] : null;
            }
            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update($gaUpdates);
        });

        return $this->submitDeptHeadInternal($ticketId, $user, $oldReqId, $revisionNo, true);
    }

    protected function submitDeptHeadInternal(
        int $ticketId,
        $user,
        ?int $parentRequestId,
        int $revisionNo,
        bool $isResubmit
    ) {
        $approvalReq = DB::transaction(function () use ($ticketId, $user, $parentRequestId, $revisionNo, $isResubmit) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $ga = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first();

            if (!$ticket || !$ga || (string) $ticket->Module !== 'GA') {
                throw new InvalidArgumentException('Tiket GA tidak ditemukan.');
            }
            if (strtoupper((string) ($ga->Workflow_Mode ?? '')) !== TicketService::MODE_WORK_ORDER_VOUCHER) {
                throw new InvalidArgumentException('Tiket ini bukan mode Order Pekerjaan & Voucher.');
            }

            $fromStatus = strtoupper((string) $ticket->Ticket_Status);
            if (!in_array($fromStatus, [TicketService::STATUS_OPEN, TicketService::STATUS_REJECTED], true)) {
                throw new InvalidArgumentException('Tiket tidak berada pada status yang dapat diajukan ke Dept Head.');
            }

            $requesterId = (int) ($ga->Reporter_User_Id ?: $user->id);
            $context = [
                'Request_Type' => 'GA_TICKET_DEPT_HEAD',
                'Location'     => trim((string) ($ticket->Location ?: $user->Location ?: 'GEU-HO')),
                'Company_ID'   => trim((string) ($ticket->Company_ID ?: $user->Company_ID ?: 'GEU')),
                'Department'   => trim((string) ($ga->Department ?: $ticket->Department ?: $ga->Reporter_Department ?: $user->Department ?: '')),
                'Amount'       => (float) ($ga->Estimated_Amount ?? 0),
            ];

            $options = [];
            if ($parentRequestId) {
                $options['Parent_Request_ID'] = $parentRequestId;
                $options['Revision_No'] = $revisionNo;
            }

            $reqNo = mb_substr($ticket->Ticket_No . '-DH' . ($revisionNo > 1 ? '-R' . $revisionNo : ''), 0, 50);
            $req = $this->workflow->submit([
                'Request_No'  => $reqNo,
                'Description' => 'Order Pekerjaan GA ' . $ticket->Ticket_No . ' - ' . $ticket->Ticket_Subject,
            ], $context, $requesterId, $options);

            DB::table('approval_request')->where('ID', (int) $req->ID)->update([
                'Map_To' => $ticketId,
            ]);

            $now = Carbon::now();
            DB::table('ticket')->where('ID', $ticketId)->update([
                'Ticket_Status' => TicketService::STATUS_WAITING_DEPT_HEAD,
            ]);
            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                'Dept_Approval_Request_ID' => (int) $req->ID,
                'Rejection_Reason'         => null,
                'Updated_By'               => (string) $user->username,
                'Updated_At'               => $now,
            ]);

            $note = $isResubmit
                ? 'Pengajuan ulang Order Pekerjaan ke Approval Dept Head (' . $req->Request_No . ', Revisi #' . $revisionNo . ').'
                : 'Order Pekerjaan diajukan ke Approval Dept Head (' . $req->Request_No . ').';

            $this->tickets->recordHistory(
                $ticketId,
                $fromStatus,
                TicketService::STATUS_WAITING_DEPT_HEAD,
                $note,
                (string) $user->username,
                $now
            );

            return $req;
        });

        if ($approvalReq && strtoupper((string) $approvalReq->Status) === 'APPROVED') {
            $this->syncDeptHeadApproval((int) $approvalReq->ID);
        }

        return $this->tickets->find($ticketId);
    }

    public function syncDeptHeadApproval(int $approvalRequestId)
    {
        $req = DB::table('approval_request')->where('ID', $approvalRequestId)->first();
        if (!$req || strtoupper((string) $req->Request_Type) !== 'GA_TICKET_DEPT_HEAD') {
            return null;
        }

        $ticketId = (int) ($req->Map_To ?: DB::table('ga_ticket')->where('Dept_Approval_Request_ID', $approvalRequestId)->value('Ticket_ID'));
        if ($ticketId < 1) {
            return null;
        }

        $reqStatus = strtoupper((string) $req->Status);
        if (!in_array($reqStatus, ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return $this->tickets->find($ticketId);
        }

        $notifyReporter = null;
        $notifyTitle = '';
        $notifyBody = '';

        DB::transaction(function () use ($ticketId, $req, $reqStatus, &$notifyReporter, &$notifyTitle, &$notifyBody) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $ga = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first();
            if (!$ticket || !$ga) {
                return;
            }

            if (!empty($ga->Dept_Approval_Request_ID) && (int) $ga->Dept_Approval_Request_ID !== (int) $req->ID) {
                return;
            }

            $from = strtoupper((string) $ticket->Ticket_Status);
            $now = Carbon::now();

            if ($reqStatus === 'APPROVED') {
                if (!in_array($from, [TicketService::STATUS_OPEN, TicketService::STATUS_WAITING_DEPT_HEAD], true)) {
                    return;
                }

                DB::table('ticket')->where('ID', $ticketId)->update([
                    'Ticket_Status' => TicketService::STATUS_GA_VERIFICATION,
                ]);
                DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                    'Updated_At' => $now,
                ]);

                $this->tickets->recordHistory(
                    $ticketId,
                    $from,
                    TicketService::STATUS_GA_VERIFICATION,
                    'Disetujui Dept Head (' . $req->Request_No . '). Menunggu proses GA Verification.',
                    'system',
                    $now
                );

                $notifyReporter = (int) ($ga->Reporter_User_Id ?? 0);
                $notifyTitle = 'Disetujui Dept Head: ' . $ticket->Ticket_No;
                $notifyBody = 'Tiket Order Pekerjaan Anda memasuki tahap GA Verification.';
                return;
            }

            if (!in_array($from, [TicketService::STATUS_WAITING_DEPT_HEAD, TicketService::STATUS_OPEN], true)) {
                return;
            }

            $reason = $this->latestApprovalNote((int) $req->ID) ?: 'Ditolak pada approval Dept Head.';
            DB::table('ticket')->where('ID', $ticketId)->update([
                'Ticket_Status' => TicketService::STATUS_REJECTED,
            ]);
            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                'Rejection_Reason' => mb_substr($reason, 0, 2000),
                'Updated_At'       => $now,
            ]);

            $this->tickets->recordHistory(
                $ticketId,
                $from,
                TicketService::STATUS_REJECTED,
                'Approval Dept Head ditolak: ' . $reason,
                'system',
                $now
            );

            $notifyReporter = (int) ($ga->Reporter_User_Id ?? 0);
            $notifyTitle = 'Ditolak Dept Head: ' . $ticket->Ticket_No;
            $notifyBody = 'Alasan: ' . $reason;
        });

        if ($notifyReporter && $notifyTitle !== '') {
            $this->notifications->notifyUser($notifyReporter, $notifyTitle, $notifyBody, 'ga_ticket', [
                'Ticket_ID' => $ticketId,
            ]);
        }

        return $this->tickets->find($ticketId);
    }

    public function verifyAndSubmitBudgetApproval(int $ticketId, array $data, $user)
    {
        $verifiedAmount = isset($data['Verified_Amount']) ? round((float) $data['Verified_Amount'], 2) : 0.0;
        if ($verifiedAmount <= 0) {
            throw new InvalidArgumentException('Nominal verifikasi biaya wajib diisi dan lebih dari 0.');
        }

        $note = trim((string) ($data['GA_Verification_Note'] ?? $data['Verification_Note'] ?? ''));

        $approvalReq = DB::transaction(function () use ($ticketId, $data, $user, $verifiedAmount, $note) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $ga = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first();

            if (!$ticket || !$ga || (string) $ticket->Module !== 'GA') {
                throw new InvalidArgumentException('Tiket GA tidak ditemukan.');
            }
            if (strtoupper((string) ($ga->Workflow_Mode ?? '')) !== TicketService::MODE_WORK_ORDER_VOUCHER) {
                throw new InvalidArgumentException('Tiket ini bukan mode Order Pekerjaan & Voucher.');
            }
            if (strtoupper((string) $ticket->Ticket_Status) !== TicketService::STATUS_GA_VERIFICATION) {
                throw new InvalidArgumentException('GA Verification hanya dapat dilakukan saat status GA_VERIFICATION.');
            }

            $oldBudgetReqId = !empty($ga->Budget_Approval_Request_ID) ? (int) $ga->Budget_Approval_Request_ID : null;
            $revisionNo = 1;
            $options = [];
            if ($oldBudgetReqId) {
                $oldReq = DB::table('approval_request')->where('ID', $oldBudgetReqId)->first();
                $revisionNo = $oldReq ? ((int) $oldReq->Revision_No + 1) : ((int) ($ga->Approval_Revision_Count ?? 0) + 1);
                $options['Parent_Request_ID'] = $oldBudgetReqId;
                $options['Revision_No'] = $revisionNo;
            }

            $requesterId = (int) ($ga->Reporter_User_Id ?: $user->id);
            $context = [
                'Request_Type' => 'GA_TICKET_BUDGET',
                'Location'     => trim((string) ($ticket->Location ?: $user->Location ?: 'GEU-HO')),
                'Company_ID'   => trim((string) ($ticket->Company_ID ?: $user->Company_ID ?: 'GEU')),
                'Department'   => trim((string) ($ga->Department ?: $ticket->Department ?: $ga->Reporter_Department ?: $user->Department ?: 'HCGA')),
                'Amount'       => $verifiedAmount,
            ];

            $reqNo = mb_substr($ticket->Ticket_No . '-BG' . ($revisionNo > 1 ? '-R' . $revisionNo : ''), 0, 50);
            $req = $this->workflow->submit([
                'Request_No'  => $reqNo,
                'Description' => 'Approval Biaya Order Pekerjaan GA ' . $ticket->Ticket_No . ' (Rp ' . number_format($verifiedAmount, 0, ',', '.') . ')',
            ], $context, $requesterId, $options);

            DB::table('approval_request')->where('ID', (int) $req->ID)->update([
                'Map_To' => $ticketId,
            ]);

            $now = Carbon::now();
            $gaUpdates = [
                'Verified_Amount'            => $verifiedAmount,
                'GA_Verified_By'             => (string) $user->username,
                'GA_Verified_At'             => $now,
                'GA_Verification_Note'       => $note !== '' ? mb_substr($note, 0, 2000) : $ga->GA_Verification_Note,
                'Budget_Approval_Request_ID' => (int) $req->ID,
                'First_Response_At'          => $ga->First_Response_At ?: $now,
                'Rejection_Reason'           => null,
                'Updated_By'                 => (string) $user->username,
                'Updated_At'                 => $now,
            ];
            if (array_key_exists('Preferred_Vendor_ID', $data)) {
                $gaUpdates['Preferred_Vendor_ID'] = !empty($data['Preferred_Vendor_ID']) ? (int) $data['Preferred_Vendor_ID'] : null;
            }

            DB::table('ticket')->where('ID', $ticketId)->update([
                'Ticket_Status' => TicketService::STATUS_WAITING_BUDGET_APPROVAL,
            ]);
            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update($gaUpdates);

            $tier = $this->tierPreview($verifiedAmount);
            $historyNote = 'GA Verification selesai (Nominal: Rp ' . number_format($verifiedAmount, 0, ',', '.') . ', Tier: ' . $tier['tier_label'] . '). Diajukan ke ' . $req->Request_No
                . ($revisionNo > 1 ? ' (Revisi #' . $revisionNo . ').' : '.');
            if ($note !== '') {
                $historyNote .= ' Catatan: ' . $note;
            }

            $this->tickets->recordHistory(
                $ticketId,
                TicketService::STATUS_GA_VERIFICATION,
                TicketService::STATUS_WAITING_BUDGET_APPROVAL,
                $historyNote,
                (string) $user->username,
                $now
            );

            return $req;
        });

        if ($approvalReq && strtoupper((string) $approvalReq->Status) === 'APPROVED') {
            $this->syncBudgetApproval((int) $approvalReq->ID);
        }

        return $this->tickets->find($ticketId);
    }

    public function syncBudgetApproval(int $approvalRequestId)
    {
        $req = DB::table('approval_request')->where('ID', $approvalRequestId)->first();
        if (!$req || strtoupper((string) $req->Request_Type) !== 'GA_TICKET_BUDGET') {
            return null;
        }

        $ticketId = (int) ($req->Map_To ?: DB::table('ga_ticket')->where('Budget_Approval_Request_ID', $approvalRequestId)->value('Ticket_ID'));
        if ($ticketId < 1) {
            return null;
        }

        $reqStatus = strtoupper((string) $req->Status);
        if (!in_array($reqStatus, ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return $this->tickets->find($ticketId);
        }

        $notifyIds = [];
        $notifyTitle = '';
        $notifyBody = '';

        DB::transaction(function () use ($ticketId, $req, $reqStatus, &$notifyIds, &$notifyTitle, &$notifyBody) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $ga = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first();
            if (!$ticket || !$ga) {
                return;
            }

            if (!empty($ga->Budget_Approval_Request_ID) && (int) $ga->Budget_Approval_Request_ID !== (int) $req->ID) {
                return;
            }

            $from = strtoupper((string) $ticket->Ticket_Status);
            $now = Carbon::now();

            if ($reqStatus === 'APPROVED') {
                if (!in_array($from, [TicketService::STATUS_WAITING_BUDGET_APPROVAL, TicketService::STATUS_GA_VERIFICATION], true)) {
                    return;
                }

                DB::table('ticket')->where('ID', $ticketId)->update([
                    'Ticket_Status' => TicketService::STATUS_READY_FOR_VOUCHER,
                ]);
                DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                    'Rejection_Reason' => null,
                    'Updated_At'       => $now,
                ]);

                $this->tickets->recordHistory(
                    $ticketId,
                    $from,
                    TicketService::STATUS_READY_FOR_VOUCHER,
                    'Approval biaya selesai disetujui (' . $req->Request_No . '). Tiket siap dibuatkan Voucher Pembayaran.',
                    'system',
                    $now
                );

                if (!empty($ga->Reporter_User_Id)) {
                    $notifyIds[] = (int) $ga->Reporter_User_Id;
                }
                if (!empty($ga->GA_Verified_By)) {
                    $verifierId = DB::table('users')->where('username', $ga->GA_Verified_By)->value('id');
                    if ($verifierId) {
                        $notifyIds[] = (int) $verifierId;
                    }
                }
                $notifyTitle = 'Approval Biaya Disetujui: ' . $ticket->Ticket_No;
                $notifyBody = 'Tiket siap dibuatkan Voucher Pembayaran ke Finance.';
                return;
            }

            // Reject Loop: hanya dapat terjadi saat WAITING_BUDGET_APPROVAL dan kembali ke GA_VERIFICATION
            if ($from !== TicketService::STATUS_WAITING_BUDGET_APPROVAL) {
                return;
            }

            $reason = $this->latestApprovalNote((int) $req->ID) ?: 'Ditolak pada approval biaya.';
            $nextRevisionCount = ((int) ($ga->Approval_Revision_Count ?? 0)) + 1;

            DB::table('ticket')->where('ID', $ticketId)->update([
                'Ticket_Status' => TicketService::STATUS_GA_VERIFICATION,
            ]);
            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                'Approval_Revision_Count' => $nextRevisionCount,
                'Rejection_Reason'        => mb_substr($reason, 0, 2000),
                'Updated_At'              => $now,
            ]);

            $this->tickets->recordHistory(
                $ticketId,
                $from,
                TicketService::STATUS_GA_VERIFICATION,
                'Approval biaya ditolak (' . $req->Request_No . '): ' . $reason
                . ' — dikembalikan ke GA Verification (Revisi #' . $nextRevisionCount . ', tanpa mengulang Dept Head).',
                'system',
                $now
            );

            if (!empty($ga->Reporter_User_Id)) {
                $notifyIds[] = (int) $ga->Reporter_User_Id;
            }
            if (!empty($ga->GA_Verified_By)) {
                $verifierId = DB::table('users')->where('username', $ga->GA_Verified_By)->value('id');
                if ($verifierId) {
                    $notifyIds[] = (int) $verifierId;
                }
            }
            $notifyTitle = 'Revisi GA Verification: ' . $ticket->Ticket_No;
            $notifyBody = 'Approval biaya ditolak: ' . $reason;
        });

        if (!empty($notifyIds) && $notifyTitle !== '') {
            $this->notifications->notifyUsers($notifyIds, $notifyTitle, $notifyBody, 'ga_ticket', [
                'Ticket_ID' => $ticketId,
            ]);
        }

        return $this->tickets->find($ticketId);
    }

    public function tierPreview(float $amount): array
    {
        if ($amount < 3000000) {
            return [
                'policy_code' => 'GA-TICKET-BUDGET-LOW',
                'tier_label'  => '< Rp 3.000.000 (1 Tahap)',
                'stages'      => [
                    '1. HCGA Manager (MHCGA)',
                ],
            ];
        }

        if ($amount <= 5000000) {
            return [
                'policy_code' => 'GA-TICKET-BUDGET-MID',
                'tier_label'  => 'Rp 3.000.000 - Rp 5.000.000 (3 Tahap)',
                'stages'      => [
                    '1. HCGA Manager (MHCGA)',
                    '2. FAT Manager (MFA)',
                    '3. VP FAT (VPFAT)',
                ],
            ];
        }

        return [
            'policy_code' => 'GA-TICKET-BUDGET-HIGH',
            'tier_label'  => '> Rp 5.000.000 (4 Tahap)',
            'stages'      => [
                '1. HCGA Manager (MHCGA)',
                '2. FAT Manager (MFA)',
                '3. VP FAT (VPFAT)',
                '4. BOD / Vice President Director (VPD)',
            ],
        ];
    }

    public function getApprovalTimelines($ticket): array
    {
        $deptHead = !empty($ticket->Dept_Approval_Request_ID)
            ? $this->loadApprovalTimeline((int) $ticket->Dept_Approval_Request_ID)
            : null;

        $budgetCurrent = !empty($ticket->Budget_Approval_Request_ID)
            ? $this->loadApprovalTimeline((int) $ticket->Budget_Approval_Request_ID)
            : null;

        $budgetHistory = [];
        if ($ticket && !empty($ticket->ID)) {
            $reqIds = DB::table('approval_request')
                ->where('Request_Type', 'GA_TICKET_BUDGET')
                ->where('Map_To', (int) $ticket->ID)
                ->orderBy('ID', 'desc')
                ->pluck('ID');

            foreach ($reqIds as $reqId) {
                $timeline = $this->loadApprovalTimeline((int) $reqId);
                if ($timeline) {
                    $budgetHistory[] = $timeline;
                }
            }
        }

        return [
            'dept_head'        => $deptHead,
            'budget'           => $budgetCurrent,
            'budget_revisions' => $budgetHistory,
        ];
    }

    protected function loadApprovalTimeline(int $requestId): ?array
    {
        $request = DB::table('approval_request')->where('ID', $requestId)->first();
        if (!$request) {
            return null;
        }

        $tasks = DB::table('approval_request_task')
            ->where('Request_ID', $requestId)
            ->orderBy('Stage_Order', 'asc')
            ->orderBy('ID', 'asc')
            ->get();

        $logs = DB::table('approval_log as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.Approver_Id')
            ->where('l.Request_Id', $requestId)
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
