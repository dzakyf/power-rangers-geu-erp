<?php

namespace App\Listeners\Approval;

use App\Events\Approval\ApprovalRequestUpdated;
use App\Services\Approval\RequisitionApprovalService;
use App\Services\Approval\StatusChangeApprovalService;
use App\Services\GA\WorkOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        try {
            switch ($type) {
                case 'STATUS_CHANGE':
                    $this->handleStatusChange($requestId);
                    break;

                case 'REQUISITION':
                    $this->handleRequisition($requestId);
                    break;

                case 'GA_WORK_ORDER':
                    $this->handleGaWorkOrder($requestId);
                    break;

                case 'ATTENDANCE_OVERTIME':
                    $this->handleAttendanceOvertime($requestId, $request->Status);
                    break;

                case 'ATTENDANCE_LEAVE':
                    $this->handleAttendanceLeave($requestId, $request->Status);
                    break;

                case 'ATTENDANCE_CORRECTION':
                    $this->handleAttendanceCorrection($requestId, $request->Status);
                    break;

                case 'ATTENDANCE_CHANGE_SHIFT':
                    $this->handleAttendanceChangeShift($requestId, $request->Status);
                    break;

                case 'SPPD':
                    $this->handleSppd($requestId);
                    break;

                case 'REIMBURSEMENT':
                    $this->handleReimbursement($requestId, $request->Status);
                    break;

                case 'PAYROLL':
                case 'SALARY':
                    $this->handlePayroll($requestId, $request->Status);
                    break;

                default:
                    // Request_Type lain dapat didaftarkan di sini
                    break;
            }
        } catch (\Exception $e) {
            Log::error("SyncModuleApprovalStatus error for Request_Type {$type} [ID: {$requestId}]: " . $e->getMessage(), [
                'exception' => $e,
            ]);
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
}

