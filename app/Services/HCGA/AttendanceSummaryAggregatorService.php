<?php

namespace App\Services\HCGA;

use App\Models\HCGA\AttendanceDailySummary;
use App\Models\HCGA\AttendanceMonthlySummary;
use App\Models\HCGA\AttendanceWeeklySummary;
use App\Services\Attendance\AttendanceCutoffService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSummaryAggregatorService
{
    protected $cutoffService;

    public function __construct(AttendanceCutoffService $cutoffService)
    {
        $this->cutoffService = $cutoffService;
    }

    /**
     * Hitung akumulasi jam kerja mingguan dan evaluasi target 40 jam kerja per minggu.
     *
     * @param string $userId
     * @param int $year
     * @param int $weekNumber
     * @param string|null $actor
     * @return AttendanceWeeklySummary
     */
    public function aggregateWeeklySummary(string $userId, int $year, int $weekNumber, ?string $actor = 'SYSTEM'): AttendanceWeeklySummary
    {
        // Tentukan tanggal awal dan akhir dari nomor minggu
        $startOfWeek = Carbon::now()->setISODate($year, $weekNumber)->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->setISODate($year, $weekNumber)->endOfWeek()->toDateString();

        $dailyRecords = AttendanceDailySummary::where('user_id', $userId)
            ->whereBetween('work_date', [$startOfWeek, $endOfWeek])
            ->get();

        $actualMinutes = (int) $dailyRecords->sum('actual_work_minutes');
        $overtimeMinutes = (int) $dailyRecords->sum('overtime_minutes');
        $lateMinutes = (int) $dailyRecords->sum('late_minutes');
        $earlyLeaveMinutes = (int) $dailyRecords->sum('early_leave_minutes');

        $daysPresent = $dailyRecords->whereIn('attendance_status', ['PRESENT', 'LATE', 'EARLY_LEAVE'])->count();
        $daysAbsent = $dailyRecords->where('attendance_status', 'ABSENT')->count();
        $daysOff = $dailyRecords->whereIn('attendance_status', ['OFF', 'HOLIDAY'])->count();
        $daysLeave = $dailyRecords->whereIn('attendance_status', ['LEAVE', 'BUSINESS_TRIP', 'TRAINING'])->count();

        $targetMinutes = 2400; // 40 jam = 2400 menit
        $isAchieved = $actualMinutes >= $targetMinutes;

        return AttendanceWeeklySummary::updateOrCreate(
            [
                'user_id'     => $userId,
                'year'        => $year,
                'week_number' => $weekNumber,
            ],
            [
                'week_start_date'     => $startOfWeek,
                'week_end_date'       => $endOfWeek,
                'target_work_minutes' => $targetMinutes,
                'actual_work_minutes' => $actualMinutes,
                'overtime_minutes'    => $overtimeMinutes,
                'late_minutes'        => $lateMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'days_present'        => $daysPresent,
                'days_absent'         => $daysAbsent,
                'days_off'            => $daysOff,
                'days_leave'          => $daysLeave,
                'is_target_achieved'  => $isAchieved,
                'remarks'             => $isAchieved ? "Target 40 Jam Tercapai ({$actualMinutes} menit)" : "Kurang " . ($targetMinutes - $actualMinutes) . " menit dari target",
                'calculated_at'       => now(),
                'updated_by'          => $actor,
                'updated_at'          => now(),
            ]
        );
    }

    /**
     * Hitung akumulasi bulanan berdasarkan periode cutoff aktif untuk kebutuhan posting payroll.
     *
     * @param string $userId
     * @param int $periode YYYYMM (misal 202611)
     * @param string|null $actor
     * @return AttendanceMonthlySummary
     */
    public function aggregateMonthlySummary(string $userId, int $periode, ?string $actor = 'SYSTEM'): AttendanceMonthlySummary
    {
        $cutoffRule = $this->cutoffService->resolveCutoffRule($userId);
        $range = $this->cutoffService->getCutoffPeriodRange((string) $periode, $cutoffRule);

        $startDate = $range['start_date'];
        $endDate = $range['end_date'];

        $dailyRecords = AttendanceDailySummary::where('user_id', $userId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        $totalScheduled = $dailyRecords->whereNotIn('attendance_status', ['OFF', 'HOLIDAY'])->count();
        $totalPresent = $dailyRecords->whereIn('attendance_status', ['PRESENT', 'LATE', 'EARLY_LEAVE'])->count();
        $totalAbsent = $dailyRecords->where('attendance_status', 'ABSENT')->count();
        $totalLateDays = $dailyRecords->where('late_minutes', '>', 0)->count();
        $totalEarlyLeaveDays = $dailyRecords->where('early_leave_minutes', '>', 0)->count();
        $totalLeaveDays = $dailyRecords->whereIn('attendance_status', ['LEAVE', 'BUSINESS_TRIP', 'TRAINING'])->count();

        $totalWorkMinutes = (int) $dailyRecords->sum('actual_work_minutes');
        $totalOvertimeMinutes = (int) $dailyRecords->sum('overtime_minutes');
        $totalLateMinutes = (int) $dailyRecords->sum('late_minutes');

        return AttendanceMonthlySummary::updateOrCreate(
            [
                'user_id' => $userId,
                'periode' => $periode,
            ],
            [
                'cutoff_start_date'      => $startDate,
                'cutoff_end_date'        => $endDate,
                'total_scheduled_days'   => $totalScheduled,
                'total_present_days'     => $totalPresent,
                'total_absent_days'      => $totalAbsent,
                'total_late_days'        => $totalLateDays,
                'total_early_leave_days' => $totalEarlyLeaveDays,
                'total_leave_days'       => $totalLeaveDays,
                'total_overtime_minutes' => $totalOvertimeMinutes,
                'total_late_minutes'     => $totalLateMinutes,
                'total_work_minutes'     => $totalWorkMinutes,
                'payroll_post_status'    => 'DRAFT',
                'remarks'                => "Rekap Periode Cutoff {$startDate} s/d {$endDate}",
                'calculated_at'          => now(),
                'updated_by'             => $actor,
                'updated_at'             => now(),
            ]
        );
    }
}
