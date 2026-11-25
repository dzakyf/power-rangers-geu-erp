<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Illuminate\Support\Facades\DB;

/**
 * Adapter antara modul Procurement (Purchase Request) dan engine Approval Policy.
 * Terhubung ke Approval Inbox terpusat & SyncModuleApprovalStatus.
 */
class ProcurementApprovalService
{
    const REQUEST_TYPE = 'PROCUREMENT';

    protected $workflow;
    protected $policyResolver;
    protected $authorizationService;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalPolicyResolver $policyResolver,
        ApprovalAuthorizationService $authorizationService
    ) {
        $this->workflow = $workflow;
        $this->policyResolver = $policyResolver;
        $this->authorizationService = $authorizationService;
    }

    /**
     * Preflight cek policy sebelum PR disimpan.
     */
    public function assertCanSubmit($prData, $user): array
    {
        $context = $this->buildContext($prData, $user);
        return $this->policyResolver->resolve($context);
    }

    /**
     * Submit PR ke Approval Workflow (Approval Inbox & Mobile Apps).
     */
    public function submit($pr, $user)
    {
        $prObj = is_array($pr) ? (object) $pr : $pr;
        $context = $this->buildContext($prObj, $user);

        $suppliesLabel = strtoupper($prObj->Supplies_Type ?? 'NON-OPS');
        $catLabel      = strtoupper($prObj->Category ?? 'NON ASSET');
        $description   = "PR [{$suppliesLabel} - {$catLabel}] No: {$prObj->PR_No}" . (!empty($prObj->Note) ? " ({$prObj->Note})" : '');

        $approvalReq = $this->workflow->submit([
            'Request_No'  => $prObj->PR_No,
            'Description' => $description,
        ], $context, (int) $user->id);

        DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
            'Map_To' => (int) $prObj->ID,
        ]);

        $initialStatus = $this->mapApprovalStatusToPrStatus((string) $approvalReq->Status);

        DB::table('purchase_request')->where('ID', (int) $prObj->ID)->update([
            'Approval_Request_ID' => (int) $approvalReq->ID,
            'Status'              => $initialStatus,
        ]);

        return $this->workflow->getRequest((int) $approvalReq->ID);
    }

    /**
     * Resubmit PR yang dikembalikan (RETURNED) setelah direvisi.
     */
    public function resubmit($pr, $user)
    {
        $prObj   = is_array($pr) ? (object) $pr : $pr;
        $context = $this->buildContext($prObj, $user);

        $newReq = $this->workflow->resubmit((int) $prObj->Approval_Request_ID, $context, (int) $user->id);

        DB::table('approval_request')->where('ID', (int) $newReq->ID)->update([
            'Map_To' => (int) $prObj->ID,
        ]);

        $newStatus = $this->mapApprovalStatusToPrStatus((string) $newReq->Status);

        DB::table('purchase_request')->where('ID', (int) $prObj->ID)->update([
            'Approval_Request_ID' => (int) $newReq->ID,
            'Status'              => $newStatus,
        ]);

        return $newReq;
    }

    /**
     * Cari task aktif milik user pada stage yang sedang berjalan.
     */
    public function pendingTasksForUser(int $prId, int $userId): array
    {
        $requestId = (int) DB::table('purchase_request')
            ->where('ID', $prId)
            ->value('Approval_Request_ID');

        if ($requestId < 1) {
            return [];
        }

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

        return $query->select('t.*')
            ->orderBy('t.ID', 'ASC')
            ->get()
            ->all();
    }

    /**
     * Process approval step dengan auto-claim jika task masih PENDING.
     */
    public function approve(int $prId, $user, string $notes = '')
    {
        return $this->actOnPr($prId, (int) $user->id, 'APPROVE', $notes, $user);
    }

    /**
     * Process rejection step.
     */
    public function reject(int $prId, $user, string $reason)
    {
        return $this->actOnPr($prId, (int) $user->id, 'REJECT', $reason, $user);
    }

    /**
     * Eksekusi aksi approval/rejection ke workflow engine dari halaman PR.
     */
    public function actOnPr(int $prId, int $userId, string $action, string $notes, $user): array
    {
        $action = strtoupper(trim($action));
        if (!in_array($action, ['APPROVE', 'REJECT'], true)) {
            throw new ApprovalWorkflowException('Action harus APPROVE atau REJECT.', 'ACTION_INVALID', 422);
        }

        return DB::transaction(function () use ($prId, $userId, $action, $notes, $user) {
            $pr = DB::table('purchase_request')->where('ID', $prId)->lockForUpdate()->first();
            if (!$pr) {
                throw new ApprovalWorkflowException('Purchase Request tidak ditemukan.', 'PR_NOT_FOUND', 404);
            }
            if (empty($pr->Approval_Request_ID)) {
                throw new ApprovalWorkflowException('Purchase Request belum terhubung ke workflow approval.', 'NO_APPROVAL_REQUEST', 422);
            }

            $tasks = $this->pendingTasksForUser($prId, $userId);
            if (empty($tasks)) {
                throw new ApprovalWorkflowException('Anda tidak memiliki tugas approval aktif untuk PR ini.', 'UNAUTHORIZED_APPROVER', 403);
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

            // Sinkronkan status purchase_request & riwayat catatan
            $this->syncApprovalStatus($prId);

            return $acted;
        });
    }

    /**
     * Dipanggil otomatis oleh SyncModuleApprovalStatus (ketika di-approve lewat Approval Inbox / Mobile)
     * maupun setelah actOnPr() dari halaman detail PR.
     */
    public function syncApprovalStatus(int $prId): void
    {
        $pr = DB::table('purchase_request')->where('ID', $prId)->first();
        if (!$pr || empty($pr->Approval_Request_ID)) {
            return;
        }

        $req = $this->workflow->getRequest((int) $pr->Approval_Request_ID);
        if (!$req) {
            return;
        }

        $newPrStatus = $this->mapApprovalStatusToPrStatus((string) $req->Status);

        // Ambil info approver terakhir yang mengeksekusi task
        $lastTask = DB::table('approval_request_task')
            ->where('Request_ID', (int) $req->ID)
            ->whereNotNull('Acted_By')
            ->orderBy('ID', 'DESC')
            ->first();

        $approverName = null;
        if ($lastTask && $lastTask->Acted_By) {
            $approver = DB::table('users')->where('id', $lastTask->Acted_By)->first();
            $approverName = $approver->Name ?? $approver->username ?? 'SYSTEM';
        }

        $updateData = [
            'Status' => $newPrStatus,
        ];

        if (in_array($newPrStatus, ['APPROVED', 'REJECTED', 'RETURNED'], true)) {
            $updateData['Approval_User'] = $approverName ?? $pr->Approval_User;
            $updateData['Approval_Date'] = now();
        }

        DB::table('purchase_request')->where('ID', $prId)->update($updateData);

        // Catat ke pr_notes_history agar tampil di riwayat percakapan detail PR
        if ($lastTask && !empty($lastTask->Notes)) {
            $alreadyLogged = DB::table('pr_notes_history')
                ->where('PR_No', $pr->PR_No)
                ->where('Note', $lastTask->Notes)
                ->where('Created_By', $approverName ?? 'SYSTEM')
                ->exists();

            if (!$alreadyLogged) {
                DB::table('pr_notes_history')->insert([
                    'PR_No'         => $pr->PR_No,
                    'Action_Status' => $newPrStatus,
                    'Note'          => $lastTask->Notes,
                    'Created_By'    => $approverName ?? 'SYSTEM',
                    'Created_Date'  => now(),
                ]);
            }
        }
    }

    /**
     * Membangun payload konteks untuk evaluasi policy approval sesuai 3 Matriks SK.
     */
    protected function buildContext($pr, $user): array
    {
        $location = trim((string) ($pr->Location ?? $user->Location ?? ''));
        $company  = trim((string) ($user->Company_ID ?? 'GEU'));
        $dept     = trim((string) ($pr->Department ?? $user->Department ?? ''));

        if ($location === '') {
            $location = 'GEU-HO';
        }
        if ($company === '') {
            $company = 'GEU';
        }

        $estimatedBudget = (float) ($pr->Estimated_Budget ?? 0);

        return [
            'Request_Type'         => self::REQUEST_TYPE,
            'Location'             => $location,
            'Company_ID'           => $company,
            'Department'           => $dept,
            'Amount'               => $estimatedBudget,
            'Estimated_Budget'     => $estimatedBudget,
            // Atribut penentu 3 Matriks SK (OPS / NON-OPS / NON-OPS-CNS & ASSET / NON ASSET):
            'Supplies_Type'        => strtoupper((string) ($pr->Supplies_Type ?? 'NON-OPS')),
            'Category'             => strtoupper((string) ($pr->Category ?? 'NON ASSET')),
            'Sub_Category'         => strtoupper((string) ($pr->Sub_Category ?? 'BARANG')),
            'Procurement_Category' => (string) ($pr->Procurement_Category ?? 'Rutin'),
            'Priority_Status'      => strtoupper((string) ($pr->Urgency ?? $pr->Priority_Status ?? 'NORMAL')),
        ];
    }

    /**
     * Memetakan status ApprovalWorkflow ke status purchase_request.
     * PENDING / APPROVAL_PROCESS mengarahkan ke form detail pending/approval.
     * APPROVED mengarahkan ke form detail approved (siap buat Multi-Vendor RFQ).
     */
    protected function mapApprovalStatusToPrStatus(string $status): string
    {
        switch (strtoupper($status)) {
            case 'APPROVED':
                return 'APPROVED';
            case 'REJECTED':
                return 'REJECTED';
            case 'RETURNED':
                return 'RETURNED';
            case 'CANCELLED':
                return 'CANCELLED';
            case 'IN_PROGRESS':
            case 'BLOCKED':
            default:
                return 'PENDING';
        }
    }
}