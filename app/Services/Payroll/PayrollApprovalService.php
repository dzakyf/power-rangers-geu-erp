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
 * Service Adapter Persetujuan Penggajian (Payroll Approval)
 * Terintegrasi penuh dengan Dynamic Approval Engine (ApprovalWorkflowService).
 * Seluruh tahapan/stage, jabatan approver, dan kondisi nominal dapat diatur melalui sistem (/hrd-approval-policy).
 */
class PayrollApprovalService
{
    const REQUEST_TYPE = 'PAYROLL';

    /**
     * @var ApprovalWorkflowService
     */
    protected $workflow;

    /**
     * @var ApprovalAuthorizationService
     */
    protected $authorization;

    /**
     * @var PayrollAttendanceSyncService
     */
    protected $attendanceSync;

    public function __construct(
        ApprovalWorkflowService $workflow,
        ApprovalAuthorizationService $authorization,
        PayrollAttendanceSyncService $attendanceSync
    ) {
        $this->workflow = $workflow;
        $this->authorization = $authorization;
        $this->attendanceSync = $attendanceSync;
    }

    /**
     * Pastikan policy default PAYROLL tersedia di tabel approval_policy.
     */
    public function ensureDefaultPayrollPolicy(): void
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

        if (!class_exists('ApprovalPayrollPolicySeeder') && file_exists(database_path('seeds/ApprovalPayrollPolicySeeder.php'))) {
            require_once database_path('seeds/ApprovalPayrollPolicySeeder.php');
        }
        if (class_exists('ApprovalPayrollPolicySeeder')) {
            (new \ApprovalPayrollPolicySeeder())->run();
        }
    }

    /**
     * Pastikan batch salary_main memiliki dokumen approval_request aktif di ApprovalWorkflowService.
     *
     * @param object $salaryMain
     * @param object|null $user
     * @return object|null Baris approval_request
     */
    public function ensureApprovalRequest($salaryMain, $user = null)
    {
        if (!$salaryMain) {
            return null;
        }

        $this->ensureDefaultPayrollPolicy();

        // Cek apakah sudah terhubung dengan approval_request
        if (!empty($salaryMain->approval_request_id)) {
            $req = DB::table('approval_request')->where('ID', (int)$salaryMain->approval_request_id)->first();
            if ($req) {
                return $req;
            }
        }

        // Cek berdasarkan Request_No = Task_ID atau Map_To = salary_main.ID
        $req = DB::table('approval_request')
            ->where('Request_Type', self::REQUEST_TYPE)
            ->where(function ($q) use ($salaryMain) {
                $q->where('Request_No', $salaryMain->Task_ID)
                  ->orWhere('Map_To', (int)$salaryMain->ID);
            })
            ->orderBy('ID', 'DESC')
            ->first();

        if ($req) {
            DB::table('salary_main')->where('ID', $salaryMain->ID)->update([
                'approval_request_id' => (int)$req->ID,
            ]);
            $salaryMain->approval_request_id = (int)$req->ID;
            return $req;
        }

        // Jika belum ada dan status batch masih aktif (belum final), daftarkan ke ApprovalWorkflowService
        $statusUpper = strtoupper(trim((string)$salaryMain->Status));
        if (in_array($statusUpper, ['APPROVED', 'CANCELLED', 'CANCELED', 'REJECTED'], true)) {
            return null;
        }

        $requesterId = $user && !empty($user->id) ? (int)$user->id : (int)(Auth::id() ?: 1);
        if (!empty($salaryMain->Created_By)) {
            $creator = DB::table('users')->where('username', $salaryMain->Created_By)->first();
            if ($creator && !empty($creator->id)) {
                $requesterId = (int)$creator->id;
            }
        }

        // Resolusi Location & Company_ID yang selaras dengan master_position aktif
        $loc = !empty($salaryMain->Location) ? trim((string)$salaryMain->Location) : 'GEU-HO';
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

        $context = [
            'Request_Type' => self::REQUEST_TYPE,
            'Department'   => $salaryMain->Department ?: 'HCGA',
            'Location'     => $loc,
            'Company_ID'   => $compId,
            'Amount'       => (float)($salaryMain->Total ?? 0),
        ];

        $data = [
            'Request_No'  => $salaryMain->Task_ID,
            'Description' => "Batch Payroll {$salaryMain->Category} Periode {$salaryMain->Salary_Period} Group {$salaryMain->Salary_Group}",
            'Amount'      => (float)($salaryMain->Total ?? 0),
        ];

        try {
            $apprReq = $this->workflow->submit($data, $context, $requesterId);
        } catch (\Throwable $e) {
            // Fallback ke wildcard location & department jika policy spesifik departemen/lokasi tidak ditemukan
            try {
                $context['Location'] = null;
                $context['Department'] = null;
                $apprReq = $this->workflow->submit($data, $context, $requesterId);
            } catch (\Throwable $ex) {
                Log::warning("PayrollApprovalService: Gagal submit ke ApprovalWorkflowService untuk {$salaryMain->Task_ID}: " . $ex->getMessage());
                return null;
            }
        }

        if ($apprReq && !empty($apprReq->ID)) {
            $requestId = (int)$apprReq->ID;
            DB::table('approval_request')->where('ID', $requestId)->update([
                'Map_To' => (int)$salaryMain->ID,
            ]);
            DB::table('salary_main')->where('ID', $salaryMain->ID)->update([
                'approval_request_id' => $requestId,
            ]);
            $salaryMain->approval_request_id = $requestId;
            $this->syncSalaryMainStatus($salaryMain->Task_ID, $salaryMain->Created_By ?: 'SYSTEM');
            return DB::table('approval_request')->where('ID', $requestId)->first();
        }

        return null;
    }

    /**
     * Ambil daftar batch payroll yang menunggu persetujuan beserta metadata tahapan dari ApprovalWorkflowService.
     *
     * @param object|null $currentUser
     * @return array
     */
    public function getPendingBatchesWithApproval($currentUser = null): array
    {
        $this->ensureDefaultPayrollPolicy();

        $rows = DB::table('salary_main')
            ->whereNotIn('Status', ['APPROVED', 'CANCELED', 'CANCELLED', 'REJECTED'])
            ->orderBy('ID', 'DESC')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $this->ensureApprovalRequest($row, $currentUser);
            $freshRow = DB::table('salary_main')->where('ID', $row->ID)->first() ?: $row;
            $summary = $this->buildApprovalSummaryForRow($freshRow, $currentUser);
            $result[] = [
                'salary_main' => $freshRow,
                'approval'    => $summary,
            ];
        }

        return $result;
    }

    /**
     * Susun ringkasan tahapan approval dinamis untuk satu baris salary_main.
     *
     * @param object $salaryMain
     * @param object|null $currentUser
     * @return array
     */
    public function buildApprovalSummaryForRow($salaryMain, $currentUser = null): array
    {
        $userId = $currentUser && !empty($currentUser->id) ? (int)$currentUser->id : (int)(Auth::id() ?: 0);
        $isAdmin = $currentUser ? $this->authorization->isWildcardAdmin($currentUser) : false;

        $default = [
            'request_id'         => null,
            'policy_code'        => '-',
            'policy_name'        => 'Default System Policy',
            'request_status'     => $salaryMain->Status,
            'current_stage'      => 1,
            'total_stages'       => 1,
            'current_step_label' => $salaryMain->Status,
            'current_approvers'  => '-',
            'display_status'     => $salaryMain->Status,
            'can_approve'        => $isAdmin,
            'active_task'        => null,
            'tasks'              => [],
        ];

        if (empty($salaryMain->approval_request_id)) {
            return $default;
        }

        $requestId = (int)$salaryMain->approval_request_id;
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

            // Ambil daftar kandidat aktif untuk task ini
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

        // Tambahkan preview untuk stage berikutnya dari Policy_Snapshot yang belum dibuat task-nya
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
        if ($reqStatusUpper === 'APPROVED') {
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
            'policy_code'        => $policy ? $policy->Policy_Code : ($snapshot['Policy']['Policy_Code'] ?? 'PAYROLL-POLICY'),
            'policy_name'        => $policy ? $policy->Policy_Name : ($snapshot['Policy']['Policy_Name'] ?? 'Dynamic Payroll Policy'),
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
     * Ambil detail lengkap batch payroll & riwayat ApprovalWorkflowService untuk halaman detail.
     *
     * @param string $taskId
     * @param object|null $currentUser
     * @return array
     */
    public function getBatchApprovalDetails(string $taskId, $currentUser = null): array
    {
        $salaryMain = DB::table('salary_main')->where('Task_ID', $taskId)->first();
        if (!$salaryMain) {
            return [
                'salary_main' => null,
                'approval'    => null,
                'logs'        => [],
            ];
        }

        $this->ensureApprovalRequest($salaryMain, $currentUser);
        $salaryMain = DB::table('salary_main')->where('ID', $salaryMain->ID)->first() ?: $salaryMain;
        $summary = $this->buildApprovalSummaryForRow($salaryMain, $currentUser);

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
            'salary_main' => $salaryMain,
            'approval'    => $summary,
            'logs'        => $logs,
        ];
    }

    /**
     * Eksekusi persetujuan (Approve) tahap aktif pada Batch Payroll menggunakan ApprovalWorkflowService.
     *
     * @param string $taskId Task_ID pada salary_main
     * @param object|null $user User yang sedang login
     * @param string $notes Catatan persetujuan
     * @return array ['success' => bool, 'message' => string, 'status' => string]
     */
    public function approveBatch(string $taskId, $user = null, string $notes = ''): array
    {
        $user = $user ?: Auth::user();
        $userId = $user && !empty($user->id) ? (int)$user->id : 1;
        $username = $user && !empty($user->username) ? $user->username : 'SYSTEM';

        $salaryMain = DB::table('salary_main')->where('Task_ID', $taskId)->first();
        if (!$salaryMain) {
            return [
                'success' => false,
                'message' => "Batch Payroll {$taskId} tidak ditemukan.",
                'status'  => 'NOT_FOUND',
            ];
        }

        if (strtoupper((string)$salaryMain->Status) === 'APPROVED') {
            return [
                'success' => true,
                'message' => "Batch Payroll {$taskId} sudah berstatus APPROVED.",
                'status'  => 'APPROVED',
            ];
        }

        $request = $this->ensureApprovalRequest($salaryMain, $user);
        if (!$request) {
            return [
                'success' => false,
                'message' => "Gagal menginisialisasi workflow approval untuk batch {$taskId}. Pastikan Policy PAYROLL aktif di menu Konfigurasi Approval Policy.",
                'status'  => $salaryMain->Status,
            ];
        }

        $summaryBefore = $this->buildApprovalSummaryForRow($salaryMain, $user);
        $activeTask = $summaryBefore['active_task'];

        if (!$activeTask || empty($activeTask->ID)) {
            // Evaluasi ulang status jika seluruh stage sudah selesai
            $synced = $this->syncSalaryMainStatus($taskId, $username);
            return [
                'success' => true,
                'message' => "Status batch {$taskId} disinkronkan menjadi {$synced['status']}.",
                'status'  => $synced['status'],
            ];
        }

        $activeTaskId = (int)$activeTask->ID;
        $actedStageLabel = $activeTask->Step_Label ?? ('Stage ' . $activeTask->Stage_Order);

        try {
            $this->prepareCandidateAndClaim($request, $activeTask, $user, $userId);

            $approvalNotes = trim($notes) !== ''
                ? trim($notes)
                : "Approved {$actedStageLabel} via Payroll Approval oleh {$username}";

            $this->workflow->act($activeTaskId, $userId, 'APPROVE', $approvalNotes);

            $synced = $this->syncSalaryMainStatus($taskId, $username);

            if ($synced['is_final_approved']) {
                $msg = "Batch Payroll {$taskId} telah disetujui sepenuhnya (FINAL APPROVED) pada {$actedStageLabel}. Data presensi periode {$salaryMain->Salary_Period} kini dikunci (LOCKED).";
            } else {
                $msg = "Tahap [{$actedStageLabel}] untuk Batch {$taskId} berhasil disetujui. Saat ini berlanjut ke: {$synced['next_step_label']}.";
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
                'status'  => $salaryMain->Status,
            ];
        } catch (\Throwable $e) {
            Log::error("PayrollApprovalService::approveBatch error [{$taskId}]: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memproses approval payroll: ' . $e->getMessage(),
                'status'  => $salaryMain->Status,
            ];
        }
    }

    /**
     * Tolak (Reject) atau Batalkan (Cancel) Batch Payroll melalui ApprovalWorkflowService.
     *
     * @param string $taskId
     * @param object|null $user
     * @param string $mode 'REJECT' atau 'CANCEL'
     * @param string $reason
     * @return array ['success' => bool, 'message' => string, 'status' => string]
     */
    public function rejectOrCancelBatch(string $taskId, $user = null, string $mode = 'CANCEL', string $reason = ''): array
    {
        $user = $user ?: Auth::user();
        $userId = $user && !empty($user->id) ? (int)$user->id : 1;
        $username = $user && !empty($user->username) ? $user->username : 'SYSTEM';
        $mode = strtoupper(trim($mode)) === 'REJECT' ? 'REJECT' : 'CANCEL';

        $salaryMain = DB::table('salary_main')->where('Task_ID', $taskId)->first();
        if (!$salaryMain) {
            return [
                'success' => false,
                'message' => "Batch Payroll {$taskId} tidak ditemukan.",
                'status'  => 'NOT_FOUND',
            ];
        }

        $reasonText = trim($reason) !== ''
            ? trim($reason)
            : ($mode === 'REJECT' ? "Ditolak oleh {$username}" : "Dibatalkan oleh {$username}");

        try {
            if (!empty($salaryMain->approval_request_id)) {
                $reqId = (int)$salaryMain->approval_request_id;
                $req = DB::table('approval_request')->where('ID', $reqId)->first();

                if ($req && !in_array(strtoupper((string)$req->Status), ['APPROVED', 'REJECTED', 'CANCELLED'], true)) {
                    if ($mode === 'REJECT') {
                        $summary = $this->buildApprovalSummaryForRow($salaryMain, $user);
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
            DB::table('salary_main')->where('Task_ID', $taskId)->update([
                'Status'     => $finalStatus,
                'Posting_By' => $username,
            ]);

            $this->releasePayrollLocks($taskId, $salaryMain->Salary_Period, $username);

            return [
                'success' => true,
                'message' => "Batch Payroll {$taskId} telah di-" . strtolower($finalStatus) . " dan penguncian lembur/KJK telah dilepaskan.",
                'status'  => $finalStatus,
            ];
        } catch (\Throwable $e) {
            Log::error("PayrollApprovalService::rejectOrCancelBatch error [{$taskId}]: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membatalkan/menolak batch payroll: ' . $e->getMessage(),
                'status'  => $salaryMain->Status,
            ];
        }
    }

    /**
     * Sinkronkan status salary_main berdasarkan status terkini di approval_request & approval_request_task.
     *
     * @param string $taskId
     * @param string $actorUsername
     * @return array
     */
    public function syncSalaryMainStatus(string $taskId, string $actorUsername = 'SYSTEM'): array
    {
        $salaryMain = DB::table('salary_main')->where('Task_ID', $taskId)->first();
        if (!$salaryMain || empty($salaryMain->approval_request_id)) {
            return [
                'status'            => $salaryMain ? $salaryMain->Status : 'UNKNOWN',
                'is_final_approved' => false,
                'next_step_label'   => '-',
            ];
        }

        $req = DB::table('approval_request')->where('ID', (int)$salaryMain->approval_request_id)->first();
        if (!$req) {
            return [
                'status'            => $salaryMain->Status,
                'is_final_approved' => false,
                'next_step_label'   => '-',
            ];
        }

        $reqStatus = strtoupper(trim((string)$req->Status));
        $now = Carbon::now()->toDateTimeString();

        if (in_array($reqStatus, ['APPROVED', 'COMPLETED'], true)) {
            DB::table('salary_main')->where('Task_ID', $taskId)->update([
                'Status'       => 'APPROVED',
                'Posting'      => 1,
                'Posting_By'   => $actorUsername,
                'Posting_Date' => $now,
            ]);

            $this->attendanceSync->lockAttendancePeriod($salaryMain->Salary_Period, 'LOCKED', $actorUsername);

            return [
                'status'            => 'APPROVED',
                'is_final_approved' => true,
                'next_step_label'   => 'APPROVED',
            ];
        }

        if (in_array($reqStatus, ['REJECTED', 'RETURNED'], true)) {
            DB::table('salary_main')->where('Task_ID', $taskId)->update([
                'Status'     => 'REJECTED',
                'Posting_By' => $actorUsername,
            ]);
            $this->releasePayrollLocks($taskId, $salaryMain->Salary_Period, $actorUsername);

            return [
                'status'            => 'REJECTED',
                'is_final_approved' => false,
                'next_step_label'   => 'REJECTED',
            ];
        }

        if ($reqStatus === 'CANCELLED') {
            DB::table('salary_main')->where('Task_ID', $taskId)->update([
                'Status'     => 'CANCELLED',
                'Posting_By' => $actorUsername,
            ]);
            $this->releasePayrollLocks($taskId, $salaryMain->Salary_Period, $actorUsername);

            return [
                'status'            => 'CANCELLED',
                'is_final_approved' => false,
                'next_step_label'   => 'CANCELLED',
            ];
        }

        // Masih IN_PROGRESS -> ambil stage aktif saat ini
        $summary = $this->buildApprovalSummaryForRow($salaryMain);
        $activeTask = $summary['active_task'];
        $stageNum = (int)$summary['current_stage'];
        $posCode = $activeTask && !empty($activeTask->Position_Code) ? $activeTask->Position_Code : ('S' . $stageNum);
        $dynamicStatus = substr("STAGE_{$stageNum}_{$posCode}", 0, 30);

        DB::table('salary_main')->where('Task_ID', $taskId)->update([
            'Status'     => $dynamicStatus,
            'Posting_By' => $actorUsername,
        ]);

        return [
            'status'            => $dynamicStatus,
            'is_final_approved' => false,
            'next_step_label'   => $summary['current_step_label'],
        ];
    }

    /**
     * Lepaskan penguncian Salary_ID pada Overtime, KJK, dan ringkasan presensi apabila batch dibatalkan/ditolak.
     */
    public function releasePayrollLocks(string $taskId, string $period = null, string $actorUsername = 'SYSTEM'): void
    {
        if ($taskId !== '') {
            if (Schema::hasTable('attendance_overtime_requests')) {
                DB::table('attendance_overtime_requests')
                    ->where('salary_id', $taskId)
                    ->update([
                        'salary_id'            => null,
                        'salary_period'        => null,
                        'is_payroll_processed' => 0,
                    ]);
            }
            if (Schema::hasTable('overtime_lists')) {
                DB::table('overtime_lists')
                    ->where('Salary_ID', $taskId)
                    ->update([
                        'Salary_ID'     => null,
                        'Salary_Period' => null,
                    ]);
            }
            if (Schema::hasTable('kjk_lists')) {
                DB::table('kjk_lists')
                    ->where('Salary_ID', $taskId)
                    ->update([
                        'Salary_ID'     => null,
                        'Salary_Period' => null,
                    ]);
            }
        }

        if (!empty($period)) {
            $this->attendanceSync->lockAttendancePeriod($period, 'DRAFT', $actorUsername);
        }
    }

    /**
     * Siapkan kandidat & claim task pada ApprovalWorkflowService sebelum eksekusi act().
     */
    protected function prepareCandidateAndClaim($request, $activeTask, $user, int $userId): void
    {
        $taskId = (int)$activeTask->ID;
        $isAdmin = $user ? $this->authorization->isWildcardAdmin($user) : false;

        // Cek apakah user sudah terdaftar sebagai kandidat aktif pada task ini
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

            // Wildcard admin / HRD Admin berhak mengeksekusi sebagai ADMIN candidate
            DB::table('approval_task_candidate')->insert([
                'Task_ID'        => $taskId,
                'User_Id'        => $userId,
                'Position_ID'    => (int)($activeTask->Position_ID ?? 0),
                'Candidate_Type' => 'ADMIN',
                'Status'         => 'ACTIVE',
                'Created_At'     => Carbon::now(),
            ]);
        }

        // Jika user adalah pembuat request sendiri dan policy melarang self-approval, berikan self-exception jika admin
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
                    'Reason'          => 'Admin Payroll Batch Approval',
                    'Expires_At'      => null,
                    'Created_At'      => Carbon::now(),
                ]);
            }
        }

        // Jika task sudah di-claim oleh user lain dan eksekutor saat ini adalah admin, lepaskan dulu claim-nya
        $freshTask = DB::table('approval_request_task')->where('ID', $taskId)->first();
        if (
            $freshTask
            && strtoupper((string)$freshTask->Status) === 'CLAIMED'
            && (int)$freshTask->Claimed_By !== $userId
            && $isAdmin
        ) {
            $this->workflow->release($taskId, $userId, 'Admin override pada Payroll Approval');
            $freshTask = DB::table('approval_request_task')->where('ID', $taskId)->first();
        }

        if ($freshTask && strtoupper((string)$freshTask->Status) !== 'CLAIMED') {
            $this->workflow->claim($taskId, $userId);
        }
    }
}
