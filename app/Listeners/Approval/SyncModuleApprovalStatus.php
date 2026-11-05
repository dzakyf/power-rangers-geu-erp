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
}

