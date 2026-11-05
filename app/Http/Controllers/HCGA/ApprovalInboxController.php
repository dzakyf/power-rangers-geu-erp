<?php

namespace App\Http\Controllers\HCGA;

use App\Exceptions\ApprovalWorkflowException;
use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalAuthorizationService;
use App\Services\Approval\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApprovalInboxController extends Controller
{
    protected $workflow;
    protected $authorization;

    public function __construct(ApprovalWorkflowService $workflow, ApprovalAuthorizationService $authorization)
    {
        $this->workflow = $workflow;
        $this->authorization = $authorization;
    }

    public function index()
    {
        $isAdmin = $this->authorization->isWildcardAdmin(Auth::user());
        $userId = (int) Auth::id();

        $policyCategoryRows = DB::table('approval_policy')
            ->select(
                'Request_Type',
                DB::raw('COUNT(*) as Total_Policies'),
                DB::raw("SUM(CASE WHEN Status = 'ACTIVE' THEN 1 ELSE 0 END) as Active_Policies")
            )
            ->whereNotNull('Request_Type')
            ->where('Request_Type', '!=', '')
            ->groupBy('Request_Type')
            ->orderBy('Request_Type', 'ASC')
            ->get();

        $policiesByType = DB::table('approval_policy')
            ->select('ID', 'Policy_Code', 'Policy_Name', 'Request_Type', 'Status')
            ->whereNotNull('Request_Type')
            ->where('Request_Type', '!=', '')
            ->orderBy('Status', 'ASC')
            ->orderBy('ID', 'ASC')
            ->get()
            ->groupBy('Request_Type');

        $stepsByPolicy = DB::table('approval_policy_step as s')
            ->leftJoin('master_position as mp', 'mp.ID', '=', 's.Position_ID')
            ->select(
                's.ID',
                's.Policy_ID',
                's.Stage_Order',
                's.Level_Number',
                's.Position_ID',
                's.Position_Code',
                's.Department',
                's.Approval_Mode',
                's.Quorum',
                's.Is_Required',
                's.Label',
                'mp.Position as Master_Position_Name'
            )
            ->orderBy('s.Policy_ID', 'ASC')
            ->orderBy('s.Stage_Order', 'ASC')
            ->orderBy('s.Level_Number', 'ASC')
            ->orderBy('s.ID', 'ASC')
            ->get()
            ->groupBy('Policy_ID');

        $taskCountsQuery = $this->inboxBaseQuery($userId, $isAdmin)
            ->select('r.Request_Type', DB::raw('COUNT(*) as Total_Tasks'))
            ->groupBy('r.Request_Type')
            ->pluck('Total_Tasks', 'r.Request_Type')
            ->toArray();

        $totalAllTasks = array_sum($taskCountsQuery);

        $categoryMeta = [
            'REQUISITION' => [
                'label' => 'PTK / Requisition',
                'icon' => 'fa-user-plus',
                'badge_color' => 'primary',
            ],
            'STATUS_CHANGE' => [
                'label' => 'Perubahan Status Karyawan',
                'icon' => 'fa-user-Gear',
                'badge_color' => 'info',
            ],
            'PROCUREMENT' => [
                'label' => 'Procurement (PR)',
                'icon' => 'fa-cart-shopping',
                'badge_color' => 'success',
            ],
            'GA_WORK_ORDER' => [
                'label' => 'GA Work Order',
                'icon' => 'fa-screwdriver-wrench',
                'badge_color' => 'warning',
            ],
            'GENERAL' => [
                'label' => 'General Approval',
                'icon' => 'fa-file-signature',
                'badge_color' => 'secondary',
            ],
        ];

        $policyTabs = [];
        $registeredTypes = [];

        foreach ($policyCategoryRows as $row) {
            $type = strtoupper(trim((string) $row->Request_Type));
            if ($type === '') {
                continue;
            }
            $registeredTypes[$type] = true;
            $meta = $categoryMeta[$type] ?? [
                'label' => ucwords(strtolower(str_replace(['_', '-'], ' ', $type))),
                'icon' => 'fa-folder-open',
                'badge_color' => 'primary',
            ];

            $policiesList = $policiesByType->get($row->Request_Type, collect());

            $policyTabs[] = [
                'key' => $type,
                'label' => $meta['label'],
                'icon' => $meta['icon'] === 'fa-user-Gear' ? 'fa-user-pen' : $meta['icon'],
                'badge_color' => $meta['badge_color'],
                'total_policies' => (int) $row->Total_Policies,
                'active_policies' => (int) $row->Active_Policies,
                'task_count' => (int) ($taskCountsQuery[$type] ?? $taskCountsQuery[$row->Request_Type] ?? 0),
                'policies' => $policiesList->map(function ($p) use ($stepsByPolicy) {
                    $rawSteps = $stepsByPolicy->get($p->ID, collect());
                    return [
                        'id' => (int) $p->ID,
                        'code' => (string) $p->Policy_Code,
                        'name' => (string) $p->Policy_Name,
                        'status' => (string) $p->Status,
                        'steps' => $rawSteps->map(function ($s) {
                            $targetLabel = (string) ($s->Label ?: '');
                            if ($targetLabel === '') {
                                if (!empty($s->Position_Code)) {
                                    $targetLabel = $s->Position_Code . ($s->Master_Position_Name ? ' - ' . $s->Master_Position_Name : '');
                                } else {
                                    $targetLabel = 'Level ' . (int) $s->Level_Number . ($s->Department ? ' (' . $s->Department . ')' : '');
                                }
                            }
                            return [
                                'id' => (int) $s->ID,
                                'stage' => (int) $s->Stage_Order,
                                'level' => (int) $s->Level_Number,
                                'position_code' => (string) ($s->Position_Code ?: ''),
                                'department' => (string) ($s->Department ?: ''),
                                'mode' => (string) ($s->Approval_Mode ?: 'ALL'),
                                'quorum' => $s->Quorum ? (int) $s->Quorum : null,
                                'required' => (bool) $s->Is_Required,
                                'label' => $targetLabel,
                            ];
                        })->values()->all(),
                    ];
                })->values()->all(),
            ];
        }

        // Sertakan juga kategori yang memiliki antrean task aktif walaupun belum terdaftar di tabel approval_policy
        foreach ($taskCountsQuery as $reqType => $count) {
            $type = strtoupper(trim((string) $reqType));
            if ($type === '' || isset($registeredTypes[$type])) {
                continue;
            }
            $meta = $categoryMeta[$type] ?? [
                'label' => ucwords(strtolower(str_replace(['_', '-'], ' ', $type))),
                'icon' => 'fa-folder-open',
                'badge_color' => 'secondary',
            ];
            $policyTabs[] = [
                'key' => $type,
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'badge_color' => $meta['badge_color'],
                'total_policies' => 0,
                'active_policies' => 0,
                'task_count' => (int) $count,
                'policies' => [],
            ];
        }

        return view('humanresources.approval.index', compact('isAdmin', 'policyTabs', 'totalAllTasks'));
    }

    public function data(Request $request)
    {
        if (!headers_sent()) {
            header('Content-type: text/xml; charset=UTF-8');
        }

        $userId = (int) Auth::id();
        $isAdmin = $this->authorization->isWildcardAdmin(Auth::user());
        $requestTypeFilter = strtoupper(trim((string) $request->input('request_type', '')));
        $policyIdFilter = (int) $request->input('policy_id', 0);
        $stageFilter = (int) $request->input('stage_order', 0);

        $query = $this->inboxBaseQuery($userId, $isAdmin)
            ->leftJoin('approval_policy as p', 'p.ID', '=', 'r.Policy_ID')
            ->leftJoin('approval_policy_step as ps', function ($join) {
                $join->on('ps.Policy_ID', '=', 'r.Policy_ID')
                     ->on('ps.Stage_Order', '=', 't.Stage_Order');
            })
            ->select(
                't.ID',
                't.Request_ID',
                't.Stage_Order',
                't.Level_Number',
                't.Position_Code',
                't.Position_Name',
                't.Approval_Mode',
                't.Quorum',
                't.Status as Task_Status',
                't.Claimed_By',
                't.Claimed_At',
                't.Held_Reason',
                'r.Request_No',
                'r.Request_Type',
                'r.Policy_ID',
                'p.Policy_Code',
                'p.Policy_Name',
                'ps.Label as Step_Label',
                DB::raw('(SELECT COUNT(DISTINCT s2.Stage_Order) FROM approval_policy_step s2 WHERE s2.Policy_ID = r.Policy_ID) as Total_Policy_Stages'),
                'r.Status as Request_Status',
                'r.Submitted_At',
                'r.Location',
                'r.Company_ID',
                'u.Name as Requester_Name'
            );

        if ($requestTypeFilter !== '' && $requestTypeFilter !== 'ALL') {
            $query->where('r.Request_Type', $requestTypeFilter);
        }

        if ($policyIdFilter > 0) {
            $query->where('r.Policy_ID', $policyIdFilter);
        }

        if ($stageFilter > 0) {
            $query->where('t.Stage_Order', $stageFilter);
        }

        $tasks = $query->orderBy('r.Submitted_At', 'ASC')
            ->orderBy('t.Stage_Order', 'ASC')
            ->orderBy('t.ID', 'ASC')
            ->get();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rows>';

        $no = 1;
        foreach ($tasks as $task) {
            $taskStatus = strtoupper((string) $task->Task_Status);
            if ($taskStatus === 'CLAIMED') {
                $taskStatus = 'CLAIMED BY YOU';
            }

            $totalStages = (int) $task->Total_Policy_Stages;
            $stageInfo = 'Stage ' . (int) $task->Stage_Order . ($totalStages > 0 ? '/' . $totalStages : '');
            $stepLabel = trim((string) $task->Step_Label);
            if ($stepLabel === '') {
                $stepLabel = trim($task->Position_Code . ' - ' . $task->Position_Name, ' -');
            }
            $policyStepDisplay = ($task->Policy_Code ? '[' . $task->Policy_Code . '] ' : '') . $stageInfo . ': ' . $stepLabel;

            echo '<row id="' . (int) $task->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($task->Request_No) . '</cell>';
            echo '<cell>' . $this->xml($task->Request_Type) . '</cell>';
            echo '<cell>' . $this->xml($policyStepDisplay) . '</cell>';
            echo '<cell>' . $this->xml($task->Position_Code . ' - ' . $task->Position_Name) . '</cell>';
            echo '<cell>' . (int) $task->Stage_Order . '</cell>';
            echo '<cell>' . (int) $task->Level_Number . '</cell>';
            echo '<cell>' . $this->xml($task->Approval_Mode . ($task->Quorum ? ' (' . $task->Quorum . ')' : '')) . '</cell>';
            echo '<cell>' . $this->xml($taskStatus) . '</cell>';
            echo '<cell>' . $this->xml($task->Requester_Name) . '</cell>';
            echo '<cell>' . $this->xml($task->Location) . '</cell>';
            echo '<cell>' . $this->xml($task->Submitted_At) . '</cell>';
            echo '<userdata name="request_type">' . $this->xml(strtoupper(trim((string) $task->Request_Type))) . '</userdata>';
            echo '<userdata name="policy_id">' . (int) $task->Policy_ID . '</userdata>';
            echo '<userdata name="policy_code">' . $this->xml($task->Policy_Code ?: '-') . '</userdata>';
            echo '<userdata name="policy_name">' . $this->xml($task->Policy_Name ?: '-') . '</userdata>';
            echo '<userdata name="stage_order">' . (int) $task->Stage_Order . '</userdata>';
            echo '<userdata name="step_label">' . $this->xml($stepLabel) . '</userdata>';
            echo '</row>';
            $no++;
        }

        echo '</rows>';
    }

    private function inboxBaseQuery(int $userId, bool $isAdmin)
    {
        $query = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->join('users as u', 'u.id', '=', 'r.Requester_Id')
            ->whereNotIn('r.Status', ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED']);

        if ($isAdmin) {
            // Wildcard admin needs visibility of claimed tasks to release
            // claims manually.
            $query->whereIn('t.Status', ['PENDING', 'HELD', 'CLAIMED']);
        } else {
            $now = \Illuminate\Support\Carbon::now();

            $query->where(function ($subQuery) use ($userId) {
                // Hanya task PENDING/HELD yang belum di-claim siapapun, ATAU CLAIMED yang sedang dipegang oleh user ini
                $subQuery->where(function ($pendingSub) {
                    $pendingSub->whereIn('t.Status', ['PENDING', 'HELD'])
                        ->whereNull('t.Claimed_By');
                })->orWhere(function ($claimedSub) use ($userId) {
                    $claimedSub->where('t.Status', 'CLAIMED')
                        ->where('t.Claimed_By', $userId);
                });
            })
            // Harus terdaftar sebagai kandidat aktif (holder, acting, delegate, atau atasan karena hierarki/vacant)
            ->whereExists(function ($candidateQuery) use ($userId) {
                $candidateQuery->select(DB::raw(1))
                    ->from('approval_task_candidate as c')
                    ->whereColumn('c.Task_ID', 't.ID')
                    ->where('c.User_Id', $userId)
                    ->where('c.Status', 'ACTIVE');
            })
            // Anti self-approval: Jika user adalah pemohon, sembunyikan kecuali ada izin pengecualian aktif
            ->where(function ($selfGuard) use ($userId, $now) {
                $selfGuard->where('r.Requester_Id', '!=', $userId)
                    ->orWhereExists(function ($exceptionQuery) use ($userId, $now) {
                        $exceptionQuery->select(DB::raw(1))
                            ->from('approval_self_exception as ex')
                            ->whereColumn('ex.Request_ID', 'r.ID')
                            ->where('ex.User_Id', $userId)
                            ->whereNull('ex.Revoked_At')
                            ->where(function ($q) {
                                $q->whereNull('ex.Task_ID')->orWhereColumn('ex.Task_ID', 't.ID');
                            })
                            ->where(function ($q) use ($now) {
                                $q->whereNull('ex.Expires_At')->orWhere('ex.Expires_At', '>', $now);
                            });
                    });
            });
        }

        return $query;
    }

    public function detail(Request $request)
    {
        $taskId = (int) $request->input('ID');
        $task = $this->taskQuery()->where('t.ID', $taskId)->first();

        if (!$task) {
            abort(404);
        }

        $this->assertCanViewTask($task);

        $candidates = DB::table('approval_task_candidate as c')
            ->join('users as u', 'u.id', '=', 'c.User_Id')
            ->select('c.*', 'u.Name as User_Name', 'u.username', 'u.Position', 'u.Department')
            ->where('c.Task_ID', $taskId)
            ->orderBy('c.Candidate_Type', 'ASC')
            ->get();

        $logs = DB::table('approval_log as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.Approver_Id')
            ->select('l.*', 'u.Name as Approver_Name')
            ->where('l.Request_Id', (int) $task->Request_ID)
            ->orderBy('l.Action_Date', 'ASC')
            ->orderBy('l.ID', 'ASC')
            ->get();

        $userId = (int) Auth::id();
        $isAdmin = $this->authorization->isWildcardAdmin(Auth::user());
        $isMyClaim = ((int) $task->Claimed_By === $userId);
        $isPending = (strtoupper((string) $task->Task_Status) === 'PENDING');
        $isCandidate = DB::table('approval_task_candidate')
            ->where('Task_ID', $taskId)
            ->where('User_Id', $userId)
            ->where('Status', 'ACTIVE')
            ->exists();

        // Cek self-approval jika pemohon adalah user yang sedang login
        $isRequester = ((int) $task->Requester_Id === $userId);
        $hasSelfException = true;
        if ($isRequester) {
            $now = \Illuminate\Support\Carbon::now();
            $hasSelfException = DB::table('approval_self_exception')
                ->where('Request_ID', (int) $task->Request_ID)
                ->where('User_Id', $userId)
                ->whereNull('Revoked_At')
                ->where(function ($q) use ($taskId) {
                    $q->whereNull('Task_ID')->orWhere('Task_ID', $taskId);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('Expires_At')->orWhere('Expires_At', '>', $now);
                })
                ->exists();
        }

        $canClaim = ($isPending && ($isCandidate || $isAdmin) && (!$isRequester || $hasSelfException));
        $canAct = ($isMyClaim || $canClaim || $isAdmin);
        $canRelease = ($isAdmin && strtoupper((string) $task->Task_Status) === 'CLAIMED');

        return view('humanresources.approval.detail', compact(
            'task',
            'candidates',
            'logs',
            'isAdmin',
            'canAct',
            'canClaim',
            'canRelease',
            'isMyClaim'
        ));
    }

    public function claim(Request $request)
    {
        $taskId = (int) $request->input('Task_ID');
        if ($taskId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Task ID tidak valid.'], 422);
        }

        return $this->jsonWorkflow(function () use ($taskId) {
            $task = $this->workflow->claim($taskId, (int) Auth::id());

            return ['message' => 'Task berhasil di-claim.', 'task' => $task];
        });
    }

    public function act(Request $request)
    {
        $action = strtoupper(trim((string) $request->input('Action')));
        $notes = trim((string) $request->input('Notes'));
        $taskId = (int) $request->input('Task_ID');

        if ($taskId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Pilih task approval terlebih dahulu.'], 422);
        }

        return $this->jsonWorkflow(function () use ($taskId, $action, $notes) {
            $userId = (int) Auth::id();

            $task = DB::table('approval_request_task')->where('ID', $taskId)->first();
            if (!$task) {
                throw new ApprovalWorkflowException('Task approval tidak ditemukan.', 'TASK_NOT_FOUND', 404);
            }

            // Jika status masih PENDING, otomatis claim terlebih dahulu agar user tidak terblokir
            if (strtoupper((string) $task->Status) === 'PENDING') {
                $this->workflow->claim($taskId, $userId);
            }

            $task = $this->workflow->act($taskId, $userId, $action, $notes);

            $message = $action === 'APPROVE'
                ? 'Permohonan berhasil disetujui (Approved).'
                : 'Permohonan berhasil ditolak (Rejected).';

            return ['message' => $message, 'task' => $task];
        });
    }

    public function release(Request $request)
    {
        $taskId = (int) $request->input('Task_ID');
        $reason = trim((string) $request->input('Reason'));

        if ($taskId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Pilih task approval terlebih dahulu.'], 422);
        }

        return $this->jsonWorkflow(function () use ($taskId, $reason) {
            $task = $this->workflow->release($taskId, (int) Auth::id(), $reason);

            return ['message' => 'Claim berhasil di-release.', 'task' => $task];
        });
    }

    public function grantSelfException(Request $request)
    {
        $reason = trim((string) $request->input('Reason'));
        $wantsJson = $request->expectsJson() || $request->ajax();

        try {
            $exceptionId = $this->workflow->grantSelfApprovalException(
                (int) $request->input('Request_ID'),
                (int) ($request->input('User_Id') ?: Auth::id()),
                (int) Auth::id(),
                $reason,
                $request->input('Expires_At') ?: null
            );

            if ($wantsJson) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'Exception self-approval diberikan.',
                    'exception_id' => $exceptionId,
                ]);
            }

            return redirect('hrd-approval-inbox')->with('success', 'Exception self-approval diberikan.');
        } catch (ApprovalWorkflowException $exception) {
            if ($wantsJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                    'code' => $exception->getErrorCode(),
                ], $exception->getStatusCode());
            }

            return redirect('hrd-approval-inbox')->with('error', $exception->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            if ($wantsJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage() ?: 'Anda tidak berwenang.',
                    'code' => 'FORBIDDEN',
                ], 403);
            }

            return redirect('hrd-approval-inbox')->with('error', $exception->getMessage() ?: 'Anda tidak berwenang.');
        }
    }

    public function addCandidate(Request $request)
    {
        $reason = trim((string) $request->input('Reason'));
        $wantsJson = $request->expectsJson() || $request->ajax();

        try {
            $this->workflow->addCandidate(
                (int) $request->input('Task_ID'),
                (int) $request->input('User_Id'),
                (int) Auth::id(),
                $reason
            );

            if ($wantsJson) {
                return response()->json(['status' => 'ok', 'message' => 'Kandidat approval berhasil ditambahkan.']);
            }

            return redirect('hrd-approval-inbox')->with('success', 'Kandidat approval berhasil ditambahkan.');
        } catch (ApprovalWorkflowException $exception) {
            if ($wantsJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                    'code' => $exception->getErrorCode(),
                ], $exception->getStatusCode());
            }

            return redirect('hrd-approval-inbox')->with('error', $exception->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            if ($wantsJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage() ?: 'Anda tidak berwenang.',
                    'code' => 'FORBIDDEN',
                ], 403);
            }

            return redirect('hrd-approval-inbox')->with('error', $exception->getMessage() ?: 'Anda tidak berwenang.');
        }
    }

    /**
     * Daftar user aktif (users + employment) yang boleh ditugaskan pada task HELD.
     * Mengecualikan requester dan kandidat yang sudah aktif pada task tersebut.
     */
    public function candidateOptions(Request $request)
    {
        if (!Gate::allows('approval.policy.manage')) {
            return response()->json(['status' => 'error', 'message' => 'Tidak berwenang.'], 403);
        }

        $taskId = (int) $request->input('task_id');
        $task = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->where('t.ID', $taskId)
            ->select('r.Requester_Id')
            ->first();
        if (!$task) {
            return response()->json(['status' => 'error', 'message' => 'Task tidak ditemukan.'], 404);
        }

        $existing = DB::table('approval_task_candidate')
            ->where('Task_ID', $taskId)->where('Status', 'ACTIVE')->pluck('User_Id')->all();
        $today = date('Y-m-d');

        $rows = DB::table('users as u')
            ->leftJoin('employment as e', function ($join) {
                $join->on('e.NIK', '=', 'u.Emp_ID');
            })
            ->where('u.Status', 'ACTIVE')
            ->where('u.id', '!=', (int) $task->Requester_Id)
            ->when(!empty($existing), function ($q) use ($existing) {
                $q->whereNotIn('u.id', $existing);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('e.NIK')
                    ->orWhere(function ($s) use ($today) {
                        $s->where(function ($a) {
                            $a->whereNull('e.Status')->orWhere('e.Status', 'ACTIVE');
                        })->where(function ($a) use ($today) {
                            $a->whereNull('e.End_Date')->orWhere('e.End_Date', '')
                                ->orWhere('e.End_Date', '0000-00-00')->orWhere('e.End_Date', '>=', $today);
                        });
                    });
            })
            ->select('u.id', 'u.Name', 'u.Emp_ID', 'e.Position', 'e.Department')
            ->orderBy('u.Name')
            ->limit(1000)
            ->get();

        return response()->json(['status' => 'ok', 'data' => $rows]);
    }

    public function escalation()
    {
        if (!Gate::allows('approval.policy.manage')) {
            abort(403, 'Hanya wildcard admin yang dapat membuka escalation queue.');
        }

        return view('humanresources.approval.escalation');
    }

    public function escalationData()
    {
        if (!Gate::allows('approval.policy.manage')) {
            abort(403);
        }

        if (!headers_sent()) {
            header('Content-type: text/xml; charset=UTF-8');
        }

        $held = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->join('users as u', 'u.id', '=', 'r.Requester_Id')
            ->select(
                't.ID',
                't.Request_ID',
                't.Stage_Order',
                't.Level_Number',
                't.Position_Code',
                't.Position_Name',
                't.Held_Reason',
                't.Held_At',
                'r.Request_No',
                'r.Request_Type',
                'u.Name as Requester_Name'
            )
            ->where('t.Status', 'HELD')
            ->whereNotIn('r.Status', ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'])
            ->orderBy('t.Held_At', 'ASC')
            ->get();

        $blocked = DB::table('approval_request as r')
            ->join('users as u', 'u.id', '=', 'r.Requester_Id')
            ->select(
                'r.ID as Request_ID',
                'r.Request_No',
                'r.Request_Type',
                'r.Current_Stage',
                'r.Current_Level',
                'r.Updated_At',
                'u.Name as Requester_Name'
            )
            ->where('r.Status', 'BLOCKED')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('approval_request_task as t')
                    ->whereColumn('t.Request_ID', 'r.ID')
                    ->where('t.Status', 'HELD');
            })
            ->orderBy('r.Updated_At', 'ASC')
            ->get();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rows>';

        $no = 1;
        foreach ($held as $task) {
            echo '<row id="task-' . (int) $task->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>HELD TASK</cell>';
            echo '<cell>' . $this->xml($task->Request_No) . '</cell>';
            echo '<cell>' . $this->xml($task->Request_Type) . '</cell>';
            echo '<cell>' . $this->xml($task->Position_Code . ' - ' . $task->Position_Name . ' / Stage ' . $task->Stage_Order) . '</cell>';
            echo '<cell>' . $this->xml($task->Held_Reason) . '</cell>';
            echo '<cell>' . $this->xml($task->Requester_Name) . '</cell>';
            echo '<cell>' . $this->xml($task->Held_At) . '</cell>';
            echo '</row>';
            $no++;
        }

        foreach ($blocked as $request) {
            $reason = DB::table('approval_log')
                ->where('Request_Id', (int) $request->Request_ID)
                ->whereIn('Action', ['STAGE_HELD', 'WAIT_GROUP_BLOCKED', 'HELD'])
                ->orderBy('Action_Date', 'DESC')
                ->value('Notes');

            echo '<row id="request-' . (int) $request->Request_ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>BLOCKED REQUEST</cell>';
            echo '<cell>' . $this->xml($request->Request_No) . '</cell>';
            echo '<cell>' . $this->xml($request->Request_Type) . '</cell>';
            echo '<cell>' . $this->xml('Stage ' . $request->Current_Stage . ' / Level ' . $request->Current_Level) . '</cell>';
            echo '<cell>' . $this->xml($reason ?: 'No recipient available') . '</cell>';
            echo '<cell>' . $this->xml($request->Requester_Name) . '</cell>';
            echo '<cell>' . $this->xml($request->Updated_At) . '</cell>';
            echo '</row>';
            $no++;
        }

        echo '</rows>';
    }

    private function taskQuery()
    {
        return DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->join('users as u', 'u.id', '=', 'r.Requester_Id')
            ->select(
                't.*',
                't.Status as Task_Status',
                'r.Request_No',
                'r.Request_Type',
                'r.Status as Request_Status',
                'r.Description',
                'r.Submitted_At',
                'r.Location',
                'r.Company_ID',
                'r.Department',
                'r.Amount',
                'r.Requester_Id',
                'r.Policy_ID',
                'r.Revision_No',
                'r.Parent_Request_ID',
                'u.Name as Requester_Name'
            );
    }

    private function assertCanViewTask($task): void
    {
        $userId = (int) Auth::id();

        if ((int) $task->Requester_Id === $userId) {
            return;
        }

        if ($this->authorization->isWildcardAdmin(Auth::user())) {
            return;
        }

        $isCandidate = DB::table('approval_task_candidate')
            ->where('Task_ID', (int) $task->ID)
            ->where('User_Id', $userId)
            ->exists();

        if (!$isCandidate) {
            abort(403, 'Anda tidak memiliki akses ke task approval ini.');
        }
    }

    private function jsonWorkflow(callable $callback)
    {
        try {
            return response()->json(array_merge(['status' => 'ok'], $callback()));
        } catch (ApprovalWorkflowException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'code' => $exception->getErrorCode(),
                'context' => $exception->getContext(),
            ], $exception->getStatusCode());
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() ?: 'Anda tidak berwenang.',
                'code' => 'FORBIDDEN',
            ], 403);
        }
    }

    private function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}