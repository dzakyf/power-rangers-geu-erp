<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use App\Services\Employee\EmployeeMobileService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeDataUpdateApprovalService
{
    const REQUEST_TYPE = 'EMPLOYEE_DATA_UPDATE';

    protected $workflow;
    protected $policyResolver;
    protected $employeeService;
    protected $mailService;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalPolicyResolver $policyResolver,
        EmployeeMobileService $employeeService,
        ApprovalMailService $mailService
    ) {
        $this->workflow = $workflow;
        $this->policyResolver = $policyResolver;
        $this->employeeService = $employeeService;
        $this->mailService = $mailService;
    }

    /**
     * Submit request update ke workflow engine & kirim notifikasi email ke HCGA
     */
    public function submit($updateRequest, $user)
    {
        $req = is_array($updateRequest) ? (object) $updateRequest : $updateRequest;
        $reqId = (int) $req->id;

        return DB::transaction(function () use ($req, $reqId, $user) {
            $context = [
                'Request_Type' => self::REQUEST_TYPE,
                'Location'     => $user->Location ?? 'GEU-HO',
                'Company_ID'   => $user->Company_ID ?? 'GEU',
                'Department'   => $user->Department ?? 'HCGA',
                'Amount'       => 0,
            ];

            $description = "Perubahan Data Mandiri Karyawan: {$req->field_name} (NIK: {$req->nik})";

            $approvalReq = null;
            try {
                $approvalReq = $this->workflow->submit([
                    'Request_No'  => $req->request_no,
                    'Description' => $description,
                ], $context, (int) $user->id);
            } catch (\Throwable $e) {
                // Fallback jika policy approval workflow engine belum disetting, status tetap WAITING_APPROVAL
                Log::warning('ApprovalWorkflow engine submit fallback: ' . $e->getMessage());
            }

            if ($approvalReq && !empty($approvalReq->ID)) {
                DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
                    'Map_To' => $reqId,
                ]);

                DB::table('employee_data_update_requests')->where('id', $reqId)->update([
                    'approval_request_id' => (int) $approvalReq->ID,
                    'status'              => 'WAITING_APPROVAL',
                    'updated_at'          => Carbon::now(),
                ]);
            }

            // Kirim notifikasi email ke Staf HCGA / Verifikator
            $this->notifyHcgaStaff($req, $user);

            return $approvalReq;
        });
    }

    /**
     * Notifikasi Email ke Tim HCGA / HRD
     */
    protected function notifyHcgaStaff($req, $user)
    {
        try {
            $hcgaEmails = DB::table('users')
                ->where(function ($q) {
                    $q->where('Department', 'LIKE', '%HCGA%')
                      ->orWhere('Department', 'LIKE', '%HRD%')
                      ->orWhere('Priv_ID', 'ADMIN')
                      ->orWhere('username', 'manager.hcga');
                })
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->toArray();

            if (!empty($hcgaEmails)) {
                $uniqueEmails = array_unique(array_filter($hcgaEmails));
                $subject = "[HCGA Alert] Permohonan Perubahan Data Mandiri: {$req->request_no}";
                $bodyHtml = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <h3 style='color: #1e3a8a;'>Permohonan Perubahan Data Mandiri Karyawan</h3>
                        <p>Terdapat permohonan pembaruan data karyawan yang memerlukan verifikasi tim HCGA:</p>
                        <table style='border-collapse: collapse; width: 100%; max-width: 500px; margin-bottom: 20px;'>
                            <tr><td style='padding: 6px; font-weight: bold; border-bottom: 1px solid #ddd;'>No. Tiket:</td><td style='padding: 6px; border-bottom: 1px solid #ddd;'>{$req->request_no}</td></tr>
                            <tr><td style='padding: 6px; font-weight: bold; border-bottom: 1px solid #ddd;'>NIK Karyawan:</td><td style='padding: 6px; border-bottom: 1px solid #ddd;'>{$req->nik}</td></tr>
                            <tr><td style='padding: 6px; font-weight: bold; border-bottom: 1px solid #ddd;'>Pemohon:</td><td style='padding: 6px; border-bottom: 1px solid #ddd;'>{$user->name} ({$user->username})</td></tr>
                            <tr><td style='padding: 6px; font-weight: bold; border-bottom: 1px solid #ddd;'>Kolom Diubah:</td><td style='padding: 6px; border-bottom: 1px solid #ddd; color: #2563eb;'>{$req->field_name} ({$req->field_category})</td></tr>
                            <tr><td style='padding: 6px; font-weight: bold; border-bottom: 1px solid #ddd;'>Nilai Lama:</td><td style='padding: 6px; border-bottom: 1px solid #ddd;'><del style='color: #dc2626;'>{$req->old_value}</del></td></tr>
                            <tr><td style='padding: 6px; font-weight: bold; border-bottom: 1px solid #ddd;'>Nilai Baru:</td><td style='padding: 6px; border-bottom: 1px solid #ddd; font-weight: bold; color: #16a34a;'>{$req->new_value}</td></tr>
                            <tr><td style='padding: 6px; font-weight: bold; border-bottom: 1px solid #ddd;'>Alasan:</td><td style='padding: 6px; border-bottom: 1px solid #ddd;'>{$req->reason}</td></tr>
                        </table>
                        <p><a href='" . url('employee/directory') . "' style='background: #1e3a8a; color: #fff; padding: 10px 18px; text-decoration: none; border-radius: 6px; display: inline-block;'>Buka Portal HCGA Mobile</a></p>
                    </div>
                ";

                foreach ($uniqueEmails as $email) {
                    // Masukkan ke antrean email lewat DB helper email log atau ApprovalMailService
                    DB::table('email_logs')->insert([
                        'to'         => $email,
                        'subject'    => $subject,
                        'body'       => $bodyHtml,
                        'status'     => 'QUEUED',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal antre notifikasi email HCGA: ' . $e->getMessage());
        }
    }

    /**
     * Sinkronkan status approval_request ke employee_data_update_requests dan tabel employment.
     */
    public function syncApprovalStatus(int $reqId, string $actor = 'system'): void
    {
        $req = DB::table('employee_data_update_requests')->where('id', $reqId)->first();
        if (!$req || empty($req->approval_request_id)) {
            return;
        }

        if ($req->status !== 'WAITING_APPROVAL') {
            return;
        }

        $approvalReq = $this->workflow->getRequest((int) $req->approval_request_id);
        if (!$approvalReq) {
            return;
        }

        $statusUpper = strtoupper((string) $approvalReq->Status);
        if (!in_array($statusUpper, ['APPROVED', 'COMPLETED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return;
        }

        $lastTask = DB::table('approval_request_task')
            ->where('Request_ID', (int) $approvalReq->ID)
            ->whereIn('Status', ['APPROVED', 'AUTO_APPROVED', 'REJECTED'])
            ->orderBy('ID', 'DESC')
            ->first();

        $approvedBy = $actor;
        $note = '';
        if ($lastTask) {
            $note = (string) ($lastTask->Notes ?? '');
            if (!empty($lastTask->Acted_By)) {
                $actorUser = DB::table('users')->where('id', (int) $lastTask->Acted_By)->first();
                if ($actorUser) {
                    $approvedBy = $actorUser->username ?? $actorUser->name ?? $actor;
                }
            }
        }

        DB::transaction(function () use ($req, $reqId, $statusUpper, $approvedBy, $note) {
            if (in_array($statusUpper, ['APPROVED', 'COMPLETED'], true)) {
                $this->applyEmploymentColumnChange($req);
                $targetStatus = 'APPROVED';
            } elseif ($statusUpper === 'CANCELLED') {
                $targetStatus = 'CANCELLED';
            } else {
                $targetStatus = 'REJECTED';
            }

            DB::table('employee_data_update_requests')->where('id', $reqId)->update([
                'status'        => $targetStatus,
                'approved_by'   => $approvedBy,
                'approved_at'   => Carbon::now(),
                'approval_note' => $note,
                'updated_at'    => Carbon::now(),
            ]);
        });
    }

    /**
     * Terapkan perubahan kolom yang disetujui ke tabel employment.
     */
    protected function applyEmploymentColumnChange($req): void
    {
        $column = $req->field_name;
        $allowedEmploymentCols = [
            'Handphone', 'Phone', 'Email_Individual', 'Address', 'City', 'Province',
            'District', 'Sub_District', 'ZipCode', 'Address_Living', 'City_Living',
            'Province_Living', 'District_Living', 'Sub_District_Living', 'ZipCode_Living',
            'Blood', 'Blood_Rhesus', 'Marital_Status', 'PTKP_Status', 'Payment_Reference',
            'Payment_Reference2', 'Payment_Reference3', 'Payment_Method', 'NPWP',
        ];

        if (in_array($column, $allowedEmploymentCols, true)) {
            DB::table('employment')->where('Task_ID', $req->task_id)->update([
                $column => $req->new_value,
            ]);
        }
    }

    /**
     * Proses aksi melalui ApprovalWorkflowService jika request terhubung ke engine.
     */
    protected function actThroughWorkflowIfLinked($req, $user, string $action, string $note = ''): bool
    {
        if (empty($req->approval_request_id)) {
            return false;
        }

        $approvalReq = $this->workflow->getRequest((int) $req->approval_request_id);
        if (!$approvalReq || in_array(strtoupper((string) $approvalReq->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return false;
        }

        $userId = (int) ($user->id ?? 0);
        if ($userId < 1) {
            throw new \Exception('User approver tidak valid.');
        }

        $task = DB::table('approval_request_task as t')
            ->leftJoin('approval_task_candidate as c', function ($join) use ($userId) {
                $join->on('c.Task_ID', '=', 't.ID')
                     ->where('c.User_Id', '=', $userId)
                     ->where('c.Status', '=', 'ACTIVE');
            })
            ->where('t.Request_ID', (int) $approvalReq->ID)
            ->where('t.Stage_Order', (int) $approvalReq->Current_Stage)
            ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
            ->where(function ($q) use ($userId) {
                $q->where('t.Claimed_By', $userId)
                  ->orWhereNotNull('c.ID');
            })
            ->select('t.*')
            ->orderBy('t.ID', 'ASC')
            ->first();

        if (!$task && app(ApprovalAuthorizationService::class)->isWildcardAdmin($user)) {
            $task = DB::table('approval_request_task')
                ->where('Request_ID', (int) $approvalReq->ID)
                ->where('Stage_Order', (int) $approvalReq->Current_Stage)
                ->whereIn('Status', ['PENDING', 'CLAIMED'])
                ->orderBy('ID', 'ASC')
                ->first();
        }

        if (!$task) {
            throw new ApprovalWorkflowException('Anda tidak memiliki task approval aktif untuk permohonan ini.', 'TASK_FORBIDDEN', 403);
        }

        if (strtoupper((string) $task->Status) === 'PENDING') {
            $this->workflow->claim((int) $task->ID, $userId);
        }

        $this->workflow->act((int) $task->ID, $userId, $action, $note);

        return true;
    }

    /**
     * Approve perubahan data & terapkan (sync) langsung ke tabel employment
     */
    public function approve($reqId, $user, $note = '')
    {
        $req = DB::table('employee_data_update_requests')->where('id', $reqId)->first();
        if (!$req) {
            throw new \Exception('Data permohonan tidak ditemukan.');
        }

        if ($req->status !== 'WAITING_APPROVAL') {
            throw new \Exception("Permohonan ini telah berstatus {$req->status}.");
        }

        if ($this->actThroughWorkflowIfLinked($req, $user, 'APPROVE', (string) $note)) {
            return true;
        }

        return DB::transaction(function () use ($req, $reqId, $user, $note) {
            $this->applyEmploymentColumnChange($req);

            DB::table('employee_data_update_requests')->where('id', $reqId)->update([
                'status'        => 'APPROVED',
                'approved_by'   => $user->username ?? $user->name,
                'approved_at'   => Carbon::now(),
                'approval_note' => $note,
                'updated_at'    => Carbon::now(),
            ]);

            return true;
        });
    }

    /**
     * Tolak permohonan perubahan data
     */
    public function reject($reqId, $user, $note = '')
    {
        $req = DB::table('employee_data_update_requests')->where('id', $reqId)->first();
        if (!$req) {
            throw new \Exception('Data permohonan tidak ditemukan.');
        }

        if ($req->status !== 'WAITING_APPROVAL') {
            throw new \Exception("Permohonan ini telah berstatus {$req->status}.");
        }

        if ($this->actThroughWorkflowIfLinked($req, $user, 'REJECT', (string) $note)) {
            return true;
        }

        return DB::transaction(function () use ($reqId, $user, $note) {
            DB::table('employee_data_update_requests')->where('id', $reqId)->update([
                'status'        => 'REJECTED',
                'approved_by'   => $user->username ?? $user->name,
                'approved_at'   => Carbon::now(),
                'approval_note' => $note,
                'updated_at'    => Carbon::now(),
            ]);

            return true;
        });
    }
}
