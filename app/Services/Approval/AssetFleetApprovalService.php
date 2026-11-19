<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Illuminate\Support\Facades\DB;

/**
 * Adapter terpusat antara modul Asset Maintenance Schedule & Vehicle Fleet Management
 * dengan ApprovalWorkflowService.
 */
class AssetFleetApprovalService
{
    public const TYPE_MAINTENANCE_SCHEDULE = 'ASSET_MAINTENANCE_SCHEDULE';
    public const TYPE_VEHICLE_TAX          = 'VEHICLE_TAX_REQUEST';
    public const TYPE_VEHICLE_FLEET        = 'VEHICLE_FLEET';
    public const TYPE_VEHICLE_LOGBOOK      = 'VEHICLE_LOGBOOK';

    /** @var ApprovalWorkflowService */
    protected $workflow;

    /** @var ApprovalAuthorizationService */
    protected $authorizationService;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalAuthorizationService $authorizationService
    ) {
        $this->workflow = $workflow;
        $this->authorizationService = $authorizationService;
    }

    /**
     * Submit pengajuan ke ApprovalWorkflowService.
     */
    public function submit(
        string $requestType,
        int $domainId,
        string $docNo,
        string $description,
        int $requesterId,
        array $extraContext = []
    ): object {
        $user = DB::table('users')->where('id', $requesterId)->first();
        $context = array_merge([
            'Request_Type' => $requestType,
            'Location'     => ($user->Location ?? '') ?: 'GEU-HO',
            'Company_ID'   => ($user->Company_ID ?? '') ?: 'GEU',
            'Department'   => ($user->Department ?? '') ?: 'HCGA',
            'Amount'       => (float) ($extraContext['Amount'] ?? 0),
        ], $extraContext);

        $approvalReq = $this->workflow->submit([
            'Request_No'  => $docNo,
            'Description' => $description,
        ], $context, $requesterId);

        DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
            'Map_To' => $domainId,
        ]);

        return $this->workflow->getRequest((int) $approvalReq->ID);
    }

    /**
     * Eksekusi APPROVE / REJECT pada approval_request yang terhubung ke domain.
     */
    public function actOnRequest(int $approvalRequestId, int $userId, string $action, string $notes = ''): array
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
            throw new ApprovalWorkflowException('Anda tidak memiliki tugas approval aktif untuk pengajuan ini.', 'UNAUTHORIZED_APPROVER', 403);
        }

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

        $this->syncStatus((string) $request->Request_Type, $approvalRequestId);

        return $acted;
    }

    /**
     * Ajukan ulang pengajuan yang berstatus RETURNED / REJECTED.
     */
    public function resubmit(int $oldApprovalRequestId, int $domainId, int $userId, array $context = []): object
    {
        $newReq = $this->workflow->resubmit($oldApprovalRequestId, $context, $userId);

        DB::table('approval_request')->where('ID', (int) $newReq->ID)->update([
            'Map_To' => $domainId,
        ]);

        return $this->workflow->getRequest((int) $newReq->ID);
    }

    /**
     * Eksekusi Force Approve khusus untuk username 'admin' melalui ApprovalWorkflowService.
     */
    public function forceApprove(int $approvalRequestId, int $adminId, string $reason): void
    {
        $admin = DB::table('users')->where('id', $adminId)->first();
        if (!$admin || strtolower((string) ($admin->username ?? '')) !== 'admin') {
            throw new ApprovalWorkflowException('Force Approve hanya diizinkan untuk username admin.', 'FORCE_APPROVE_FORBIDDEN', 403);
        }

        $request = $this->workflow->forceApprove($approvalRequestId, $adminId, $reason);
        $this->syncStatus((string) $request->Request_Type, $approvalRequestId);
    }

    /**
     * Cek apakah user merupakan kandidat aktual pada stage berjalan (tanpa wildcard admin).
     */
    public function isActualCandidate(int $requestId, int $userId): bool
    {
        if ($requestId < 1 || $userId < 1) {
            return false;
        }

        $request = $this->workflow->getRequest($requestId);
        if (!$request || in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return false;
        }

        return DB::table('approval_request_task as t')
            ->join('approval_task_candidate as c', function ($join) use ($userId) {
                $join->on('c.Task_ID', '=', 't.ID')
                    ->where('c.User_Id', '=', $userId)
                    ->where('c.Status', '=', 'ACTIVE');
            })
            ->where('t.Request_ID', $requestId)
            ->where('t.Stage_Order', (int) $request->Current_Stage)
            ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
            ->where(function ($task) use ($userId) {
                $task->where('t.Status', 'PENDING')
                    ->orWhere(function ($claimed) use ($userId) {
                        $claimed->where('t.Status', 'CLAIMED')
                            ->where('t.Claimed_By', $userId);
                    });
            })
            ->exists();
    }

    /**
     * Cari task aktif user pada stage berjalan.
     */
    public function pendingTasksForUser(int $requestId, int $userId): array
    {
        $request = $this->workflow->getRequest($requestId);
        if (!$request || in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return [];
        }

        $user = \App\User::find($userId);
        $isWildcard = $user ? $this->authorizationService->isWildcardAdmin($user) : false;

        $query = DB::table('approval_request_task as t')
            ->where('t.Request_ID', $requestId)
            ->where('t.Stage_Order', (int) $request->Current_Stage)
            ->whereIn('t.Status', ['PENDING', 'CLAIMED']);

        if (!$isWildcard) {
            $query->join('approval_task_candidate as c', function ($join) use ($userId) {
                $join->on('c.Task_ID', '=', 't.ID')
                    ->where('c.User_Id', '=', $userId)
                    ->where('c.Status', '=', 'ACTIVE');
            })
            ->where(function ($task) use ($userId) {
                $task->where('t.Status', 'PENDING')
                    ->orWhere(function ($claimed) use ($userId) {
                        $claimed->where('t.Status', 'CLAIMED')
                            ->where('t.Claimed_By', $userId);
                    });
            });
        }

        return $query->select('t.*')->orderBy('t.ID', 'ASC')->get()->all();
    }

    /**
     * Sinkronisasi status approval_request ke tabel domain masing-masing (idempoten).
     */
    public function syncStatus(string $requestType, int $approvalRequestId): void
    {
        $request = $this->workflow->getRequest($approvalRequestId);
        if (!$request) {
            return;
        }

        $statusUpper = strtoupper((string) $request->Status);
        $lastNote = $this->getLastActionNote($approvalRequestId);

        switch (strtoupper(trim($requestType))) {
            case self::TYPE_MAINTENANCE_SCHEDULE:
                $row = DB::table('asset_maintenance_schedule')->where('Approval_Request_ID', $approvalRequestId)->first();
                if (!$row) {
                    $row = DB::table('asset_maintenance_schedule')->where('ID', (int) ($request->Map_To ?? 0))->first();
                }
                if (!$row) {
                    return;
                }

                if ($statusUpper === 'APPROVED') {
                    if (in_array($row->Status, ['FINANCE_SUBMITTED', 'SCHEDULED', 'IN_EXECUTION', 'COMPLETED'], true)) {
                        return;
                    }
                    $voucherNo = $row->Voucher_No ?: ('VCH/MNT/' . date('Ym') . '-' . str_pad((string) $row->ID, 4, '0', STR_PAD_LEFT));
                    DB::table('asset_maintenance_schedule')->where('ID', $row->ID)->update([
                        'Status'        => 'FINANCE_SUBMITTED',
                        'Voucher_No'    => $voucherNo,
                        'Approval_Note' => $lastNote ?: $row->Approval_Note,
                        'Updated_At'    => now(),
                    ]);
                } elseif (in_array($statusUpper, ['RETURNED', 'REJECTED'], true)) {
                    DB::table('asset_maintenance_schedule')->where('ID', $row->ID)->update([
                        'Status'        => $statusUpper,
                        'Approval_Note' => $lastNote ?: $row->Approval_Note,
                        'Updated_At'    => now(),
                    ]);
                }
                break;

            case self::TYPE_VEHICLE_TAX:
                $row = DB::table('vehicle_tax_request')->where('Approval_Request_ID', $approvalRequestId)->first();
                if (!$row) {
                    $row = DB::table('vehicle_tax_request')->where('ID', (int) ($request->Map_To ?? 0))->first();
                }
                if (!$row) {
                    return;
                }

                if ($statusUpper === 'APPROVED') {
                    if (in_array($row->Status, ['FINANCE_SUBMITTED', 'PAID'], true)) {
                        return;
                    }
                    $voucherNo = $row->Voucher_No ?: ('VCH/TAX/' . date('Ym') . '-' . str_pad((string) $row->ID, 4, '0', STR_PAD_LEFT));
                    DB::table('vehicle_tax_request')->where('ID', $row->ID)->update([
                        'Status'        => 'FINANCE_SUBMITTED',
                        'Voucher_No'    => $voucherNo,
                        'Approval_Note' => $lastNote ?: $row->Approval_Note,
                        'Updated_At'    => now(),
                    ]);
                } elseif (in_array($statusUpper, ['RETURNED', 'REJECTED'], true)) {
                    DB::table('vehicle_tax_request')->where('ID', $row->ID)->update([
                        'Status'        => $statusUpper,
                        'Approval_Note' => $lastNote ?: $row->Approval_Note,
                        'Updated_At'    => now(),
                    ]);
                }
                break;

            case self::TYPE_VEHICLE_FLEET:
                $row = DB::table('asset_list')->where('Fleet_Approval_Request_ID', $approvalRequestId)->first();
                if (!$row) {
                    $row = DB::table('asset_list')->where('ID', (int) ($request->Map_To ?? 0))->first();
                }
                if (!$row) {
                    return;
                }

                if ($statusUpper === 'APPROVED') {
                    DB::table('asset_list')->where('ID', $row->ID)->update([
                        'Fleet_Approval_Status' => 'APPROVED',
                        'Status'                => 'ACTIVE',
                        'Last_Modified_Date'    => now(),
                    ]);
                } elseif (in_array($statusUpper, ['RETURNED', 'REJECTED'], true)) {
                    DB::table('asset_list')->where('ID', $row->ID)->update([
                        'Fleet_Approval_Status' => $statusUpper,
                        'Status'                => 'INACTIVE',
                        'Last_Modified_Date'    => now(),
                    ]);
                }
                break;

            case self::TYPE_VEHICLE_LOGBOOK:
                $row = DB::table('vehicle_logbook')->where('Approval_Request_ID', $approvalRequestId)->first();
                if (!$row) {
                    $row = DB::table('vehicle_logbook')->where('id', (int) ($request->Map_To ?? 0))->first();
                }
                if (!$row) {
                    return;
                }

                $rowId = $row->id ?? $row->ID;
                if ($statusUpper === 'APPROVED') {
                    $actor = $this->getLastActorUsername($approvalRequestId);
                    DB::table('vehicle_logbook')->where('id', $rowId)->update([
                        'Approval_Status' => 'APPROVED',
                        'Approved_By'     => $actor,
                        'Approved_At'     => now(),
                        'Approval_Note'   => $lastNote,
                    ]);
                } elseif (in_array($statusUpper, ['RETURNED', 'REJECTED'], true)) {
                    DB::table('vehicle_logbook')->where('id', $rowId)->update([
                        'Approval_Status' => $statusUpper,
                        'Approval_Note'   => $lastNote,
                    ]);
                }
                break;
        }
    }

    protected function getLastActionNote(int $requestId): ?string
    {
        $log = DB::table('approval_log')
            ->where('Request_Id', $requestId)
            ->whereIn('Action', ['APPROVED', 'REJECTED', 'RETURNED'])
            ->orderBy('ID', 'DESC')
            ->first();

        return ($log && !empty($log->Notes)) ? (string) $log->Notes : null;
    }

    protected function getLastActorUsername(int $requestId): string
    {
        $log = DB::table('approval_log')
            ->where('Request_Id', $requestId)
            ->where('Action', 'APPROVED')
            ->orderBy('ID', 'DESC')
            ->first();

        if ($log && !empty($log->Approver_Id)) {
            $u = DB::table('users')->where('id', (int) $log->Approver_Id)->value('username');
            if ($u) {
                return (string) $u;
            }
        }

        return 'SYSTEM';
    }
}
