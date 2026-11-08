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
        return view('humanresources.performance.kpi.index', compact('periods'));
    }

    /**
     * XML Feed untuk dhtmlXGrid
     */
    public function data(Request $request)
    {
        $this->xmlHeader();

        $query = DB::table('performance_goals as pg')
            ->join('performance_periods as pp', 'pp.id', '=', 'pg.period_id')
            ->join('users as u', 'u.id', '=', 'pg.user_id')
            ->select([
                'pg.id',
                'pg.goal_no',
                'pp.name as period_name',
                'u.Name as employee_name',
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

        $rows = $query->orderBy('pg.id', 'desc')->get();

        echo '<rows>';
        foreach ($rows as $r) {
            $statusBadge = $r->status;
            echo '<row id="' . $r->id . '">';
            echo '<cell>' . $this->xml($r->goal_no) . '</cell>';
            echo '<cell>' . $this->xml($r->period_name) . '</cell>';
            echo '<cell>' . $this->xml($r->employee_name) . '</cell>';
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
        $periods = PerformancePeriod::whereIn('status', ['DRAFT', 'ACTIVE'])->get();
        $users = User::where('Status', 'ACTIVE')->select('id', 'Name', 'Emp_ID', 'Department', 'Position')->get();
        return view('humanresources.performance.kpi.create', compact('periods', 'users'));
    }

    /**
     * Simpan Goal Sheet
     */
    public function store(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:performance_periods,id',
            'user_id'   => 'required|exists:users,id',
            'goal_type' => 'required|in:HYBRID,KPI,OKR',
            'items'     => 'required|array|min:1',
        ]);

        try {
            $goal = $this->kpiService->createGoal(
                $request->only(['period_id', 'user_id', 'goal_type', 'level', 'notes']),
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
     * Detail Goal Sheet & Tracking
     */
    public function detail($id)
    {
        $goal = PerformanceGoal::with(['period', 'user', 'items.progressLogs.creator'])->findOrFail($id);
        return view('humanresources.performance.kpi.detail', compact('goal'));
    }

    /**
     * Ajukan Goal Sheet ke Dynamic Approval Workflow Engine
     */
    public function submitApproval(Request $request, $id)
    {
        $goal = PerformanceGoal::findOrFail($id);
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
}

