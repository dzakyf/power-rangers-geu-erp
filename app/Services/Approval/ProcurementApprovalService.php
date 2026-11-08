<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Adapter antara modul Procurement (Purchase Request) dan engine Approval Policy.
 *
 * Mengelola lifecycle approval untuk PR, auto-generation Purchase Order (PO)
 * saat final approval, dan integrasi nomor dokumen ke inventory incoming.
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
     * Submit PR ke Approval Workflow.
     *
     * @param object|array $pr
     * @param object $user
     * @return object approval_request
     */
    public function submit($pr, $user)
    {
        $prObj = is_array($pr) ? (object) $pr : $pr;
        $context = $this->buildContext($prObj, $user);

        $description = 'Pengadaan barang ' . ($prObj->Category ?? 'PR') . ' No: ' . $prObj->PR_No . ' (' . ($prObj->Remark ?? '') . ')';

        $approvalReq = $this->workflow->submit([
            'Request_No' => $prObj->PR_No,
            'Description' => $description,
        ], $context, (int) $user->id);

        DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
            'Map_To' => (int) $prObj->ID,
        ]);

        $initialStatus = $this->mapApprovalStatusToPrStatus($approvalReq->Status);

        DB::table('purchase_request')->where('ID', (int) $prObj->ID)->update([
            'Approval_Request_ID' => (int) $approvalReq->ID,
            'Status' => $initialStatus,
        ]);

        return $this->workflow->getRequest((int) $approvalReq->ID);
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
     * Eksekusi aksi approval/rejection ke workflow engine.
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

            $request = $this->workflow->getRequest((int) $pr->Approval_Request_ID);
            $newPrStatus = $this->mapApprovalStatusToPrStatus($request->Status);

            DB::table('purchase_request')->where('ID', $prId)->update([
                'Status' => $newPrStatus,
            ]);

            // Catat log
            DB::table('purchase_request_log')->insert([
                'PR_No' => $pr->PR_No,
                'Action_By' => $user->username ?? 'SYSTEM',
                'Action_Date' => now(),
                'Action_Status' => $newPrStatus,
                'Last_ID' => $pr->ID,
                'Category' => $pr->Category ?? '',
                'Sub_Category' => $pr->Sub_Category ?? '',
                'Supplies_Type' => $pr->Supplies_Type ?? '',
                'Note' => $notes ?: ($action === 'APPROVE' ? 'Approved step' : 'Rejected'),
                'Reference' => $pr->Reference ?? '',
            ]);

            // Jika status request selesai disetujui (APPROVED), otomatis buat Purchase Order (PO)
            if ($request->Status === 'APPROVED') {
                $this->onFinalApproved($pr, $user);
            }

            return $acted;
        });
    }

    /**
     * Trigger ketika PR mencapai status APPROVED akhir.
     * Membuat data di tabel purchase_order dan purchase_order_dtl jika belum ada,
     * sehingga terhubung langsung ke inventori saat penerimaan barang.
     */
    public function onFinalApproved($pr, $user)
    {
        $existingPO = DB::table('purchase_order')->where('PR_No', $pr->PR_No)->first();
        if ($existingPO) {
            return $existingPO->PO_No;
        }

        $location = $pr->Location ?: ($user->Location ?? 'GEU-HO');
        $username = $user->username ?? 'SYSTEM';
        $dateR = date('Ymd');
        $year = date('Y');

        // Generate PO Number
        $countYear = DB::table('purchase_order')->where('PO_Date', 'LIKE', "$year%")->count() + 1;
        $seq = str_pad($countYear, 3, '0', STR_PAD_LEFT);
        $poNo = "PO/{$location}/INV/{$dateR}-{$seq}";

        $details = DB::table('purchase_request_dtl')->where('PR_No', $pr->PR_No)->get();

        $subtotal = 0;
        foreach ($details as $dtl) {
            $qty = (float) ($dtl->Qty ?? 1);
            $price = (float) ($dtl->Price ?? $dtl->Default_Price ?? 0);
            $total = $qty * $price;
            $subtotal += $total;

            DB::table('purchase_order_dtl')->insert([
                'Location' => $location,
                'PO_No' => $poNo,
                'Category' => $dtl->Category ?? ($pr->Category ?? 'NON ASSET'),
                'Sub_Category' => $dtl->Sub_Category ?? ($pr->Sub_Category ?? 'BARANG'),
                'Purchase_Type' => $dtl->Purchase_Type ?? 'DIRECT',
                'Product_Code' => $dtl->Product_Code ?? 'GENERAL',
                'Barcode' => $dtl->Product_Code ?? 'GENERAL',
                'Product_Desc' => $dtl->Product_Desc ?? $dtl->Item_Name ?? 'Item Pengadaan',
                'Product_Type' => $dtl->Product_Code ?? 'GENERAL',
                'Qty' => $qty,
                'Qty_Receive' => 0,
                'Qty_Remain' => $qty,
                'Unit' => $dtl->Unit ?? 'PCS',
                'Price' => $price,
                'Subtotal' => $total,
                'PPN' => 0,
                'Total' => $total,
                'Remark' => $dtl->Specification ?? $dtl->Remark ?? '',
                'PR_No' => $pr->PR_No,
                'Currency' => 'RP',
            ]);
        }

        $finalTotal = $subtotal > 0 ? $subtotal : (float) ($pr->Estimated_Budget ?? 0);

        DB::table('purchase_order')->insert([
            'Location' => $location,
            'Process_By' => $username,
            'Purchase_Type' => $pr->Purchase_Type ?? 'DIRECT',
            'Purchase_Category' => $pr->Category ?? 'NON ASSET',
            'Purchase_Sub_Category' => $pr->Sub_Category ?? 'BARANG',
            'PO_No' => $poNo,
            'PO_Date' => now(),
            'PO_Method' => 'POSTPAID',
            'Supplier' => $pr->Supplier_ID ?? 'DEFAULT',
            'Subtotal' => $finalTotal,
            'PPN' => 0,
            'Total' => $finalTotal,
            'Status' => 'APPROVED',
            'Status_Receive' => 'OPEN',
            'Remark' => $pr->Remark ?? 'Auto-generated from PR: ' . $pr->PR_No,
            'Date_Trx' => now(),
            'User_Trx' => $username,
            'PR_No' => $pr->PR_No,
            'Currency' => 'RP',
        ]);

        DB::table('purchase_request')->where('ID', $pr->ID)->update([
            'PO_No' => $poNo,
            'PO_Status' => 'NORMAL',
            'Processing_Status' => 'PO CREATED',
            'Processing_Date' => now(),
        ]);

        return $poNo;
    }

    /**
     * Membangun payload konteks untuk evaluasi policy approval.
     */
    protected function buildContext($pr, $user): array
    {
        $location = trim((string) ($pr->Location ?? $user->Location ?? ''));
        $company = trim((string) ($user->Company_ID ?? 'GEU'));
        $dept = trim((string) ($pr->Department ?? $user->Department ?? ''));

        if ($location === '') {
            $location = 'GEU-HO';
        }
        if ($company === '') {
            $company = 'GEU';
        }

        $estimatedBudget = (float) ($pr->Estimated_Budget ?? 0);

        return [
            'Request_Type' => self::REQUEST_TYPE,
            'Location' => $location,
            'Company_ID' => $company,
            'Department' => $dept,
            'Amount' => $estimatedBudget,
            'Estimated_Budget' => $estimatedBudget,
            'Category' => $pr->Category ?? 'NON ASSET',
            'Sub_Category' => $pr->Sub_Category ?? 'BARANG',
            'Priority_Status' => $pr->Priority_Status ?? 'NORMAL',
        ];
    }

    /**
     * Memetakan status ApprovalWorkflow ke status purchase_request.
     */
    protected function mapApprovalStatusToPrStatus(string $status): string
    {
        switch ($status) {
            case 'APPROVED':
                return 'APPROVED';
            case 'REJECTED':
                return 'REJECTED';
            case 'RETURNED':
                return 'RETURNED';
            case 'IN_PROGRESS':
            default:
                return 'APPROVAL_PROCESS';
        }
    }
}
