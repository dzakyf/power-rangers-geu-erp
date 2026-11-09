<?php

namespace App\Services\Payroll;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\AttendanceCutoffService;

class PayrollAttendanceSyncService
{
    /**
     * @var AttendanceService
     */
    protected $attendanceService;

    /**
     * @var AttendanceCutoffService
     */
    protected $cutoffService;

    public function __construct(AttendanceService $attendanceService, AttendanceCutoffService $cutoffService)
    {
        $this->attendanceService = $attendanceService;
        $this->cutoffService = $cutoffService;
    }

    /**
     * Ambil Rekapitulasi Presensi Karyawan untuk Periode Payroll Tertentu (Berdasarkan Siklus Cutoff)
     * Termasuk evaluasi ketat absensi tidak lengkap (hanya in/out atau di luar shift) yang dianggap ALPHA.
     *
     * @param string $nik NIK Karyawan
     * @param string $periodeYm Format YYYY-MM atau YYYYMM (misal '2026-11' atau 202611)
     * @param array $options Opsi tambahan (departemen, dll.)
     * @return array
     */
    public function getEmployeeAttendanceForPeriod($nik, $periodeYm, $options = [])
    {
        $cleanPeriode = str_replace('-', '', $periodeYm);
        $userDept = isset($options['department']) ? $options['department'] : null;

        if (!$userDept) {
            $user = DB::table('users')->where('Emp_ID', $nik)->first();
            $userDept = $user ? $user->Department : null;
        }

        // 1. Resolusi Aturan Cutoff Karyawan
        $cutoffRule = $this->cutoffService->resolveCutoffRule($nik, $userDept);
        $cutoffRange = $this->cutoffService->getCutoffPeriodRange($cleanPeriode, $cutoffRule);

        $startDate = $cutoffRange['start_date'];
        $endDate = $cutoffRange['end_date'];

        $dtStart = Carbon::parse($startDate);
        $dtEnd = Carbon::parse($endDate);

        // 2. Ambil Semua Log Presensi Karyawan dalam Rentang Cutoff
        $logs = DB::table('attendant_log')
            ->where('NIK', $nik)
            ->whereBetween('Scan_Date', [$startDate, $endDate])
            ->get()
            ->keyBy('Scan_Date');

        // 3. Ambil Semua Cuti / Izin Disetujui dalam Rentang Cutoff
        $leaves = [];
        if (Schema::hasTable('attendance_leave_requests')) {
            $leaves = DB::table('attendance_leave_requests')
                ->where('nik', $nik)
                ->where('status', 'APPROVED')
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate)
                ->get();
        }

        // 4. Inisialisasi Akumulator Rekapitulasi
        $totalScheduledDays = 0;
        $totalPresentDays = 0;
        $totalAbsentDays = 0; // Alpha / Mangkir / Tanpa Keterangan
        $totalLateDays = 0;    // Berapa kali terlambat (frekuensi)
        $totalLateMinutes = 0;
        $totalLeavePaidDays = 0;
        $totalUnpaidLeaveDays = 0;
        $totalOvertimeMinutes = 0;
        $incompleteLogsCount = 0;
        $dailyBreakdown = [];

