<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use App\Services\HCGA\ExitClearanceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExitClearanceApprovalService
{
    const REQUEST_TYPE_RESIGNATION = 'EMPLOYEE_RESIGNATION';
    const REQUEST_TYPE_CLEARANCE   = 'EXIT_CLEARANCE';

    protected $workflow;
    protected $policyResolver;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalPolicyResolver $policyResolver
    ) {
        $this->workflow = $workflow;
        $this->policyResolver = $policyResolver;
    }

    /**
     * Submit pengajuan Resign ke Approval Workflow Engine
     */
    public function submitResignation($resignation, $user)
    {
        $resignationObj = (object) $resignation;
        $resignationId = (int) $resignationObj->id;

        return DB::transaction(function () use ($resignationObj, $resignationId, $user) {
            $context = [
                'Request_Type' => self::REQUEST_TYPE_RESIGNATION,
                'Location'     => $user->Location ?? ($user->location ?? 'HEAD_OFFICE'),
                'Company_ID'   => $user->Company_ID ?? ($user->company_id ?? 1),
                'Department'   => $resignationObj->Departemen ?? ($user->Department ?? 'GENERAL'),
                'Amount'       => 0,
            ];

            $description = "Pengajuan Resign: {$resignationObj->Nama} ({$resignationObj->Jabatan}) - Efektif: {$resignationObj->Last_Working_Date}";

            $approvalReq = $this->workflow->submit([
                'Request_No'  => $resignationObj->No_Trx,
                'Description' => $description,
            ], $context, (int) $resignationObj->User_ID);

            if ($approvalReq && !empty($approvalReq->ID)) {
                DB::table('approval_request')->where('ID', $approvalReq->ID)->update([
                    'Map_To' => $resignationId,
                ]);

                DB::table('employee_resignations')->where('id', $resignationId)->update([
                    'Approval_Request_ID' => $approvalReq->ID,
                    'Status'              => 'PENDING_APPROVAL',
                    'updated_at'          => Carbon::now(),
                ]);
            }

            return $approvalReq;
        });
    }

    /**
     * Submit pengisian Exit Clearance ke Approval Workflow Engine
     */
    public function submitClearance($clearance, $user)
    {
        $clearanceObj = (object) $clearance;
        $clearanceId = (int) $clearanceObj->id;

        return DB::transaction(function () use ($clearanceObj, $clearanceId, $user) {
            $context = [
                'Request_Type' => self::REQUEST_TYPE_CLEARANCE,
                'Location'     => $user->Location ?? ($user->location ?? 'HEAD_OFFICE'),
                'Company_ID'   => $user->Company_ID ?? ($user->company_id ?? 1),
                'Department'   => $clearanceObj->Departemen ?? 'GENERAL',
                'Amount'       => 0,
            ];

            $description = "Exit Clearance & Interview: {$clearanceObj->Nama} ({$clearanceObj->No_Trx})";

            $approvalReq = $this->workflow->submit([
                'Request_No'  => $clearanceObj->No_Trx,
                'Description' => $description,
            ], $context, (int) $clearanceObj->User_ID);

            if ($approvalReq && !empty($approvalReq->ID)) {
                DB::table('approval_request')->where('ID', $approvalReq->ID)->update([
                    'Map_To' => $clearanceId,
                ]);

                DB::table('exit_clearances')->where('id', $clearanceId)->update([
                    'Approval_Request_ID' => $approvalReq->ID,
                    'Status'              => 'PENDING_INTERVIEW',
                    'updated_at'          => Carbon::now(),
                ]);
            }

            return $approvalReq;
        });
    }

    /**
     * Proses tindakan approval (APPROVE/REJECT) melalui Workflow Task
     */
    public function actOnTask(int $taskId, int $userId, string $action, string $notes = '')
    {
        $task = DB::table('approval_request_task')->where('ID', $taskId)->first();
        if (!$task) {
            throw new ApprovalWorkflowException('Task approval tidak ditemukan.', 'TASK_NOT_FOUND', 404);
        }

        if (strtoupper((string) $task->Status) === 'PENDING') {
            $this->workflow->claim($taskId, $userId);
        }

        return $this->workflow->act($taskId, $userId, $action, $notes);
    }
}

