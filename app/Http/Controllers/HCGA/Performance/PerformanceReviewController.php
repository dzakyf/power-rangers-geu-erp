<?php

namespace App\Http\Controllers\HCGA\Performance;

use App\Http\Controllers\Controller;
use App\PerformanceGoal;
use App\PerformancePeriod;
use App\PerformanceReview;
use App\PerformanceReviewCycle;
use App\Services\HCGA\PerformanceReviewService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceReviewController extends Controller
{
    protected $reviewService;

    public function __construct(PerformanceReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
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
     * Dashboard Performance Review
     */
    public function index()
    {
        $cycles = PerformanceReviewCycle::with('period')->orderBy('id', 'desc')->get();
        return view('humanresources.performance.review.index', compact('cycles'));
    }

    /**
     * Data XML untuk dhtmlXGrid
     */
    public function data(Request $request)
    {
        $this->xmlHeader();

        $query = DB::table('performance_reviews as pr')
            ->join('performance_review_cycles as prc', 'prc.id', '=', 'pr.cycle_id')
            ->join('users as u', 'u.id', '=', 'pr.user_id')
            ->leftJoin('users as mgr', 'mgr.id', '=', 'pr.manager_id')
            ->leftJoin('performance_goals as pg', 'pg.id', '=', 'pr.goal_id')
            ->select([
                'pr.id',
                'pr.review_no',
                'prc.title as cycle_title',
                'u.Name as employee_name',
                'u.Department as department',
                'mgr.Name as manager_name',
                'pg.goal_no',
                'pr.kpi_score',
                'pr.self_score',
                'pr.manager_score',
                'pr.final_score',
                'pr.rating_grade',
                'pr.status',
            ]);

        if ($request->filled('cycle_id')) {
            $query->where('pr.cycle_id', $request->cycle_id);
        }

        if ($request->filled('status')) {
            $query->where('pr.status', $request->status);
        }

        $rows = $query->orderBy('pr.id', 'desc')->get();

        echo '<rows>';
        foreach ($rows as $r) {
            echo '<row id="' . $r->id . '">';
            echo '<cell>' . $this->xml($r->review_no) . '</cell>';
            echo '<cell>' . $this->xml($r->cycle_title) . '</cell>';
            echo '<cell>' . $this->xml($r->employee_name) . '</cell>';
            echo '<cell>' . $this->xml($r->department ?? '-') . '</cell>';
            echo '<cell>' . $this->xml($r->manager_name ?? '-') . '</cell>';
            echo '<cell>' . $this->xml($r->goal_no ?? 'Belum Ditautkan') . '</cell>';
            echo '<cell>' . number_format($r->kpi_score, 1) . '%</cell>';
            echo '<cell>' . ($r->self_score !== null ? number_format($r->self_score, 1) : '-') . '</cell>';
            echo '<cell>' . ($r->manager_score !== null ? number_format($r->manager_score, 1) : '-') . '</cell>';
            echo '<cell>' . ($r->final_score !== null ? number_format($r->final_score, 1) : '-') . '</cell>';
            echo '<cell>' . ($r->rating_grade ?? '-') . '</cell>';
            echo '<cell>' . $this->xml($r->status) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    /**
     * Form Tambah Siklus / Inisiasi Review
     */
    public function createCycle()
    {
        $periods = PerformancePeriod::orderBy('id', 'desc')->get();
        return view('humanresources.performance.review.create-cycle', compact('periods'));
    }

    /**
     * Simpan Siklus Review
     */
    public function storeCycle(Request $request)
    {
        $request->validate([
            'cycle_code' => 'required|string|max:50|unique:performance_review_cycles,cycle_code',
            'title'      => 'required|string|max:150',
            'period_id'  => 'required|exists:performance_periods,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $cycle = PerformanceReviewCycle::create([
            'cycle_code'            => strtoupper(trim($request->cycle_code)),
            'title'                 => $request->title,
            'period_id'             => $request->period_id,
            'review_type'           => $request->review_type ?? 'ANNUAL',
            'enable_self_review'    => $request->has('enable_self_review'),
            'enable_manager_review' => $request->has('enable_manager_review'),
            'enable_peer_review'    => $request->has('enable_peer_review'),
            'weight_kpi'            => $request->weight_kpi ?? 70.00,
            'weight_competency'     => $request->weight_competency ?? 30.00,
            'start_date'            => $request->start_date,
            'end_date'              => $request->end_date,
            'status'                => 'ONGOING',
            'description'           => $request->description,
            'created_by'            => Auth::id() ?? 1,
            'updated_by'            => Auth::id() ?? 1,
        ]);

        return redirect()->route('hrd.performance.review.index')->with('success', "Siklus Review {$cycle->title} berhasil dibuat.");
    }

    /**
     * Inisiasi Massal Review untuk Karyawan yang memiliki Goal Approved
     */
    public function generateReviews(Request $request, $cycleId)
    {
        $cycle = PerformanceReviewCycle::findOrFail($cycleId);
        $employees = User::where('Status', 'ACTIVE')->get();
        $count = 0;

        foreach ($employees as $emp) {
            // Cek apakah review untuk employee sudah pernah digenerate di cycle ini
            $exists = PerformanceReview::where('cycle_id', $cycle->id)->where('user_id', $emp->id)->exists();
            if (!$exists) {
                // Cari manager jika ada
                $manager = User::where('Department', $emp->Department)
                    ->where('Position', 'LIKE', '%Manager%')
                    ->first();

                $this->reviewService->initiateReviewForEmployee($cycle, $emp, $manager);
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menginisiasi {$count} dokumen review kinerja karyawan."
        ]);
    }

    /**
     * Detail Lembar Penilaian / Appraisal Sheet
     */
    public function detail($id)
    {
        $review = PerformanceReview::with([
            'cycle.period',
            'user',
            'manager',
            'goal.items.progressLogs',
            'feedbacks.reviewer',
            'feedbacks.competencyScores.competency'
        ])->findOrFail($id);

        $competencies = \App\PerformanceCompetency::where('is_active', true)->get();

        return view('humanresources.performance.review.detail', compact('review', 'competencies'));
    }

    /**
     * Submit Skor & Feedback Evaluasi
     */
    public function submitFeedback(Request $request, $id)
    {
        $request->validate([
            'score'    => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $review = PerformanceReview::findOrFail($id);
        $user = Auth::user() ?? User::find($review->manager_id ?? $review->user_id);

        try {
            $feedback = $this->reviewService->submitFeedback($review, $user, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Penilaian evaluasi berhasil disimpan.',
                'data'    => $feedback,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Finalisasi / Kalibrasi Skor Akhir
     */
    public function finalize(Request $request, $id)
    {
        $review = PerformanceReview::findOrFail($id);
        $calibratedScore = $request->filled('calibrated_score') ? (float) $request->calibrated_score : null;

        try {
            $finalized = $this->reviewService->finalizeReview($review, $calibratedScore, $request->notes);
            return response()->json([
                'success' => true,
                'message' => "Review {$finalized->review_no} berhasil difinalisasi dengan Grade [{$finalized->rating_grade}].",
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}

