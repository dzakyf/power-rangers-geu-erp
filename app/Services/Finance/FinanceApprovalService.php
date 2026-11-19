<?php

namespace App\Services\Finance;

use App\Services\Approval\ApprovalAuthorizationService;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\HCGA\AssetMaintenanceScheduleService;
use App\Services\HCGA\VehicleFleetService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Service terpusat untuk kebutuhan Approval & Verifikasi Voucher di modul Finance
 * tanpa mengharuskan user Finance mengakses modul HRD/GA lain.
 */
class FinanceApprovalService
{
    /** @var ApprovalWorkflowService */
    protected $workflow;

    /** @var ApprovalAuthorizationService */
    protected $authorization;

    /** @var AssetMaintenanceScheduleService */
    protected $maintenanceScheduleService;

    /** @var VehicleFleetService */
    protected $vehicleFleetService;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalAuthorizationService $authorization,
        AssetMaintenanceScheduleService $maintenanceScheduleService,
        VehicleFleetService $vehicleFleetService
    ) {
        $this->workflow = $workflow;
        $this->authorization = $authorization;
        $this->maintenanceScheduleService = $maintenanceScheduleService;
        $this->vehicleFleetService = $vehicleFleetService;
    }

    /**
     * Ambil daftar task ApprovalWorkflowService yang aktif untuk user Finance.
     */
    public function getPendingWorkflowTasks(int $userId): array
    {
        if ($userId < 1) {
            return [];
        }

        $user = \App\User::find($userId);
        $isWildcard = $user ? $this->authorization->isWildcardAdmin($user) : false;

        $query = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->leftJoin('users as req_user', 'req_user.id', '=', 'r.Requester_Id')
            ->where('r.Status', 'IN_PROGRESS')
            ->whereColumn('t.Stage_Order', 'r.Current_Stage')
            ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
            ->where(function ($fin) {
                $fin->whereIn(DB::raw("UPPER(COALESCE(t.Department, ''))"), ['FIN', 'FINANCE', 'FA'])
                    ->orWhereIn(DB::raw("UPPER(COALESCE(t.Position_Code, ''))"), ['MFA', 'FIN', 'FINANCE', 'SFA'])
                    ->orWhere('t.Position_Name', 'LIKE', '%Finance%');
            });

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

        return $query->select(
                't.ID as Task_ID',
                't.Request_ID',
                't.Stage_Order',
                't.Status as Task_Status',
                'r.Request_No',
                'r.Request_Type',
                'r.Description',
                'r.Department',
                'r.Amount',
                'r.Submitted_At',
                'req_user.Name as Requester_Name',
                'req_user.username as Requester_Username'
            )
            ->orderBy('t.ID', 'DESC')
            ->limit(200)
            ->get()
            ->all();
    }

    /**
     * Proses APPROVE / REJECT pada task ApprovalWorkflowService.
     */
    public function actWorkflowTask(int $taskId, int $userId, string $action, string $notes = ''): void
    {
        $task = DB::table('approval_request_task')->where('ID', $taskId)->first();
        if (!$task) {
            throw new \RuntimeException('Task approval tidak ditemukan.');
        }

        if (strtoupper((string) $task->Status) === 'PENDING') {
            $this->workflow->claim($taskId, $userId);
        }

        $this->workflow->act($taskId, $userId, strtoupper(trim($action)), $notes);
    }

    /**
     * Ambil daftar Voucher GA & Aset yang menunggu proses Finance (FINANCE_SUBMITTED).
     */
    public function getPendingVouchers(): array
    {
        $maintenance = DB::table('asset_maintenance_schedule as s')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(DB::raw('CONVERT(s.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'));
            })
            ->leftJoin('supplier as sup', function ($join) {
                $join->on(DB::raw('CONVERT(s.Supplier_ID USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', DB::raw('CONVERT(sup.Supplier_ID USING utf8mb4) COLLATE utf8mb4_unicode_ci'));
            })
            ->where('s.Status', 'FINANCE_SUBMITTED')
            ->select(
                's.*',
                'a.Product_Description as Asset_Name',
                'a.Asset_Location',
                'sup.Company_Name as Vendor_Name'
            )
            ->orderBy('s.ID', 'DESC')
            ->get()
            ->all();

        $vehicleTax = DB::table('vehicle_tax_request as t')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(DB::raw('CONVERT(t.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'));
            })
            ->where('t.Status', 'FINANCE_SUBMITTED')
            ->select(
                't.*',
                'a.Registration_No',
                'a.Product_Description',
                'a.Asset_Location'
            )
            ->orderBy('t.ID', 'DESC')
            ->get()
            ->all();

        $historyMaintenance = DB::table('asset_maintenance_schedule as s')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(DB::raw('CONVERT(s.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'));
            })
            ->whereIn('s.Status', ['SCHEDULED', 'IN_EXECUTION', 'COMPLETED'])
            ->select('s.*', 'a.Product_Description as Asset_Name', 'a.Asset_Location')
            ->orderBy('s.ID', 'DESC')
            ->limit(50)
            ->get()
            ->all();

        $historyTax = DB::table('vehicle_tax_request as t')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(DB::raw('CONVERT(t.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'));
            })
            ->where('t.Status', 'PAID')
            ->select('t.*', 'a.Registration_No', 'a.Product_Description', 'a.Asset_Location')
            ->orderBy('t.ID', 'DESC')
            ->limit(50)
            ->get()
            ->all();

        return [
            'maintenance'         => $maintenance,
            'vehicle_tax'         => $vehicleTax,
            'history_maintenance' => $historyMaintenance,
            'history_tax'         => $historyTax,
        ];
    }

    /**
     * Ambil detail task ApprovalWorkflowService beserta riwayat approval_log.
     */
    public function getWorkflowTaskDetail(int $taskId): ?array
    {
        $task = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->leftJoin('users as req_user', 'req_user.id', '=', 'r.Requester_Id')
            ->where('t.ID', $taskId)
            ->select(
                't.ID as Task_ID',
                't.Stage_Order',
                't.Status as Task_Status',
                'r.*',
                'req_user.Name as Requester_Name',
                'req_user.username as Requester_Username'
            )
            ->first();

        if (!$task) {
            return null;
        }

        $logs = DB::table('approval_log as l')
            ->leftJoin('users as u', 'l.Approver_Id', '=', 'u.id')
            ->where('l.Request_Id', (int) $task->ID)
            ->select('l.Action', 'l.Notes', 'l.Action_Date', 'l.Stage_Order', 'u.Name as Actor_Name', 'u.username as Actor_Username')
            ->orderBy('l.ID', 'ASC')
            ->get()
            ->all();

        return [
            'task'    => $task,
            'logs'    => $logs,
            'context' => json_decode((string) ($task->Context ?? '{}'), true) ?: [],
            'policy'  => json_decode((string) ($task->Policy_Snapshot ?? '{}'), true) ?: [],
        ];
    }

    public function getMaintenanceVoucherDetail(int $id): ?object
    {
        return DB::table('asset_maintenance_schedule as s')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(DB::raw('CONVERT(s.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'));
            })
            ->leftJoin('supplier as sup', function ($join) {
                $join->on(DB::raw('CONVERT(s.Supplier_ID USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', DB::raw('CONVERT(sup.Supplier_ID USING utf8mb4) COLLATE utf8mb4_unicode_ci'));
            })
            ->where('s.ID', $id)
            ->select('s.*', 'a.Product_Description as Asset_Name', 'a.Asset_Location', 'a.User_Responsible', 'sup.Company_Name as Vendor_Name')
            ->first();
    }

    public function getTaxVoucherDetail(int $id): ?object
    {
        return DB::table('vehicle_tax_request as t')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(DB::raw('CONVERT(t.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'));
            })
            ->where('t.ID', $id)
            ->select('t.*', 'a.Registration_No', 'a.Product_Description', 'a.Product_Brand', 'a.Model', 'a.Asset_Location', 'a.User_Responsible')
            ->first();
    }

    public function approveMaintenanceVoucher(int $scheduleId): object
    {
        return $this->maintenanceScheduleService->confirmScheduleAndNotify($scheduleId);
    }

    public function payVehicleTaxVoucher(
        int $taxId,
        string $paidDate,
        string $newDueDate,
        string $username,
        ?string $bukti = null,
        ?UploadedFile $buktiFile = null
    ): object {
        return $this->vehicleFleetService->markTaxPaid($taxId, $paidDate, $newDueDate, $username, $bukti, $buktiFile);
    }
}
