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
            $legacySync = app(\App\Services\Attendance\OvertimeLegacySyncService::class);
            $submitApproval = ($targetStatus === 'APPROVED') ? 1 : 0;
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
}

