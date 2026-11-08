<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service Adapter untuk menghubungkan Pengajuan Reimbursement dengan ApprovalWorkflowService.
 */
class ReimbursementApprovalService
{
    const REQUEST_TYPE = 'REIMBURSEMENT';

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
     * Daftarkan pengajuan Reimbursement baru ke ApprovalWorkflowService.
     *
     * @param object|array $reimbursement
     * @param object $user
     * @return object|null approval_request
     */
    public function submit($reimbursement, $user)
    {
        $rmbObj = is_array($reimbursement) ? (object) $reimbursement : $reimbursement;
        $rmbId  = (int) $rmbObj->ID;

        $context = $this->buildContext($rmbObj, $user);

        $description = sprintf(
            'Reimbursement %s [%s]: %s - Total Rp %s',
            $rmbObj->Reimbursement_No,
            $rmbObj->Department ?? '-',
            $rmbObj->Description ?? '-',
            number_format((float) ($rmbObj->Total ?? 0), 0, ',', '.')
        );

        $approvalReq = $this->workflow->submit([
            'Request_No'  => $rmbObj->Reimbursement_No ?: null,
            'Description' => $description,
        ], $context, (int) $user->id);

        if (!$approvalReq || empty($approvalReq->ID)) {
            throw new ApprovalWorkflowException('Gagal membuat approval_request untuk Reimbursement.', 'REIMBURSEMENT_APPROVAL_SUBMIT_FAILED', 500);
        }

        DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
            'Map_To' => $rmbId,
        ]);

        DB::table('reimbursement')->where('ID', $rmbId)->update([
            'Approval_Request_ID' => (int) $approvalReq->ID,
        ]);

        $this->syncApprovalStatus($rmbId);

        // Kirim notifikasi email awal ke kandidat approver stage 1
        try {
            $this->notifyCurrentStageApprovers($rmbId, 'Request Reimbursement Diajukan');
        } catch (\Exception $mailEx) {
            Log::warning('Email notification failed on submit for Reimbursement #' . $rmbId . ': ' . $mailEx->getMessage());
        }

        return $this->workflow->getRequest((int) $approvalReq->ID);
    }

    /**
     * Bangun context policy approval Reimbursement.
     */
    public function buildContext($reimbursement, $user): array
    {
        $emp = DB::table('employment')->where('NIK', $user->Emp_ID)->first();
        $pos = null;
        if ($emp && !empty($emp->Position)) {
            $pos = DB::table('master_position')->where('Initial', $emp->Position)->first();
        }

        $department = trim((string) ($reimbursement->Department ?? ($user->Department ?? '')));
        $companyId  = $pos->Company_ID ?? ($user->Company_ID ?? 'GEU');
        $location   = $pos->Location ?? ($user->Location ?? 'GEU-HO');

        return [
            'Request_Type' => self::REQUEST_TYPE,
            'Location'     => $location,
            'Company_ID'   => $companyId,
            'Department'   => $department,
            'Amount'       => (float) ($reimbursement->Total ?? 0),
        ];
    }

    /**
     * Sinkronkan status approval_request ke tabel reimbursement dan reimbursement_log.
     */
    public function syncApprovalStatus(int $rmbId, string $actor = 'system'): void
    {
        $rmb = DB::table('reimbursement')->where('ID', $rmbId)->first();
        if (!$rmb || empty($rmb->Approval_Request_ID)) {
            return;
        }

        $requestId = (int) $rmb->Approval_Request_ID;
        $apprReq = DB::table('approval_request')->where('ID', $requestId)->first();
        if (!$apprReq) {
            return;
        }

        $currentTask = DB::table('approval_request_task as t')
            ->where('t.Request_ID', $requestId)
            ->where('t.Status', 'PENDING')
            ->orderBy('t.Stage_Order', 'ASC')
            ->first();

        $approverId = null;
        $approverName = null;
        if ($currentTask) {
            if (!empty($currentTask->Claimed_By)) {
                $approverUser = DB::table('users')->where('id', (int) $currentTask->Claimed_By)->first();
                if ($approverUser) {
                    $approverId = (int) $approverUser->id;
                    $approverName = $approverUser->Name ?: $approverUser->username;
                }
            } else {
                $candidate = DB::table('approval_task_candidate as c')
                    ->join('users as u', 'u.id', '=', 'c.User_Id')
                    ->where('c.Task_ID', $currentTask->ID)
                    ->where('c.Status', 'ACTIVE')
                    ->select('u.id', 'u.Name', 'u.username')
                    ->first();
                if ($candidate) {
                    $approverId = (int) $candidate->id;
                    $approverName = $candidate->Name ?: $candidate->username;
                }
            }
            if (!$approverName && !empty($currentTask->Position_Name)) {
                $approverName = $currentTask->Position_Name;
            }
        }

        $newRmbStatus = $this->mapWorkflowStatusToRmb($apprReq->Status, $currentTask);

        $updateData = [
            'Status'        => $newRmbStatus,
            'Approver_ID'   => $approverId ?: $rmb->Approver_ID,
            'Approver_Name' => $approverName ?: $rmb->Approver_Name,
        ];

        if (in_array($apprReq->Status, ['APPROVED', 'COMPLETED'])) {
            $updateData['Status'] = 'APPROVED';
            $updateData['Approved_At'] = $apprReq->Completed_At ?: now();
        } elseif ($apprReq->Status === 'REJECTED') {
            $updateData['Status'] = 'REJECT';
        }

        DB::table('reimbursement')->where('ID', $rmbId)->update($updateData);

        // Catat ke reimbursement_log jika status berubah
        if ($newRmbStatus !== $rmb->Status) {
            DB::table('reimbursement_log')->insert([
                'Location'      => $rmb->Location ?: 'GEU-HO',
                'Cash_No'       => $rmb->Reimbursement_No,
                'Transaction'   => 'REIMBURSEMENT_APPROVAL',
                'Description'   => 'Status sinkron ke ' . $newRmbStatus . ' via Approval Engine',
                'Reference'     => $rmb->Reference_No ?: '',
                'Status'        => $newRmbStatus,
                'Created_Date'  => now(),
                'Created_By'    => $actor,
            ]);
        }
    }

    /**
     * Kirimkan email notifikasi ke approver atau kandidat pada stage aktif.
     *
     * @param int $rmbId
     * @param string $eventTitle
     * @return void
     */
    public function notifyCurrentStageApprovers(int $rmbId, string $eventTitle = 'Permintaan Approval Reimbursement'): void
    {
        $rmb = DB::table('reimbursement')->where('ID', $rmbId)->first();
        if (!$rmb || empty($rmb->Approval_Request_ID)) {
            return;
        }

        $requestId = (int) $rmb->Approval_Request_ID;
        $apprReq = DB::table('approval_request')->where('ID', $requestId)->first();
        if (!$apprReq) {
            return;
        }

        // Ambil task pending saat ini
        $currentTask = DB::table('approval_request_task')
            ->where('Request_ID', $requestId)
            ->where('Status', 'PENDING')
            ->orderBy('Stage_Order', 'ASC')
            ->first();

        if (!$currentTask) {
            return;
        }

        // Dapatkan semua user ID approver / kandidat
        $candidateUserIds = [];
        if (!empty($currentTask->Claimed_By)) {
            $candidateUserIds[] = (int) $currentTask->Claimed_By;
        } else {
            $candidates = DB::table('approval_task_candidate')
                ->where('Task_ID', $currentTask->ID)
                ->where('Status', 'ACTIVE')
                ->pluck('User_Id')
                ->all();
            $candidateUserIds = array_merge($candidateUserIds, $candidates);
        }

        $candidateUserIds = array_values(array_unique(array_map('intval', $candidateUserIds)));
        if (empty($candidateUserIds)) {
            return;
        }

        $approverUsers = DB::table('users')
            ->whereIn('id', $candidateUserIds)
            ->where('Status', 'ACTIVE')
            ->get();

        $approvalUrl = url('hrd-reimbursement-approval-detail') . "?ID=" . $rmb->ID;

        foreach ($approverUsers as $apprUser) {
            $email = trim($apprUser->email ?? '');
            if (empty($email)) {
                continue;
            }

            $subject = "IMPORTANT: APPROVAL REIMBURSEMENT {$rmb->Reimbursement_No} [Stage {$currentTask->Stage_Order}]";
            $body = "<div style=\"font-family:Arial, sans-serif; font-size:13px; color:#333; line-height:1.6;\">
                <p>Kepada Yth.<br><b>" . htmlspecialchars($apprUser->Name ?: $apprUser->username) . "</b></p>
                <p>Terdapat permohonan <b>Klaim Reimbursement</b> yang memerlukan verifikasi dan persetujuan Anda:</p>
                <table style=\"border-collapse:collapse; width:100%; max-width:600px; margin:15px 0;\">
                    <tr>
                        <td style=\"padding:6px 10px; border:1px solid #ddd; background:#f8fafc; font-weight:bold; width:180px;\">No Reimbursement</td>
                        <td style=\"padding:6px 10px; border:1px solid #ddd;\">" . htmlspecialchars($rmb->Reimbursement_No) . "</td>
                    </tr>
                    <tr>
                        <td style=\"padding:6px 10px; border:1px solid #ddd; background:#f8fafc; font-weight:bold;\">Pemohon</td>
                        <td style=\"padding:6px 10px; border:1px solid #ddd;\">" . htmlspecialchars($rmb->User_Responsibility_Name) . " (" . htmlspecialchars($rmb->User_Responsibility_ID) . ")</td>
                    </tr>
                    <tr>
                        <td style=\"padding:6px 10px; border:1px solid #ddd; background:#f8fafc; font-weight:bold;\">Departemen</td>
                        <td style=\"padding:6px 10px; border:1px solid #ddd;\">" . htmlspecialchars($rmb->Department) . "</td>
                    </tr>
                    <tr>
                        <td style=\"padding:6px 10px; border:1px solid #ddd; background:#f8fafc; font-weight:bold;\">Total Nominal</td>
                        <td style=\"padding:6px 10px; border:1px solid #ddd; font-weight:bold; color:#2563eb;\">Rp " . number_format((float) $rmb->Total, 0, ',', '.') . "</td>
                    </tr>
                    <tr>
                        <td style=\"padding:6px 10px; border:1px solid #ddd; background:#f8fafc; font-weight:bold;\">Posisi Tahap Approval</td>
                        <td style=\"padding:6px 10px; border:1px solid #ddd;\">" . htmlspecialchars($currentTask->Label ?: ($currentTask->Position_Name ?: 'Tahap ' . $currentTask->Stage_Order)) . "</td>
                    </tr>
                    <tr>
                        <td style=\"padding:6px 10px; border:1px solid #ddd; background:#f8fafc; font-weight:bold;\">Keterangan / Keperluan</td>
                        <td style=\"padding:6px 10px; border:1px solid #ddd;\">" . nl2br(htmlspecialchars($rmb->Description)) . "</td>
                    </tr>
                </table>
                <p>Silakan klik tautan berikut untuk memproses persetujuan dokumen:</p>
                <p><a href=\"{$approvalUrl}\" style=\"display:inline-block; padding:8px 16px; background:#2563eb; color:#fff; text-decoration:none; border-radius:6px; font-weight:bold;\">Buka Halaman Approval</a></p>
                <br>
                <p style=\"font-size:12px; color:#777;\">Pemberitahuan otomatis dari Sistem ERP Green Energi Utama. Harap tidak membalas email ini.</p>
            </div>";

            DB::table('mail_logs')->insert([
                'Email_To'      => $email,
                'Subject'       => $subject,
                'Contents'      => $body,
                'Status'        => 'UNSENT',
                'Inserted_Date' => now(),
                'Send_Date'     => now(),
            ]);

            Log::info("Queued approval email for Reimbursement {$rmb->Reimbursement_No} to {$email} [Stage {$currentTask->Stage_Order}]");
        }
    }

    /**
     * Petakan status internal ApprovalWorkflowService ke status modul Reimbursement.
     */
    protected function mapWorkflowStatusToRmb(string $workflowStatus, $currentTask = null): string
    {
        switch (strtoupper($workflowStatus)) {
            case 'APPROVED':
            case 'COMPLETED':
                return 'APPROVED';

            case 'REJECTED':
                return 'REJECT';

            case 'IN_PROGRESS':
            case 'PENDING':
                if ($currentTask && isset($currentTask->Stage_Order)) {
                    if ((int) $currentTask->Stage_Order === 1) {
                        return 'APPROVAL_UPPER';
                    } elseif ((int) $currentTask->Stage_Order === 2) {
                        return 'APPROVAL_HCGA';
                    } elseif ((int) $currentTask->Stage_Order >= 3) {
                        return 'APPROVAL_VP';
                    }
                }
                return 'APPROVAL_UPPER';

            case 'CANCELLED':
                return 'CANCELED';

            default:
                return 'APPROVAL_UPPER';
        }
    }
}

