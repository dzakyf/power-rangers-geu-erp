<?php

namespace App\Http\Controllers\HCGA\Performance;

use App\Http\Controllers\Controller;
use App\PerformanceGoal;
use App\PerformanceGoalItem;
use App\PerformancePeriod;
use App\Services\HCGA\PerformanceKpiService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceKpiController extends Controller
{
    protected $kpiService;

    public function __construct(PerformanceKpiService $kpiService)
    {
        $this->kpiService = $kpiService;
    }

    protected function xmlHeader()
    {
        if (!headers_sent()) {
            header('Content-type: text/xml; charset=UTF-8');
        }
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    }

    protected function xml($val)
    {
        return htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Halaman Utama KPI & OKR (Adminor V3 Layout)
     */
    public function index()
    {
        $periods = PerformancePeriod::orderBy('id', 'desc')->get();
        $canCreateGoal = $this->kpiService->canUserCreateGoal(Auth::user());
        return view('humanresources.performance.kpi.index', compact('periods', 'canCreateGoal'));
    }

    /**
     * XML Feed untuk dhtmlXGrid
     */
    public function data(Request $request)
    {
        $this->xmlHeader();

        $query = DB::table('performance_goals as pg')
            ->join('performance_periods as pp', 'pp.id', '=', 'pg.period_id')
            ->leftJoin('users as u', 'u.id', '=', 'pg.user_id')
            ->leftJoin('master_position as mp', 'mp.ID', '=', 'pg.position_id')
            ->select([
                'pg.id',
                'pg.goal_no',
                'pg.scope_type',
                'pp.name as period_name',
                'u.Name as employee_name',
                'mp.Position as position_name',
                'pg.department',
                'pg.position',
                'pg.goal_type',
                'pg.total_kpi_weight',
                'pg.overall_progress_percent',
                'pg.status',
                'pg.created_at',
            ]);

        if ($request->filled('period_id')) {
            $query->where('pg.period_id', $request->period_id);
        }

        if ($request->filled('status')) {
            $query->where('pg.status', $request->status);
        }

        $currentUser = Auth::user();
        if ($currentUser && !$this->kpiService->isHcgaOrAdmin($currentUser)) {
            $picDepts = $this->kpiService->getPicDepartments($currentUser);
            $reviewedGoalIds = DB::table('performance_reviews')
                ->where('user_id', $currentUser->id)
                ->whereNotNull('goal_id')
                ->pluck('goal_id')
                ->all();

            $query->where(function ($q) use ($currentUser, $picDepts, $reviewedGoalIds) {
                $q->where('pg.user_id', $currentUser->id);

                if (!empty($picDepts)) {
                    $q->orWhereIn('pg.department', $picDepts);
                }

                if (!empty($currentUser->Department) && !empty($currentUser->Position)) {
                    $q->orWhere(function ($sub) use ($currentUser) {
                        $sub->where('pg.scope_type', 'POSITION')
                            ->where('pg.status', 'APPROVED')
                            ->where('pg.department', $currentUser->Department)
                            ->where('pg.position', $currentUser->Position);
                    });
                }

                if (!empty($reviewedGoalIds)) {
                    $q->orWhereIn('pg.id', $reviewedGoalIds);
                }
            });
        }

        $rows = $query->orderBy('pg.id', 'desc')->get();

        echo '<rows>';
        foreach ($rows as $r) {
            $statusBadge = $r->status;
            $targetLabel = $r->scope_type === 'POSITION'
                ? ('[Posisi] ' . ($r->position_name ?: $r->position ?: '-'))
                : ($r->employee_name ?? '-');
            echo '<row id="' . $r->id . '">';
            echo '<cell>' . $this->xml($r->goal_no) . '</cell>';
            echo '<cell>' . $this->xml($r->period_name) . '</cell>';
            echo '<cell>' . $this->xml($targetLabel) . '</cell>';
            echo '<cell>' . $this->xml($r->department ?? '-') . '</cell>';
            echo '<cell>' . $this->xml($r->goal_type) . '</cell>';
            echo '<cell>' . number_format($r->total_kpi_weight, 1) . '%</cell>';
            echo '<cell>' . number_format($r->overall_progress_percent, 1) . '%</cell>';
            echo '<cell>' . $this->xml($statusBadge) . '</cell>';
            echo '<cell>' . date('d-m-Y H:i', strtotime($r->created_at)) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    /**
     * Form Buat Sheet Goal Baru
     */
    public function create()
    {
        $currentUser = Auth::user();
        if (!$this->kpiService->canUserCreateGoal($currentUser)) {
            return redirect()->route('hrd.performance.kpi.index')
                ->with('error', 'Hanya PIC Departemen atau HCGA yang dapat menyusun target KPI.');
        }

        $periods = PerformancePeriod::whereIn('status', ['DRAFT', 'ACTIVE'])->get();
        $positionsQuery = DB::table('master_position')
            ->where(function ($q) {
                $q->whereNull('Status')->orWhere('Status', 'ACTIVE');
            })
            ->select('ID', 'Initial', 'Position', 'Department', 'Level')
            ->orderBy('Department')
            ->orderBy('Position');

        if ($currentUser && !$this->kpiService->isHcgaOrAdmin($currentUser)) {
            $picDepts = $this->kpiService->getPicDepartments($currentUser);
            $positionsQuery->whereIn('Department', empty($picDepts) ? ['__NONE__'] : $picDepts);
        }

        $positions = $positionsQuery->get();

        $employeesQuery = User::where('Status', 'ACTIVE')->orderBy('Name');
        if ($currentUser && !$this->kpiService->isHcgaOrAdmin($currentUser)) {
            $picDepts = $this->kpiService->getPicDepartments($currentUser);
            $employeesQuery->whereIn('Department', empty($picDepts) ? ['__NONE__'] : $picDepts);
        }
        $employees = $employeesQuery->select('id', 'Name', 'Emp_ID', 'Department', 'Position')->get();

        return view('humanresources.performance.kpi.create', compact('periods', 'positions', 'employees'));
    }

    /**
     * Simpan Goal Sheet
     */
    public function store(Request $request)
    {
        $request->validate([
            'period_id'   => 'required|exists:performance_periods,id',
            'position_id' => 'required_without:user_id|nullable|integer',
            'user_id'     => 'required_without:position_id|nullable|exists:users,id',
            'goal_type'   => 'required|in:HYBRID,KPI,OKR',
            'items'       => 'required|array|min:1',
        ]);

        $currentUser = Auth::user();
        if (!$this->kpiService->canUserCreateGoal($currentUser)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya PIC Departemen atau HCGA yang dapat menyusun KPI.'
            ], 403);
        }

        if ($request->filled('position_id') && $currentUser && !$this->kpiService->isHcgaOrAdmin($currentUser)) {
            $pos = DB::table('master_position')->where('ID', (int) $request->position_id)->first();
            if (!$pos || !$this->kpiService->isDeptPic($currentUser, $pos->Department)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat menyusun KPI untuk posisi di departemen yang Anda pegang sebagai PIC.'
                ], 403);
            }
        }

        try {
            $goal = $this->kpiService->createGoal(
                $request->only([
                    'period_id', 'position_id', 'user_id', 'goal_type', 'level', 'notes',
                    'is_addon', 'goal_category', 'probation_months', 'probation_start_date', 'probation_end_date'
                ]),
                $request->input('items', []),
                Auth::id() ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => "Goal Sheet {$goal->goal_no} berhasil disimpan sebagai Draft.",
                'data'    => $goal,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Form Edit Goal Sheet (untuk status DRAFT atau REJECTED)
     */
    public function edit($id)
    {
        $goal = PerformanceGoal::with(['period', 'user', 'items'])->findOrFail($id);
        if (!$this->kpiService->canUserManageGoal(Auth::user(), $goal)) {
            abort(403, 'Hanya PIC Departemen terkait atau HCGA yang dapat mengedit KPI ini.');
        }

        if (!in_array($goal->status, ['DRAFT', 'REJECTED'])) {
            return redirect()->route('hrd.performance.kpi.detail', $goal->id)
                ->with('error', "Target dengan status [{$goal->status}] tidak dapat diedit.");
        }

        return view('humanresources.performance.kpi.edit', compact('goal'));
    }

    /**
     * Update Goal Sheet (DRAFT atau REJECTED)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'goal_type' => 'required|in:HYBRID,KPI,OKR',
            'items'     => 'required|array|min:1',
        ]);

        $goal = PerformanceGoal::findOrFail($id);
        if (!$this->kpiService->canUserManageGoal(Auth::user(), $goal)) {
            return response()->json(['success' => false, 'message' => 'Hanya PIC Departemen terkait atau HCGA yang dapat memperbarui KPI ini.'], 403);
        }

        try {
            $updated = $this->kpiService->updateGoal(
                $goal,
                $request->only(['goal_type', 'level', 'notes']),
                $request->input('items', []),
                Auth::id() ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => "Goal Sheet {$updated->goal_no} berhasil diperbarui.",
                'data'    => $updated,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Detail Goal Sheet & Tracking
     */
    public function detail($id)
    {
        $goal = PerformanceGoal::with(['period', 'user', 'items.progressLogs.creator'])->findOrFail($id);
        if (!$this->kpiService->canUserViewGoal(Auth::user(), $goal)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat KPI ini.');
        }
        $canManageGoal = $this->kpiService->canUserManageGoal(Auth::user(), $goal);

        $approvalRequest = null;
        $approvalTasks = collect();
        $approvalStages = collect();
        $approvalLogs = collect();
        $isCurrentUserApprover = false;

        if ($goal->approval_request_id) {
            $approvalRequest = DB::table('approval_request')
                ->where('ID', $goal->approval_request_id)
                ->first();

            $approvalTasks = DB::table('approval_request_task as t')
                ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                ->leftJoin('users as u', 'u.id', '=', 'c.User_ID')
                ->where('t.Request_ID', $goal->approval_request_id)
                ->select([
                    't.ID',
                    't.Stage_Order',
                    't.Position_Name as Label',
                    't.Status',
                    't.Acted_At as Completed_At',
                    DB::raw('GROUP_CONCAT(DISTINCT u.Name SEPARATOR ", ") as approver_names'),
                ])
                ->groupBy('t.ID', 't.Stage_Order', 't.Position_Name', 't.Status', 't.Acted_At')
                ->orderBy('t.Stage_Order')
                ->get();

            $tasksByStage = $approvalTasks->keyBy('Stage_Order');
            $plannedSteps = [];
            if ($approvalRequest && !empty($approvalRequest->Policy_Snapshot)) {
                $snap = json_decode($approvalRequest->Policy_Snapshot, true);
                $plannedSteps = $snap['steps'] ?? [];
            }
            if (empty($plannedSteps) && $approvalRequest && !empty($approvalRequest->Policy_ID)) {
                $plannedSteps = DB::table('approval_policy_step')
                    ->where('Policy_ID', $approvalRequest->Policy_ID)
                    ->orderBy('Stage_Order')
                    ->get()
                    ->map(function ($s) {
                        return (array) $s;
                    })
                    ->all();
            }

            $pendingTaskIds = $approvalTasks->where('Status', 'PENDING')->pluck('ID')->all();
            $myPendingTaskIds = [];
            if (!empty($pendingTaskIds) && Auth::check()) {
                $myPendingTaskIds = DB::table('approval_task_candidate')
                    ->whereIn('Task_ID', $pendingTaskIds)
                    ->where('User_ID', Auth::id())
                    ->pluck('Task_ID')
                    ->all();
                $isCurrentUserApprover = !empty($myPendingTaskIds);
            }

            if (!empty($plannedSteps)) {
                foreach ($plannedSteps as $st) {
                    $order = (int) ($st['Stage_Order'] ?? 0);
                    $task = $tasksByStage->get($order);
                    $approvalStages->push((object) [
                        'Stage_Order'    => $order,
                        'Label'          => $task ? ($task->Label ?: ($st['Label'] ?? "Stage {$order}")) : ($st['Label'] ?? "Stage {$order}"),
                        'Role_Label'     => $st['Label'] ?? '',
                        'Status'         => $task ? $task->Status : 'WAITING_PREVIOUS',
                        'approver_names' => $task ? $task->approver_names : null,
                        'Completed_At'   => $task ? $task->Completed_At : null,
                        'is_my_task'     => $task ? in_array($task->ID, $myPendingTaskIds, true) : false,
                    ]);
                }
            } else {
                foreach ($approvalTasks as $task) {
                    $approvalStages->push((object) [
                        'Stage_Order'    => $task->Stage_Order,
                        'Label'          => $task->Label,
                        'Role_Label'     => $task->Label,
                        'Status'         => $task->Status,
                        'approver_names' => $task->approver_names,
                        'Completed_At'   => $task->Completed_At,
                        'is_my_task'     => in_array($task->ID, $myPendingTaskIds, true),
                    ]);
                }
            }

            $approvalLogs = DB::table('approval_log as l')
                ->leftJoin('users as u', 'u.id', '=', 'l.Approver_Id')
                ->where('l.Request_Id', $goal->approval_request_id)
                ->select([
                    'l.Action',
                    'l.Notes as Note',
                    'l.Action_Date as Created_At',
                    'u.Name as actor_name',
                ])
                ->orderBy('l.ID', 'desc')
                ->get();
        }

        return view('humanresources.performance.kpi.detail', compact(
            'goal',
            'approvalRequest',
            'approvalTasks',
            'approvalStages',
            'approvalLogs',
            'canManageGoal',
            'isCurrentUserApprover'
        ));
    }

    /**
     * Ajukan Goal Sheet ke Dynamic Approval Workflow Engine
     */
    public function submitApproval(Request $request, $id)
    {
        $goal = PerformanceGoal::findOrFail($id);
        if (!$this->kpiService->canUserManageGoal(Auth::user(), $goal)) {
            return response()->json(['success' => false, 'message' => 'Hanya PIC Departemen terkait atau HCGA yang dapat memperbarui KPI ini.'], 403);
        }
        $user = Auth::user() ?? User::find($goal->user_id);

        try {
            $updated = $this->kpiService->submitToApproval($goal, $user);
            return response()->json([
                'success' => true,
                'message' => "Goal Sheet {$updated->goal_no} berhasil diajukan ke Approval.",
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Simpan Check-in Progress Item Target
     */
    public function recordProgress(Request $request, $itemId)
    {
        $request->validate([
            'new_value'     => 'required|numeric',
            'check_in_date' => 'required|date',
            'comment'       => 'nullable|string',
        ]);

        $item = PerformanceGoalItem::findOrFail($itemId);

        try {
            $progress = $this->kpiService->recordProgress(
                $item,
                $request->only(['new_value', 'check_in_date', 'comment']),
                Auth::id() ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'Progres target berhasil diperbarui.',
                'data'    => $progress,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Ubah durasi / waktu penilaian KPI yang sudah diajukan atau disetujui (mis. 6 bulan -> 3 bulan)
     */
    public function rescheduleGoal(Request $request, $id)
    {
        $currentUser = Auth::user();
        if (!$this->kpiService->isHcgaOrAdmin($currentUser)) {
            return response()->json(['success' => false, 'message' => 'Hanya HCGA/Admin yang dapat mengubah durasi/waktu KPI.'], 403);
        }

        $request->validate([
            'probation_months'     => 'nullable|integer|min:0|max:24',
            'probation_start_date' => 'nullable|date',
            'probation_end_date'   => 'nullable|date',
            'reschedule_reason'    => 'required|string|max:500',
        ]);

        $goal = PerformanceGoal::findOrFail($id);
        $goal->probation_months = $request->filled('probation_months') && (int) $request->probation_months > 0 ? (int) $request->probation_months : null;
        $goal->probation_start_date = $request->filled('probation_start_date') ? $request->probation_start_date : $goal->probation_start_date;
        $goal->probation_end_date = $request->filled('probation_end_date') ? $request->probation_end_date : $goal->probation_end_date;
        $goal->reschedule_reason = $request->reschedule_reason;
        $goal->rescheduled_at = \Carbon\Carbon::now();
        $goal->updated_by = Auth::id();
        $goal->save();

        // Sinkronkan probation_end_date ke employment & review aktif jika KPI individu
        if ($goal->user_id && $goal->probation_end_date) {
            $empUser = \App\User::find($goal->user_id);
            if ($empUser && $empUser->Emp_ID) {
                DB::table('employment')
                    ->where('NIK', $empUser->Emp_ID)
                    ->update(['probation_end_date' => $goal->probation_end_date]);
            }
            \App\PerformanceReview::where('goal_id', $goal->id)
                ->where('status', '!=', 'FINALIZED')
                ->update(['probation_end_date' => $goal->probation_end_date]);
        }

        return response()->json([
            'success' => true,
            'message' => "Waktu penilaian KPI {$goal->goal_no} berhasil diubah.",
        ]);
    }
}

