<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\EmployeeRosterAssignment;
use App\Models\HCGA\EmployeeSchedule;
use App\Models\HCGA\MasterShift;
use App\Models\HCGA\RosterPattern;
use App\Services\HCGA\RosterScheduleService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RosterScheduleMatrixController extends Controller
{
    protected $Saya;
    protected $rosterScheduleService;

    public function __construct(RosterScheduleService $rosterScheduleService)
    {
        $this->rosterScheduleService = $rosterScheduleService;

        $this->middleware(function ($request, $next) {
            $this->Saya = Auth::user()->username ?? '';
            return $next($request);
        });
    }

    /**
     * Halaman Utama Matriks Roster Kerja Karyawan
     */
    public function index(Request $request)
    {
        $yearMonth = $request->input('month', date('Y-m'));
        $startOfMonth = Carbon::parse($yearMonth . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $shifts = MasterShift::where('status', 'ACTIVE')->orderBy('id', 'asc')->get();
        $calendars = DB::table('work_calendars')->orderBy('Name', 'asc')->get();

        $selectedCalendarId = $request->input('calendar_id');

        $patternsQuery = RosterPattern::where('status', 'ACTIVE');
        if (!empty($selectedCalendarId)) {
            $patternsQuery->where('calendar_id', $selectedCalendarId);
        }
        $patterns = $patternsQuery->orderBy('id', 'asc')->get();

        // Ambil daftar karyawan aktif yang terdaftar di employee_roster_assignments
        $assignedUsersQuery = EmployeeRosterAssignment::with('pattern')
            ->leftJoin('users', function ($join) {
                $join->on('employee_roster_assignments.user_id', '=', 'users.username')
                    ->orOn('employee_roster_assignments.user_id', '=', 'users.Emp_ID');
            })
            ->select(
                'employee_roster_assignments.*',
                'users.Name as employee_name',
                'users.Department as department',
                'users.Position as position'
            )
            ->where('employee_roster_assignments.status', 'ACTIVE');

        if (!empty($selectedCalendarId)) {
            $matchingPatternIds = RosterPattern::where('calendar_id', $selectedCalendarId)->pluck('id');
            $assignedUsersQuery->whereIn('pattern_id', $matchingPatternIds);
        }
        $assignedUsers = $assignedUsersQuery->get();

        // Ambil daftar master user karyawan untuk modal dropdown penugasan roster
        $employeeList = DB::table('users')
            ->select('username', 'Emp_ID', 'Name', 'Department', 'Position')
            ->whereNotNull('Emp_ID')
            ->where('Emp_ID', '!=', '')
            ->orderBy('Name', 'asc')
            ->get();

        // Ambil schedule seluruh karyawan dalam bulan terpilih
        $dates = [];
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        foreach ($period as $dt) {
            $dates[] = $dt->format('Y-m-d');
        }

        $schedules = EmployeeSchedule::with('shift')
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(['user_id', 'work_date']);

        return view('humanresources.master.roster-matrix', compact(
            'yearMonth',
            'startOfMonth',
            'endOfMonth',
            'dates',
            'shifts',
            'patterns',
            'calendars',
            'selectedCalendarId',
            'assignedUsers',
            'employeeList',
            'schedules'
        ));
    }

    /**
     * API AJAX untuk melakukan penyesuaian manual shift (Manual Override / Tukar Shift)
     */
    public function updateManualSchedule(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|string',
            'work_date' => 'required|date_format:Y-m-d',
            'shift_id'  => 'required|integer|exists:master_shifts,id',
            'notes'     => 'nullable|string|max:255',
        ]);

        try {
            $updated = $this->rosterScheduleService->setManualSchedule(
                $request->input('user_id'),
                $request->input('work_date'),
                (int) $request->input('shift_id'),
                $request->input('notes') ?? 'Manual Shift Adjustment via Web UI',
                $this->Saya
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Jadwal shift berhasil diperbarui.',
                'data'    => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengubah jadwal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API AJAX untuk menugaskan pola roster ke karyawan baru & langsung generate schedule
     */
    public function assignPattern(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|string',
            'pattern_id'         => 'required|integer|exists:roster_patterns,id',
            'start_date'         => 'required|date_format:Y-m-d',
            'end_date'           => 'nullable|date_format:Y-m-d',
            'start_day_sequence' => 'nullable|integer|min:1',
        ]);

        try {
            $assignment = EmployeeRosterAssignment::updateOrCreate(
                [
                    'user_id'    => $request->input('user_id'),
                    'start_date' => $request->input('start_date'),
                ],
                [
                    'pattern_id'         => (int) $request->input('pattern_id'),
                    'end_date'           => $request->input('end_date'),
                    'start_day_sequence' => (int) ($request->input('start_day_sequence') ?? 1),
                    'status'             => 'ACTIVE',
                    'updated_by'         => $this->Saya,
                    'updated_at'         => now(),
                ]
            );

            // Generate otomatis jadwal 30 hari ke depan
            $endDateGen = $request->input('end_date') ?: Carbon::parse($request->input('start_date'))->addDays(30)->toDateString();
            $this->rosterScheduleService->generateEmployeeSchedule(
                $request->input('user_id'),
                $request->input('start_date'),
                $endDateGen,
                $this->Saya
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Penugasan roster berhasil dan jadwal kerja telah di-generate.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menugaskan roster: ' . $e->getMessage(),
            ], 500);
        }
    }
}
