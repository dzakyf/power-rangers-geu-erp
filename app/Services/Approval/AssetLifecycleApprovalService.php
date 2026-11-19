<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use App\Services\HCGA\AssetLifecycleService;
use Illuminate\Support\Facades\DB;

/**
 * Adapter terpusat antara modul Siklus Hidup Aset
 * (asset_fixing, asset_moving, asset_disposal) dengan ApprovalWorkflowService.
 */
class AssetLifecycleApprovalService
{
    public const TYPE_ASSET_FIXING   = 'ASSET_FIXING';
    public const TYPE_ASSET_MOVING   = 'ASSET_MOVING';
    public const TYPE_ASSET_DISPOSAL = 'ASSET_DISPOSAL';

    /** @var ApprovalWorkflowService */
    protected $workflow;

    /** @var ApprovalAuthorizationService */
    protected $authorizationService;

    /** @var AssetLifecycleService */
    protected $lifecycleService;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalAuthorizationService $authorizationService,
        AssetLifecycleService $lifecycleService
    ) {
        $this->workflow = $workflow;
        $this->authorizationService = $authorizationService;
        $this->lifecycleService = $lifecycleService;
    }

    /**
     * Submit pengajuan ASSET_FIXING / ASSET_MOVING / ASSET_DISPOSAL ke ApprovalWorkflowService.
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

        $freshReq = $this->workflow->getRequest((int) $approvalReq->ID);
        $this->bindDomainApproval($requestType, $domainId, $freshReq);

        return $freshReq;
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

        $this->syncStatus((string) $request->Request_Type, $approvalRequestId, $notes);

        return $acted;
    }

    /**
     * Ambil daftar task aktif yang dapat diproses oleh user tertentu.
     */
    public function pendingTasksForUser(int $approvalRequestId, int $userId): array
    {
        $request = $this->workflow->getRequest($approvalRequestId);
        if (!$request || in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return [];
        }

        $user = \App\User::find($userId);
        $isWildcard = $user ? $this->authorizationService->isWildcardAdmin($user) : false;

        $query = DB::table('approval_request_task as t')
            ->where('t.Request_ID', $approvalRequestId)
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

        return $query->select('t.*')->get()->all();
    }

    /**
     * Sinkronisasi status tabel domain & asset_list setelah aksi ApprovalWorkflowService.
     */
    public function syncStatus(string $requestType, int $approvalRequestId, string $notes = ''): void
    {
        $request = $this->workflow->getRequest($approvalRequestId);
        if (!$request) {
            return;
        }

        $reqStatus = strtoupper((string) $request->Status);
        $domainId = (int) ($request->Map_To ?? 0);

        if ($requestType === self::TYPE_ASSET_FIXING && $domainId > 0) {
            $fixing = DB::table('asset_fixing')->where('ID', $domainId)->first();
            if (!$fixing) {
                return;
            }
            $newStatus = $this->mapDomainStatus($reqStatus, $request->Current_Stage);
            DB::table('asset_fixing')->where('ID', $domainId)->update([
                'Status'     => $newStatus,
                'updated_at' => now(),
            ]);
            if ($reqStatus === 'APPROVED' && !empty($fixing->Asset_No)) {
                $this->lifecycleService->markInMaintenance((string) $fixing->Asset_No, 'approval_engine');
            }
        } elseif ($requestType === self::TYPE_ASSET_MOVING && $domainId > 0) {
            $moving = DB::table('moving_asset')->where('ID', $domainId)->first();
            if (!$moving) {
                return;
            }
            $newStatus = $this->mapMovingStatus($reqStatus, (int) $request->Current_Stage);
            DB::table('moving_asset')->where('ID', $domainId)->update([
                'Status'     => $newStatus,
                'updated_at' => now(),
            ]);
            if ($reqStatus === 'APPROVED' && !empty($moving->Asset_No)) {
                $this->lifecycleService->updateLocation(
                    (string) $moving->Asset_No,
                    $moving->New_Location ?? null,
                    $moving->Receipent_Department ?? null,
                    null,
                    'approval_engine'
                );
                if (!empty($moving->Receipent_Name)) {
                    $this->lifecycleService->recordUserTransfer(
                        (string) $moving->Asset_No,
                        $moving->UserTrx ?? null,
                        (string) $moving->Receipent_Name,
                        'ASSET_MOVING',
                        $moving->No_Trx ?? null,
                        'approval_engine',
                        $moving->Moving_Reason ?? 'Pemindahan aset disetujui'
                    );
                }
            }
        } elseif ($requestType === self::TYPE_ASSET_DISPOSAL && $domainId > 0) {
            $disposal = DB::table('asset_disposal')->where('ID', $domainId)->first();
            if (!$disposal) {
                return;
            }
            $newStatus = $this->mapDomainStatus($reqStatus, (int) $request->Current_Stage);
            $update = [
                'Status'       => $newStatus,
                'Updated_Date' => now(),
            ];
            if ($notes !== '') {
                $update['Approval_Note'] = $notes;
            }
            if ($reqStatus === 'APPROVED') {
                $update['Finance_Verified_By'] = 'approval_engine';
                $update['Finance_Verified_At'] = now();
            }
            DB::table('asset_disposal')->where('ID', $domainId)->update($update);

            $assetNo = trim((string) (($disposal->Asset_No ?? '') ?: ($disposal->Asset_Serial_No ?? '')));
            if ($reqStatus === 'APPROVED' && $assetNo !== '') {
                $this->lifecycleService->markDisposed(
                    $assetNo,
                    $disposal->Disposal_Actual_Date ?? null,
                    'approval_engine'
                );
            }
        }
    }

    /**
     * Ambil timeline approval_request + stages + approval_log untuk UI detail.
     */
    public function getRequestWithTimeline(?int $approvalRequestId): ?array
    {
        if (!$approvalRequestId || $approvalRequestId < 1) {
            return null;
        }

        $request = $this->workflow->getRequest($approvalRequestId);
        if (!$request) {
            return null;
        }

        $tasks = DB::table('approval_request_task')
            ->where('Request_ID', $approvalRequestId)
            ->orderBy('Stage_Order', 'ASC')
            ->get()
            ->all();

        $logs = DB::table('approval_log as l')
            ->leftJoin('users as u', 'l.Approver_Id', '=', 'u.id')
            ->where('l.Request_Id', $approvalRequestId)
            ->select('l.Action', 'l.Notes', 'l.Action_Date', 'l.Stage_Order', 'u.Name as Actor_Name', 'u.username as Actor_Username')
            ->orderBy('l.ID', 'ASC')
            ->get()
            ->all();

        $policy = json_decode((string) ($request->Policy_Snapshot ?? '{}'), true) ?: [];

        return [
            'request' => $request,
            'tasks'   => $tasks,
            'logs'    => $logs,
            'steps'   => $policy['steps'] ?? [],
        ];
    }

    protected function bindDomainApproval(string $requestType, int $domainId, object $approvalReq): void
    {
        $reqStatus = strtoupper((string) $approvalReq->Status);
        if ($requestType === self::TYPE_ASSET_FIXING) {
            DB::table('asset_fixing')->where('ID', $domainId)->update([
                'Approval_Request_ID' => (int) $approvalReq->ID,
                'Status'              => $reqStatus === 'APPROVED' ? 'APPROVED' : 'WAITING_APPROVAL',
            ]);
        } elseif ($requestType === self::TYPE_ASSET_MOVING) {
            DB::table('moving_asset')->where('ID', $domainId)->update([
                'Approval_Request_ID' => (int) $approvalReq->ID,
                'Status'              => $reqStatus === 'APPROVED' ? 'APPROVED' : 'APPROVAL_UPPER',
            ]);
        } elseif ($requestType === self::TYPE_ASSET_DISPOSAL) {
            DB::table('asset_disposal')->where('ID', $domainId)->update([
                'Approval_Request_ID' => (int) $approvalReq->ID,
                'Status'              => $reqStatus === 'APPROVED' ? 'APPROVED' : 'WAITING_APPROVAL',
            ]);
        }
    }

    protected function mapDomainStatus(string $reqStatus, int $stage): string
    {
        if ($reqStatus === 'APPROVED') {
            return 'APPROVED';
        }
        if ($reqStatus === 'REJECTED') {
            return 'REJECTED';
        }
        if ($reqStatus === 'RETURNED') {
            return 'RETURNED';
        }
        return 'WAITING_APPROVAL';
    }

    protected function mapMovingStatus(string $reqStatus, int $stage): string
    {
        if ($reqStatus === 'APPROVED') {
            return 'APPROVED';
        }
        if ($reqStatus === 'REJECTED') {
            return 'REJECTED';
        }
        if ($stage === 1) {
            return 'APPROVAL_UPPER';
        }
        if ($stage === 2) {
            return 'APPROVAL_GA';
        }
        return 'APPROVAL_BOD';
    }
}
