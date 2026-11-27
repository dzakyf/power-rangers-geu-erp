<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use App\Services\HCGA\AssetHandoverBastService;
use App\Services\HCGA\AssetLifecycleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adapter terpusat antara modul Asset Handover & Return
 * dengan Dynamic Approval Engine (ApprovalWorkflowService) + sinkronisasi asset_list.
 */
class AssetHandoverApprovalService
{
    public const TYPE_HANDOVER = 'ASSET_HANDOVER';
    public const TYPE_RETURN   = 'ASSET_HANDOVER_RETURN';

    /** @var ApprovalWorkflowService */
    protected $workflow;

    /** @var ApprovalAuthorizationService */
    protected $authorizationService;

    /** @var AssetLifecycleService */
    protected $lifecycleService;

    /** @var AssetHandoverBastService */
    protected $bastService;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalAuthorizationService $authorizationService,
        AssetLifecycleService $lifecycleService,
        AssetHandoverBastService $bastService
    ) {
        $this->workflow = $workflow;
        $this->authorizationService = $authorizationService;
        $this->lifecycleService = $lifecycleService;
        $this->bastService = $bastService;
    }

    /**
     * Validasi NIK pada tabel employment serta pastikan memiliki akun aktif di users.
     *
     * @return array{employee: object, user: object}
     */
    public function resolveEmployeeAndUserByNik(string $nik, string $roleLabel): array
    {
        $nik = trim($nik);
        if ($nik === '') {
            throw new \InvalidArgumentException("{$roleLabel} wajib diisi dari data karyawan (employment).");
        }

        $employee = null;
        if (Schema::hasTable('employment')) {
            $employee = DB::table('employment')
                ->where('NIK', $nik)
                ->first();

            if (!$employee) {
                throw new \RuntimeException("{$roleLabel} (NIK: {$nik}) tidak ditemukan pada tabel employment.");
            }
        }

        $user = DB::table('users')
            ->where('Emp_ID', $nik)
            ->where('Status', 'ACTIVE')
            ->orderBy('id', 'ASC')
            ->first();

        if (!$user) {
            throw new \RuntimeException("Karyawan {$roleLabel} (NIK: {$nik}) belum memiliki akun login aktif pada tabel users.");
        }

        return [
            'employee' => $employee ?: (object) ['NIK' => $nik, 'Name' => $user->Name, 'Department' => $user->Department ?? null],
            'user'     => $user,
        ];
    }

    /**
     * Daftarkan pengajuan Handover baru ke ApprovalWorkflowService.
     * Bila Lead Pemberi == User Create, Stage 1 otomatis dilewati (AUTO_SKIP) menuju Approval PIC.
     */
    public function submitHandover(int $handoverId, int $requesterId): object
    {
        $handover = DB::table('asset_handover')->where('ID', $handoverId)->first();
        if (!$handover) {
            throw new \RuntimeException('Data Asset Handover tidak ditemukan.');
        }

        $leadNik = trim((string) ($handover->Lead_NIK ?? '')) ?: trim((string) ($handover->User_NIK ?? ''));
        $leadResolved = $this->resolveEmployeeAndUserByNik($leadNik, 'Lead Pemberi');
        $picResolved  = $this->resolveEmployeeAndUserByNik((string) ($handover->PIC_NIK ?? ''), 'Penerima Barang (PIC)');

        $leadEmp  = $leadResolved['employee'];
        $leadUser = $leadResolved['user'];
        $picEmp   = $picResolved['employee'];
        $picUser  = $picResolved['user'];

        if ((int) $picUser->id === $requesterId) {
            throw new \RuntimeException('Penerima Barang (PIC) tidak boleh sama dengan Pembuat (User Create) karena aturan anti self-approval.');
        }

        if ((int) $leadUser->id === (int) $picUser->id) {
            throw new \RuntimeException('Lead Pemberi dan Penerima Barang (PIC) tidak boleh orang yang sama.');
        }

        $requester = DB::table('users')->where('id', $requesterId)->first();
        $requesterNik = trim((string) ($requester->Emp_ID ?? $handover->User_NIK ?? ''));

        $skipStages = [];
        if ((int) $leadUser->id === $requesterId || ($requesterNik !== '' && strcasecmp($leadNik, $requesterNik) === 0)) {
            $skipStages['1'] = 'Lead Pemberi sama dengan Pembuat (User Create); Stage 1 dilewati otomatis menuju Approval PIC.';
        }

        $leadDisplayName = trim((string) ($handover->Lead_Name ?? '')) ?: ($leadEmp->Name ?? $leadUser->Name);
        $picDisplayName  = trim((string) ($handover->PIC_Name ?? '')) ?: ($picEmp->Name ?? $picUser->Name);

        $context = [
            'Request_Type'             => self::TYPE_HANDOVER,
            'Handover_No'              => (string) $handover->Handover_No,
            'Location'                 => ($requester->Location ?? '') ?: 'GEU-HO',
            'Company_ID'               => ($requester->Company_ID ?? '') ?: 'GEU',
            'Department'               => ($handover->PIC_Department ?? '') ?: (($picEmp->Department ?? '') ?: (($requester->Department ?? '') ?: 'HCGA')),
            'Amount'                   => 0,
            'Skip_Stages'              => $skipStages,
            'Stage_Candidate_User_Ids' => [
                '1' => [(int) $leadUser->id],
                '2' => [(int) $picUser->id],
            ],
            'Stage_Candidate_Meta' => [
                '1' => [
                    'Code'       => 'HANDOVER_LEAD',
                    'Name'       => 'Lead Pemberi: ' . $leadDisplayName,
                    'Level'      => 3,
                    'Department' => $leadEmp->Department ?? ($leadUser->Department ?? ($requester->Department ?? null)),
                ],
                '2' => [
                    'Code'       => 'HANDOVER_PIC',
                    'Name'       => 'Penerima PIC: ' . $picDisplayName,
                    'Level'      => 5,
                    'Department' => $handover->PIC_Department ?: ($picEmp->Department ?? ($picUser->Department ?? null)),
                ],
            ],
        ];

        $description = sprintf(
            'Serah terima aset %s dari Lead %s kepada PIC %s (%s). Catatan: %s',
            $handover->Handover_No,
            $leadDisplayName ?: $leadNik,
            $picDisplayName ?: $handover->PIC_NIK,
            $handover->PIC_Department ?: '-',
            $handover->Remark ?: '-'
        );

        $approvalReq = $this->workflow->submit([
            'Request_No'  => (string) $handover->Handover_No,
            'Description' => $description,
        ], $context, $requesterId);

        DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
            'Map_To' => $handoverId,
        ]);

        $freshReq = $this->workflow->getRequest((int) $approvalReq->ID);
        $reqStatus = strtoupper((string) $freshReq->Status);

        $updateHeader = [
            'Approval_Request_ID' => (int) $freshReq->ID,
            'Status'              => $reqStatus === 'APPROVED' ? 'APPROVED' : 'WAITING_APPROVAL',
        ];
        if (Schema::hasColumn('asset_handover', 'Lead_NIK')) {
            $updateHeader['Lead_NIK']  = $leadNik;
            $updateHeader['Lead_Name'] = $leadDisplayName;
        }
        DB::table('asset_handover')->where('ID', $handoverId)->update($updateHeader);

        DB::table('asset_handover_dtl')
            ->where('Handover_No', $handover->Handover_No)
            ->where(function ($q) {
                $q->whereNull('Status')
                  ->orWhere('Status', '')
                  ->orWhereIn('Status', ['CREATED', 'UPDATED', 'REJECTED', 'CANCELLED']);
            })
            ->update([
                'Status' => $reqStatus === 'APPROVED' ? 'APPROVED' : 'WAITING_APPROVAL',
            ]);

        $freshHeader = DB::table('asset_handover')->where('ID', $handoverId)->first();
        $this->bastService->writeAuditLog(
            $freshHeader ?: $handover,
            $requester->username ?? 'system',
            'HANDOVER_SUBMITTED',
            'Pengajuan handover dikirim ke ApprovalWorkflowService (Req #' . $freshReq->ID . ')'
        );

        return $freshReq;
    }

    /**
     * Daftarkan pengajuan Return ke ApprovalWorkflowService.
     * Bila Return_Lead_NIK == pembuat pengajuan return, Stage 1 otomatis dilewati ke Penerima Pengembalian.
     */
    public function submitReturn(
        int $handoverId,
        int $requesterId,
        string $returnLeadNik,
        string $returnLeadName,
        string $returnReceiverNik,
        string $returnReceiverName,
        string $note = ''
    ): object {
        $handover = DB::table('asset_handover')->where('ID', $handoverId)->lockForUpdate()->first();
        if (!$handover) {
            throw new \RuntimeException('Data Asset Handover tidak ditemukan.');
        }

        $headerStatus = strtoupper((string) ($handover->Status ?? ''));
        if (!in_array($headerStatus, ['APPROVED', 'UPDATED'], true)) {
            throw new \RuntimeException('Pengembalian hanya dapat diajukan untuk transaksi Handover berstatus APPROVED.');
        }

        if (!empty($handover->Return_Approval_Request_ID)) {
            $activeReturnReq = DB::table('approval_request')
                ->where('ID', (int) $handover->Return_Approval_Request_ID)
                ->first();
            if ($activeReturnReq && in_array(strtoupper((string) $activeReturnReq->Status), ['IN_PROGRESS', 'BLOCKED', 'PENDING', 'HELD'], true)) {
                throw new \RuntimeException('Pengajuan pengembalian untuk transaksi ini sedang dalam proses approval (Req #' . $activeReturnReq->ID . ').');
            }
        }

        $pendingItems = DB::table('asset_handover_dtl')
            ->where('Handover_No', $handover->Handover_No)
            ->where('Status', 'RETURN_PENDING')
            ->get();

        if ($pendingItems->isEmpty()) {
            throw new \RuntimeException('Tidak ada item berstatus RETURN_PENDING. Centang minimal satu item yang masih aktif dan pastikan detail pengembalian tersimpan.');
        }

        foreach ($pendingItems as $pendingItem) {
            $origQty = (float) ($pendingItem->Qty ?? 0);
            $retQty  = (float) ($pendingItem->Returned_Qty ?? 0);
            if ($retQty <= 0 || ($origQty > 0 && $retQty > $origQty)) {
                throw new \RuntimeException('Jumlah pengembalian (Returned Qty) untuk item "' . ($pendingItem->Description ?? $pendingItem->Product_Code) . '" tidak valid (harus > 0 dan <= Qty handover).');
            }
        }

        $returnLeadResolved     = $this->resolveEmployeeAndUserByNik($returnLeadNik, 'PIC Approval Pengembalian');
        $returnReceiverResolved = $this->resolveEmployeeAndUserByNik($returnReceiverNik, 'Penerima Pengembalian');

        $returnLeadEmp      = $returnLeadResolved['employee'];
        $returnLeadUser     = $returnLeadResolved['user'];
        $returnReceiverEmp  = $returnReceiverResolved['employee'];
        $returnReceiverUser = $returnReceiverResolved['user'];

        if ((int) $returnReceiverUser->id === $requesterId) {
            throw new \RuntimeException('Penerima Pengembalian Aset (Stage 2) tidak boleh sama dengan Pembuat Pengajuan Return karena aturan anti self-approval.');
        }

        $requester = DB::table('users')->where('id', $requesterId)->first();
        $requesterNik = trim((string) ($requester->Emp_ID ?? ''));

        $skipStages = [];
        if ((int) $returnLeadUser->id === $requesterId || ($requesterNik !== '' && strcasecmp(trim($returnLeadNik), $requesterNik) === 0)) {
            $skipStages['1'] = 'PIC Approval Pengembalian sama dengan Pembuat Pengajuan; Stage 1 dilewati otomatis menuju Penerima Pengembalian.';
        }

        $leadDisplay     = $returnLeadName !== '' ? $returnLeadName : ($returnLeadEmp->Name ?? ($returnLeadUser->Name ?? $returnLeadNik));
        $receiverDisplay = $returnReceiverName !== '' ? $returnReceiverName : ($returnReceiverEmp->Name ?? ($returnReceiverUser->Name ?? $returnReceiverNik));

        $updateHeader = [];
        if (Schema::hasColumn('asset_handover', 'Return_Lead_NIK')) {
            $updateHeader['Return_Lead_NIK']      = trim($returnLeadNik);
            $updateHeader['Return_Lead_Name']     = $leadDisplay;
            $updateHeader['Return_Receiver_NIK']  = trim($returnReceiverNik);
            $updateHeader['Return_Receiver_Name'] = $receiverDisplay;
        }
        if ($note !== '') {
            $updateHeader['Approval_Note'] = $note;
        }
        $updateHeader['Updated_By']   = $requester->username ?? 'system';
        $updateHeader['Updated_Date'] = now();

        if (!empty($updateHeader)) {
            DB::table('asset_handover')->where('ID', $handoverId)->update($updateHeader);
        }

        $handover = DB::table('asset_handover')->where('ID', $handoverId)->first();

        $context = [
            'Request_Type'             => self::TYPE_RETURN,
            'Handover_No'              => (string) $handover->Handover_No,
            'Location'                 => ($requester->Location ?? '') ?: 'GEU-HO',
            'Company_ID'               => ($requester->Company_ID ?? '') ?: 'GEU',
            'Department'               => ($handover->PIC_Department ?? '') ?: (($requester->Department ?? '') ?: 'HCGA'),
            'Amount'                   => 0,
            'Skip_Stages'              => $skipStages,
            'Stage_Candidate_User_Ids' => [
                '1' => [(int) $returnLeadUser->id],
                '2' => [(int) $returnReceiverUser->id],
            ],
            'Stage_Candidate_Meta' => [
                '1' => [
                    'Code'       => 'RETURN_PIC',
                    'Name'       => 'PIC Approval Return: ' . $leadDisplay,
                    'Level'      => 5,
                    'Department' => $handover->PIC_Department ?: ($returnLeadEmp->Department ?? null),
                ],
                '2' => [
                    'Code'       => 'RETURN_RECEIVER',
                    'Name'       => 'Penerima Pengembalian: ' . $receiverDisplay,
                    'Level'      => 3,
                    'Department' => $returnReceiverEmp->Department ?? ($returnReceiverUser->Department ?? 'HCGA'),
                ],
            ],
        ];

        $description = sprintf(
            'Pengembalian aset %s (PIC Approval: %s -> Penerima: %s). %s',
            $handover->Handover_No,
            $leadDisplay,
            $receiverDisplay,
            $note !== '' ? ('Catatan: ' . $note) : ''
        );

        $approvalReq = $this->workflow->submit([
            'Request_No'  => (string) $handover->Handover_No,
            'Description' => trim($description),
        ], $context, $requesterId);

        DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
            'Map_To' => $handoverId,
        ]);

        $freshReq = $this->workflow->getRequest((int) $approvalReq->ID);
        if (Schema::hasColumn('asset_handover', 'Return_Approval_Request_ID')) {
            DB::table('asset_handover')->where('ID', $handoverId)->update([
                'Return_Approval_Request_ID' => (int) $freshReq->ID,
            ]);
        }

        $freshHeader = DB::table('asset_handover')->where('ID', $handoverId)->first();
        $this->bastService->writeAuditLog(
            $freshHeader ?: $handover,
            $requester->username ?? 'system',
            'RETURN_SUBMITTED',
            'Pengajuan pengembalian dikirim ke ApprovalWorkflowService (Req #' . $freshReq->ID . ')'
        );

        return $freshReq;
    }

    /**
     * BAST kini otomatis disistemkan (mPDF), sehingga approval tidak lagi diblokir upload manual.
     */
    public function assertBastBeforeApproveIfFinal(object $approvalRequest, bool $isForceApprove = false): void
    {
        // Intentionally non-blocking: BAST is system-generated by AssetHandoverBastPdfService.
    }

    public function isOnFinalStage(object $approvalRequest): bool
    {
        $steps = $this->workflow->stepsForRequest($approvalRequest);
        if (empty($steps)) {
            return true;
        }

        $maxStage = 0;
        foreach ($steps as $step) {
            $st = (int) ($step['Stage_Order'] ?? 0);
            if ($st > $maxStage) {
                $maxStage = $st;
            }
        }

        return (int) ($approvalRequest->Current_Stage ?? 0) >= $maxStage;
    }

    public function resolveHandoverFromRequest(object $approvalRequest): ?object
    {
        $mapTo = (int) ($approvalRequest->Map_To ?? 0);
        if ($mapTo > 0) {
            $row = DB::table('asset_handover')->where('ID', $mapTo)->first();
            if ($row) {
                return $row;
            }
        }

        $context = json_decode((string) ($approvalRequest->Context ?? '{}'), true) ?: [];
        $handoverNo = trim((string) ($context['Handover_No'] ?? ($approvalRequest->Request_No ?? '')));
        if ($handoverNo !== '') {
            return DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        }

        return null;
    }

    public function resolveHandoverNoFromRequest(object $approvalRequest): string
    {
        $context = json_decode((string) ($approvalRequest->Context ?? '{}'), true) ?: [];
        if (!empty($context['Handover_No'])) {
            return trim((string) $context['Handover_No']);
        }

        $row = $this->resolveHandoverFromRequest($approvalRequest);
        if ($row && !empty($row->Handover_No)) {
            return (string) $row->Handover_No;
        }

        return trim((string) ($approvalRequest->Request_No ?? ''));
    }

    public function actOnHandover(int $approvalRequestId, int $userId, string $action, string $notes = ''): array
    {
        return $this->actOnRequest($approvalRequestId, $userId, $action, $notes, self::TYPE_HANDOVER);
    }

    public function actOnReturn(int $approvalRequestId, int $userId, string $action, string $notes = ''): array
    {
        return $this->actOnRequest($approvalRequestId, $userId, $action, $notes, self::TYPE_RETURN);
    }

    protected function actOnRequest(int $approvalRequestId, int $userId, string $action, string $notes, string $expectedType): array
    {
        $action = strtoupper(trim($action));
        if (!in_array($action, ['APPROVE', 'REJECT'], true)) {
            throw new ApprovalWorkflowException('Action harus APPROVE atau REJECT.', 'ACTION_INVALID', 422);
        }

        $request = $this->workflow->getRequest($approvalRequestId);
        if (!$request) {
            throw new ApprovalWorkflowException('Approval request tidak ditemukan.', 'REQUEST_NOT_FOUND', 404);
        }

        $tasks = $this->pendingTasksForUser($approvalRequestId, $userId);
        if (empty($tasks)) {
            throw new ApprovalWorkflowException('Anda tidak memiliki tugas approval aktif pada tahap ini.', 'UNAUTHORIZED_APPROVER', 403);
        }

        return DB::transaction(function () use ($approvalRequestId, $userId, $action, $notes, $expectedType, $tasks) {
            $acted = [];
            foreach ($tasks as $task) {
                $current = DB::table('approval_request_task')->where('ID', (int) $task->ID)->first();
                if (!$current || !in_array(strtoupper((string) $current->Status), ['PENDING', 'CLAIMED'], true)) {
                    continue;
                }

                if (strtoupper((string) $current->Status) === 'PENDING') {
                    $this->workflow->claim((int) $current->ID, $userId);
                }

                $this->workflow->act((int) $current->ID, $userId, $action, $notes);
                $acted[] = (int) $current->ID;

                if ($action === 'REJECT') {
                    break;
                }
            }

            $finalStatus = (string) ($this->workflow->getRequest($approvalRequestId)->Status ?? '');
            if ($expectedType === self::TYPE_HANDOVER) {
                $this->syncHandoverFromWorkflow($approvalRequestId, $finalStatus, $notes);
            } else {
                $this->syncReturnFromWorkflow($approvalRequestId, $finalStatus, $notes);
            }

            return $acted;
        });
    }

    public function pendingTasksForUser(int $approvalRequestId, int $userId): array
    {
        $request = $this->workflow->getRequest($approvalRequestId);
        if (!$request || in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return [];
        }

        $query = DB::table('approval_request_task as t')
            ->join('approval_task_candidate as c', function ($join) use ($userId) {
                $join->on('c.Task_ID', '=', 't.ID')
                    ->where('c.User_Id', '=', $userId)
                    ->where('c.Status', '=', 'ACTIVE');
            })
            ->where('t.Request_ID', $approvalRequestId)
            ->where('t.Stage_Order', (int) $request->Current_Stage)
            ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
            ->where(function ($task) use ($userId) {
                $task->where('t.Status', 'PENDING')
                    ->orWhere(function ($claimed) use ($userId) {
                        $claimed->where('t.Status', 'CLAIMED')
                            ->where('t.Claimed_By', $userId);
                    });
            });

        if ((int) $request->Requester_Id === $userId) {
            $now = \Illuminate\Support\Carbon::now();
            $query->whereExists(function ($ex) use ($userId, $now) {
                $ex->select(DB::raw(1))
                    ->from('approval_self_exception as ase')
                    ->whereColumn('ase.Request_ID', 't.Request_ID')
                    ->where('ase.User_Id', $userId)
                    ->whereNull('ase.Revoked_At')
                    ->where(function ($q) {
                        $q->whereNull('ase.Task_ID')->orWhereColumn('ase.Task_ID', 't.ID');
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('ase.Expires_At')->orWhere('ase.Expires_At', '>', $now);
                    });
            });
        }

        return $query->select('t.*')->get()->all();
    }

    /**
     * Sinkronisasi status asset_handover, asset_handover_dtl, & asset_list setelah approval Handover.
     */
    public function syncHandoverFromWorkflow(int $approvalRequestId, ?string $reqStatus = null, string $notes = ''): void
    {
        $request = $this->workflow->getRequest($approvalRequestId);
        if (!$request) {
            return;
        }

        $handover = $this->resolveHandoverFromRequest($request);
        if (!$handover) {
            return;
        }

        $statusUpper = strtoupper((string) ($reqStatus ?: $request->Status));
        $actor = $this->resolveLatestActorUsername($approvalRequestId) ?: 'approval_engine';
        $finalNote = $notes !== '' ? $notes : ($this->resolveLatestApprovalNote($approvalRequestId) ?: (string) ($handover->Approval_Note ?? ''));

        if ($statusUpper === 'APPROVED') {
            $alreadyApproved = strtoupper((string) $handover->Status) === 'APPROVED';

            DB::table('asset_handover')->where('ID', $handover->ID)->update([
                'Status'        => 'APPROVED',
                'Approval_Note' => $finalNote,
                'Updated_By'    => $actor,
                'Updated_Date'  => now(),
            ]);

            DB::table('asset_handover_dtl')
                ->where('Handover_No', $handover->Handover_No)
                ->where(function ($q) {
                    $q->whereNull('Status')
                      ->orWhere('Status', '')
                      ->orWhereIn('Status', ['CREATED', 'UPDATED', 'WAITING_APPROVAL']);
                })
                ->update([
                    'Status'        => 'APPROVED',
                    'Received_Date' => DB::raw('COALESCE(Received_Date, CURDATE())'),
                ]);

            if (!$alreadyApproved) {
                $this->syncAssetListOnHandoverApproved($handover, $actor, $finalNote);

                $fresh = DB::table('asset_handover')->where('ID', $handover->ID)->first();
                $this->bastService->writeAuditLog($fresh ?: $handover, $actor, 'HANDOVER_APPROVED', $finalNote ?: 'Handover disetujui via ApprovalWorkflowService');
            }
        } elseif (in_array($statusUpper, ['REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            $targetStatus = $statusUpper === 'CANCELLED' ? 'CANCELLED' : 'REJECTED';
            if (strtoupper((string) $handover->Status) !== $targetStatus) {
                DB::table('asset_handover')->where('ID', $handover->ID)->update([
                    'Status'        => $targetStatus,
                    'Approval_Note' => $finalNote,
                    'Updated_By'    => $actor,
                    'Updated_Date'  => now(),
                ]);

                DB::table('asset_handover_dtl')
                    ->where('Handover_No', $handover->Handover_No)
                    ->whereNotIn('Status', ['RETURNED'])
                    ->update(['Status' => $targetStatus]);

                $fresh = DB::table('asset_handover')->where('ID', $handover->ID)->first();
                $this->bastService->writeAuditLog($fresh ?: $handover, $actor, 'HANDOVER_' . $targetStatus, $finalNote ?: 'Handover ditolak via ApprovalWorkflowService');
            }
        }
    }

    /**
     * Sinkronisasi status asset_handover, asset_handover_dtl, & asset_list setelah approval Return.
     */
    public function syncReturnFromWorkflow(int $approvalRequestId, ?string $reqStatus = null, string $notes = ''): void
    {
        $request = $this->workflow->getRequest($approvalRequestId);
        if (!$request) {
            return;
        }

        $handover = $this->resolveHandoverFromRequest($request);
        if (!$handover) {
            return;
        }

        $statusUpper = strtoupper((string) ($reqStatus ?: $request->Status));
        $actor = $this->resolveLatestActorUsername($approvalRequestId) ?: 'approval_engine';
        $finalNote = $notes !== '' ? $notes : ($this->resolveLatestApprovalNote($approvalRequestId) ?: (string) ($handover->Approval_Note ?? ''));

        if ($statusUpper === 'APPROVED') {
            $this->processReturnApprovalOutcome($handover, true, $actor, $finalNote);
        } elseif (in_array($statusUpper, ['REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            $this->processReturnApprovalOutcome($handover, false, $actor, $finalNote);
        }
    }

    /**
     * Eksekusi final approval/rejection return secara idempoten (dipakai workflow modern & fallback legacy).
     * Mendukung pengembalian sebagian qty (split detail row) dan pembaruan asset_list kembali ke GA / Pool.
     */
    public function processReturnApprovalOutcome(object $handover, bool $approved, string $actor, string $finalNote = ''): void
    {
        if ($approved) {
            $pendingItems = DB::table('asset_handover_dtl')
                ->where('Handover_No', $handover->Handover_No)
                ->where('Status', 'RETURN_PENDING')
                ->get();

            $unreturnedCount = DB::table('asset_handover_dtl')
                ->where('Handover_No', $handover->Handover_No)
                ->where('Status', '!=', 'RETURNED')
                ->count();

            $alreadyFullySynced = $pendingItems->isEmpty()
                && ($unreturnedCount > 0 || strtoupper((string) ($handover->Status ?? '')) === 'RETURNED');
            if ($alreadyFullySynced) {
                return;
            }

            if ($pendingItems->isNotEmpty()) {
                foreach ($pendingItems as $item) {
                    $origQty = (float) ($item->Qty ?? 0);
                    $retQty  = (float) ($item->Returned_Qty ?? $origQty);

                    if ($retQty > 0 && $origQty > 0 && $retQty < $origQty) {
                        $remQty = $origQty - $retQty;
                        $unitPrice = (float) ($item->Default_Price ?? 0);

                        DB::table('asset_handover_dtl')
                            ->where('ID', $item->ID)
                            ->update([
                                'Qty'          => $retQty,
                                'Returned_Qty' => $retQty,
                                'Prices'       => $unitPrice > 0 ? ($unitPrice * $retQty) : ($item->Prices ?? 0),
                                'Status'       => 'RETURNED',
                            ]);

                        $remainingRow = (array) $item;
                        unset($remainingRow['ID']);
                        $remainingRow['Qty']                = $remQty;
                        $remainingRow['Prices']             = $unitPrice > 0 ? ($unitPrice * $remQty) : 0;
                        $remainingRow['Status']             = 'APPROVED';
                        $remainingRow['Returned_Qty']       = null;
                        $remainingRow['Returned_Condition'] = null;
                        $remainingRow['Returned_Date']      = null;
                        $remainingRow['Returned_Remark']    = null;
                        DB::table('asset_handover_dtl')->insert($remainingRow);
                    } else {
                        DB::table('asset_handover_dtl')
                            ->where('ID', $item->ID)
                            ->update([
                                'Status'       => 'RETURNED',
                                'Returned_Qty' => $origQty > 0 ? $origQty : $retQty,
                            ]);
                    }
                }

                $this->syncAssetListOnReturnApproved($handover, $pendingItems, $actor, $finalNote);
            }

            $unreturnedAfter = DB::table('asset_handover_dtl')
                ->where('Handover_No', $handover->Handover_No)
                ->where('Status', '!=', 'RETURNED')
                ->count();

            $updateData = [
                'Approval_Note' => $finalNote,
                'Updated_By'    => $actor,
                'Updated_Date'  => now(),
            ];
            if ($unreturnedAfter === 0) {
                $updateData['Status'] = 'RETURNED';
            }

            DB::table('asset_handover')->where('ID', $handover->ID)->update($updateData);

            $fresh = DB::table('asset_handover')->where('ID', $handover->ID)->first();
            $this->bastService->writeAuditLog($fresh ?: $handover, $actor, 'RETURN_APPROVED', $finalNote ?: 'Pengembalian aset disetujui');
        } else {
            $reverted = DB::table('asset_handover_dtl')
                ->where('Handover_No', $handover->Handover_No)
                ->where('Status', 'RETURN_PENDING')
                ->update(['Status' => 'APPROVED']);

            if ($reverted > 0) {
                DB::table('asset_handover')->where('ID', $handover->ID)->update([
                    'Approval_Note' => $finalNote,
                    'Updated_By'    => $actor,
                    'Updated_Date'  => now(),
                ]);

                $fresh = DB::table('asset_handover')->where('ID', $handover->ID)->first();
                $this->bastService->writeAuditLog($fresh ?: $handover, $actor, 'RETURN_REJECTED', $finalNote ?: 'Pengajuan pengembalian ditolak');
            }
        }
    }

    /**
     * Perbarui pemegang & departemen unit aset di asset_list (halaman asset-lists) saat Handover disetujui PIC.
     */
    public function syncAssetListOnHandoverApproved(object $handover, string $actor, string $note = ''): void
    {
        if (!Schema::hasTable('asset_list')) {
            return;
        }

        $items = DB::table('asset_handover_dtl')->where('Handover_No', $handover->Handover_No)->get();
        $getPICWH = DB::table('users')->select('WH_ID')->where('Emp_ID', $handover->PIC_NIK)->first();
        $hasAssetNoCol = Schema::hasColumn('asset_handover_dtl', 'Asset_No');
        $effectiveDate = !empty($handover->Handover_Date) ? date('Y-m-d', strtotime($handover->Handover_Date)) : date('Y-m-d');

        foreach ($items as $item) {
            $matchedAssets = [];
            $itemAssetNo = $hasAssetNoCol ? trim((string) ($item->Asset_No ?? '')) : '';

            if ($itemAssetNo !== '') {
                $found = DB::table('asset_list')->where('Asset_No', $itemAssetNo)->first();
                if ($found) {
                    $assetStatus = strtoupper((string) ($found->Status ?? ''));
                    if ($assetStatus === 'DISPOSED') {
                        throw new \RuntimeException("Aset {$found->Asset_No} sudah berstatus DISPOSED dan tidak dapat diserahkan.");
                    }
                    $currentHolder = trim((string) ($found->User_Responsible ?? ''));
                    $currentHolderNik = property_exists($found, 'User_Responsible_NIK') ? trim((string) ($found->User_Responsible_NIK ?? '')) : '';
                    $isAvailable = $currentHolder === '' || strcasecmp($currentHolder, 'GA / Pool') === 0;
                    $isSamePic = strcasecmp($currentHolder, (string) ($handover->PIC_Name ?? '')) === 0
                        || ($currentHolderNik !== '' && strcasecmp($currentHolderNik, (string) ($handover->PIC_NIK ?? '')) === 0);
                    if (!$isAvailable && !$isSamePic) {
                        throw new \RuntimeException("Aset {$found->Asset_No} saat ini sedang dipegang oleh {$currentHolder}.");
                    }
                    $matchedAssets[] = $found;
                }
            }

            if (empty($matchedAssets) && !empty($item->GR_No)) {
                $found = DB::table('asset_list')
                    ->where('GR_No', $item->GR_No)
                    ->where(function ($q) use ($item) {
                        $q->where('Product_Description', $item->Description ?? '')
                          ->orWhere('Asset_Name', $item->Description ?? '');
                    })
                    ->first();
                if ($found) {
                    $matchedAssets[] = $found;
                }
            }

            if (empty($matchedAssets) && !empty($item->Product_Code)) {
                $qtyLimit = max(1, (int) round((float) ($item->Qty ?? 1)));
                $matchedAssets = DB::table('asset_list')
                    ->where('Product_Code', $item->Product_Code)
                    ->where(function ($q) {
                        $q->whereNull('User_Responsible')
                          ->orWhere('User_Responsible', '')
                          ->orWhere('User_Responsible', 'GA / Pool');
                    })
                    ->where(function ($q) {
                        $q->whereNull('Status')->orWhere('Status', '!=', 'DISPOSED');
                    })
                    ->orderBy('ID', 'ASC')
                    ->limit($qtyLimit)
                    ->get()
                    ->all();

                if ($hasAssetNoCol && count($matchedAssets) === 1 && $itemAssetNo === '') {
                    DB::table('asset_handover_dtl')->where('ID', $item->ID)->update([
                        'Asset_No' => $matchedAssets[0]->Asset_No,
                    ]);
                }
            }

            foreach ($matchedAssets as $assetExists) {
                $extraUpdates = [
                    'Status' => 'ACTIVE',
                ];
                if (Schema::hasColumn('asset_list', 'User_Responsible_NIK')) {
                    $extraUpdates['User_Responsible_NIK'] = $handover->PIC_NIK;
                }
                if (Schema::hasColumn('asset_list', 'Asset_Location_Department') && !empty($handover->PIC_Department)) {
                    $extraUpdates['Asset_Location_Department'] = $handover->PIC_Department;
                }
                if (Schema::hasColumn('asset_list', 'WH_ID')) {
                    $extraUpdates['WH_ID'] = $getPICWH ? $getPICWH->WH_ID : ($assetExists->WH_ID ?? null);
                }
                if (Schema::hasColumn('asset_list', 'Handover_Date')) {
                    $extraUpdates['Handover_Date'] = $effectiveDate;
                }

                $this->lifecycleService->recordUserTransfer(
                    $assetExists->Asset_No,
                    $assetExists->User_Responsible ?? null,
                    (string) $handover->PIC_Name,
                    'HANDOVER',
                    (string) $handover->Handover_No,
                    $actor,
                    $note !== '' ? $note : ($handover->Remark ?? 'Handover aset disetujui'),
                    $effectiveDate,
                    $extraUpdates
                );
            }
        }
    }

    /**
     * Kembalikan pemegang aset pada asset_list ke GA / Pool saat pengembalian disetujui.
     */
    public function syncAssetListOnReturnApproved(object $handover, $returnedItems, string $actor, string $note = ''): void
    {
        if (!Schema::hasTable('asset_list')) {
            return;
        }

        $hasAssetNoCol = Schema::hasColumn('asset_handover_dtl', 'Asset_No');
        $receiverNik = trim((string) ($handover->Return_Receiver_NIK ?? ''));
        $receiverUser = $receiverNik !== '' ? DB::table('users')->select('WH_ID', 'Department')->where('Emp_ID', $receiverNik)->first() : null;
        $receiverEmp  = ($receiverNik !== '' && Schema::hasTable('employment'))
            ? DB::table('employment')->select('Department')->where('NIK', $receiverNik)->first()
            : null;
        $receiverDept = trim((string) ($receiverEmp->Department ?? ($receiverUser->Department ?? 'HCGA'))) ?: 'HCGA';

        foreach ($returnedItems as $item) {
            $matchedAssets = [];
            $itemAssetNo = $hasAssetNoCol ? trim((string) ($item->Asset_No ?? '')) : '';

            if ($itemAssetNo !== '') {
                $found = DB::table('asset_list')->where('Asset_No', $itemAssetNo)->first();
                if ($found) {
                    $matchedAssets[] = $found;
                }
            }

            if (empty($matchedAssets) && !empty($item->GR_No)) {
                $found = DB::table('asset_list')
                    ->where('GR_No', $item->GR_No)
                    ->where(function ($q) use ($item) {
                        $q->where('Product_Description', $item->Description ?? '')
                          ->orWhere('Asset_Name', $item->Description ?? '');
                    })
                    ->first();
                if ($found) {
                    $matchedAssets[] = $found;
                }
            }

            if (empty($matchedAssets) && !empty($item->Product_Code)) {
                $qtyToReturn = max(1, (int) round((float) ($item->Returned_Qty ?? ($item->Qty ?? 1))));
                $matchedAssets = DB::table('asset_list')
                    ->where('Product_Code', $item->Product_Code)
                    ->where(function ($q) use ($handover) {
                        $q->where('User_Responsible', $handover->PIC_Name ?? '');
                        if (Schema::hasColumn('asset_list', 'User_Responsible_NIK') && !empty($handover->PIC_NIK)) {
                            $q->orWhere('User_Responsible_NIK', $handover->PIC_NIK);
                        }
                    })
                    ->orderBy('ID', 'ASC')
                    ->limit($qtyToReturn)
                    ->get()
                    ->all();
            }

            foreach ($matchedAssets as $assetExists) {
                if (strtoupper((string) ($assetExists->Status ?? '')) === 'DISPOSED') {
                    continue;
                }

                $extraUpdates = [];
                if (Schema::hasColumn('asset_list', 'User_Responsible_NIK')) {
                    $extraUpdates['User_Responsible_NIK'] = null;
                }
                if (Schema::hasColumn('asset_list', 'Asset_Location_Department')) {
                    $extraUpdates['Asset_Location_Department'] = $receiverDept;
                }
                if (Schema::hasColumn('asset_list', 'WH_ID') && $receiverUser && !empty($receiverUser->WH_ID)) {
                    $extraUpdates['WH_ID'] = $receiverUser->WH_ID;
                }
                if (Schema::hasColumn('asset_list', 'Kondisi_Aset') && !empty($item->Returned_Condition)) {
                    $extraUpdates['Kondisi_Aset'] = $this->normalizeConditionForAssetList((string) $item->Returned_Condition);
                }
                if (strtoupper((string) ($assetExists->Status ?? '')) !== 'MAINTENANCE') {
                    $extraUpdates['Status'] = 'ACTIVE';
                }

                $retDate = !empty($item->Returned_Date) ? date('Y-m-d', strtotime($item->Returned_Date)) : date('Y-m-d');

                $this->lifecycleService->recordUserTransfer(
                    $assetExists->Asset_No,
                    $assetExists->User_Responsible ?? null,
                    'GA / Pool',
                    'RETURN',
                    (string) $handover->Handover_No,
                    $actor,
                    $item->Returned_Remark ?: ($note ?: 'Pengembalian aset disetujui'),
                    $retDate,
                    $extraUpdates
                );
            }
        }
    }

    protected function normalizeConditionForAssetList(string $raw): string
    {
        $upper = strtoupper(trim($raw));
        if ($upper === '' || $upper === 'BAIK' || $upper === 'GOOD') {
            return 'BAIK';
        }
        if (strpos($upper, 'BERAT') !== false) {
            return 'RUSAK BERAT';
        }
        if (strpos($upper, 'RUSAK') !== false || strpos($upper, 'DAMAGE') !== false) {
            return 'RUSAK RINGAN';
        }

        return mb_substr($upper, 0, 20);
    }

    protected function resolveLatestActorUsername(int $approvalRequestId): ?string
    {
        $row = DB::table('approval_log as l')
            ->join('users as u', 'u.id', '=', 'l.Approver_Id')
            ->where('l.Request_Id', $approvalRequestId)
            ->whereIn('l.Action', ['APPROVE', 'REJECT', 'FORCE_APPROVED'])
            ->orderBy('l.ID', 'DESC')
            ->select('u.username')
            ->first();

        return $row ? (string) $row->username : null;
    }

    protected function resolveLatestApprovalNote(int $approvalRequestId): ?string
    {
        $note = DB::table('approval_log')
            ->where('Request_Id', $approvalRequestId)
            ->whereIn('Action', ['APPROVE', 'REJECT', 'FORCE_APPROVED'])
            ->whereNotNull('Notes')
            ->where('Notes', '!=', '')
            ->orderBy('ID', 'DESC')
            ->value('Notes');

        return $note ? (string) $note : null;
    }
}