        $currDate = $dtStart->copy();
        while ($currDate->lte($dtEnd)) {
            $dateStr = $currDate->toDateString();
            $schedule = $this->attendanceService->getWorkScheduleForDate($nik, $dateStr);
            $isWorkingDay = ($schedule['is_working'] == 1);

            if ($isWorkingDay) {
                $totalScheduledDays++;
            }

            // Cek apakah tanggal ini ter-cover permohonan cuti / izin
            $matchingLeave = null;
            foreach ($leaves as $l) {
                if ($dateStr >= $l->start_date && $dateStr <= $l->end_date) {
                    $matchingLeave = $l;
                    break;
                }
            }

            $log = $logs->get($dateStr);
            $statusDay = 'UNKNOWN';
            $isAbsentAlpha = false;
            $isLate = false;
            $lateMinutes = 0;
            $note = '';

            if (!$isWorkingDay) {
                $statusDay = 'LIBUR / OFF';
            } elseif ($matchingLeave) {
                // Ada permohonan izin/cuti resmi
                $leaveType = strtoupper(trim($matchingLeave->leave_type ?? ''));
                if (in_array($leaveType, ['UNPAID_LEAVE', 'CUTI_DILUAR_TANGGUNGAN', 'IJIN_TIDAK_DIBAYAR'])) {
                    $totalUnpaidLeaveDays++;
                    $totalAbsentDays++;
                    $isAbsentAlpha = true;
                    $statusDay = 'UNPAID_LEAVE_APPROVED';
                    $note = 'Izin Tidak Dibayar Resmi';
                } else {
                    $totalLeavePaidDays++;
                    $statusDay = 'LEAVE_PAID (' . $leaveType . ')';
                    $note = $matchingLeave->reason ?? '';
                }
            } else {
                // Hari kerja tanpa permohonan cuti yang disetujui: Evaluasi Log Presensi
                if (!$log) {
                    // Kasus 1: Tidak ada presensi sama sekali pada hari kerja
                    $totalAbsentDays++;
                    $isAbsentAlpha = true;
                    $statusDay = 'ALPHA_NO_LOG';
                    $note = 'Tanpa Keterangan (Tidak Ada Log)';
                } else {
                    $clockIn = !empty($log->Start_Time) ? trim($log->Start_Time) : null;
                    $clockOut = !empty($log->End_Time) ? trim($log->End_Time) : null;

                    // Evaluasi absensi tidak lengkap (Hanya Clock In ATAU Hanya Clock Out)
                    $isIncomplete = (empty($clockIn) || empty($clockOut) || $clockIn === '--:--' || $clockOut === '--:--');

                    // Evaluasi perbedaan hari / log di luar shifting (mismatched shift cross-day)
                    $isOutsideShift = false;
                    if (!empty($log->Shift_1) && $log->Shift_1 === 'OUTSIDE_SHIFT') {
                        $isOutsideShift = true;
                    }

                    if ($isIncomplete || $isOutsideShift) {
                        // KEBUTUHAN KHUSUS: Absensi tidak lengkap atau di luar shift dianggap ALPHA / Tanpa Keterangan!
                        $totalAbsentDays++;
                        $incompleteLogsCount++;
                        $isAbsentAlpha = true;
                        $statusDay = $isIncomplete ? 'ALPHA_INCOMPLETE_PRESENCE' : 'ALPHA_OUTSIDE_SHIFT';
                        $note = $isIncomplete ? 'Absensi Tidak Lengkap (Hanya In/Out)' : 'Presensi Di Luar Shifting';
                    } else {
                        // Presensi Lengkap (Hadir)
                        $totalPresentDays++;
                        $statusDay = 'HADIR';

                        // Evaluasi Keterlambatan
                        $lateMinutes = (int)($log->Late_Minutes ?? 0);
                        $statusClockIn = strtoupper(trim($log->Clock_In_Status ?? ''));

                        if ($lateMinutes > 0 || $statusClockIn === 'LATE') {
                            $isLate = true;
                            $totalLateDays++;
                            $totalLateMinutes += $lateMinutes;
                            $note = 'Terlambat ' . $lateMinutes . ' menit';
                        }
                    }

                    // Akumulasi lembur dari log harian
                    if (!empty($log->Overtime_Minutes) && (int)$log->Overtime_Minutes > 0) {
                        $totalOvertimeMinutes += (int)$log->Overtime_Minutes;
                    }
                }
            }

            $dailyBreakdown[] = [
                'date'           => $dateStr,
                'is_working_day' => $isWorkingDay,
                'status'         => $statusDay,
                'is_alpha'       => $isAbsentAlpha,
                'is_late'        => $isLate,
                'late_minutes'   => $lateMinutes,
                'clock_in'       => $log ? ($log->Start_Time ?? null) : null,
                'clock_out'      => $log ? ($log->End_Time ?? null) : null,
                'note'           => $note,
            ];

            $currDate->addDay();
        }

        // 5. Cek Lembur Disetujui dari attendance_overtime_requests jika tersedia
        $approvedOvertimeAmount = 0.0;
        if (Schema::hasTable('attendance_overtime_requests')) {
            $otRequests = DB::table('attendance_overtime_requests')
                ->where('nik', $nik)
                ->where('status', 'APPROVED')
                ->whereBetween('overtime_date', [$startDate, $endDate])
                ->get();

            foreach ($otRequests as $ot) {
                if (isset($ot->total_calculated_amount)) {
                    $approvedOvertimeAmount += (float)$ot->total_calculated_amount;
                }
            }
        }

        return [
            'nik'                     => $nik,
            'periode'                 => $cleanPeriode,
            'cutoff_start_date'       => $startDate,
            'cutoff_end_date'         => $endDate,
            'cutoff_label'            => $cutoffRange['formatted_label'] ?? ($startDate . ' s/d ' . $endDate),
            'total_scheduled_days'    => $totalScheduledDays,
            'total_present_days'      => $totalPresentDays,
            'total_absent_days'       => $totalAbsentDays, // Total Alpha (termasuk tidak lengkap & luar shift)
            'total_late_days'         => $totalLateDays,   // Total Frekuensi Terlambat
            'total_late_minutes'      => $totalLateMinutes,
            'total_leave_paid_days'   => $totalLeavePaidDays,
            'total_unpaid_leave_days' => $totalUnpaidLeaveDays,
            'incomplete_logs_count'   => $incompleteLogsCount,
            'total_overtime_minutes'  => $totalOvertimeMinutes,
            'approved_overtime_amount'=> $approvedOvertimeAmount,
            'daily_breakdown'         => $dailyBreakdown,
        ];
    }

    /**
     * Kunci Status Presensi Bulanan (Data Freezing) Saat Payroll Dibuat / Disetujui
     *
     * @param string $periodeYm
     * @param string $newStatus 'POSTED' | 'LOCKED'
     * @param string $updatedBy Username admin
     * @param array $nikList Daftar NIK yang dikunci (kosong jika semua)
     * @return int Jumlah row yang di-update
     */
    public function lockAttendancePeriod($periodeYm, $newStatus = 'LOCKED', $updatedBy = 'SYSTEM', $nikList = [])
    {
        $cleanPeriode = (int)str_replace('-', '', $periodeYm);

        if (!Schema::hasTable('attendance_monthly_summaries')) {
            return 0;
        }

        $query = DB::table('attendance_monthly_summaries')
            ->where('periode', $cleanPeriode);

        if (!empty($nikList)) {
            $query->whereIn('user_id', $nikList);
        }

        return $query->update([
            'payroll_post_status' => $newStatus,
            'payroll_posted_at'   => Carbon::now()->toDateTimeString(),
            'updated_by'          => $updatedBy,
            'updated_at'          => Carbon::now()->toDateTimeString(),
        ]);
    }
}

