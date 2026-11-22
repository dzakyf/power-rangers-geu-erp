<?php

namespace App\Services\Payroll;

use App\Exceptions\ApprovalWorkflowException;
use App\Services\Approval\ApprovalAuthorizationService;
use App\Services\Approval\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Service Adapter Persetujuan Batch THR Mandiri (PAYROLL_THR)
 * Terintegrasi penuh dengan Dynamic Approval Engine (ApprovalWorkflowService).
 * Tidak menyentuh/mengunci presensi maupun tabel payroll reguler (salary_main/salary).
 */
class PayrollThrApprovalService
{
    public const REQUEST_TYPE = 'PAYROLL_THR';

    /**
     * @var ApprovalWorkflowService
     */
    protected $workflow;

    /**
     * @var ApprovalAuthorizationService
     */
    protected $authorization;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalAuthorizationService $authorization
    ) {
        $this->workflow = $workflow;
        $this->authorization = $authorization;
    }

    /**
     * Pastikan policy default PAYROLL_THR tersedia dan aktif di tabel approval_policy.
     */
    public function ensureDefaultThrPolicy(): void
    {
        if (!Schema::hasTable('approval_policy') || !Schema::hasTable('approval_policy_step')) {
            return;
        }

        $policy = DB::table('approval_policy')
            ->where('Request_Type', self::REQUEST_TYPE)
            ->where('Status', 'ACTIVE')
            ->first();

        if ($policy) {
            DB::table('approval_policy_step')
                ->where('Policy_ID', $policy->ID)
                ->whereNotIn('Approval_Mode', ['ALL', 'ANY', 'QUORUM'])
                ->update(['Approval_Mode' => 'ALL', 'Quorum' => null]);
            return;
        }

        if (!class_exists('ApprovalPayrollThrPolicySeeder') && file_exists(database_path('seeds/ApprovalPayrollThrPolicySeeder.php'))) {
            require_once database_path('seeds/ApprovalPayrollThrPolicySeeder.php');
        }
        if (class_exists('ApprovalPayrollThrPolicySeeder')) {
            (new \ApprovalPayrollThrPolicySeeder())->run();
        }
    }

    /**
     * Ajukan batch THR berstatus DRAFT ke Dynamic Approval Engine (PAYROLL_THR).
     *
     * @param int $batchId
     * @param object|null $user
     * @return array
     */
    public function submitBatchForApproval(int $batchId, $user = null): array
    {
        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            throw new \InvalidArgumentException('Batch THR tidak ditemukan.');
        }

        if (strtoupper((string)$batch->status) !== 'DRAFT') {
            throw new \RuntimeException('Hanya batch berstatus DRAFT yang dapat diajukan ke approval.');
        }

        if ((int)$batch->total_employees <= 0 || (float)$batch->total_amount <= 0) {
            throw new \RuntimeException('Batch THR harus memiliki minimal 1 karyawan eligible dengan nominal THR > 0.');
        }

        $this->ensureDefaultThrPolicy();

        $user = $user ?: Auth::user();
        $requesterId = $user && !empty($user->id) ? (int)$user->id : (int)(Auth::id() ?: 1);
        $username = $user && !empty($user->username) ? $user->username : 'SYSTEM';

        $loc = $user && !empty($user->Location) ? trim((string)$user->Location) : 'GEU-HO';
        $compId = 'GEU';
        if (Schema::hasTable('master_position')) {
            $hasLoc = DB::table('master_position')->where('Location', $loc)->exists();
            if (!$hasLoc) {
                $defaultPos = DB::table('master_position')
                    ->where(function ($q) {
                        $q->whereNull('Status')->orWhere('Status', 'ACTIVE');
                    })
                    ->whereNotNull('Location')
                    ->where('Location', '<>', '')
                    ->first();
                if ($defaultPos) {
                    $loc = $defaultPos->Location;
                    $compId = $defaultPos->Company_ID ?: 'GEU';
                }
            } else {
                $posComp = DB::table('master_position')->where('Location', $loc)->value('Company_ID');
                if (!empty($posComp)) {
                    $compId = $posComp;
                }
            }
        }

        $effectiveAmount = (float)($batch->total_net_amount > 0 ? $batch->total_net_amount : $batch->total_amount);
        $taxModeLabel = strtoupper((string)($batch->tax_mode ?? 'NONE')) === 'TER_STANDALONE'
            ? 'TER PP 58/2023'
            : 'Tanpa Potongan PPh 21';

        $context = [
            'Request_Type' => self::REQUEST_TYPE,
            'Department'   => 'HCGA',
            'Location'     => $loc,
            'Company_ID'   => $compId,
            'Amount'       => $effectiveAmount,
        ];

        $data = [
            'Request_No'  => $batch->batch_no,
            'Description' => "Batch THR {$batch->thr_event_name} Periode {$batch->salary_period} ({$batch->total_employees} Karyawan, Mode Pajak: {$taxModeLabel})",
            'Amount'      => $effectiveAmount,
        ];

        try {
            $apprReq = $this->workflow->submit($data, $context, $requesterId);
        } catch (\Throwable $e) {
            $context['Location'] = null;
            $context['Department'] = null;
            $apprReq = $this->workflow->submit($data, $context, $requesterId);
        }

        if (!$apprReq || empty($apprReq->ID)) {
            throw new \RuntimeException('Gagal membuat dokumen request approval THR di ApprovalWorkflowService.');
        }

        $requestId = (int)$apprReq->ID;
        $now = Carbon::now()->toDateTimeString();

        DB::table('approval_request')->where('ID', $requestId)->update([
            'Map_To' => (int)$batch->id,
        ]);

        $updateBatch = [
            'approval_request_id' => $requestId,
            'updated_at'          => $now,
        ];
        if (Schema::hasColumn('payroll_thr_batches', 'submitted_by')) {
            $updateBatch['submitted_by'] = $username;
            $updateBatch['submitted_at'] = $now;
            $updateBatch['rejected_by'] = null;
            $updateBatch['rejected_at'] = null;
            $updateBatch['rejection_reason'] = null;
        }

        DB::table('payroll_thr_batches')->where('id', $batchId)->update($updateBatch);

        $synced = $this->syncThrBatchStatus($batchId, $username);

        return [
            'success'             => true,
            'approval_request_id' => $requestId,
            'status'              => $synced['status'],
            'next_step_label'     => $synced['next_step_label'],
            'message'             => $synced['is_final_approved']
                ? "Batch THR {$batch->batch_no} disetujui otomatis (APPROVED)."
                : "Batch THR {$batch->batch_no} berhasil diajukan ke Approval ({$synced['next_step_label']}).",
        ];
    }

    /**
     * Ambil daftar batch THR yang sedang menunggu approval beserta ringkasan tahapannya.
     *
     * @param object|null $currentUser
     * @return array
     */
    public function getPendingBatchesWithApproval($currentUser = null): array
    {
        $this->ensureDefaultThrPolicy();

        $rows = DB::table('payroll_thr_batches')
            ->whereNotIn('status', ['DRAFT', 'APPROVED', 'REJECTED', 'CANCELLED', 'CANCELED', 'LOCKED', 'POSTED'])
            ->orderBy('id', 'DESC')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $summary = $this->buildApprovalSummaryForRow($row, $currentUser);
            $result[] = [
                'batch'    => $row,
                'approval' => $summary,
            ];
        }

        return $result;
    }

    /**
     * Susun ringkasan tahapan approval dinamis untuk satu baris payroll_thr_batches.
     *
     * @param object $batch
     * @param object|null $currentUser
     * @return array
     */
    public function buildApprovalSummaryForRow($batch, $currentUser = null): array
    {
        $userId = $currentUser && !empty($currentUser->id) ? (int)$currentUser->id : (int)(Auth::id() ?: 0);
        $isAdmin = $currentUser ? $this->authorization->isWildcardAdmin($currentUser) : false;

        $default = [
            'request_id'         => null,
            'policy_code'        => 'PAYROLL-THR-STANDARD-POLICY',
            'policy_name'        => 'Approval Batch THR',
            'request_status'     => $batch->status,
            'current_stage'      => 1,
            'total_stages'       => 1,
            'current_step_label' => $batch->status,
            'current_approvers'  => '-',
            'display_status'     => $batch->status,
            'can_approve'        => $isAdmin,
            'active_task'        => null,
            'tasks'              => [],
        ];

        if (empty($batch->approval_request_id)) {
            return $default;
        }

        $requestId = (int)$batch->approval_request_id;
        $request = DB::table('approval_request')->where('ID', $requestId)->first();
        if (!$request) {
            return $default;
        }

        $policy = !empty($request->Policy_ID)
            ? DB::table('approval_policy')->where('ID', (int)$request->Policy_ID)->first()
            : null;

        $snapshot = !empty($request->Policy_Snapshot) ? json_decode($request->Policy_Snapshot, true) : [];
        $snapshotSteps = isset($snapshot['Steps']) && is_array($snapshot['Steps']) ? $snapshot['Steps'] : [];
        $stageNumbers = [];
        foreach ($snapshotSteps as $st) {
            $stageNumbers[] = (int)($st['Stage_Order'] ?? 1);
        }

        $tasks = DB::table('approval_request_task as t')
            ->leftJoin('users as cu', 'cu.id', '=', 't.Claimed_By')
            ->leftJoin('employment as ce', 'ce.NIK', '=', 'cu.Emp_ID')
            ->leftJoin('users as au', 'au.id', '=', 't.Acted_By')
            ->leftJoin('employment as ae', 'ae.NIK', '=', 'au.Emp_ID')
            ->where('t.Request_ID', $requestId)
            ->select(
                't.*',
                'cu.Name as Claimed_User_Name',
                'cu.username as Claimed_Username',
                'ce.Name as Claimed_Emp_Name',
                'au.Name as Acted_User_Name',
                'au.username as Acted_Username',
                'ae.Name as Acted_Emp_Name'
            )
            ->orderBy('t.Stage_Order', 'ASC')
            ->orderBy('t.ID', 'ASC')
            ->get();

        foreach ($tasks as $t) {
            $stageNumbers[] = (int)$t->Stage_Order;
        }
        $totalStages = !empty($stageNumbers) ? count(array_unique($stageNumbers)) : 1;
        $currentStage = max(1, (int)$request->Current_Stage);

        $activeTask = null;
        $canApprove = false;
        $currentStepLabel = '';
        $currentApproversList = [];
        $enrichedTasks = [];

        foreach ($tasks as $t) {
            $tSnap = !empty($t->Snapshot) ? json_decode($t->Snapshot, true) : [];
            $stepLabel = $tSnap['Step']['Label'] ?? ($t->Position_Name ?: ('Stage ' . $t->Stage_Order));
            $t->Step_Label = $stepLabel;

            $t->Claimed_Display = !empty($t->Claimed_By)
                ? (trim((string)($t->Claimed_Emp_Name ?: $t->Claimed_User_Name)) ?: $t->Claimed_Username)
                : null;

            $t->Acted_Display = !empty($t->Acted_By)
                ? (trim((string)($t->Acted_Emp_Name ?: $t->Acted_User_Name)) ?: $t->Acted_Username)
                : null;

            $candidates = DB::table('approval_task_candidate as c')
                ->join('users as u', 'u.id', '=', 'c.User_Id')
                ->leftJoin('employment as e', 'e.NIK', '=', 'u.Emp_ID')
                ->where('c.Task_ID', $t->ID)
                ->where('c.Status', 'ACTIVE')
                ->select('c.User_Id', 'c.Candidate_Type', 'u.username', 'u.Name as User_Name', 'e.Name as Emp_Name')
                ->orderBy('c.ID', 'ASC')
                ->get();

            $candNames = [];
            $isUserCandidate = false;
            foreach ($candidates as $cand) {
                $disp = trim((string)($cand->Emp_Name ?: $cand->User_Name)) ?: $cand->username;
                $candNames[] = $disp;
                if ($userId > 0 && (int)$cand->User_Id === $userId) {
                    $isUserCandidate = true;
                }
            }
            $t->Candidate_Names = array_values(array_unique($candNames));
            $t->Is_User_Candidate = $isUserCandidate;

            if ((int)$t->Stage_Order === $currentStage && in_array(strtoupper((string)$t->Status), ['PENDING', 'CLAIMED', 'HELD'], true)) {
                if (!$activeTask) {
                    $activeTask = $t;
                }
                $currentStepLabel = "Stage {$currentStage}/{$totalStages}: {$stepLabel}" . ($t->Position_Code ? " ({$t->Position_Code})" : '');
                if (!empty($t->Claimed_Display)) {
                    $currentApproversList[] = $t->Claimed_Display . ' (Claimed)';
                } elseif (!empty($t->Candidate_Names)) {
                    $currentApproversList = array_merge($currentApproversList, $t->Candidate_Names);
                } elseif (!empty($t->Position_Name)) {
                    $currentApproversList[] = $t->Position_Name;
                }

                if ($isUserCandidate || $isAdmin) {
                    $canApprove = true;
                }
            }

            $enrichedTasks[] = $t;
        }

        $existingStages = array_map(function ($t) {
            return (int)$t->Stage_Order;
        }, $enrichedTasks);

        foreach ($snapshotSteps as $sStep) {
            $sOrder = (int)($sStep['Stage_Order'] ?? 0);
            if ($sOrder > 0 && !in_array($sOrder, $existingStages, true)) {
                $posName = $sStep['Position_Code'] ?? ('Level ' . ($sStep['Level_Number'] ?? ''));
                if (!empty($sStep['Position_Code'])) {
                    $mp = DB::table('master_position')->where('Initial', $sStep['Position_Code'])->first();
                    if ($mp) {
                        $posName = $mp->Position . ' (' . $mp->Initial . ')';
                    }
                }
                $enrichedTasks[] = (object)[
                    'ID'                => null,
                    'Stage_Order'       => $sOrder,
                    'Level_Number'      => $sStep['Level_Number'] ?? null,
                    'Position_Code'     => $sStep['Position_Code'] ?? null,
                    'Position_Name'     => $posName,
                    'Department'        => $sStep['Department'] ?? null,
                    'Status'            => 'UPCOMING',
                    'Step_Label'        => $sStep['Label'] ?? $posName,
                    'Claimed_Display'   => null,
                    'Acted_Display'     => null,
                    'Acted_At'          => null,
                    'Notes'             => null,
                    'Candidate_Names'   => [],
                    'Is_User_Candidate' => false,
                ];
            }
        }

        $reqStatusUpper = strtoupper((string)$request->Status);
        if (in_array($reqStatusUpper, ['APPROVED', 'COMPLETED'], true)) {
            $displayStatus = 'APPROVED';
            $currentStepLabel = "Selesai ({$totalStages}/{$totalStages} Stage Approved)";
        } elseif (in_array($reqStatusUpper, ['REJECTED', 'RETURNED'], true)) {
            $displayStatus = 'REJECTED';
        } elseif ($reqStatusUpper === 'CANCELLED') {
            $displayStatus = 'CANCELLED';
        } else {
            $posBadge = $activeTask && !empty($activeTask->Position_Code)
                ? $activeTask->Position_Code
                : ('STAGE_' . $currentStage);
            $displayStatus = "STAGE {$currentStage}/{$totalStages}: {$posBadge}";
            if ($currentStepLabel === '') {
                $currentStepLabel = "Stage {$currentStage}/{$totalStages}";
            }
        }

        return [
            'request_id'         => $requestId,
            'policy_id'          => $policy ? (int)$policy->ID : null,
            'policy_code'        => $policy ? $policy->Policy_Code : ($snapshot['Policy']['Policy_Code'] ?? 'PAYROLL-THR-STANDARD-POLICY'),
            'policy_name'        => $policy ? $policy->Policy_Name : ($snapshot['Policy']['Policy_Name'] ?? 'Dynamic Payroll THR Policy'),
            'request_status'     => $reqStatusUpper,
            'current_stage'      => $currentStage,
            'total_stages'       => $totalStages,
            'current_step_label' => $currentStepLabel,
            'current_approvers'  => !empty($currentApproversList) ? implode(', ', array_unique($currentApproversList)) : '-',
            'display_status'     => $displayStatus,
            'can_approve'        => $canApprove,
            'active_task'        => $activeTask,
            'tasks'              => $enrichedTasks,
        ];
    }

    /**
     * Ambil detail lengkap batch THR & riwayat approval untuk halaman detail.
     *
     * @param int $batchId
     * @param object|null $currentUser
     * @return array
     */
    public function getBatchApprovalDetails(int $batchId, $currentUser = null): array
    {
        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            return [
                'batch'    => null,
                'approval' => null,
                'logs'     => [],
            ];
        }

        $summary = $this->buildApprovalSummaryForRow($batch, $currentUser);

        $logs = [];
        if (!empty($summary['request_id'])) {
            $logs = DB::table('approval_log as l')
                ->leftJoin('users as u', 'u.id', '=', 'l.Approver_Id')
                ->leftJoin('employment as e', 'e.NIK', '=', 'u.Emp_ID')
                ->where('l.Request_Id', (int)$summary['request_id'])
                ->select('l.*', 'u.username', 'u.Name as User_Name', 'e.Name as Emp_Name')
                ->orderBy('l.ID', 'DESC')
                ->get();
        }

        return [
            'batch'    => $batch,
            'approval' => $summary,
            'logs'     => $logs,
        ];
    }

    /**
     * Eksekusi Approve tahap aktif Batch THR.
     *
     * @param int $batchId
     * @param object|null $user
     * @param string $notes
     * @return array
     */
    public function approveBatch(int $batchId, $user = null, string $notes = ''): array
    {
        $user = $user ?: Auth::user();
        $userId = $user && !empty($user->id) ? (int)$user->id : 1;
        $username = $user && !empty($user->username) ? $user->username : 'SYSTEM';

        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            return [
                'success' => false,
                'message' => 'Batch THR tidak ditemukan.',
                'status'  => 'NOT_FOUND',
            ];
        }

        if (strtoupper((string)$batch->status) === 'APPROVED') {
            return [
                'success' => true,
                'message' => "Batch THR {$batch->batch_no} sudah berstatus APPROVED.",
                'status'  => 'APPROVED',
            ];
        }

        if (empty($batch->approval_request_id)) {
            return [
                'success' => false,
                'message' => "Batch THR {$batch->batch_no} belum diajukan ke workflow approval.",
                'status'  => $batch->status,
            ];
        }

        $request = DB::table('approval_request')->where('ID', (int)$batch->approval_request_id)->first();
        if (!$request) {
            return [
                'success' => false,
                'message' => 'Dokumen approval_request untuk batch THR tidak ditemukan.',
                'status'  => $batch->status,
            ];
        }

        $summaryBefore = $this->buildApprovalSummaryForRow($batch, $user);
        $activeTask = $summaryBefore['active_task'];

        if (!$activeTask || empty($activeTask->ID)) {
            $synced = $this->syncThrBatchStatus($batchId, $username);
            return [
                'success' => true,
                'message' => "Status batch THR {$batch->batch_no} disinkronkan menjadi {$synced['status']}.",
                'status'  => $synced['status'],
            ];
        }

        $activeTaskId = (int)$activeTask->ID;
        $actedStageLabel = $activeTask->Step_Label ?? ('Stage ' . $activeTask->Stage_Order);

        try {
            $this->prepareCandidateAndClaim($request, $activeTask, $user, $userId);

            $approvalNotes = trim($notes) !== ''
                ? trim($notes)
                : "Approved {$actedStageLabel} via Payroll THR Approval oleh {$username}";

            $this->workflow->act($activeTaskId, $userId, 'APPROVE', $approvalNotes);

            $synced = $this->syncThrBatchStatus($batchId, $username);

            if ($synced['is_final_approved']) {
                $msg = "Batch THR {$batch->batch_no} telah disetujui sepenuhnya (FINAL APPROVED). Payslip THR Final kini dapat diterbitkan dan di-blast via email.";
            } else {
                $msg = "Tahap [{$actedStageLabel}] untuk Batch THR {$batch->batch_no} berhasil disetujui. Berlanjut ke: {$synced['next_step_label']}.";
            }

            return [
                'success' => true,
                'message' => $msg,
                'status'  => $synced['status'],
            ];
        } catch (ApprovalWorkflowException $ex) {
            return [
                'success' => false,
                'message' => 'Approval ditolak oleh sistem: ' . $ex->getMessage(),
                'status'  => $batch->status,
            ];
        } catch (\Throwable $e) {
            Log::error("PayrollThrApprovalService::approveBatch error [{$batchId}]: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memproses approval THR: ' . $e->getMessage(),
                'status'  => $batch->status,
            ];
        }
    }

    /**
     * Tolak (Reject) atau Batalkan (Cancel) Batch THR.
     *
     * @param int $batchId
     * @param object|null $user
     * @param string $mode 'REJECT' | 'CANCEL'
     * @param string $reason
     * @return array
     */
    public function rejectOrCancelBatch(int $batchId, $user = null, string $mode = 'REJECT', string $reason = ''): array
    {
        $user = $user ?: Auth::user();
        $userId = $user && !empty($user->id) ? (int)$user->id : 1;
        $username = $user && !empty($user->username) ? $user->username : 'SYSTEM';
        $mode = strtoupper(trim($mode)) === 'REJECT' ? 'REJECT' : 'CANCEL';

        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            return [
                'success' => false,
                'message' => 'Batch THR tidak ditemukan.',
                'status'  => 'NOT_FOUND',
            ];
        }

        $reasonText = trim($reason) !== ''
            ? trim($reason)
            : ($mode === 'REJECT' ? "Ditolak oleh {$username}" : "Dibatalkan oleh {$username}");

        try {
            if (!empty($batch->approval_request_id)) {
                $reqId = (int)$batch->approval_request_id;
                $req = DB::table('approval_request')->where('ID', $reqId)->first();

                if ($req && !in_array(strtoupper((string)$req->Status), ['APPROVED', 'REJECTED', 'CANCELLED'], true)) {
                    if ($mode === 'REJECT') {
                        $summary = $this->buildApprovalSummaryForRow($batch, $user);
                        $activeTask = $summary['active_task'];
                        if ($activeTask && !empty($activeTask->ID)) {
                            $this->prepareCandidateAndClaim($req, $activeTask, $user, $userId);
                            $this->workflow->act((int)$activeTask->ID, $userId, 'REJECT', $reasonText);
                        } else {
                            $this->workflow->cancel($reqId, $userId, $reasonText);
                        }
                    } else {
                        $this->workflow->cancel($reqId, $userId, $reasonText);
                    }
                }
            }

            $finalStatus = ($mode === 'REJECT') ? 'REJECTED' : 'CANCELLED';
            $now = Carbon::now()->toDateTimeString();
            $update = [
                'status'     => $finalStatus,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('payroll_thr_batches', 'rejected_by')) {
                $update['rejected_by'] = $username;
                $update['rejected_at'] = $now;
                $update['rejection_reason'] = $reasonText;
            }

            DB::table('payroll_thr_batches')->where('id', $batchId)->update($update);

            return [
                'success' => true,
                'message' => "Batch THR {$batch->batch_no} telah di-" . strtolower($finalStatus) . '.',
                'status'  => $finalStatus,
            ];
        } catch (\Throwable $e) {
            Log::error("PayrollThrApprovalService::rejectOrCancelBatch error [{$batchId}]: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menolak/membatalkan batch THR: ' . $e->getMessage(),
                'status'  => $batch->status,
            ];
        }
    }

    /**
     * Sinkronkan status payroll_thr_batches berdasarkan status terkini di approval_request.
     *
     * @param int $batchId
     * @param string $actorUsername
     * @return array
     */
    public function syncThrBatchStatus(int $batchId, string $actorUsername = 'SYSTEM'): array
    {
        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch || empty($batch->approval_request_id)) {
            return [
                'status'            => $batch ? $batch->status : 'UNKNOWN',
                'is_final_approved' => $batch ? (strtoupper((string)$batch->status) === 'APPROVED') : false,
                'next_step_label'   => '-',
            ];
        }

        $req = DB::table('approval_request')->where('ID', (int)$batch->approval_request_id)->first();
        if (!$req) {
            return [
                'status'            => $batch->status,
                'is_final_approved' => false,
                'next_step_label'   => '-',
            ];
        }

        $reqStatus = strtoupper(trim((string)$req->Status));
        $now = Carbon::now()->toDateTimeString();

        if (in_array($reqStatus, ['APPROVED', 'COMPLETED'], true)) {
            $update = [
                'status'     => 'APPROVED',
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('payroll_thr_batches', 'approved_by')) {
                $update['approved_by'] = $actorUsername;
                $update['approved_at'] = $now;
            }
            DB::table('payroll_thr_batches')->where('id', $batchId)->update($update);

            return [
                'status'            => 'APPROVED',
                'is_final_approved' => true,
                'next_step_label'   => 'APPROVED',
            ];
        }

        if (in_array($reqStatus, ['REJECTED', 'RETURNED'], true)) {
            $lastLog = DB::table('approval_log')
                ->where('Request_Id', (int)$req->ID)
                ->whereIn('Action', ['REJECT', 'REJECTED', 'RETURN', 'RETURNED'])
                ->orderBy('ID', 'DESC')
                ->first();

            $update = [
                'status'     => 'REJECTED',
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('payroll_thr_batches', 'rejected_by')) {
                $update['rejected_by'] = $actorUsername;
                $update['rejected_at'] = $now;
                $update['rejection_reason'] = $lastLog && !empty($lastLog->Note)
                    ? $lastLog->Note
                    : 'Ditolak pada proses approval.';
            }
            DB::table('payroll_thr_batches')->where('id', $batchId)->update($update);

            return [
                'status'            => 'REJECTED',
                'is_final_approved' => false,
                'next_step_label'   => 'REJECTED',
            ];
        }

        if ($reqStatus === 'CANCELLED') {
            DB::table('payroll_thr_batches')->where('id', $batchId)->update([
                'status'     => 'CANCELLED',
                'updated_at' => $now,
            ]);

            return [
                'status'            => 'CANCELLED',
                'is_final_approved' => false,
                'next_step_label'   => 'CANCELLED',
            ];
        }

        $summary = $this->buildApprovalSummaryForRow($batch);
        $activeTask = $summary['active_task'];
        $stageNum = (int)$summary['current_stage'];
        $posCode = $activeTask && !empty($activeTask->Position_Code) ? $activeTask->Position_Code : ('S' . $stageNum);
        $dynamicStatus = substr("STAGE_{$stageNum}_{$posCode}", 0, 40);

        DB::table('payroll_thr_batches')->where('id', $batchId)->update([
            'status'     => $dynamicStatus,
            'updated_at' => $now,
        ]);

        return [
            'status'            => $dynamicStatus,
            'is_final_approved' => false,
            'next_step_label'   => $summary['current_step_label'],
        ];
    }

    /**
     * Siapkan kandidat & claim task pada ApprovalWorkflowService sebelum eksekusi act().
     */
    protected function prepareCandidateAndClaim($request, $activeTask, $user, int $userId): void
    {
        $taskId = (int)$activeTask->ID;
        $isAdmin = $user ? $this->authorization->isWildcardAdmin($user) : false;

        $candidate = DB::table('approval_task_candidate')
            ->where('Task_ID', $taskId)
            ->where('User_Id', $userId)
            ->where('Status', 'ACTIVE')
            ->first();

        if (!$candidate) {
            if (!$isAdmin) {
                $posLabel = $activeTask->Position_Name ?: ($activeTask->Position_Code ?: 'Approver');
                throw new ApprovalWorkflowException(
                    "Anda bukan kandidat approver untuk tahap [{$activeTask->Step_Label} - {$posLabel}].",
                    'NOT_A_CANDIDATE',
                    403
                );
            }

            DB::table('approval_task_candidate')->insert([
                'Task_ID'        => $taskId,
                'User_Id'        => $userId,
                'Position_ID'    => (int)($activeTask->Position_ID ?? 0),
                'Candidate_Type' => 'ADMIN',
                'Status'         => 'ACTIVE',
                'Created_At'     => Carbon::now(),
            ]);
        }

        if ((int)$request->Requester_Id === $userId) {
            $hasException = DB::table('approval_self_exception')
                ->where('Request_ID', (int)$request->ID)
                ->where('User_Id', $userId)
                ->whereNull('Revoked_At')
                ->where(function ($q) use ($taskId) {
                    $q->whereNull('Task_ID')->orWhere('Task_ID', $taskId);
                })
                ->exists();

            if (!$hasException && $isAdmin) {
                DB::table('approval_self_exception')->insert([
                    'Request_ID'      => (int)$request->ID,
                    'Task_ID'         => null,
                    'User_Id'         => $userId,
                    'Granted_By'      => $userId,
                    'Permission_Used' => 'approval.self-approval.grant',
                    'Reason'          => 'Admin Payroll THR Approval',
                    'Expires_At'      => null,
                    'Created_At'      => Carbon::now(),
                ]);
            }
        }

        $freshTask = DB::table('approval_request_task')->where('ID', $taskId)->first();
        if (
            $freshTask
            && strtoupper((string)$freshTask->Status) === 'CLAIMED'
            && (int)$freshTask->Claimed_By !== $userId
            && $isAdmin
        ) {
            $this->workflow->release($taskId, $userId, 'Admin override pada Payroll THR Approval');
            $freshTask = DB::table('approval_request_task')->where('ID', $taskId)->first();
        }

        if ($freshTask && strtoupper((string)$freshTask->Status) !== 'CLAIMED') {
            $this->workflow->claim($taskId, $userId);
        }
    }
}
