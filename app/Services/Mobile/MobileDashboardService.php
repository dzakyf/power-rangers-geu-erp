<?php

namespace App\Services\Mobile;

use App\Attendance;
use App\AttendanceMain;
use App\Services\Attendance\AttendanceService;
use App\Services\Employee\EmployeeMobileService;
use App\Support\Modules;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileDashboardService
{
    protected $announcementService;
    protected $employeeMobileService;
    protected $attendanceService;

    public function __construct(
        EmployeeMobileService $employeeMobileService,
        AttendanceService $attendanceService,
        $announcementService = null
    ) {
        $this->employeeMobileService = $employeeMobileService;
        $this->attendanceService = $attendanceService;
        $this->announcementService = $announcementService;
    }

    /**
     * Build the unified mobile landing dashboard data for the given user.
     *
     * @param  \App\User  $user
     * @return array
     */
    public function getDashboardData(User $user): array
    {
        $today = date('Y-m-d');
        $empId = (string) ($user->Emp_ID ?? '');

        $employee = null;
        try {
            $employee = $this->employeeMobileService->getMyEmployeeData($user);
        } catch (\Throwable $e) {
            $employee = null;
        }

        $todayAttendance = null;
        $todaySchedule = null;
        try {
            if ($empId !== '' && Schema::hasTable('attendant_log')) {
                $todayAttendance = Attendance::where('NIK', $empId)
                    ->where('Scan_Date', $today)
                    ->first();

                if (!$todayAttendance && Schema::hasTable('attendant_main')) {
                    $main = AttendanceMain::where('NIK', $empId)
                        ->where('Periode', (int) date('Ym'))
                        ->first();
                    if ($main) {
                        $todayAttendance = Attendance::where('Task_ID', $main->Task_ID)
                            ->where('Scan_Date', $today)
                            ->first();
                    }
                }
            }
            if ($empId !== '') {
                $todaySchedule = $this->attendanceService->getWorkScheduleForDate($empId, $today);
            }
        } catch (\Throwable $e) {
            $todayAttendance = null;
        }

        $pendingApprovalsCount = $this->countPendingApprovals((int) $user->id);

        $latestAnnouncements = collect([]);
        try {
            $feedRequest = new Request(['limit' => 3]);
            $announcements = $this->announcementService();

            if ($announcements) {
                $feed = $announcements->getMobileFeed($feedRequest, $user);
                if ($feed instanceof \Illuminate\Pagination\AbstractPaginator) {
                    $latestAnnouncements = collect($feed->items())->take(3);
                } else {
                    $latestAnnouncements = collect($feed)->take(3);
                }
            }
        } catch (\Throwable $e) {
            $latestAnnouncements = collect([]);
        }

        $activeTokensCount = 0;
        try {
            if (Schema::hasTable('personal_access_tokens')) {
                $activeTokensCount = $user->tokens()->count();
            }
        } catch (\Throwable $e) {
            $activeTokensCount = 0;
        }

            $clockInTime = $todayAttendance && !empty($todayAttendance->Start_Time) ? $todayAttendance->Start_Time : null;
            $clockOutTime = $todayAttendance && !empty($todayAttendance->End_Time) ? $todayAttendance->End_Time : null;

            return [
            'user' => [
                'id' => (int) $user->id,
                'username' => $user->username,
                'name' => $employee->Nama ?? ($user->Name ?? $user->username),
                'emp_id' => $empId,
                'department' => $employee->Department ?? ($user->Department ?? '-'),
                'position' => $employee->Jabatan ?? ($user->Position ?? '-'),
                'location' => $employee->Lokasi ?? ($user->Location ?? '-'),
            ],
            'attendance_today' => [
                'date' => $today,
                'clock_in' => $clockInTime,
                'clock_out' => $clockOutTime,
                'clock_in_status' => $todayAttendance->Clock_In_Status ?? null,
                'clock_out_status' => $todayAttendance->Clock_Out_Status ?? null,
                'status' => $clockOutTime
                    ? 'CLOCKED_OUT'
                    : ($clockInTime ? 'CLOCKED_IN' : 'NOT_CLOCKED_IN'),
                'schedule' => $todaySchedule,
            ],
            'pending_approvals_count' => $pendingApprovalsCount,
            'latest_announcements' => $latestAnnouncements->values(),
            'active_tokens_count' => $activeTokensCount,
            'modules' => $this->getModuleMenuList($pendingApprovalsCount),
        ];
    }

    /**
     * Count pending approval tasks assigned to or claimable by the user.
     *
     * @param  int  $userId
     * @return int
     */
    protected function countPendingApprovals(int $userId): int
    {
        try {
            if (!Schema::hasTable('approval_request_task') || !Schema::hasTable('approval_task_candidate')) {
                return 0;
            }

            return (int) DB::table('approval_request_task as t')
                ->join('approval_task_candidate as tc', 'tc.Task_ID', '=', 't.ID')
                ->where('tc.User_ID', $userId)
                ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                ->distinct()
                ->count('t.ID');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Return structured navigation menu items for the Mobile Landing Page.
     *
     * @param  int  $pendingApprovalsCount
     * @return array
     */
    public function getModuleMenuList(int $pendingApprovalsCount = 0): array
    {
        return [
            [
                'key' => 'ess',
                'label' => 'ESS',
                'subtitle' => '7 Pengajuan Mandiri',
                'icon' => 'fa-briefcase',
                'color' => 'emerald',
                'route_name' => 'mobile.ess',
                'api_endpoint' => '/api/v1/mobile/ess/profile',
            ],
            [
                'key' => 'attendance',
                'label' => 'Absensi',
                'subtitle' => 'Clock In/Out & Timesheet',
                'icon' => 'fa-fingerprint',
                'color' => 'mint',
                'route_name' => 'attendance.mobile.index',
                'api_endpoint' => '/api/v1/mobile/attendance/summary',
            ],
            [
                'key' => 'approval',
                'label' => 'Approval',
                'subtitle' => 'Inbox Persetujuan',
                'icon' => 'fa-check-double',
                'color' => 'amber',
                'badge' => $pendingApprovalsCount,
                'route_name' => 'approval.mobile.index',
                'api_endpoint' => '/api/v1/mobile/approvals',
            ],
            [
                'key' => 'sppd',
                'label' => 'SPPD',
                'subtitle' => 'Perjalanan Dinas',
                'icon' => 'fa-plane-departure',
                'color' => 'purple',
                'route_name' => 'sppd.mobile.index',
                'api_endpoint' => '/api/v1/mobile/sppd',
            ],
            [
                'key' => 'reimbursement',
                'label' => 'Reimburse',
                'subtitle' => 'Klaim & Pengajuan',
                'icon' => 'fa-receipt',
                'color' => 'coral',
                'route_name' => 'reimbursement.mobile.index',
                'api_endpoint' => '/api/v1/mobile/reimbursement',
            ],
            [
                'key' => 'payslip',
                'label' => 'Payslip',
                'subtitle' => 'Slip Gaji & Riwayat',
                'icon' => 'fa-file-invoice-dollar',
                'color' => 'emerald',
                'route_name' => 'payslip.mobile.index',
                'api_endpoint' => '/mobile/payslip',
            ],
            [
                'key' => 'announcement',
                'label' => 'Pengumuman',
                'subtitle' => 'Berita Perusahaan',
                'icon' => 'fa-bullhorn',
                'color' => 'teal',
                'route_name' => 'announcement.mobile.index',
                'api_endpoint' => '/api/v1/mobile/announcements',
            ],
            [
                'key' => 'guideline',
                'label' => 'Guideline',
                'subtitle' => 'Kebijakan & SOP',
                'icon' => 'fa-book-open',
                'color' => 'slate',
                'route_name' => 'company-guideline.mobile.index',
                'api_endpoint' => '/api/v1/mobile/guidelines',
            ],
            [
                'key' => 'profile',
                'label' => 'Profil & Akun',
                'subtitle' => 'Data Diri & Keamanan',
                'icon' => 'fa-user-shield',
                'color' => 'blue',
                'route_name' => 'employee.mobile.my-profile',
                'api_endpoint' => '/api/v1/mobile/ess/profile',
            ],
        ];
    }

    /**
     * Build context data for ESS Hub and its 7 self-service request flows.
     *
     * @param  \App\User  $user
     * @return array
     */
    public function getEssContext(User $user): array
    {
        $empId = (string) ($user->Emp_ID ?? '');
        $today = date('Y-m-d');

        $locations = collect([]);
        $availableShifts = collect([]);
        $colleagues = collect([]);
        $schedule = ['shift_name' => 'Regular', 'default_start' => '08:00', 'default_end' => '17:00'];
        $leaveBalance = null;

        try {
            if (Schema::hasTable('attendance_locations')) {
                $locations = DB::table('attendance_locations')->where('status', 'ACTIVE')->get();
            }
            if (Schema::hasTable('master_shifts')) {
                $availableShifts = DB::table('master_shifts')->where('status', 'ACTIVE')->orderBy('id', 'asc')->get();
            }
            if (Schema::hasTable('users')) {
                $colleagues = DB::table('users')
                    ->where('Status', 'ACTIVE')
                    ->where('Emp_ID', '!=', $empId)
                    ->whereNotNull('Emp_ID')
                    ->when(!empty($user->Department), function ($q) use ($user) {
                        return $q->where('Department', $user->Department);
                    })
                    ->select('Emp_ID', 'Name', 'Department', 'Position')
                    ->orderBy('Name', 'asc')
                    ->get();
            }
            if ($empId !== '') {
                $schedule = $this->attendanceService->getWorkScheduleForDate($empId, $today);
                $leaveBalance = app(\App\Services\Attendance\LeaveEntitlementService::class)->getOrCreateUserBalance($empId);
            }
        } catch (\Throwable $e) {
            // Keep safe defaults if any optional table is unavailable
        }

        $essItems = [
            [
                'key' => 'sppd',
                'title' => 'Pengajuan SPPD',
                'subtitle' => 'Surat Perintah Perjalanan Dinas',
                'icon' => 'fa-plane-departure',
                'tone' => 'purple',
                'route' => route('sppd.mobile.index'),
                'cta' => 'Ajukan / Daftar',
            ],
            [
                'key' => 'reimbursement',
                'title' => 'Pengajuan Reimbursement',
                'subtitle' => 'Klaim biaya operasional & medis',
                'icon' => 'fa-receipt',
                'tone' => 'coral',
                'route' => route('reimbursement.mobile.index'),
                'cta' => 'Ajukan / Daftar',
            ],
            [
                'key' => 'attendance',
                'title' => 'Pengajuan Absensi',
                'subtitle' => 'Clock In, Clock Out & Timesheet',
                'icon' => 'fa-fingerprint',
                'tone' => 'emerald',
                'route' => route('attendance.mobile.index'),
                'cta' => 'Buka Absensi',
            ],
            [
                'key' => 'correction',
                'title' => 'Request Kehadiran',
                'subtitle' => 'Koreksi jam masuk / pulang',
                'icon' => 'fa-clock',
                'tone' => 'amber',
                'route' => route('mobile.ess.correction'),
                'cta' => 'Buat Request',
            ],
            [
                'key' => 'leave',
                'title' => 'Pengajuan Izin & Cuti',
                'subtitle' => 'Cuti tahunan, sakit, atau izin khusus',
                'icon' => 'fa-calendar-minus',
                'tone' => 'teal',
                'route' => route('attendance.mobile.leave'),
                'cta' => 'Ajukan Izin',
            ],
            [
                'key' => 'overtime',
                'title' => 'Pengajuan Lembur',
                'subtitle' => 'Pra-shift, pasca-shift & estimasi tarif',
                'icon' => 'fa-business-time',
                'tone' => 'blue',
                'route' => route('mobile.ess.overtime'),
                'cta' => 'Ajukan Lembur',
            ],
            [
                'key' => 'shift_exchange',
                'title' => 'Pertukaran Shift',
                'subtitle' => 'Ganti shift mandiri atau tukar rekan',
                'icon' => 'fa-exchange-alt',
                'tone' => 'mint',
                'route' => route('mobile.ess.shift-exchange'),
                'cta' => 'Tukar Shift',
            ],
            [
                'key' => 'payslip',
                'title' => 'Slip Gaji (Payslip)',
                'subtitle' => 'Lihat riwayat slip gaji & rincian potongan',
                'icon' => 'fa-file-invoice-dollar',
                'tone' => 'emerald',
                'route' => route('payslip.mobile.index'),
                'cta' => 'Lihat Slip Gaji',
            ],
        ];

        return compact('locations', 'availableShifts', 'colleagues', 'schedule', 'leaveBalance', 'essItems');
    }

    private function announcementService()
    {
        if ($this->announcementService) {
            return $this->announcementService;
        }

        $class = '\\App\\Services\\'.'HCGA\\AnnouncementService';

        if (!Modules::isEnabled('hcga') || !class_exists($class)) {
            return null;
        }

        return $this->announcementService = app($class);
    }
}
