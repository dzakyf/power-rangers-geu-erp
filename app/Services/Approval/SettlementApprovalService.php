<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service Adapter untuk menghubungkan Settlement SPPD dengan ApprovalWorkflowService.
 */
class SettlementApprovalService
{
    const REQUEST_TYPE = 'SETTLEMENT';

    /**
     * @var ApprovalWorkflowService
     */
    protected $workflow;

    /**
     * @var ApprovalPolicyResolver
     */
    protected $policyResolver;

    public function __construct(ApprovalWorkflowService $workflow, ApprovalPolicyResolver $policyResolver)
    {
        $this->workflow = $workflow;
        $this->policyResolver = $policyResolver;
    }

    /**
     * Daftarkan pengajuan Settlement baru ke ApprovalWorkflowService.
     *
     * @param object|array $settlement
     * @param object $user
     * @return object|null approval_request
     */
    public function submit($settlement, $user)
    {
        $sttlObj = is_array($settlement) ? (object) $settlement : $settlement;
        $sttlId  = (int) $sttlObj->ID;

        $context = $this->buildContext($sttlObj, $user);

        // Hitung total real
        $expensesTotal = (float) DB::table('settlement_sppd_expense')->where('Settlement_Sppd_ID', $sttlId)->sum('Cost');
        $matrixAmt = isset($sttlObj->Matrix_Amount) ? (float) $sttlObj->Matrix_Amount : 0;
        $grandTotal = $expensesTotal + $matrixAmt;

        $description = sprintf(
            'Settlement SPPD %s [%s]: Pemohon %s (SPPD %s) - Total Diperhitungkan Rp %s',
            $sttlObj->STTL_No,
            $sttlObj->Department ?? '-',
            $sttlObj->Requester_Name ?? '-',
            $sttlObj->SPPD_No ?: 'Tanpa SPPD',
            number_format($grandTotal, 0, ',', '.')
        );

        $approvalReq = $this->workflow->submit([
            'Request_No'  => $sttlObj->STTL_No ?: null,
            'Description' => $description,
        ], $context, (int) $user->id);

        if (!$approvalReq || empty($approvalReq->ID)) {
            throw new ApprovalWorkflowException('Gagal membuat approval_request untuk Settlement.', 'SETTLEMENT_APPROVAL_SUBMIT_FAILED', 500);
        }

        DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
            'Map_To' => $sttlId,
        ]);

        DB::table('settlement_sppd')->where('ID', $sttlId)->update([
            'Approval_Request_ID' => (int) $approvalReq->ID,
        ]);

        $this->syncApprovalStatus($sttlId);

        return $this->workflow->getRequest((int) $approvalReq->ID);
    }

    /**
     * Bangun context policy approval Settlement.
     */
    public function buildContext($settlement, $user): array
    {
        $nik = !empty($settlement->Requester_NIK) ? $settlement->Requester_NIK : ($user->Emp_ID ?? null);
        $emp = null;
        if ($nik) {
            $emp = DB::table('employment')->where('NIK', $nik)->first();
        }
        if (!$emp && !empty($user->Emp_ID)) {
            $emp = DB::table('employment')->where('NIK', $user->Emp_ID)->first();
        }

        $pos = null;
        if ($emp && !empty($emp->Position)) {
            $pos = DB::table('master_position')->where('Initial', $emp->Position)->first();
        }

        $department = trim((string) ($settlement->Department ?? ($user->Department ?? '')));
        $companyId  = $pos->Company_ID ?? ($user->Company_ID ?? 'GEU');
        $location   = $pos->Location ?? ($user->Location ?? 'GEU-HO');

        // Hitung total nilai diperhitungkan (manual expense + matrix allowance)
        $sttlId = (int) ($settlement->ID ?? 0);
        $costSum = 0;
        if ($sttlId > 0) {
            $costSum = (float) DB::table('settlement_sppd_expense')->where('Settlement_Sppd_ID', $sttlId)->sum('Cost');
        }
        $matrixAmt = isset($settlement->Matrix_Amount) ? (float) $settlement->Matrix_Amount : 0;
        $totalCost = $costSum + $matrixAmt;

        return [
            'Request_Type' => self::REQUEST_TYPE,
            'Location'     => $location,
            'Company_ID'   => $companyId,
            'Department'   => $department,
            'Amount'       => $totalCost,
            'SPPD_No'      => $settlement->SPPD_No ?? '',
        ];
    }

    /**
     * Sinkronkan status approval_request kembali ke tabel settlement_sppd.
     */
    public function syncApprovalStatus(int $settlementId): void
    {
        $sttl = DB::table('settlement_sppd')->where('ID', $settlementId)->first();
        if (!$sttl || empty($sttl->Approval_Request_ID)) {
            return;
        }

        $req = $this->workflow->getRequest((int) $sttl->Approval_Request_ID);
        if (!$req) {
            return;
        }

        $moduleStatus = $this->mapApprovalStatusToModule($req->Status);

        $update = [
            'Status'       => $moduleStatus,
            'Updated_Date' => now(),
        ];

        if ($req->Status === 'COMPLETED' || $req->Status === 'APPROVED') {
            $update['Approved_At'] = now();
            // Ambil actor approval terakhir
            $lastApprovedTask = DB::table('approval_request_task')
                ->where('Request_ID', $req->ID)
                ->where('Status', 'APPROVED')
                ->orderBy('ID', 'DESC')
                ->first();
            if ($lastApprovedTask && $lastApprovedTask->Acted_By) {
                $approverUser = DB::table('users')->where('id', $lastApprovedTask->Acted_By)->first();
                if ($approverUser) {
                    $update['Validator_ID']   = $approverUser->id;
                    $update['Validator_Name'] = $approverUser->Name ?: $approverUser->username;
                }
            }
        }

        DB::table('settlement_sppd')->where('ID', $settlementId)->update($update);
    }

    /**
     * Memeriksa apakah user saat ini dapat melakukan approval terhadap settlement ini.
     * Mengacu ke active task pada Approval Engine & Authorization.
     */
    public function canUserApprove(int $settlementId, $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return false;
        }

        $sttl = DB::table('settlement_sppd')->where('ID', $settlementId)->first();
        if (!$sttl || in_array(strtoupper($sttl->Status), ['APPROVED', 'CLOSED', 'REJECT', 'REJECTED', 'CANCELED'])) {
            return false;
        }

        if (empty($sttl->Approval_Request_ID)) {
            // Fallback legacy jika belum terdaftar approval_request: HCGA Dept Head / Admin
            return \App\Http\Controllers\HCGA\SettlementController::isDeptHead() || \App\Http\Controllers\HCGA\SppdController::isAdministrator($user);
        }

        // Cek task PENDING/CLAIMED pada approval_request_task
        $task = DB::table('approval_request_task')
            ->where('Request_ID', (int) $sttl->Approval_Request_ID)
            ->whereIn('Status', ['PENDING', 'CLAIMED'])
            ->orderBy('Stage_Order', 'ASC')
            ->first();

        if (!$task) {
            return false;
        }

        // Jika sudah di-claim oleh user ini
        if ($task->Status === 'CLAIMED' && (int) $task->Claimed_By === (int) $user->id) {
            return true;
        }

        // Cek apakah user adalah kandidat aktif
        $isCandidate = DB::table('approval_task_candidate')
            ->where('Task_ID', $task->ID)
            ->where('User_Id', $user->id)
            ->where('Status', 'ACTIVE')
            ->exists();

        if ($isCandidate) {
            return true;
        }

        // Administrator bypass / HCGA verifier
        $uname = strtolower(trim((string) $user->username));
        if (in_array($uname, ['admin', 'administrator', 'superadmin']) || (int) ($user->is_admin ?? 0) === 1) {
            return true;
        }

        return false;
    }

    /**
     * Petakan status internal ApprovalWorkflowService ke status modul Settlement.
     */
    protected function mapApprovalStatusToModule(string $approvalStatus): string
    {
        switch (strtoupper($approvalStatus)) {
            case 'COMPLETED':
            case 'APPROVED':
                return 'APPROVED';
            case 'REJECTED':
                return 'REJECTED';
            case 'CANCELLED':
            case 'CANCELED':
                return 'CANCELED';
            case 'IN_PROGRESS':
            case 'SUBMITTED':
            default:
                return 'SUBMITTED';
        }
    }
}

