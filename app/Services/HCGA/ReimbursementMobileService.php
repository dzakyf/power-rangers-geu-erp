<?php

namespace App\Services\HCGA;

use App\Services\UploadService;
use App\Services\Approval\ReimbursementApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service pendukung fungsionalitas Reimbursement versi Mobile Webview & API.
 */
class ReimbursementMobileService
{
    /**
     * @var UploadService
     */
    protected $uploadService;

    /**
     * @var ReimbursementApprovalService
     */
    protected $approvalService;

    public function __construct(UploadService $uploadService, ReimbursementApprovalService $approvalService)
    {
        $this->uploadService   = $uploadService;
        $this->approvalService = $approvalService;
    }

    /**
     * Ambil daftar riwayat reimbursement milik user (dengan filter status opsional).
     */
    public function getUserReimbursementList(int $userId, string $status = 'ALL', int $limit = 30)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return collect();
        }

        $query = DB::table('reimbursement as r')
            ->leftJoin('meta_files as mf', 'mf.ID', '=', 'r.Attachment_File')
            ->where(function ($w) use ($user) {
                $w->where('r.User_Responsibility_ID', $user->Emp_ID)
                  ->orWhere('r.Created_By', $user->username);
            })
            ->select(
                'r.*',
                'mf.Filename as Attachment_Filename',
                'mf.Extension as Attachment_Extension'
            )
            ->orderBy('r.ID', 'DESC');

        if ($status !== 'ALL') {
            if ($status === 'APPROVED') {
                $query->whereIn('r.Status', ['APPROVED', 'CLOSED']);
            } elseif ($status === 'REJECTED') {
                $query->whereIn('r.Status', ['REJECT', 'REJECTED', 'CANCELED']);
            } elseif ($status === 'PENDING') {
                $query->whereIn('r.Status', ['APPROVAL_UPPER', 'APPROVAL_VP', 'APPROVAL_HCGA', 'APPROVAL_DIRECTOR', 'WAITING_APPROVAL', 'IN_PROGRESS', 'SUBMITTED', 'CREATED']);
            } else {
                $query->where('r.Status', $status);
            }
        }

        return $query->limit($limit)->get();
    }

    /**
     * Ambil daftar antrean persetujuan reimbursement untuk approver.
     */
    public function getApproverQueue($user, int $limit = 30)
    {
        $isAdmin = \App\Http\Controllers\HCGA\ReimbursementApprovalController::isAdministrator($user);
        $isHcga  = \App\Http\Controllers\HCGA\ReimbursementApprovalController::isHcgaVerifier($user);

        $query = DB::table('reimbursement as r')
            ->leftJoin('approval_request as ar', 'ar.ID', '=', 'r.Approval_Request_ID')
            ->leftJoin('approval_request_task as t', function ($join) {
                $join->on('t.Request_ID', '=', 'ar.ID')
                     ->where('t.Status', '=', 'PENDING');
            })
            ->leftJoin('approval_task_candidate as c', function ($join) {
                $join->on('c.Task_ID', '=', 't.ID')
                     ->where('c.Status', '=', 'ACTIVE');
            })
            ->select(
                'r.*',
                't.Label as Task_Label',
                't.Position_Name as Task_Position',
                't.Stage_Order as Task_Stage_Order'
            )
            ->where(function ($w) {
                $w->whereIn('r.Status', ['APPROVAL_UPPER', 'APPROVAL_VP', 'APPROVAL_HCGA', 'APPROVAL_DIRECTOR', 'WAITING_APPROVAL', 'IN_PROGRESS', 'SUBMITTED'])
                  ->orWhereNull('r.Status');
            });

        if (!$isAdmin && !$isHcga) {
            $query->where(function ($w) use ($user) {
                $w->where('t.Claimed_By', $user->id)
                  ->orWhere('c.User_Id', $user->id)
                  ->orWhere('r.Approver_ID', $user->id);
            });
        }

        return $query->distinct()->orderBy('r.ID', 'DESC')->limit($limit)->get();
    }

    /**
     * Ambil rincian lengkap satu dokumen reimbursement.
     */
    public function getDetail($id, $user)
    {
        $reimbursement = DB::table('reimbursement')->where('ID', $id)->first();
        if (!$reimbursement) {
            return null;
        }

        $dtls = DB::table('reimbursement_dtl')
            ->where('Reimbursement_No', $reimbursement->Reimbursement_No)
            ->orderBy('ID', 'ASC')
            ->get();

        $logs = DB::table('reimbursement_log')
            ->where('Cash_No', $reimbursement->Reimbursement_No)
            ->orderBy('ID', 'ASC')
            ->get();

        $attachment = $reimbursement->Attachment_File ? DB::table('meta_files')->where('ID', $reimbursement->Attachment_File)->first() : null;

        // Cek current approval task
        $currentTask = null;
        $canApprove = false;
        if (!empty($reimbursement->Approval_Request_ID)) {
            $currentTask = DB::table('approval_request_task as t')
                ->where('t.Request_ID', (int) $reimbursement->Approval_Request_ID)
                ->where('t.Status', 'PENDING')
                ->orderBy('t.Stage_Order', 'ASC')
                ->first();

            if ($currentTask) {
                if ((int) $currentTask->Claimed_By === (int) $user->id) {
                    $canApprove = true;
                } else {
                    $isCandidate = DB::table('approval_task_candidate')
                        ->where('Task_ID', $currentTask->ID)
                        ->where('User_Id', $user->id)
                        ->where('Status', 'ACTIVE')
                        ->exists();
                    if ($isCandidate) {
                        $canApprove = true;
                    }
                }
            }
        }

        if (\App\Http\Controllers\HCGA\ReimbursementApprovalController::isAdministrator($user) || \App\Http\Controllers\HCGA\ReimbursementApprovalController::isHcgaVerifier($user)) {
            $canApprove = true;
        }

        if (in_array(strtoupper($reimbursement->Status), ['APPROVED', 'CLOSED', 'REJECT', 'REJECTED', 'CANCELED'])) {
            $canApprove = false;
        }

        return compact('reimbursement', 'dtls', 'logs', 'attachment', 'currentTask', 'canApprove');
    }
}

