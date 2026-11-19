<?php

namespace App\Http\Controllers\HCGA\Performance;

use App\Http\Controllers\Controller;
use App\PerformanceDeptPic;
use App\PerformanceDeptPicLog;
use App\PerformancePeriod;
use App\PerformanceSetting;
use App\Services\HCGA\PerformanceKpiService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformancePeriodController extends Controller
{
    public function index()
    {
        $periods = PerformancePeriod::orderBy('id', 'desc')->paginate(15);
        $allowSelfPlanning = PerformanceSetting::getValue('allow_employee_self_planning', '1');
        $defaultPipThreshold = PerformanceSetting::getValue('default_pip_threshold_score', '60');
        $isHcgaAdmin = app(PerformanceKpiService::class)->isHcgaOrAdmin(Auth::user());

        $deptPics = collect();
        $deptPicLogs = collect();
        $departments = collect();
        $activeUsers = collect();

        if ($isHcgaAdmin) {
            $deptPics = PerformanceDeptPic::with(['user', 'assigner'])->orderBy('department')->get();
            $deptPicLogs = PerformanceDeptPicLog::with(['previousUser', 'newUser', 'actor'])
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get();
            $departments = DB::table('master_position')
                ->whereNotNull('Department')
                ->where('Department', '!=', '')
                ->distinct()
                ->orderBy('Department')
                ->pluck('Department');
            $activeUsers = User::where('Status', 'ACTIVE')
                ->select('id', 'Name', 'Emp_ID', 'Department', 'Position')
                ->orderBy('Name')
                ->get();
        }

        return view('humanresources.performance.period.index', compact(
            'periods',
            'allowSelfPlanning',
            'defaultPipThreshold',
            'isHcgaAdmin',
            'deptPics',
            'deptPicLogs',
            'departments',
            'activeUsers'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_code' => 'required|string|max:50|unique:performance_periods,period_code',
            'name'        => 'required|string|max:150',
            'period_type' => 'required|in:ANNUAL,SEMESTER,QUARTERLY,MONTHLY',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);

        PerformancePeriod::create([
            'period_code' => strtoupper(trim($request->period_code)),
            'name'        => $request->name,
            'period_type' => $request->period_type,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'status'      => 'ACTIVE',
            'description' => $request->description,
            'created_by'  => Auth::id() ?? 1,
            'updated_by'  => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Master Periode berhasil ditambahkan.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:DRAFT,ACTIVE,EVALUATION,CLOSED',
        ]);

        $period = PerformancePeriod::findOrFail($id);
        $period->update([
            'status'     => $request->status,
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json(['success' => true, 'message' => "Status periode {$period->period_code} berhasil diubah menjadi {$period->status}."]);
    }

    public function updateSettings(Request $request)
    {
        if (!app(PerformanceKpiService::class)->isHcgaOrAdmin(Auth::user())) {
            abort(403, 'Hanya HCGA / Admin yang dapat mengubah pengaturan kebijakan Performance.');
        }

        $request->validate([
            'allow_employee_self_planning' => 'required|in:0,1',
            'default_pip_threshold_score'  => 'required|numeric|min:0|max:100',
        ]);

        $userId = Auth::id() ?? 1;
        PerformanceSetting::setValue('allow_employee_self_planning', $request->allow_employee_self_planning, $userId);
        PerformanceSetting::setValue('default_pip_threshold_score', $request->default_pip_threshold_score, $userId);

        return redirect()->back()->with('success', 'Pengaturan kebijakan Performance Management berhasil diperbarui.');
    }

    public function storeDeptPic(Request $request)
    {
        if (!app(PerformanceKpiService::class)->isHcgaOrAdmin(Auth::user())) {
            abort(403, 'Hanya HCGA / Admin yang dapat menentukan PIC Departemen.');
        }

        $request->validate([
            'department' => 'required|string|max:100',
            'user_id'    => 'required|exists:users,id',
            'notes'      => 'nullable|string|max:255',
        ]);

        $dept = trim($request->department);
        $newUserId = (int) $request->user_id;
        $actorId = Auth::id() ?? 1;

        DB::transaction(function () use ($dept, $newUserId, $actorId, $request) {
            $existing = PerformanceDeptPic::where('department', $dept)->first();
            $prevUserId = $existing ? (int) $existing->user_id : null;

            PerformanceDeptPic::updateOrCreate(
                ['department' => $dept],
                [
                    'user_id'     => $newUserId,
                    'assigned_by' => $actorId,
                    'notes'       => $request->notes,
                ]
            );

            $action = $existing ? 'CHANGED' : 'ASSIGNED';
            if (!$existing || $prevUserId !== $newUserId) {
                PerformanceDeptPicLog::create([
                    'department'       => $dept,
                    'previous_user_id' => $prevUserId,
                    'new_user_id'      => $newUserId,
                    'action'           => $action,
                    'notes'            => $request->notes,
                    'acted_by'         => $actorId,
                    'created_at'       => Carbon::now(),
                ]);
            }
        });

        return redirect()->back()->with('success', "PIC Departemen {$dept} berhasil disimpan.");
    }

    public function deleteDeptPic($id)
    {
        if (!app(PerformanceKpiService::class)->isHcgaOrAdmin(Auth::user())) {
            abort(403, 'Hanya HCGA / Admin yang dapat menghapus PIC Departemen.');
        }

        $pic = PerformanceDeptPic::findOrFail($id);
        $actorId = Auth::id() ?? 1;

        DB::transaction(function () use ($pic, $actorId) {
            PerformanceDeptPicLog::create([
                'department'       => $pic->department,
                'previous_user_id' => $pic->user_id,
                'new_user_id'      => null,
                'action'           => 'REMOVED',
                'notes'            => 'PIC Departemen dihapus oleh HCGA/Admin',
                'acted_by'         => $actorId,
                'created_at'       => Carbon::now(),
            ]);

            $pic->delete();
        });

        return response()->json(['success' => true, 'message' => "PIC Departemen {$pic->department} berhasil dihapus."]);
    }
}

