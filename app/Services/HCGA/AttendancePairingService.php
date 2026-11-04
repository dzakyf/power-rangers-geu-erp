<?php

namespace App\Services\HCGA;

use App\Models\HCGA\AttendanceDailySummary;
use App\Models\HCGA\AttendanceSession;
use App\Models\HCGA\MasterShift;
use App\Services\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendancePairingService
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Lakukan pairing sesi kehadiran untuk seorang user pada tanggal kerja tertentu.
     *
     * @param string $userId
     * @param string $workDate 'Y-m-d'
     * @param string|null $actor
     * @return AttendanceDailySummary
     */
    public function processDailyAttendancePairing(string $userId, string $workDate, ?string $actor = 'SYSTEM'): AttendanceDailySummary
    {
        // 1. Dapatkan Jadwal Kerja Riil Karyawan (Roster / Shift / Office)
        $schedule = $this->attendanceService->getWorkScheduleForDate($userId, $workDate);
        $shiftId = $schedule['shift_id'] ?? null;
        $isWorkingDay = (int) ($schedule['is_working'] ?? 1);
        $isCrossDay = (bool) ($schedule['is_cross_day'] ?? false);
        $isHoliday = (bool) ($schedule['is_holiday'] ?? false);

        // Tentukan batas pencarian log absensi (jika cross-day, clock-out bisa terjadi di pagi hari berikutnya H+1)
        $startWindow = Carbon::parse($workDate . ' 04:00:00');
        $endWindow = $isCrossDay 
            ? Carbon::parse($workDate)->addDay()->setTime(14, 0, 0)
            : Carbon::parse($workDate . ' 23:59:59');

        // Ambil raw logs dari attendant_log
        $rawLogs = DB::table('attendant_log')
            ->where(function ($q) use ($userId) {
                $q->where('NIK', $userId)
                  ->orWhere('Created_By', $userId);
            })
            ->where('Scan_Date', $workDate)
            ->first();

        $clockInTime = null;
        $clockOutTime = null;

        if ($rawLogs) {
            if (!empty($rawLogs->Start_Time)) {
                $clockInTime = Carbon::parse($workDate . ' ' . $rawLogs->Start_Time);
            }
            if (!empty($rawLogs->End_Time)) {
                $outDate = $isCrossDay ? Carbon::parse($workDate)->addDay()->toDateString() : $workDate;
                $clockOutTime = Carbon::parse($outDate . ' ' . $rawLogs->End_Time);
            }
        }

        // Hitung selisih jam kerja, terlambat, dan pulang cepat
        $lateMinutes = 0;
        $earlyLeaveMinutes = 0;
        $workDurationMinutes = 0;
        $attendanceStatus = 'ABSENT';

        if (!$isWorkingDay) {
            $attendanceStatus = $isHoliday ? 'HOLIDAY' : 'OFF';
        }

        if ($clockInTime) {
            $attendanceStatus = 'PRESENT';

            // Jam acuan shift mulai
            $scheduledStart = Carbon::parse($workDate . ' ' . $schedule['default_start']);
            if ($clockInTime->gt($scheduledStart)) {
                $lateMinutes = $clockInTime->diffInMinutes($scheduledStart);
                if ($lateMinutes > 0) {
                    $attendanceStatus = 'LATE';
                }
            }

            if ($clockOutTime) {
                $workDurationMinutes = $clockOutTime->diffInMinutes($clockInTime);
                $breakMinutes = (int) ($schedule['break_minutes'] ?? 60);
                $workDurationMinutes = max(0, $workDurationMinutes - $breakMinutes);

                // Jam acuan shift selesai
                $scheduledEnd = $isCrossDay 
                    ? Carbon::parse($workDate)->addDay()->setTimeFromTimeString($schedule['default_end'])
                    : Carbon::parse($workDate . ' ' . $schedule['default_end']);

                if ($clockOutTime->lt($scheduledEnd)) {
                    $earlyLeaveMinutes = $scheduledEnd->diffInMinutes($clockOutTime);
                    if ($earlyLeaveMinutes > 0 && $attendanceStatus !== 'LATE') {
                        $attendanceStatus = 'EARLY_LEAVE';
                    }
                }
            }
        }

        // 2. Simpan atau perbarui AttendanceSession
        $pairingStatus = 'COMPLETE';
        if (!$clockInTime && !$clockOutTime) {
            $pairingStatus = 'INCOMPLETE_IN';
        } elseif ($clockInTime && !$clockOutTime) {
            $pairingStatus = 'INCOMPLETE_OUT';
        }

        $session = AttendanceSession::updateOrCreate(
            [
                'user_id'   => $userId,
                'work_date' => $workDate,
            ],
            [
                'shift_id'              => $shiftId,
                'clock_in'              => $clockInTime ? $clockInTime->toDateTimeString() : null,
                'clock_out'             => $clockOutTime ? $clockOutTime->toDateTimeString() : null,
                'clock_in_device'       => $clockInLog->Terminal_ID ?? 'GPS_MOBILE',
                'clock_out_device'      => $clockOutLog->Terminal_ID ?? 'GPS_MOBILE',
                'pairing_status'        => $pairingStatus,
                'work_duration_minutes' => $workDurationMinutes,
                'late_minutes'          => $lateMinutes,
                'early_leave_minutes'   => $earlyLeaveMinutes,
                'updated_by'            => $actor,
                'updated_at'            => now(),
            ]
        );

        // 3. Simpan atau perbarui AttendanceDailySummary
        $periode = (int) date('Ym', strtotime($workDate));

        return AttendanceDailySummary::updateOrCreate(
            [
                'user_id'   => $userId,
                'work_date' => $workDate,
            ],
            [
                'shift_id'            => $shiftId,
                'attendance_status'   => $attendanceStatus,
                'scheduled_minutes'   => (int) ($schedule['working_minutes'] ?? 480),
                'actual_work_minutes' => $workDurationMinutes,
                'late_minutes'        => $lateMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'is_cross_day'        => $isCrossDay,
                'is_holiday'          => $isHoliday,
                'periode'             => $periode,
                'calculated_at'       => now(),
                'updated_by'          => $actor,
                'updated_at'          => now(),
            ]
        );
    }
}
