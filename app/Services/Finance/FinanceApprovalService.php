<?php

namespace App\Services\Finance;

use App\Services\Approval\ApprovalAuthorizationService;
use App\Services\Approval\ApprovalWorkflowService;
use App\Support\Modules;
use App\Services\GA\GaPaymentVoucherService;
use App\Services\HCGA\AssetMaintenanceScheduleService;
use App\Services\HCGA\VehicleFleetService;
use App\Services\Payroll\PayrollReportService;
use App\Services\Payroll\PayrollThrService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    /** @var GaPaymentVoucherService */
    protected $gaVoucherService;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalAuthorizationService $authorization,
        $maintenanceScheduleService = null,
        $vehicleFleetService = null,
        $gaVoucherService = null
    ) {
        $this->workflow = $workflow;
        $this->authorization = $authorization;
        $this->maintenanceScheduleService = $maintenanceScheduleService;
        $this->vehicleFleetService = $vehicleFleetService;
        $this->gaVoucherService = $gaVoucherService;
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
                $fin->whereIn(DB::raw("UPPER(COALESCE(t.Department, ''))"), ['FIN', 'FINANCE', 'FA', 'FAT'])
                    ->orWhereIn(DB::raw("UPPER(COALESCE(t.Position_Code, ''))"), ['MFA', 'VPFAT', 'FIN', 'FINANCE', 'SFA'])
                    ->orWhere('t.Position_Name', 'LIKE', '%Finance%')
                    ->orWhere('t.Position_Name', 'LIKE', '%FAT%');
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

        $gaVouchers = Modules::isEnabled('hcga') ? $this->gaVoucherService()->pendingForFinance()->all() : [];
        $historyGaVouchers = Modules::isEnabled('hcga') ? $this->gaVoucherService()->historyForFinance()->all() : [];

        $historyWorkflows = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->leftJoin('users as req_user', 'req_user.id', '=', 'r.Requester_Id')
            ->whereIn('t.Status', ['APPROVED', 'REJECTED'])
            ->where(function ($fin) {
                $fin->whereIn(DB::raw("UPPER(COALESCE(t.Department, ''))"), ['FIN', 'FINANCE', 'FA', 'FAT'])
                    ->orWhereIn(DB::raw("UPPER(COALESCE(t.Position_Code, ''))"), ['MFA', 'VPFAT', 'FIN', 'FINANCE', 'SFA'])
                    ->orWhere('t.Position_Name', 'LIKE', '%Finance%')
                    ->orWhere('t.Position_Name', 'LIKE', '%FAT%');
            })
            ->select(
                't.ID as Task_ID',
                't.Request_ID',
                't.Stage_Order',
                't.Status as Task_Status',
                't.Acted_At',
                'r.Request_No',
                'r.Request_Type',
                'r.Description',
                'r.Department',
                'r.Amount',
                'r.Status as Request_Status',
                'r.Submitted_At',
                'req_user.Name as Requester_Name',
                'req_user.username as Requester_Username'
            )
            ->orderBy('t.ID', 'DESC')
            ->limit(100)
            ->get()
            ->all();

        return [
            'maintenance'         => $maintenance,
            'vehicle_tax'         => $vehicleTax,
            'ga_vouchers'         => $gaVouchers,
            'history_maintenance' => $historyMaintenance,
            'history_tax'         => $historyTax,
            'history_ga_vouchers' => $historyGaVouchers,
            'history_workflows'   => $historyWorkflows,
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
            'task'              => $task,
            'logs'              => $logs,
            'context'           => json_decode((string) ($task->Context ?? '{}'), true) ?: [],
            'policy'            => json_decode((string) ($task->Policy_Snapshot ?? '{}'), true) ?: [],
            'payroll_breakdown' => $this->resolvePayrollBreakdownForRequest($task),
        ];
    }

    /**
     * Ambil paket data rincian penggajian dan rekening bank untuk request bertipe PAYROLL / SALARY / PAYROLL_THR.
     *
     * @param object $requestRow Baris approval_request atau join approval_request_task + approval_request
     * @return array|null
     */
    public function resolvePayrollBreakdownForRequest(object $requestRow): ?array
    {
        if (!Modules::isEnabled('hcga')) {
            return null;
        }

        $reqType = strtoupper(trim((string) ($requestRow->Request_Type ?? '')));
        if (!in_array($reqType, ['PAYROLL', 'SALARY', 'PAYROLL_THR'], true)) {
            return null;
        }

        $requestId = (int) ($requestRow->Request_ID ?? ($requestRow->ID ?? 0));
        $requestNo = trim((string) ($requestRow->Request_No ?? ''));
        $mapTo = isset($requestRow->Map_To) ? (int) $requestRow->Map_To : 0;

        if ($mapTo <= 0 && $requestId > 0) {
            $mapTo = (int) DB::table('approval_request')->where('ID', $requestId)->value('Map_To');
        }

        $reportService = app(PayrollReportService::class);

        if ($reqType === 'PAYROLL_THR') {
            if (!Schema::hasTable('payroll_thr_batches')) {
                return null;
            }

            $batch = null;
            if ($requestId > 0 && Schema::hasColumn('payroll_thr_batches', 'approval_request_id')) {
                $batch = DB::table('payroll_thr_batches')->where('approval_request_id', $requestId)->first();
            }
            if (!$batch && $mapTo > 0) {
                $batch = DB::table('payroll_thr_batches')->where('id', $mapTo)->first();
            }
            if (!$batch && $requestNo !== '') {
                $batch = DB::table('payroll_thr_batches')->where('batch_no', $requestNo)->first();
            }
            if (!$batch) {
                return null;
            }

            try {
                $thrService = app(PayrollThrService::class);
                $exportData = $thrService->getExportRowsForBatch((int) $batch->id);
                $rows = $exportData['rows'] ?? [];
            } catch (\Throwable $e) {
                $rows = [];
            }

            return [
                'mode'         => 'PAYROLL_THR',
                'batch_id'     => (int) $batch->id,
                'task_id'      => (string) $batch->batch_no,
                'period'       => (string) ($batch->salary_period ?? '-'),
                'event_name'   => (string) ($batch->thr_event_name ?? '-'),
                'tax_mode'     => (string) ($batch->tax_mode ?? 'NONE'),
                'status'       => (string) ($batch->status ?? '-'),
                'header'       => $batch,
                'rows'         => $rows,
                'bank_summary' => $reportService->buildBankTransferSummary($rows, 'nett_thr'),
            ];
        }

        // PAYROLL / SALARY reguler
        if (!Schema::hasTable('salary_main')) {
            return null;
        }

        $salaryMain = null;
        if ($requestId > 0 && Schema::hasColumn('salary_main', 'approval_request_id')) {
            $salaryMain = DB::table('salary_main')->where('approval_request_id', $requestId)->first();
        }
        if (!$salaryMain && $mapTo > 0) {
            $salaryMain = DB::table('salary_main')->where('ID', $mapTo)->first();
        }
        if (!$salaryMain && $requestNo !== '') {
            $salaryMain = DB::table('salary_main')->where('Task_ID', $requestNo)->first();
        }

        $batchTaskId = $salaryMain ? (string) $salaryMain->Task_ID : $requestNo;
        if ($batchTaskId === '') {
            return null;
        }

        try {
            $rows = $reportService->getReportData(['task_id' => $batchTaskId]);
        } catch (\Throwable $e) {
            $rows = [];
        }

        return [
            'mode'         => 'PAYROLL',
            'task_id'      => $batchTaskId,
            'period'       => $salaryMain ? (string) $salaryMain->Salary_Period : ($rows[0]['period'] ?? '-'),
            'salary_group' => $salaryMain ? (string) ($salaryMain->Salary_Group ?? '-') : '-',
            'category'     => $salaryMain ? (string) ($salaryMain->Category ?? 'SALARY') : 'SALARY',
            'status'       => $salaryMain ? (string) ($salaryMain->Status ?? '-') : '-',
            'header'       => $salaryMain,
            'rows'         => $rows,
            'bank_summary' => $reportService->buildBankTransferSummary($rows, 'nett'),
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
        return $this->hcgaMaintenanceScheduleService()->confirmScheduleAndNotify($scheduleId);
    }

    public function payVehicleTaxVoucher(
        int $taxId,
        string $paidDate,
        string $newDueDate,
        string $username,
        ?string $bukti = null,
        ?UploadedFile $buktiFile = null
    ): object {
        return $this->hcgaVehicleFleetService()->markTaxPaid($taxId, $paidDate, $newDueDate, $username, $bukti, $buktiFile);
    }

    private function hcgaService(string $class)
    {
        $fqcn = '\\App\\Services\\'.'HCGA\\'.$class;

        if (!Modules::isEnabled('hcga') || !class_exists($fqcn)) {
            throw new \RuntimeException('HCGA module is not enabled for this deployment.');
        }

        return app($fqcn);
    }

    private function hcgaMaintenanceScheduleService()
    {
        if ($this->maintenanceScheduleService) {
            return $this->maintenanceScheduleService;
        }

        return $this->maintenanceScheduleService = $this->hcgaService('AssetMaintenanceScheduleService');
    }

    private function hcgaVehicleFleetService()
    {
        if ($this->vehicleFleetService) {
            return $this->vehicleFleetService;
        }

        return $this->vehicleFleetService = $this->hcgaService('VehicleFleetService');
    }

    private function gaVoucherService(): GaPaymentVoucherService
    {
        if ($this->gaVoucherService) {
            return $this->gaVoucherService;
        }

        if (!Modules::isEnabled('hcga') || !class_exists(GaPaymentVoucherService::class)) {
            throw new \RuntimeException('HCGA module is not enabled for this deployment.');
        }

        return $this->gaVoucherService = app(GaPaymentVoucherService::class);
    }

    public function getGaVoucherDetail(int $id): ?object
    {
        return $this->gaVoucherService()->findById($id) ?: null;
    }

    public function payGaVoucher(
        int $voucherId,
        array $data,
        string $username,
        ?UploadedFile $proofFile = null
    ): object {
        return $this->gaVoucherService()->markPaid($voucherId, $data, $username, $proofFile);
    }
}
