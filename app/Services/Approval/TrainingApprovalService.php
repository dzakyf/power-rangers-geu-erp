<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use App\Models\HCGA\Training\HcgaTnaRequest;
use App\Models\HCGA\Training\HcgaTraining;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainingApprovalService
{
    const REQUEST_TYPE_TNA = 'TNA_REQUEST';
    const REQUEST_TYPE_TRAINING = 'TRAINING_REQUEST';

    protected $workflow;

    public function __construct(ApprovalWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Submit pengajuan TNA ke ApprovalWorkflowService.
     *
     * @param HcgaTnaRequest $tna
     * @param object $user
     * @return object
     * @throws ApprovalWorkflowException
     */
    public function submitTna(HcgaTnaRequest $tna, $user)
    {
        if (!in_array($tna->status, ['DRAFT', 'REJECTED'], true)) {
            throw new ApprovalWorkflowException(
                'Hanya TNA berstatus DRAFT atau REJECTED yang dapat diajukan approval.',
                'INVALID_TNA_STATUS',
                422
            );
        }

        return DB::transaction(function () use ($tna, $user) {
            $context = [
                'Request_Type' => self::REQUEST_TYPE_TNA,
                'Location'     => $user->Location ?? ($user->location ?? 'HEAD_OFFICE'),
                'Company_ID'   => $user->Company_ID ?? ($user->company_id ?? 1),
                'Department'   => $tna->department ?: ($user->Department ?? 'GENERAL'),
                'Amount'       => 0,
            ];

            $description = "TNA ({$tna->fiscal_year}) - {$tna->department}: {$tna->title}";

            $approvalReq = $this->workflow->submit([
                'Request_No'  => $tna->tna_no,
                'Description' => $description,
            ], $context, (int) ($user->id ?? $user->ID ?? 1));

            if ($approvalReq && !empty($approvalReq->ID)) {
                DB::table('approval_request')->where('ID', $approvalReq->ID)->update([
                    'Map_To' => (int) $tna->id,
                ]);

                $freshStatus = DB::table('approval_request')->where('ID', $approvalReq->ID)->value('Status');
                $reqStatus = strtoupper((string) ($freshStatus ?: ($approvalReq->Status ?? 'WAITING_APPROVAL')));
                $tna->approval_request_id = $approvalReq->ID;
                if (in_array($reqStatus, ['APPROVED', 'COMPLETED'], true)) {
                    $tna->status = 'APPROVED';
                    $tna->approved_at = Carbon::now();
                    $tna->approved_by = $user->username ?? 'approval-engine';
                } else {
                    $tna->status = 'WAITING_APPROVAL';
                }
                $tna->save();
            }

            return $approvalReq;
        });
    }

    /**
     * Submit Request / Jadwal Training ke ApprovalWorkflowService.
     *
     * @param HcgaTraining $training
     * @param object $user
     * @return object
     * @throws ApprovalWorkflowException
     */
    public function submitTraining(HcgaTraining $training, $user)
    {
        if (!in_array(strtoupper((string) $training->Status), ['DRAFT', 'CREATED', 'REJECTED'], true)) {
            throw new ApprovalWorkflowException(
                'Hanya Jadwal Training berstatus DRAFT/CREATED atau REJECTED yang dapat diajukan approval.',
                'INVALID_TRAINING_STATUS',
                422
            );
        }

        return DB::transaction(function () use ($training, $user) {
            $context = [
                'Request_Type' => self::REQUEST_TYPE_TRAINING,
                'Location'     => $training->Location ?: ($user->Location ?? 'HEAD_OFFICE'),
                'Company_ID'   => $user->Company_ID ?? ($user->company_id ?? 1),
                'Department'   => $user->Department ?? 'HCGA',
                'Amount'       => 0,
            ];

            $description = "Request Training: {$training->Training_Name} ({$training->Start_Date} s/d {$training->End_Date})";

            $approvalReq = $this->workflow->submit([
                'Request_No'  => $training->Task_No,
                'Description' => $description,
            ], $context, (int) ($user->id ?? $user->ID ?? 1));

            if ($approvalReq && !empty($approvalReq->ID)) {
                DB::table('approval_request')->where('ID', $approvalReq->ID)->update([
                    'Map_To' => (int) $training->ID,
                ]);

                $freshStatus = DB::table('approval_request')->where('ID', $approvalReq->ID)->value('Status');
                $reqStatus = strtoupper((string) ($freshStatus ?: ($approvalReq->Status ?? 'WAITING_APPROVAL')));
                $training->approval_request_id = $approvalReq->ID;
                if (in_array($reqStatus, ['APPROVED', 'COMPLETED'], true)) {
                    $training->Status = 'APPROVED';
                    $training->Approved_Date = Carbon::now();
                    $training->Approved_By = $user->username ?? 'approval-engine';
                } else {
                    $training->Status = 'WAITING_APPROVAL';
                }
                $training->Updated_By = $user->username ?? 'system';
                $training->Updated_Date = Carbon::now();
                $training->save();
            }

            return $approvalReq;
        });
    }
}
