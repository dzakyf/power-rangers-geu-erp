<?php

namespace App\Http\Controllers\HCGA\Performance;

use App\Http\Controllers\Controller;
use App\PerformancePipPlan;
use App\Services\HCGA\PerformancePipService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformancePipController extends Controller
{
    protected $pipService;

    public function __construct(PerformancePipService $pipService)
    {
        $this->pipService = $pipService;
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

    public function index()
    {
        return view('humanresources.performance.pip.index');
    }

    public function data(Request $request)
    {
        $this->xmlHeader();

        $rows = DB::table('performance_pip_plans as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('users as m', 'm.id', '=', 'p.mentor_id')
            ->leftJoin('performance_reviews as pr', 'pr.id', '=', 'p.review_id')
            ->select([
                'p.id',
                'p.pip_no',
                'u.Name as employee_name',
                'u.Department as department',
                'm.Name as mentor_name',
                'pr.review_no',
                'p.duration_days',
                'p.start_date',
                'p.end_date',
                'p.outcome_status',
                'p.status',
            ])
            ->orderBy('p.id', 'desc')
            ->get();

        echo '<rows>';
        foreach ($rows as $r) {
            echo '<row id="' . $r->id . '">';
            echo '<cell>' . $this->xml($r->pip_no) . '</cell>';
            echo '<cell>' . $this->xml($r->employee_name) . '</cell>';
            echo '<cell>' . $this->xml($r->department ?? '-') . '</cell>';
            echo '<cell>' . $this->xml($r->mentor_name ?? '-') . '</cell>';
            echo '<cell>' . $this->xml($r->review_no ?? 'Manual') . '</cell>';
            echo '<cell>' . $r->duration_days . ' Hari</cell>';
            echo '<cell>' . date('d/m/Y', strtotime($r->start_date)) . ' s/d ' . date('d/m/Y', strtotime($r->end_date)) . '</cell>';
            echo '<cell>' . $this->xml($r->outcome_status) . '</cell>';
            echo '<cell>' . $this->xml($r->status) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function detail($id)
    {
        $pip = PerformancePipPlan::with([
            'user',
            'mentor',
            'review.cycle',
            'actions',
            'checkins.creator',
            'trainings'
        ])->findOrFail($id);

        $availableTrainings = DB::table('training')->select('id', 'training_name', 'category')->get();

        return view('humanresources.performance.pip.detail', compact('pip', 'availableTrainings'));
    }

    public function recordCheckin(Request $request, $id)
    {
        $request->validate([
            'checkin_date'     => 'required|date',
            'discussion_notes' => 'required|string',
            'action_agreed'    => 'required|string',
        ]);

        $pip = PerformancePipPlan::findOrFail($id);
        $checkin = $this->pipService->recordWeeklyCheckin($pip, $request->all(), Auth::id() ?? 1);

        return response()->json([
            'success' => true,
            'message' => "Sesi mentoring Minggu ke-{$checkin->week_number} berhasil dicatat.",
            'data'    => $checkin,
        ]);
    }

    public function assignTraining(Request $request, $id)
    {
        $request->validate([
            'training_title' => 'required|string|max:255',
        ]);

        $pip = PerformancePipPlan::findOrFail($id);
        $training = $this->pipService->assignTraining($pip, $request->all());

        return response()->json([
            'success' => true,
            'message' => "Pelatihan {$training->training_title} berhasil ditugaskan ke dokumen PIP.",
            'data'    => $training,
        ]);
    }

    public function evaluate(Request $request, $id)
    {
        $request->validate([
            'final_score'    => 'required|numeric|min:0|max:100',
            'outcome_status' => 'required|in:PASSED,EXTENDED,FAILED',
        ]);

        $pip = PerformancePipPlan::findOrFail($id);
        $updated = $this->pipService->evaluatePip($pip, $request->all(), Auth::id() ?? 1);

        return response()->json([
            'success' => true,
            'message' => "Evaluasi PIP {$updated->pip_no} berhasil disimpan dengan hasil [{$updated->outcome_status}].",
        ]);
    }
}

