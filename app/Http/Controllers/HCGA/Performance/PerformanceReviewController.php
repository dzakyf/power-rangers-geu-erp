<?php

namespace App\Http\Controllers\HCGA\Performance;

use App\Http\Controllers\Controller;
use App\PerformanceGoal;
use App\PerformancePeriod;
use App\PerformanceReview;
use App\PerformanceReviewCycle;
use App\Services\HCGA\PerformanceReviewService;
use App\User;
use Carbon\Carbon;
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
            'review_type'           => $request->review_type ?? 'SEMESTER_1',
            'enable_self_review'    => $request->has('enable_self_review'),
            'enable_manager_review' => $request->has('enable_manager_review'),
            'enable_peer_review'    => $request->has('enable_peer_review'),
            'weight_kpi'            => $request->weight_kpi ?? 70.00,
            'weight_competency'     => $request->weight_competency ?? 30.00,
            'pip_threshold_score'   => $request->filled('pip_threshold_score') ? (float) $request->pip_threshold_score : null,
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
     * Form Edit Siklus Review yang sudah dibuat
     */
    public function editCycle($id)
    {
        $cycle = PerformanceReviewCycle::findOrFail($id);
        $periods = PerformancePeriod::orderBy('id', 'desc')->get();
        return view('humanresources.performance.review.edit-cycle', compact('cycle', 'periods'));
    }

    /**
     * Simpan Perubahan Siklus Review yang sudah dibuat
     */
    public function updateCycle(Request $request, $id)
    {
        $cycle = PerformanceReviewCycle::findOrFail($id);

        $request->validate([
            'title'             => 'required|string|max:150',
            'period_id'         => 'required|exists:performance_periods,id',
            'review_type'       => 'required|in:ANNUAL,MID_YEAR,SEMESTER_1,SEMESTER_2,PROBATION,PROJECT',
            'weight_kpi'        => 'required|numeric|min:0|max:100',
            'weight_competency' => 'required|numeric|min:0|max:100',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'status'            => 'required|in:DRAFT,ONGOING,COMPLETED,ARCHIVED',
        ]);

        if (round((float) $request->weight_kpi + (float) $request->weight_competency, 2) !== 100.00) {
            return redirect()->back()->withInput()->with('error', 'Total Bobot KPI + Kompetensi harus tepat 100%.');
        }

        $cycle->update([
            'title'                 => $request->title,
            'period_id'             => $request->period_id,
            'review_type'           => $request->review_type,
            'enable_self_review'    => $request->has('enable_self_review'),
            'enable_manager_review' => $request->has('enable_manager_review'),
            'enable_peer_review'    => $request->has('enable_peer_review'),
            'weight_kpi'            => (float) $request->weight_kpi,
            'weight_competency'     => (float) $request->weight_competency,
            'pip_threshold_score'   => $request->filled('pip_threshold_score') ? (float) $request->pip_threshold_score : null,
            'start_date'            => $request->start_date,
            'end_date'              => $request->end_date,
            'status'                => $request->status,
            'description'           => $request->description,
            'reschedule_reason'     => $request->filled('reschedule_reason') ? $request->reschedule_reason : $cycle->reschedule_reason,
            'rescheduled_at'        => $request->filled('reschedule_reason') ? Carbon::now() : $cycle->rescheduled_at,
            'updated_by'            => Auth::id() ?? 1,
        ]);

        return redirect()->route('hrd.performance.review.index')->with('success', "Siklus Review {$cycle->title} berhasil diperbarui.");
    }

    /**
     * Inisiasi Massal Review untuk Karyawan yang memiliki Goal Approved
     */
    public function generateReviews(Request $request, $cycleId)
    {
        $cycle = PerformanceReviewCycle::findOrFail($cycleId);

        $approvedGoals = PerformanceGoal::where('period_id', $cycle->period_id)
            ->whereIn('status', ['APPROVED', 'EVALUATION', 'COMPLETED'])
            ->get();

        $approvedUserIds = $approvedGoals->pluck('user_id')->filter()->unique()->all();
        $positionGoals = $approvedGoals->where('scope_type', 'POSITION');

        $employeesQuery = User::where('Status', 'ACTIVE');
        $employeesQuery->where(function ($q) use ($approvedUserIds, $positionGoals) {
            if (!empty($approvedUserIds)) {
                $q->whereIn('id', $approvedUserIds);
            }
            foreach ($positionGoals as $pg) {
                $q->orWhere(function ($sub) use ($pg) {
                    $sub->where('Department', $pg->department)
                        ->where('Position', $pg->position);
                });
            }
            if (empty($approvedUserIds) && $positionGoals->isEmpty()) {
                $q->where('id', -1);
            }
        });

        $employees = $employeesQuery->get();

        $count = 0;

        foreach ($employees as $emp) {
            $exists = PerformanceReview::where('cycle_id', $cycle->id)->where('user_id', $emp->id)->exists();
            if (!$exists) {
                $manager = $this->reviewService->resolveDirectManagerForUser($emp);
                $this->reviewService->initiateReviewForEmployee($cycle, $emp, $manager);
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menginisiasi {$count} dokumen review untuk karyawan dengan KPI Approved."
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
            'competencyVersion',
            'goal.items.progressLogs',
            'kpiItems',
            'pipPlan',
            'feedbacks.reviewer',
            'feedbacks.competencyScores.competency'
        ])->findOrFail($id);

        $scaleConfig = $this->reviewService->getScaleConfigForReview($review);

        $competenciesQuery = \App\PerformanceCompetency::where('is_active', true);
        if (!empty($scaleConfig['version_id'])) {
            $competenciesQuery->where('version_id', $scaleConfig['version_id']);
        }
        $competencies = $competenciesQuery
            ->orderBy('sort_order', 'asc')
            ->orderBy('category_group', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $currentUser = Auth::user();
        $kpiService = app(\App\Services\HCGA\PerformanceKpiService::class);
        $isHcgaAdmin = $kpiService->isHcgaOrAdmin($currentUser);
        $empDept = $review->user ? $review->user->Department : null;
        $isDeptPic = $kpiService->isDeptPic($currentUser, $empDept);
        $isEmployeeOwner = $currentUser && ((int) $currentUser->id === (int) $review->user_id);
        $isAssignedManager = $currentUser && ((int) $currentUser->id === (int) $review->manager_id);

        $canSelfReview = $review->status !== 'FINALIZED'
            && $isEmployeeOwner
            && in_array($review->status, ['SELF_REVIEW', 'SCHEDULED'], true);

        $canManagerReview = $review->status !== 'FINALIZED'
            && ($isAssignedManager || $isHcgaAdmin);

        $canFinalize = $review->status !== 'FINALIZED'
            && ($isAssignedManager || $isHcgaAdmin);

        // Role Separation: hanya PIC yang ditetapkan atau HCGA yang boleh input target/realisasi KPI
        $canEditKpiRealization = $review->status !== 'FINALIZED'
            && ($isDeptPic || $isHcgaAdmin);

        return view('humanresources.performance.review.detail', compact(
            'review',
            'competencies',
            'scaleConfig',
            'canSelfReview',
            'canManagerReview',
            'canFinalize',
            'canEditKpiRealization',
            'isHcgaAdmin',
            'isDeptPic'
        ));
    }

    /**
     * Export PDF Form Penilaian Kontrak atau Masa Percobaan (FR/HCGA/14-00)
     */
    public function exportPdf($id)
    {
        $review = PerformanceReview::with([
            'cycle.period',
            'user',
            'manager',
            'competencyVersion',
            'kpiItems',
            'feedbacks.competencyScores.competency'
        ])->findOrFail($id);

        $scaleConfig = $this->reviewService->getScaleConfigForReview($review);
        $competenciesQuery = \App\PerformanceCompetency::where('is_active', true);
        if (!empty($scaleConfig['version_id'])) {
            $competenciesQuery->where('version_id', $scaleConfig['version_id']);
        }
        $competencies = $competenciesQuery
            ->orderBy('sort_order', 'asc')
            ->orderBy('category_group', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $mgrFeedback = $review->feedbacks->where('reviewer_type', 'MANAGER')->first();
        $scoreMap = $mgrFeedback
            ? $mgrFeedback->competencyScores->keyBy('competency_id')
            : collect();

        $logoPath = public_path('assets/images/geu-logo-color.webp');
        $logoDataUri = '';
        if (is_file($logoPath)) {
            if (function_exists('imagecreatefromwebp') && function_exists('imagepng')) {
                $im = @imagecreatefromwebp($logoPath);
                if ($im) {
                    ob_start();
                    imagepng($im);
                    $pngData = ob_get_clean();
                    imagedestroy($im);
                    $logoDataUri = 'data:image/png;base64,' . base64_encode($pngData);
                }
            }
            if ($logoDataUri === '') {
                $logoDataUri = 'data:image/webp;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $html = view('humanresources.performance.review.pdf-probation', compact(
            'review',
            'competencies',
            'scaleConfig',
            'mgrFeedback',
            'scoreMap',
            'logoDataUri'
        ))->render();

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $headerPath = public_path('assets/images/surat/header_geu.jpg');
        $footerPath = public_path('assets/images/surat/footer_geu.png');

        $headerHtml = '';
        if (is_file($headerPath)) {
            $headerB64 = base64_encode(file_get_contents($headerPath));
            $headerExt = strtolower(pathinfo($headerPath, PATHINFO_EXTENSION));
            $headerHtml = '<div style="margin:0;padding:0;line-height:0;"><img src="data:image/' . $headerExt . ';base64,' . $headerB64 . '" style="width:100%;display:block;"></div>';
        }

        $footerHtml = '';
        if (is_file($footerPath)) {
            $footerB64 = base64_encode(file_get_contents($footerPath));
            $footerExt = strtolower(pathinfo($footerPath, PATHINFO_EXTENSION));
            $footerHtml = '<div style="margin:0;padding:0;line-height:0;"><img src="data:image/' . $footerExt . ';base64,' . $footerB64 . '" style="width:100%;display:block;"></div>';
        }

        $mpdf = new \Mpdf\Mpdf([
            'tempDir'             => $tempDir,
            'mode'                => 'utf-8',
            'format'              => 'A4',
            'margin_top'          => 0,
            'margin_bottom'       => 0,
            'margin_left'         => 0,
            'margin_right'        => 0,
            'margin_header'       => 0,
            'margin_footer'       => 0,
            'setAutoTopMargin'    => $headerHtml !== '' ? 'stretch' : false,
            'setAutoBottomMargin' => $footerHtml !== '' ? 'stretch' : false,
            'autoMarginPadding'   => 4,
        ]);

        $mpdf->splitTableBorderWidth = 1;

        if ($headerHtml !== '') {
            $mpdf->SetHTMLHeader($headerHtml);
        }
        if ($footerHtml !== '') {
            $mpdf->SetHTMLFooter($footerHtml);
        }

        $safeNo = str_replace(['/', '\\'], '-', $review->review_no);
        $filename = "Evaluasi-{$safeNo}.pdf";

        $mpdf->SetTitle("Evaluasi {$review->review_no}");
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Update Realisasi Item KPI langsung dari Lembar Review 6 Bulanan
     */
    public function updateKpiRealization(Request $request, $id)
    {
        $request->validate([
            'goal_item_id'  => 'required|integer',
            'new_value'     => 'required|numeric',
            'check_in_date' => 'nullable|date',
            'comment'       => 'nullable|string',
        ]);

        $review = PerformanceReview::findOrFail($id);

        try {
            $updated = $this->reviewService->updateKpiRealizationOnReview(
                $review,
                (int) $request->goal_item_id,
                $request->only(['new_value', 'check_in_date', 'comment']),
                Auth::id() ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'Realisasi KPI & skor evaluasi berhasil diperbarui.',
                'data'    => $updated,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Submit Skor & Feedback Evaluasi
     */
    public function submitFeedback(Request $request, $id)
    {
        $request->validate([
            'score'        => 'nullable|numeric|min:0|max:100',
            'competencies' => 'nullable|array',
            'feedback'     => 'nullable|string',
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

    /**
     * Ubah waktu / jadwal siklus review (mis. reguler 6 bulan dipercepat jadi 3 bulan)
     */
    public function rescheduleCycle(Request $request, $id)
    {
        $currentUser = Auth::user();
        if (!app(\App\Services\HCGA\PerformanceKpiService::class)->isHcgaOrAdmin($currentUser)) {
            return response()->json(['success' => false, 'message' => 'Hanya HCGA/Admin yang dapat mengubah jadwal siklus review.'], 403);
        }

        $request->validate([
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'review_type'       => 'nullable|in:ANNUAL,MID_YEAR,SEMESTER_1,SEMESTER_2,PROBATION,PROJECT',
            'reschedule_reason' => 'required|string|max:500',
        ]);

        $cycle = PerformanceReviewCycle::findOrFail($id);
        $cycle->start_date = $request->start_date;
        $cycle->end_date = $request->end_date;
        if ($request->filled('review_type')) {
            $cycle->review_type = $request->review_type;
        }
        $cycle->reschedule_reason = $request->reschedule_reason;
        $cycle->rescheduled_at = Carbon::now();
        $cycle->updated_by = Auth::id();
        $cycle->save();

        return response()->json([
            'success' => true,
            'message' => "Jadwal siklus {$cycle->title} berhasil diperbarui ({$cycle->start_date} s/d {$cycle->end_date}).",
        ]);
    }
}

