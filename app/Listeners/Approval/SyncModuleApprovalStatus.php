<?php

namespace App\Listeners\Approval;

use App\Events\Approval\ApprovalRequestUpdated;
use App\Services\Approval\RequisitionApprovalService;
use App\Services\Approval\StatusChangeApprovalService;
use App\Services\GA\WorkOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Approval\ProcurementApprovalService;

class SyncModuleApprovalStatus
{
    /**
     * Tangani event ApprovalRequestUpdated untuk menyelaraskan status pada modul domain.
     *
     * @param ApprovalRequestUpdated $event
     * @return void
     */
    public function handle(ApprovalRequestUpdated $event)
    {
        $request = $event->request;
        if (!$request || empty($request->Request_Type)) {
            return;
        }

        $type = strtoupper(trim((string) $request->Request_Type));
        $requestId = (int) $request->ID;

        $handlers = [
            'STATUS_CHANGE'           => 'handleStatusChange',
            'REQUISITION'             => 'handleRequisition',
            'GA_WORK_ORDER'           => 'handleGaWorkOrder',
            'ATTENDANCE_OVERTIME'     => 'handleAttendanceOvertime',
            'ATTENDANCE_LEAVE'        => 'handleAttendanceLeave',
            'ATTENDANCE_CORRECTION'   => 'handleAttendanceCorrection',
            'ATTENDANCE_CHANGE_SHIFT' => 'handleAttendanceChangeShift',
            'SPPD'                    => 'handleSppd',
            'SETTLEMENT'              => 'handleSettlement',
            'EMPLOYEE_DATA_UPDATE'    => 'handleEmployeeDataUpdate',
            'PROCUREMENT'             => 'handleProcurement',
            'PERFORMANCE_KPI'         => 'handlePerformanceKpi',
            'REIMBURSEMENT'           => 'handleReimbursement',
            'PAYROLL'                 => 'handlePayroll',
            'SALARY'                  => 'handlePayroll',
            'PAYROLL_THR'             => 'handlePayrollThr',
            'EMPLOYEE_RESIGNATION'    => 'handleEmployeeResignation',
            'EXIT_CLEARANCE'          => 'handleExitClearance',
            'TNA_REQUEST'             => 'handleTnaRequest',
            'TRAINING_REQUEST'        => 'handleTrainingRequest',
            'DISCIPLINARY_SANCTION'   => 'handleDisciplinarySanction',
            'ASSET_MAINTENANCE_SCHEDULE' => 'handleAssetMaintenanceSchedule',
            'VEHICLE_TAX_REQUEST'        => 'handleVehicleTaxRequest',
            'VEHICLE_FLEET'              => 'handleVehicleFleet',
            'VEHICLE_LOGBOOK'            => 'handleVehicleLogbook',
        ];

        $method = $handlers[$type] ?? null;
        if (!$method || !method_exists($this, $method)) {
            return;
        }

        try {
            $this->{$method}($requestId, $request->Status);

            if (\Illuminate\Support\Facades\Schema::hasColumn('approval_request', 'Sync_Status')) {
                DB::table('approval_request')->where('ID', $requestId)->update([
                    'Sync_Status'    => 'SYNCED',
                    'Sync_Error'     => null,
                    'Sync_Attempts'  => DB::raw('COALESCE(Sync_Attempts, 0) + 1'),
                    'Last_Synced_At' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("SyncModuleApprovalStatus error for Request_Type {$type} [ID: {$requestId}]: " . $e->getMessage(), [
                'exception' => $e,
            ]);

            if (\Illuminate\Support\Facades\Schema::hasColumn('approval_request', 'Sync_Status')) {
                DB::table('approval_request')->where('ID', $requestId)->update([
                    'Sync_Status'   => 'FAILED',
                    'Sync_Error'    => substr($e->getMessage(), 0, 1000),
                    'Sync_Attempts' => DB::raw('COALESCE(Sync_Attempts, 0) + 1'),
                ]);
            }
        }
    }

    protected function handleStatusChange(int $requestId): void
    {
        $sc = DB::table('status_changes')->where('approval_request_id', $requestId)->first();
        if (!$sc) {
            $sc = DB::table('status_changes')->where('id', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if ($sc) {
            app(StatusChangeApprovalService::class)->syncApprovalStatus((int) $sc->id, 'system');
        }
    }

    protected function handleRequisition(int $requestId): void
    {
        $req = DB::table('requisition')->where('Approval_Request_ID', $requestId)->first();
        if ($req) {
            $service = app(RequisitionApprovalService::class);
            $service->syncLegacyRows((int) $req->ID);
            $service->syncRequisitionStatus((int) $req->ID);
        }
    }

    protected function handleGaWorkOrder(int $requestId): void
    {
        $wo = DB::table('ga_work_order')->where('Approval_Request_ID', $requestId)->first();
        if ($wo) {
            app(WorkOrderService::class)->syncApprovalStatus((int) $wo->ID);
        }
    }

    protected function handleAttendanceOvertime(int $requestId, ?string $status): void
    {
        $statusMap = [
            'APPROVED'  => 'APPROVED',
            'REJECTED'  => 'REJECTED',
            'CANCELLED' => 'REJECTED',
        ];
        $targetStatus = $statusMap[$status] ?? null;
        if ($targetStatus) {
            DB::table('attendance_overtime_requests')
                ->where('approval_request_id', $requestId)
                ->update([
                    'status'     => $targetStatus,
                    'updated_at' => now(),
                ]);

            // Selaraskan juga status ke tabel legacy overtime_lists
            $submitApproval = ($targetStatus === 'APPROVED') ? 1 : -1;
            DB::table('overtime_lists')
                ->where('approval_request_id', $requestId)
                ->update([
                    'Submit_Approval' => $submitApproval,
                ]);
        }
    }

    protected function handleAttendanceLeave(int $requestId, ?string $status): void
    {
        $statusMap = [
            'APPROVED'  => 'APPROVED',
            'REJECTED'  => 'REJECTED',
            'CANCELLED' => 'CANCELLED',
        ];
        $targetStatus = $statusMap[$status] ?? null;
        if ($targetStatus) {
            DB::table('attendance_leave_requests')
                ->where('approval_request_id', $requestId)
                ->update([
                    'status'     => $targetStatus,
                    'updated_at' => now(),
                ]);

            // Jika status pengajuan disetujui (APPROVED), lakukan pemotongan saldo cuti otomatis
            if ($targetStatus === 'APPROVED') {
                $leave = DB::table('attendance_leave_requests')
                    ->where('approval_request_id', $requestId)
                    ->first();

                if ($leave) {
                    try {
                        app(\App\Services\Attendance\LeaveEntitlementService::class)->deductLeaveBalance(
                            $leave->nik,
                            (int) $leave->total_days,
                            $leave->request_no,
                            $leave->leave_type,
                            'SYSTEM_APPROVAL'
                        );
                    } catch (\Throwable $e) {
                        Log::warning("Gagal memotong saldo cuti untuk request {$leave->request_no}: " . $e->getMessage());
                    }
                }
            }

            // Selaraskan status ke tabel legacy izin
            try {
                $leaveItem = DB::table('attendance_leave_requests')
                    ->where('approval_request_id', $requestId)
                    ->first();
                app(\App\Services\Attendance\LeaveLegacySyncService::class)->syncApprovalStatusToLegacy(
                    $requestId,
                    $targetStatus,
                    $leaveItem ? (int) $leaveItem->id : null
                );
            } catch (\Throwable $e) {
                Log::warning("Sync status ke legacy izin gagal untuk approval_request_id {$requestId}: " . $e->getMessage());
            }
        }
    }

    protected function handleAttendanceCorrection(int $requestId, ?string $status): void
    {
        $statusMap = [
            'APPROVED'  => 'APPROVED',
            'REJECTED'  => 'REJECTED',
            'CANCELLED' => 'REJECTED',
        ];
        $targetStatus = $statusMap[$status] ?? null;
        if ($targetStatus) {
            DB::table('attendance_corrections')
                ->where('approval_request_id', $requestId)
                ->update([
                    'status'     => $targetStatus,
                    'updated_at' => now(),
                ]);

            // Jika disetujui (APPROVED), sinkronkan langsung jam masuk/pulang ke attendant_log
            if ($targetStatus === 'APPROVED') {
                $corr = DB::table('attendance_corrections')
                    ->where('approval_request_id', $requestId)
                    ->first();

                if ($corr && !empty($corr->nik) && !empty($corr->attendance_date)) {
                    $this->applyApprovedCorrectionToLog($corr);
                }
            }
        }
    }

    protected function applyApprovedCorrectionToLog($corr): void
    {
        try {
            $nik = $corr->nik;
            $date = $corr->attendance_date;
            $corrType = strtoupper(trim((string) ($corr->correction_type ?? 'BOTH')));
            $schedule = app(\App\Services\Attendance\AttendanceService::class)->getWorkScheduleForDate($nik, $date);
            $expectedStartStr = $schedule['default_start'] ?? '08:00:00';

            $existing = DB::table('attendant_log')
                ->where('NIK', $nik)
                ->where('Scan_Date', $date)
                ->first();

            $newStart = $existing ? $existing->Start_Time : null;
            $newEnd = $existing ? $existing->End_Time : null;

            if ($corrType !== 'OUT' && !empty($corr->corrected_clock_in)) {
                $newStart = $corr->corrected_clock_in;
            }
            if ($corrType !== 'IN' && !empty($corr->corrected_clock_out)) {
                $newEnd = $corr->corrected_clock_out;
            }

            $lateMinutes = 0;
            $clockInStatus = 'ON_TIME';
            if (!empty($newStart) && !empty($schedule['is_working'])) {
                $expectedCarbon = \Carbon\Carbon::parse($date . ' ' . $expectedStartStr);
                $actualCarbon = \Carbon\Carbon::parse($date . ' ' . $newStart);
                if ($actualCarbon->gt($expectedCarbon)) {
                    $diff = $actualCarbon->diffInMinutes($expectedCarbon);
                    $lateMinutes = $diff > 15 ? $diff : 0;
                    $clockInStatus = $diff > 15 ? 'LATE' : 'ON_TIME';
                }
            }

            if ($existing) {
                DB::table('attendant_log')->where('ID', $existing->ID)->update([
                    'Start_Time'      => $newStart,
                    'End_Time'        => $newEnd,
                    'Clock_In_Status' => $clockInStatus,
                    'Late_Minutes'    => $lateMinutes,
                    'Notes'           => 'Koreksi Absensi Approved (' . ($corr->correction_no ?? '') . ')',
                ]);
            } else {
                $periode = (int) date('Ym', strtotime($date));
                $main = \App\AttendanceMain::firstOrCreate(
                    ['NIK' => $nik, 'Periode' => $periode],
                    [
                        'Task_ID'      => 'ATT-' . $nik . '-' . $periode . '-000001',
                        'Status'       => 'CREATED',
                        'Created_By'   => $corr->created_by ?? 'SYSTEM',
                        'Created_Date' => now(),
                    ]
                );

                DB::table('attendant_log')->insert([
                    'Task_ID'         => $main->Task_ID,
                    'NIK'             => $nik,
                    'Periode'         => $periode,
                    'Scan_Date'       => $date,
                    'Category'        => 'KERJA',
                    'Start_Time'      => $newStart,
                    'End_Time'        => $newEnd,
                    'Shift_1'         => 1,
                    'Shift_2'         => 0,
                    'Shift_3'         => 0,
                    'Clock_In_Status' => $clockInStatus,
                    'Late_Minutes'    => $lateMinutes,
                    'Notes'           => 'Koreksi Absensi Approved (' . ($corr->correction_no ?? '') . ')',
                    'Created_By'      => $corr->created_by ?? 'SYSTEM',
                    'Created_Date'    => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal menyinkronkan koreksi absensi ke attendant_log: " . $e->getMessage());
        }
    }

    protected function handleAttendanceChangeShift(int $requestId, ?string $status): void
    {
        $statusUpper = strtoupper((string) $status);
        if ($statusUpper === 'APPROVED') {
            app(\App\Services\Attendance\ShiftExchangeService::class)->applyApprovedChangeShift($requestId);
        } elseif (in_array($statusUpper, ['REJECTED', 'CANCELLED'])) {
            DB::table('attendance_change_shifts')
                ->where('approval_request_id', $requestId)
                ->update([
                    'status'     => $statusUpper === 'CANCELLED' ? 'CANCELLED' : 'REJECTED',
                    'updated_at' => now(),
                ]);
        }
    }

    protected function handleSppd(int $requestId): void
    {
        $sppd = DB::table('sppd')->where('Approval_Request_ID', $requestId)->first();
        if (!$sppd) {
            $sppd = DB::table('sppd')->where('ID', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if ($sppd) {
            app(\App\Services\Approval\SppdApprovalService::class)->syncApprovalStatus((int) $sppd->ID, 'system');
        }
    }

    protected function handleReimbursement(int $requestId, ?string $status): void
    {
        $rmb = DB::table('reimbursement')->where('Approval_Request_ID', $requestId)->first();
        if (!$rmb) {
            $rmb = DB::table('reimbursement')->where('ID', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if ($rmb) {
            $rmbService = app(\App\Services\Approval\ReimbursementApprovalService::class);
            $rmbService->syncApprovalStatus((int) $rmb->ID, 'system');

            // Jika masih dalam proses approval (misal naik stage / belum final), kirim notifikasi email ke approver stage aktif
            $statusUpper = strtoupper((string) $status);
            if (!in_array($statusUpper, ['APPROVED', 'COMPLETED', 'REJECTED', 'CANCELLED'])) {
                $rmbService->notifyCurrentStageApprovers((int) $rmb->ID, 'Update Tahap Approval Reimbursement');
            }
        }
    }

    protected function handlePayroll(int $requestId, ?string $approvalStatus): void
    {
        $sm = DB::table('salary_main')->where('approval_request_id', $requestId)->first();
        if (!$sm) {
            $sm = DB::table('salary_main')->where('Task_ID', function ($query) use ($requestId) {
                $query->select('Request_No')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if (!$sm) {
            return;
        }

        if (class_exists(\App\Services\Payroll\PayrollApprovalService::class)) {
            app(\App\Services\Payroll\PayrollApprovalService::class)
                ->syncSalaryMainStatus((string)$sm->Task_ID, 'APPROVAL_ENGINE');
            return;
        }

        $statusUpper = strtoupper((string) $approvalStatus);
        $newSalaryStatus = 'PROPOSED';
        if (in_array($statusUpper, ['APPROVED', 'COMPLETED'])) {
            $newSalaryStatus = 'APPROVED';
            if (class_exists(\App\Services\Payroll\PayrollAttendanceSyncService::class)) {
                try {
                    app(\App\Services\Payroll\PayrollAttendanceSyncService::class)->lockAttendancePeriod($sm->Salary_Period, 'LOCKED', 'APPROVAL_ENGINE');
                } catch (\Exception $e) {
                    Log::warning("Gagal mengunci presensi untuk payroll {$sm->Task_ID}: " . $e->getMessage());
                }
            }
        } elseif (in_array($statusUpper, ['REJECTED', 'RETURNED'])) {
            $newSalaryStatus = 'REJECTED';
        } elseif ($statusUpper === 'CANCELLED') {
            $newSalaryStatus = 'CANCELLED';
        }

        DB::table('salary_main')->where('Task_ID', $sm->Task_ID)->update([
            'Status'     => $newSalaryStatus,
            'Posting_By' => 'APPROVAL_ENGINE',
        ]);
    }

    protected function handlePayrollThr(int $requestId, ?string $approvalStatus): void
    {
        $batch = DB::table('payroll_thr_batches')->where('approval_request_id', $requestId)->first();
        if (!$batch) {
            $req = DB::table('approval_request')
                ->where('ID', $requestId)
                ->select('Map_To', 'Request_No')
                ->first();

            if ($req && !empty($req->Map_To)) {
                $batch = DB::table('payroll_thr_batches')->where('id', (int)$req->Map_To)->first();
            }
            if (!$batch && $req && !empty($req->Request_No)) {
                $batch = DB::table('payroll_thr_batches')->where('batch_no', (string)$req->Request_No)->first();
            }
        }

        if (!$batch) {
            return;
        }

        $actor = optional(auth()->user())->username ?? 'APPROVAL_ENGINE';
        app(\App\Services\Payroll\PayrollThrApprovalService::class)
            ->syncThrBatchStatus((int)$batch->id, $actor);
    }

    protected function handleEmployeeResignation(int $requestId, ?string $approvalStatus): void
    {
        $resignation = DB::table('employee_resignations')->where('Approval_Request_ID', $requestId)->first();
        if (!$resignation) {
            $resignation = DB::table('employee_resignations')->where('id', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if (!$resignation) {
            return;
        }

        $statusUpper = strtoupper((string) $approvalStatus);
        $exitClearanceService = app(\App\Services\HCGA\ExitClearanceService::class);

        if (in_array($statusUpper, ['APPROVED', 'COMPLETED'])) {
            if ($resignation->Status !== 'APPROVED') {
                $exitClearanceService->approveResignation((int) $resignation->id, (int) (auth()->id() ?: 1), 'Approved via Approval Inbox');
            }
        } elseif (in_array($statusUpper, ['REJECTED', 'RETURNED'])) {
            if ($resignation->Status !== 'REJECTED') {
                $exitClearanceService->rejectResignation((int) $resignation->id, (int) (auth()->id() ?: 1), 'Rejected via Approval Inbox');
            }
        }
    }

    protected function handleExitClearance(int $requestId, ?string $approvalStatus): void
    {
        $clearance = DB::table('exit_clearances')->where('Approval_Request_ID', $requestId)->first();
        if (!$clearance) {
            $clearance = DB::table('exit_clearances')->where('id', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if (!$clearance) {
            return;
        }

        $statusUpper = strtoupper((string) $approvalStatus);
        $exitClearanceService = app(\App\Services\HCGA\ExitClearanceService::class);

        if (in_array($statusUpper, ['APPROVED', 'COMPLETED'])) {
            if ($clearance->Status !== 'COMPLETED') {
                $exitClearanceService->verifyAndApproveByHcga((int) $clearance->id, [
                    'hcga_notes' => 'Approved and completed via Approval Inbox',
                ], (int) (auth()->id() ?: 1));
            }
        } elseif (in_array($statusUpper, ['REJECTED', 'RETURNED'])) {
            DB::table('exit_clearances')->where('id', $clearance->id)->update([
                'Status' => 'REJECTED',
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
    }

    protected function handleDisciplinarySanction(int $requestId, ?string $approvalStatus): void
    {
        $sanction = DB::table('disciplinary_sanctions')->where('Approval_Request_ID', $requestId)->first();
        if (!$sanction) {
            $sanction = DB::table('disciplinary_sanctions')->where('id', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if (!$sanction) {
            return;
        }

        $statusUpper = strtoupper((string) $approvalStatus);

        if (in_array($statusUpper, ['APPROVED', 'COMPLETED'])) {
            if ($sanction->Status === 'PENDING_HCGA') {
                DB::table('disciplinary_sanctions')->where('id', $sanction->id)->update([
                    'Status' => 'WAITING_EMPLOYEE',
                    'HCGA_Approved_At' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
            }
        } elseif (in_array($statusUpper, ['REJECTED', 'RETURNED'])) {
            DB::table('disciplinary_sanctions')->where('id', $sanction->id)->update([
                'Status' => 'REJECTED',
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
    }

    protected function handleTnaRequest(int $requestId, ?string $approvalStatus): void
    {
        $tna = DB::table('hcga_tna_requests')->where('approval_request_id', $requestId)->first();
        if (!$tna) {
            $tna = DB::table('hcga_tna_requests')->where('id', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }
        if (!$tna) {
            return;
        }

        $statusUpper = strtoupper((string) $approvalStatus);
        if (in_array($statusUpper, ['APPROVED', 'COMPLETED'])) {
            DB::table('hcga_tna_requests')->where('id', $tna->id)->update([
                'status' => 'APPROVED',
                'approved_at' => \Carbon\Carbon::now(),
                'approved_by' => optional(auth()->user())->username ?? 'approval-engine',
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        } elseif (in_array($statusUpper, ['REJECTED', 'RETURNED'])) {
            DB::table('hcga_tna_requests')->where('id', $tna->id)->update([
                'status' => 'REJECTED',
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
    }

    protected function handleTrainingRequest(int $requestId, ?string $approvalStatus): void
    {
        $training = DB::table('training_hcga')->where('approval_request_id', $requestId)->first();
        if (!$training) {
            $training = DB::table('training_hcga')->where('ID', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }
        if (!$training) {
            return;
        }

        $statusUpper = strtoupper((string) $approvalStatus);
        if (in_array($statusUpper, ['APPROVED', 'COMPLETED'])) {
            DB::table('training_hcga')->where('ID', $training->ID)->update([
                'Status' => 'APPROVED',
                'Approved_Date' => \Carbon\Carbon::now(),
                'Approved_By' => optional(auth()->user())->username ?? 'approval-engine',
                'Updated_Date' => \Carbon\Carbon::now(),
            ]);
        } elseif (in_array($statusUpper, ['REJECTED', 'RETURNED'])) {
            DB::table('training_hcga')->where('ID', $training->ID)->update([
                'Status' => 'REJECTED',
                'Updated_Date' => \Carbon\Carbon::now(),
            ]);
        }
    }

    protected function handleSettlement(int $requestId): void
    {
        $sttl = DB::table('settlement_sppd')->where('Approval_Request_ID', $requestId)->first();
        if (!$sttl) {
            $sttl = DB::table('settlement_sppd')->where('ID', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if ($sttl) {
            app(\App\Services\Approval\SettlementApprovalService::class)->syncApprovalStatus((int) $sttl->ID);
        }
    }

    protected function handleEmployeeDataUpdate(int $requestId): void
    {
        $req = DB::table('employee_data_update_requests')->where('approval_request_id', $requestId)->first();
        if (!$req) {
            $req = DB::table('employee_data_update_requests')->where('id', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if ($req) {
            app(\App\Services\Approval\EmployeeDataUpdateApprovalService::class)->syncApprovalStatus((int) $req->id, 'system');
        }
    }

    protected function handleProcurement(int $requestId): void
    {
        $pr = DB::table('purchase_request')->where('Approval_Request_ID', $requestId)->first();
        if (!$pr) {
            $pr = DB::table('purchase_request')->where('ID', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if ($pr) {
            app(\App\Services\Approval\ProcurementApprovalService::class)->syncApprovalStatus((int) $pr->ID);
        }
    }

    protected function handleAssetMaintenanceSchedule(int $requestId): void
    {
        app(\App\Services\Approval\AssetFleetApprovalService::class)
            ->syncStatus(\App\Services\Approval\AssetFleetApprovalService::TYPE_MAINTENANCE_SCHEDULE, $requestId);
    }

    protected function handleVehicleTaxRequest(int $requestId): void
    {
        app(\App\Services\Approval\AssetFleetApprovalService::class)
            ->syncStatus(\App\Services\Approval\AssetFleetApprovalService::TYPE_VEHICLE_TAX, $requestId);
    }

    protected function handleVehicleFleet(int $requestId): void
    {
        app(\App\Services\Approval\AssetFleetApprovalService::class)
            ->syncStatus(\App\Services\Approval\AssetFleetApprovalService::TYPE_VEHICLE_FLEET, $requestId);
    }

    protected function handleVehicleLogbook(int $requestId): void
    {
        app(\App\Services\Approval\AssetFleetApprovalService::class)
            ->syncStatus(\App\Services\Approval\AssetFleetApprovalService::TYPE_VEHICLE_LOGBOOK, $requestId);
    }

    protected function handlePerformanceKpi(int $requestId, ?string $approvalStatus): void
    {
        $goal = DB::table('performance_goals')->where('approval_request_id', $requestId)->first();
        if (!$goal) {
            $goal = DB::table('performance_goals')->where('id', function ($query) use ($requestId) {
                $query->select('Map_To')->from('approval_request')->where('ID', $requestId);
            })->first();
        }

        if (!$goal) {
            return;
        }

        $statusUpper = strtoupper((string) $approvalStatus);
        $now = \Carbon\Carbon::now();

        if (in_array($statusUpper, ['APPROVED', 'COMPLETED'])) {
            DB::table('performance_goals')->where('id', $goal->id)->update([
                'status'           => 'APPROVED',
                'rejection_reason' => null,
                'updated_at'       => $now,
            ]);
        } elseif (in_array($statusUpper, ['REJECTED', 'RETURNED'])) {
            $lastLog = DB::table('approval_log')
                ->where('Request_ID', $requestId)
                ->whereIn('Action', ['REJECTED', 'RETURNED'])
                ->orderBy('ID', 'desc')
                ->first();

            $reason = ($lastLog && !empty($lastLog->Note))
                ? $lastLog->Note
                : 'Ditolak pada proses approval. Silakan revisi target KPI dan ajukan ulang.';

            DB::table('performance_goals')->where('id', $goal->id)->update([
                'status'           => 'REJECTED',
                'rejection_reason' => $reason,
                'updated_at'       => $now,
            ]);
        } elseif ($statusUpper === 'CANCELLED') {
            DB::table('performance_goals')->where('id', $goal->id)->update([
                'status'     => 'DRAFT',
                'updated_at' => $now,
            ]);
        }
    }
}

