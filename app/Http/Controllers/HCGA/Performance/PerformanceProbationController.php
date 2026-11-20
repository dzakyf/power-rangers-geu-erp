<?php

namespace App\Http\Controllers\HCGA\Performance;

use App\Http\Controllers\Controller;
use App\PerformanceGoal;
use App\PerformanceReview;
use App\Services\HCGA\PerformanceKpiService;
use App\Services\HCGA\PerformanceReviewService;
use App\Services\HCGA\ProbationEvaluationService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceProbationController extends Controller
{
    protected $kpiService;
    protected $reviewService;
    protected $probationService;

    public function __construct(
        PerformanceKpiService $kpiService,
        PerformanceReviewService $reviewService,
        ProbationEvaluationService $probationService
    ) {
        $this->kpiService = $kpiService;
        $this->reviewService = $reviewService;
        $this->probationService = $probationService;
    }

    /**
     * Halaman Daftar Karyawan Masa Probation & Kontrak
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isHcga = $this->kpiService->isHcgaOrAdmin($currentUser);
        $picDepts = $this->kpiService->getPicDepartments($currentUser);
        $accessibleIds = $this->kpiService->getAccessibleEmployeeIds($currentUser);

        $query = DB::table('employment as e')
            ->leftJoin('users as u', 'u.Emp_ID', '=', 'e.NIK')
            ->where('e.Status', 'ACTIVE')
            ->where(function ($q) {
                $q->where('e.Employment_Status', 'LIKE', '%PROBATION%')
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('e.probation_end_date')
                          ->where('e.probation_end_date', '!=', '0000-00-00');
                  });
            })
            ->select(
                'e.ID as emp_row_id',
                'e.NIK',
                'e.Name',
                'e.Department',
                'e.Position',
                'e.Location',
                'e.Date_in',
                'e.probation_end_date',
                'e.Employment_Status',
                'u.id as user_id'
            );

        if ($accessibleIds !== null) {
            $query->where(function ($q) use ($accessibleIds, $picDepts) {
                if (!empty($accessibleIds)) {
                    $q->whereIn('u.id', $accessibleIds);
                }
                if (!empty($picDepts)) {
                    $q->orWhereIn('e.Department', $picDepts);
                }
            });
        }

        if ($request->filled('department')) {
            $query->where('e.Department', $request->department);
        }

        $rows = $query->orderByRaw('CASE WHEN e.probation_end_date IS NULL THEN 1 ELSE 0 END, e.probation_end_date ASC')
            ->get();

        $today = Carbon::today();
        $userIds = $rows->pluck('user_id')->filter()->unique()->all();

        $goalsByUser = PerformanceGoal::whereIn('user_id', empty($userIds) ? [-1] : $userIds)
            ->where('goal_category', 'PROBATION')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('user_id');

        $reviewsByUser = PerformanceReview::whereIn('user_id', empty($userIds) ? [-1] : $userIds)
            ->whereHas('cycle', function ($q) {
                $q->where('review_type', 'PROBATION');
            })
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('user_id');

        $probationList = $rows->map(function ($r) use ($today, $goalsByUser, $reviewsByUser) {
            $daysLeft = null;
            if (!empty($r->probation_end_date) && $r->probation_end_date !== '0000-00-00') {
                $daysLeft = (int) $today->diffInDays(Carbon::parse($r->probation_end_date)->startOfDay(), false);
            }

            $goal = $r->user_id && $goalsByUser->has($r->user_id) ? $goalsByUser->get($r->user_id)->first() : null;
            $review = $r->user_id && $reviewsByUser->has($r->user_id) ? $reviewsByUser->get($r->user_id)->first() : null;

            $r->days_left = $daysLeft;
            $r->probation_goal = $goal;
            $r->probation_review = $review;
            return $r;
        });

        $departments = DB::table('employment')
            ->where('Status', 'ACTIVE')
            ->whereNotNull('Department')
            ->distinct()
            ->orderBy('Department')
            ->pluck('Department');

        $canSetupKpi = $this->kpiService->canUserCreateGoal($currentUser);

        return view('humanresources.performance.probation.index', compact(
            'probationList',
            'departments',
            'isHcga',
            'canSetupKpi'
        ));
    }

    /**
     * Generate Manual Lembar Evaluasi Probation untuk Karyawan Tertentu
     */
    public function initiateSingleReview($userId)
    {
        $currentUser = Auth::user();
        $employee = User::findOrFail($userId);

        if (!$this->kpiService->isHcgaOrAdmin($currentUser) && !$this->kpiService->isDeptPic($currentUser, $employee->Department)) {
            return redirect()->back()->with('warning', 'Hanya HCGA atau PIC Departemen yang dapat membuat lembar evaluasi Probation secara manual.');
        }

        $empRow = DB::table('employment')->where('NIK', $employee->Emp_ID)->first();
        $probEndDate = ($empRow && !empty($empRow->probation_end_date) && $empRow->probation_end_date !== '0000-00-00')
            ? $empRow->probation_end_date
            : null;

        $cycle = $this->probationService->getOrCreateActiveProbationCycle(Carbon::today());
        $manager = $this->reviewService->resolveDirectManagerForUser($employee);

        $review = $this->reviewService->initiateReviewForEmployee($cycle, $employee, $manager, $probEndDate);

        return redirect()
            ->route('hrd.performance.review.detail', $review->id)
            ->with('success', "Lembar Evaluasi Probation {$review->review_no} berhasil dibuat.");
    }
}
