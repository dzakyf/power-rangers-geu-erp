<?php

namespace App\Services\Payroll;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\Attendance\AttendanceCutoffService;
use Carbon\Carbon;

class PayrollAttendanceSyncService
{
    /**
     * @var AttendanceCutoffService
     */
    protected $cutoffService;

    public function __construct(AttendanceCutoffService $cutoffService)
    {
        $this->cutoffService = $cutoffService;
    }

    /**
     * Tarik dan Hitung Rekapitulasi Presensi, Cuti/Izin (Master Permission),
     * Overtime (Matrix & Legacy), serta KJK Karyawan untuk Periode Gaji Tertentu.
     *
     * @param string $nik NIK Karyawan
     * @param string $periodeYm Format YYYY-MM (Contoh: 2026-11)
     * @param array $options Opsi tambahan (department, cutoff_rule_id, dll.)
     * @return array
     */
    public function getEmployeeAttendanceForPeriod($nik, $periodeYm, $options = [])
    {
        $department = isset($options['department']) ? $options['department'] : null;

        // 1. Tentukan Rentang Tanggal Cutoff
        $cutoffRule = $this->cutoffService->resolveCutoffRule($nik, $department);
        $cutoffRange = $this->cutoffService->getCutoffPeriodRange($periodeYm, $cutoffRule);

        $startDate = $cutoffRange['start_date'];
        $endDate = $cutoffRange['end_date'];
        $todayStr = Carbon::now()->toDateString();

        // 2. Ambil Data Hari Libur Nasional & Cuti Bersama (master_work_calendar + libur + master_holiday)
        $holidays = $this->getHolidaysMap($startDate, $endDate, $department);

        // 3. Ambil Data Master Permission (kategori_izin) & Pemetaan Izin/Cuti Karyawan (attendance_leave_requests + izin + collective_leaves)
        $leaveMap = $this->resolveLeaveAndPermissionsMap($nik, $startDate, $endDate, $department);

        // 4. Ambil Data Jadwal Shift, Roster, & Perubahan/Tukar Shift (Change Shift / Exchange Shift dari Portal)
        $scheduleData = $this->resolveEmployeeSchedulesMap($nik, $startDate, $endDate);
        $schedulesMap = $scheduleData['schedules'];
        $changeShiftDaysCount = $scheduleData['change_shift_count'];

        // 5. Ambil Data Koreksi Absensi yang Sudah Disetujui (attendance_corrections)
        $correctionsMap = [];
        if (Schema::hasTable('attendance_corrections')) {
            $corrRows = DB::table('attendance_corrections')
                ->where('nik', $nik)
                ->where('status', 'APPROVED')
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->get();
            foreach ($corrRows as $corr) {
                $correctionsMap[$corr->attendance_date] = $corr;
            }
        }
        $approvedCorrectionsCount = count($correctionsMap);

        // 5. Ambil Log Kehadiran Aktual dari attendant_log (dan fallback attendance_logs / attendance_daily_summaries)
        $logsByDate = [];
        if (Schema::hasTable('attendant_log')) {
            $rawLogs = DB::table('attendant_log')
                ->where('NIK', $nik)
                ->whereBetween('Scan_Date', [$startDate, $endDate])
                ->get();

            foreach ($rawLogs as $rLog) {
                $dStr = $rLog->Scan_Date;
                if (!$dStr || $dStr === '0000-00-00') {
                    continue;
                }
                $logsByDate[$dStr] = (object)[
                    'date'          => $dStr,
                    'check_in'      => !empty($rLog->Start_Time) && $rLog->Start_Time !== '00:00:00' ? $rLog->Start_Time : null,
                    'check_out'     => !empty($rLog->End_Time) && $rLog->End_Time !== '00:00:00' ? $rLog->End_Time : null,
                    'status'        => strtoupper(trim((string)($rLog->Clock_In_Status ?? ''))),
                    'late_minutes'  => (int)($rLog->Late_Minutes ?? 0),
                    'early_minutes' => (int)($rLog->Early_Leave_Minutes ?? 0),
                    'ot_minutes'    => (int)($rLog->Overtime_Minutes ?? 0),
                    'category'      => strtoupper(trim((string)($rLog->Category ?? 'KERJA'))),
                ];
            }
        } elseif (Schema::hasTable('attendance_logs')) {
            $rawLogs = DB::table('attendance_logs')
                ->where('nik', $nik)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->get();

            foreach ($rawLogs as $rLog) {
                $dStr = $rLog->attendance_date;
                $logsByDate[$dStr] = (object)[
                    'date'          => $dStr,
                    'check_in'      => $rLog->check_in ?? null,
                    'check_out'     => $rLog->check_out ?? null,
                    'status'        => !empty($rLog->is_late) ? 'LATE' : 'ON_TIME',
                    'late_minutes'  => (int)($rLog->late_minutes ?? 0),
                    'early_minutes' => 0,
                    'ot_minutes'    => 0,
                    'category'      => 'KERJA',
                ];
            }
        }

        // Ambil ringkasan harian jika ada di attendance_daily_summaries
        $dailySummariesByDate = [];
        if (Schema::hasTable('attendance_daily_summaries')) {
            $sumRows = DB::table('attendance_daily_summaries')
                ->where('user_id', $nik)
                ->whereBetween('work_date', [$startDate, $endDate])
                ->get();
            foreach ($sumRows as $sRow) {
                $dailySummariesByDate[$sRow->work_date] = $sRow;
            }
        }

        $hasActiveAttendanceTracking = !empty($logsByDate) || !empty($dailySummariesByDate);

        // 6. Iterasi Hari demi Hari untuk Menghitung Alpha, Terlambat, Izin/Cuti, & Absensi Tidak Lengkap
        $totalScheduledDays = 0;
        $totalPresentDays = 0;
        $totalAlphaDays = 0;
        $totalLateDays = 0;
        $totalLateMinutes = 0;
        $totalLeavePaidDays = 0;
        $totalLeaveUnpaidDays = 0;
        $incompleteLogsCount = 0;
        $dailyBreakdown = [];

        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            $dayOfWeek = $current->dayOfWeekIso; // 1 (Senin) s/d 7 (Minggu)
            $isWeekend = ($dayOfWeek >= 6);      // Sabtu & Minggu default libur

            // Resolusi jadwal shift harian karyawan (mendukung Change Shift / Exchange Shift / Roster / Work Calendar)
            $sched = isset($schedulesMap[$dateStr]) ? $schedulesMap[$dateStr] : [
                'shift_code'   => 'OFFICE',
                'shift_name'   => 'Regular Office',
                'start_time'   => '08:00:00',
                'end_time'     => '17:00:00',
                'is_off'       => $isWeekend ? 1 : 0,
                'is_cross_day' => 0,
                'source_type'  => 'DEFAULT',
            ];

            $isShiftOff = !empty($sched['is_off']);
            $isHoliday = isset($holidays[$dateStr]);
            // Jika ada override shift aktif (misal Change Shift atau Roster), patuhi flag is_off shift tersebut;
            // jika jadwal default kalender, perhitungkan juga hari libur nasional
            if (in_array($sched['source_type'] ?? '', ['CHANGE_SHIFT', 'SHIFT_EXCHANGE_PEER', 'MANUAL_OVERRIDE', 'ROSTER'], true)) {
                $isWorkday = !$isShiftOff;
            } else {
                $isWorkday = (!$isShiftOff && !$isHoliday);
            }
            $isFutureDate = ($dateStr > $todayStr);

            if ($isWorkday) {
                $totalScheduledDays++;
            }

            $shiftStart = !empty($sched['start_time']) && $sched['start_time'] !== '00:00:00' ? $sched['start_time'] : '08:00:00';
            $shiftEnd = !empty($sched['end_time']) && $sched['end_time'] !== '00:00:00' ? $sched['end_time'] : '17:00:00';

            $log = isset($logsByDate[$dateStr]) ? $logsByDate[$dateStr] : null;
            $corr = isset($correctionsMap[$dateStr]) ? $correctionsMap[$dateStr] : null;
            $leaveInfo = isset($leaveMap[$dateStr]) ? $leaveMap[$dateStr] : null;
            $dailySum = isset($dailySummariesByDate[$dateStr]) ? $dailySummariesByDate[$dateStr] : null;

            // Terapkan koreksi absensi yang sudah di-approve (mendukung tipe IN, OUT, dan BOTH)
            if ($corr) {
                $corrType = strtoupper(trim((string)($corr->correction_type ?? 'BOTH')));
                if (!$log) {
                    $log = (object)[
                        'date'          => $dateStr,
                        'check_in'      => ($corrType !== 'OUT' && !empty($corr->corrected_clock_in)) ? $corr->corrected_clock_in : ($corr->original_clock_in ?: null),
                        'check_out'     => ($corrType !== 'IN' && !empty($corr->corrected_clock_out)) ? $corr->corrected_clock_out : ($corr->original_clock_out ?: null),
                        'status'        => 'ON_TIME',
                        'late_minutes'  => 0,
                        'early_minutes' => 0,
                        'ot_minutes'    => 0,
                        'category'      => 'KERJA',
                    ];
                } else {
                    if ($corrType !== 'OUT' && !empty($corr->corrected_clock_in)) {
                        $log->check_in = $corr->corrected_clock_in;
                    }
                    if ($corrType !== 'IN' && !empty($corr->corrected_clock_out)) {
                        $log->check_out = $corr->corrected_clock_out;
                    }
                }
            }

            // Terapkan juga jam clock-in / clock-out parsial jika ada pada pengajuan izin/cuti (attendance_leave_requests)
            if ($leaveInfo && $log) {
                if (empty($log->check_in) && !empty($leaveInfo['clock_in_time'])) {
                    $log->check_in = $leaveInfo['clock_in_time'];
                }
                if (empty($log->check_out) && !empty($leaveInfo['clock_out_time'])) {
                    $log->check_out = $leaveInfo['clock_out_time'];
                }
            }

            // Evaluasi ulang keterlambatan terhadap jadwal shift aktual (termasuk Change Shift / Koreksi Jam / Partial Leave)
            if ($log && !empty($log->check_in)) {
                if ($isShiftOff) {
                    $log->late_minutes = 0;
                    $log->status = 'ON_TIME';
                } else {
                    $effectiveExpectedStart = $shiftStart;
                    if ($leaveInfo && !empty($leaveInfo['clock_in_time'])) {
                        $effectiveExpectedStart = $leaveInfo['clock_in_time'];
                    }
                    $expectedCarbon = Carbon::parse($dateStr . ' ' . $effectiveExpectedStart);
                    $actualCarbon = Carbon::parse($dateStr . ' ' . $log->check_in);

                    if ($actualCarbon->gt($expectedCarbon)) {
                        $recalcLate = $actualCarbon->diffInMinutes($expectedCarbon);
                        $log->late_minutes = $recalcLate > 15 ? $recalcLate : 0;
                        $log->status = $recalcLate > 15 ? 'LATE' : 'ON_TIME';
                    } else {
                        $log->late_minutes = 0;
                        $log->status = 'ON_TIME';
                    }
                }
            }

            $dayStatus = 'OFF';
            $dayNotes = '';

            if ($log && (!empty($log->check_in) || !empty($log->check_out))) {
                $hasIn = !empty($log->check_in);
                $hasOut = !empty($log->check_out);

                // Jika tanggal hari ini, atau hari libur shift, atau ada koreksi/izin parsial yang disetujui
                $isComplete = ($hasIn && $hasOut)
                    || ($dateStr === $todayStr && $hasIn)
                    || $isShiftOff
                    || ($corr !== null);

                // Cek apakah ada dispensasi Lupa Absen (kat_izin_id = 3), Cepat Pulang (kat_izin_id = 2), atau Cuti/Izin Paid
                if (!$isComplete && $leaveInfo && in_array($leaveInfo['category'], ['CORRECTION_INCOMPLETE', 'CORRECTION_EARLY_OUT', 'LEAVE_PAID'], true)) {
                    $isComplete = true;
                    $dayNotes = 'Dispensasi: ' . $leaveInfo['label'];
                }

                if (!$isComplete) {
                    // Absen tidak lengkap (hanya clock-in atau hanya clock-out) -> dihitung Alpha sesuai kebijakan
                    $incompleteLogsCount++;
                    $dayStatus = 'INCOMPLETE';
                    $dayNotes = !$hasIn ? 'Tidak ada Clock-In' : 'Tidak ada Clock-Out';
                } else {
                    $totalPresentDays++;
                    $dayStatus = 'PRESENT';

                    $lateMins = (int)$log->late_minutes;
                    $isLate = ($log->status === 'LATE' || $lateMins > 15);

                    // Cek apakah ada izin Datang Telat (kat_izin_id = 1) atau jam clock_in_time parsial yang telah disetujui
                    if ($isLate && $leaveInfo && ($leaveInfo['category'] === 'CORRECTION_LATE' || !empty($leaveInfo['clock_in_time']))) {
                        $isLate = false;
                        $dayNotes = 'Dispensasi Terlambat: ' . $leaveInfo['label'];
                    }

                    if ($isLate) {
                        $totalLateDays++;
                        $totalLateMinutes += $lateMins;
                        $dayStatus = 'LATE';
                        $dayNotes = "Terlambat {$lateMins} menit (Shift {$sched['shift_name']})";
                    } elseif (!empty($sched['source_type']) && in_array($sched['source_type'], ['CHANGE_SHIFT', 'SHIFT_EXCHANGE_PEER'], true)) {
                        $dayNotes = "Hadir Sesuai Tukar/Ganti Shift ({$sched['shift_name']})";
                    }
                }
            } else {
                // Tidak ada log kehadiran pada tanggal ini
                if ($leaveInfo) {
                    if ($leaveInfo['category'] === 'LEAVE_UNPAID') {
                        $totalLeaveUnpaidDays++;
                        $dayStatus = 'LEAVE_UNPAID';
                    } else {
                        $totalLeavePaidDays++;
                        $dayStatus = 'LEAVE_PAID';
                    }
                    $dayNotes = $leaveInfo['label'];
                } elseif ($dailySum) {
                    $sumStatus = strtoupper(trim((string)$dailySum->attendance_status));
                    if (($sumStatus === 'ALPHA' || $sumStatus === 'UNPAID_LEAVE') && $isWorkday) {
                        $totalAlphaDays++;
                        $dayStatus = 'ALPHA';
                        $dayNotes = $dailySum->remarks ?: 'Alpha (Summary)';
                    } elseif (in_array($sumStatus, ['PRESENT', 'ON_TIME', 'LATE'])) {
                        $totalPresentDays++;
                        $dayStatus = 'PRESENT';
                        if ((int)$dailySum->late_minutes > 0 || $sumStatus === 'LATE') {
                            $totalLateDays++;
                            $totalLateMinutes += (int)$dailySum->late_minutes;
                            $dayStatus = 'LATE';
                        }
                    } elseif (!empty($dailySum->is_leave)) {
                        $totalLeavePaidDays++;
                        $dayStatus = 'LEAVE_PAID';
                    }
                } elseif ($isWorkday && !$isFutureDate) {
                    // Hari kerja yang sudah lewat tanpa log kehadiran & tanpa pengajuan izin/cuti/koreksi yang di-approve -> Alpha
                    $totalAlphaDays++;
                    $dayStatus = 'ALPHA';
                    $dayNotes = 'Tanpa Keterangan (Alpha)';
                } elseif ($isShiftOff && in_array($sched['source_type'] ?? '', ['CHANGE_SHIFT', 'SHIFT_EXCHANGE_PEER'], true)) {
                    $dayStatus = 'OFF_CHANGE_SHIFT';
                    $dayNotes = 'Libur Pengganti / Change Shift (' . ($sched['shift_name'] ?? 'OFF') . ')';
                } elseif ($isHoliday) {
                    $dayStatus = 'HOLIDAY';
                    $dayNotes = $holidays[$dateStr];
                }
            }

            $dailyBreakdown[$dateStr] = [
                'date'       => $dateStr,
                'shift_code' => $sched['shift_code'] ?? 'OFFICE',
                'shift_name' => $sched['shift_name'] ?? 'Regular Office',
                'shift_time' => $isShiftOff ? 'OFF' : (substr($shiftStart, 0, 5) . '-' . substr($shiftEnd, 0, 5)),
                'is_workday' => $isWorkday,
                'status'     => $dayStatus,
                'notes'      => $dayNotes,
            ];

            $current->addDay();
        }

        // Total hari potong gaji (Unpaid Leave / Alpha) = Alpha + Unpaid Leave + Absensi Tidak Lengkap
        $totalAbsentForDeduction = $totalAlphaDays + $totalLeaveUnpaidDays + $incompleteLogsCount;

        // 7. Tarik Data Lembur (Overtime) yang Sudah Disetujui (Matrix Portal + Legacy Overtime)
        $overtimeData = $this->resolveApprovedOvertimeForPeriod($nik, $periodeYm, $startDate, $endDate);

        // 8. Tarik Data KJK yang Sudah Disetujui (kjk_lists)
        $kjkData = $this->resolveApprovedKjkForPeriod($nik, $periodeYm, $startDate, $endDate);

        $summaryPayload = [
            'nik'                        => $nik,
            'periode_ym'                 => $periodeYm,
            'cutoff_start_date'          => $startDate,
            'cutoff_end_date'            => $endDate,
            'cutoff_label'               => $cutoffRange['formatted_label'],
            'total_scheduled_days'       => $totalScheduledDays,
            'total_present_days'         => $totalPresentDays,
            'total_alpha_days'           => $totalAlphaDays,
            'total_leave_paid_days'      => $totalLeavePaidDays,
            'total_leave_unpaid_days'    => $totalLeaveUnpaidDays,
            'incomplete_logs_count'      => $incompleteLogsCount,
            'total_absent_days'          => $totalAbsentForDeduction,
            'total_late_days'            => $totalLateDays,
            'total_late_minutes'         => $totalLateMinutes,
            'change_shift_days'          => $changeShiftDaysCount,
            'approved_corrections_count' => $approvedCorrectionsCount,
            'approved_overtime_hours'    => $overtimeData['total_hours'],
            'approved_overtime_amount'   => $overtimeData['total_amount'],
            'overtime_task_ids'          => $overtimeData['task_ids'],
            'approved_kjk_amount'        => $kjkData['total_amount'],
            'kjk_task_ids'               => $kjkData['task_ids'],
            'daily_breakdown'            => $dailyBreakdown,
        ];

        // 9. Sinkronkan snapshot ke tabel attendance_monthly_summaries (jika belum LOCKED)
        $this->syncMonthlySummarySnapshot($summaryPayload);

        return $summaryPayload;
    }

    /**
     * Sinkronkan hasil rekapitulasi presensi periode cutoff ke tabel attendance_monthly_summaries
     *
     * @param array $summary
     * @return void
     */
    protected function syncMonthlySummarySnapshot(array $summary)
    {
        if (!Schema::hasTable('attendance_monthly_summaries')) {
            return;
        }

        try {
            $nik = (string)$summary['nik'];
            $periodeInt = (int)preg_replace('/[^0-9]/', '', (string)$summary['periode_ym']);
            if ($periodeInt <= 0) {
                return;
            }

            $userCol = Schema::hasColumn('attendance_monthly_summaries', 'user_id') ? 'user_id' : 'nik';
            $existing = DB::table('attendance_monthly_summaries')
                ->where($userCol, $nik)
                ->where('periode', $periodeInt)
                ->first();

            if ($existing && isset($existing->payroll_post_status) && $existing->payroll_post_status === 'LOCKED') {
                return;
            }

            $now = Carbon::now()->toDateTimeString();
            $otMinutes = (int)round(((float)($summary['approved_overtime_hours'] ?? 0)) * 60);
            $workMinutes = ((int)($summary['total_present_days'] ?? 0)) * 480;

            $values = [
                'cutoff_start_date'      => $summary['cutoff_start_date'],
                'cutoff_end_date'        => $summary['cutoff_end_date'],
                'total_scheduled_days'   => (int)$summary['total_scheduled_days'],
                'total_present_days'     => (int)$summary['total_present_days'],
                'total_absent_days'      => (int)$summary['total_absent_days'],
                'total_late_days'        => (int)$summary['total_late_days'],
                'total_early_leave_days' => 0,
                'total_leave_days'       => (int)$summary['total_leave_paid_days'],
                'total_overtime_minutes' => $otMinutes,
                'total_late_minutes'     => (int)$summary['total_late_minutes'],
                'total_work_minutes'     => $workMinutes,
                'payroll_post_status'    => $existing ? $existing->payroll_post_status : 'DRAFT',
                'remarks'                => 'Rekap Periode Cutoff ' . $summary['cutoff_start_date'] . ' s/d ' . $summary['cutoff_end_date'],
                'calculated_at'          => $now,
                'updated_by'             => 'PAYROLL_SYNC',
                'updated_at'             => $now,
            ];

            if ($existing) {
                DB::table('attendance_monthly_summaries')
                    ->where('id', $existing->id)
                    ->update($values);
            } else {
                $values[$userCol] = $nik;
                $values['periode'] = $periodeInt;
                DB::table('attendance_monthly_summaries')->insert($values);
            }
        } catch (\Exception $e) {
            // Non-blocking jika ada perbedaan skema minor
        }
    }

    /**
     * Resolusi Jadwal Shift Harian Karyawan sepanjang Periode Cutoff.
     * Mengintegrasikan:
     * 1. work_calendars & work_calendar_details (jadwal mingguan default)
     * 2. employee_schedules + master_shifts (Roster / Manual Override)
     * 3. attendance_change_shifts (Pengajuan Tukar / Ganti Shift dari Portal yang sudah APPROVED,
     *    baik sebagai Pemohon [request_mode = CHANGE / EXCHANGE] maupun sebagai Rekan Pengganti [peer_nik]).
     *
     * @param string $nik
     * @param string $startDate
     * @param string $endDate
     * @return array ['schedules' => array, 'change_shift_count' => int]
     */
    protected function resolveEmployeeSchedulesMap($nik, $startDate, $endDate)
    {
        $schedules = [];
        $changeShiftDates = [];

        $user = null;
        if (Schema::hasTable('users')) {
            $user = DB::table('users')
                ->where('Emp_ID', $nik)
                ->orWhere('username', $nik)
                ->first();
        }
        $userIdMatch = $user ? $user->username : $nik;

        // 1. Ambil definisi Work Calendar Default
        $calendarDetailsByDay = [];
        if (Schema::hasTable('work_calendars') && Schema::hasTable('work_calendar_details')) {
            $calendarId = $user ? ($user->Work_Calendar_ID ?? null) : null;
            if (!$calendarId) {
                $defaultCal = DB::table('work_calendars')->where('Employee_Type', 'OFFICE')->first();
                if (!$defaultCal) {
                    $defaultCal = DB::table('work_calendars')->first();
                }
                $calendarId = $defaultCal ? $defaultCal->ID : null;
            }

            if ($calendarId) {
                $calDetails = DB::table('work_calendar_details')
                    ->where('Calendar_ID', $calendarId)
                    ->get();
                $hasAnyActiveWorkingDay = $calDetails->contains(function ($item) {
                    return (int)($item->Is_Working ?? 0) === 1 || !empty($item->Default_Start);
                });
                if ($hasAnyActiveWorkingDay) {
                    foreach ($calDetails as $cd) {
                        $calendarDetailsByDay[strtolower(trim($cd->Day_Of_Week))] = $cd;
                    }
                }
            }
        }

        // Inisialisasi jadwal dasar per tanggal dari Work Calendar
        $curr = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        while ($curr->lte($end)) {
            $dStr = $curr->toDateString();
            $dayNameLower = strtolower($curr->format('l'));
            $isWeekend = ($curr->dayOfWeekIso >= 6);

            $isOff = $isWeekend ? 1 : 0;
            $startT = '08:00:00';
            $endT = '17:00:00';

            if (isset($calendarDetailsByDay[$dayNameLower])) {
                $cd = $calendarDetailsByDay[$dayNameLower];
                $isOff = ((int)$cd->Is_Working === 1) ? 0 : 1;
                $startT = !empty($cd->Default_Start) ? $cd->Default_Start : '08:00:00';
                $endT = !empty($cd->Default_End) ? $cd->Default_End : '17:00:00';
            }

            $schedules[$dStr] = [
                'shift_id'     => null,
                'shift_code'   => $isOff ? 'OFF' : 'OFFICE',
                'shift_name'   => $isOff ? 'Libur Mingguan' : 'Regular Office',
                'start_time'   => $startT,
                'end_time'     => $endT,
                'is_off'       => $isOff,
                'is_cross_day' => 0,
                'source_type'  => 'CALENDAR_DEFAULT',
            ];

            $curr->addDay();
        }

        // Cache master_shifts
        $shiftsById = [];
        if (Schema::hasTable('master_shifts')) {
            foreach (DB::table('master_shifts')->get() as $ms) {
                $shiftsById[(int)$ms->id] = $ms;
            }
        }

        // 2. Override dengan employee_schedules (Roster / Manual Override)
        if (Schema::hasTable('employee_schedules') && !empty($shiftsById)) {
            $empScheds = DB::table('employee_schedules')
                ->where(function ($q) use ($nik, $userIdMatch) {
                    $q->where('user_id', $nik)
                      ->orWhere('user_id', $userIdMatch);
                })
                ->whereBetween('work_date', [$startDate, $endDate])
                ->get();

            foreach ($empScheds as $es) {
                $sId = (int)($es->shift_id ?? 0);
                if (isset($shiftsById[$sId])) {
                    $ms = $shiftsById[$sId];
                    $srcType = strtoupper(trim((string)($es->source_type ?? 'ROSTER')));
                    if ($srcType === 'MANUAL_OVERRIDE' && stripos((string)($es->notes ?? ''), 'Shift') !== false) {
                        $srcType = 'CHANGE_SHIFT';
                        $changeShiftDates[$es->work_date] = true;
                    }
                    $schedules[$es->work_date] = [
                        'shift_id'     => $ms->id,
                        'shift_code'   => $ms->shift_code,
                        'shift_name'   => $ms->shift_name,
                        'start_time'   => $ms->start_time ?: '08:00:00',
                        'end_time'     => $ms->end_time ?: '17:00:00',
                        'is_off'       => (int)($ms->is_off ?? 0),
                        'is_cross_day' => (int)($ms->is_cross_day ?? 0),
                        'source_type'  => $srcType,
                    ];
                }
            }
        }

        // 3. Override langsung dari attendance_change_shifts (Status = APPROVED)
        // Menjamin baik Pemohon (nik) maupun Rekan Tukar Shift (peer_nik) selalu tersinkronisasi ke Payroll
        if (Schema::hasTable('attendance_change_shifts') && !empty($shiftsById)) {
            // A. Sebagai Pemohon (nik)
            $ownChanges = DB::table('attendance_change_shifts')
                ->where('nik', $nik)
                ->where('status', 'APPROVED')
                ->whereBetween('original_date', [$startDate, $endDate])
                ->orderBy('id', 'asc')
                ->get();

            foreach ($ownChanges as $cs) {
                $targetShiftId = (int)($cs->target_shift_id ?? 0);
                if (isset($shiftsById[$targetShiftId])) {
                    $ms = $shiftsById[$targetShiftId];
                    $schedules[$cs->original_date] = [
                        'shift_id'        => $ms->id,
                        'shift_code'      => $ms->shift_code,
                        'shift_name'      => $ms->shift_name,
                        'start_time'      => $ms->start_time ?: '08:00:00',
                        'end_time'        => $ms->end_time ?: '17:00:00',
                        'is_off'          => (int)($ms->is_off ?? 0),
                        'is_cross_day'    => (int)($ms->is_cross_day ?? 0),
                        'source_type'     => 'CHANGE_SHIFT',
                        'change_shift_no' => $cs->change_shift_no,
                    ];
                    $changeShiftDates[$cs->original_date] = true;
                }
            }

            // B. Sebagai Rekan Kerja Pengganti pada mode EXCHANGE (peer_nik)
            $peerChanges = DB::table('attendance_change_shifts')
                ->where('peer_nik', $nik)
                ->where('request_mode', 'EXCHANGE')
                ->where('status', 'APPROVED')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('target_date', [$startDate, $endDate])
                      ->orWhereBetween('original_date', [$startDate, $endDate]);
                })
                ->orderBy('id', 'asc')
                ->get();

            foreach ($peerChanges as $pcs) {
                $peerDate = !empty($pcs->target_date) ? $pcs->target_date : $pcs->original_date;
                if ($peerDate < $startDate || $peerDate > $endDate) {
                    continue;
                }
                $peerShiftId = (int)($pcs->peer_target_shift_id ?: $pcs->current_shift_id);
                if (isset($shiftsById[$peerShiftId])) {
                    $ms = $shiftsById[$peerShiftId];
                    $schedules[$peerDate] = [
                        'shift_id'        => $ms->id,
                        'shift_code'      => $ms->shift_code,
                        'shift_name'      => $ms->shift_name,
                        'start_time'      => $ms->start_time ?: '08:00:00',
                        'end_time'        => $ms->end_time ?: '17:00:00',
                        'is_off'          => (int)($ms->is_off ?? 0),
                        'is_cross_day'    => (int)($ms->is_cross_day ?? 0),
                        'source_type'     => 'SHIFT_EXCHANGE_PEER',
                        'change_shift_no' => $pcs->change_shift_no,
                    ];
                    $changeShiftDates[$peerDate] = true;
                }
            }
        }

        return [
            'schedules'          => $schedules,
            'change_shift_count' => count($changeShiftDates),
        ];
    }

    /**
     * Resolusi & Sinkronisasi Cuti/Izin Karyawan dari Master Permission (kategori_izin),
     * Tabel Modern (attendance_leave_requests, attendance_collective_leaves), dan Tabel Legacy (izin).
     *
     * @param string $nik
     * @param string $startDate
     * @param string $endDate
     * @param string|null $department
     * @return array Map ['YYYY-MM-DD' => ['category' => string, 'code' => string, 'label' => string]]
     */
    protected function resolveLeaveAndPermissionsMap($nik, $startDate, $endDate, $department = null)
    {
        $byId = [];
        $byCode = [];
        $byName = [];

        if (Schema::hasTable('kategori_izin')) {
            $kats = DB::table('kategori_izin')->get();
            foreach ($kats as $k) {
                $byId[(int)$k->kat_izin_id] = $k;
                if (!empty($k->kat_izin_code)) {
                    $byCode[strtoupper(trim($k->kat_izin_code))] = $k;
                }
                if (!empty($k->kat_izin_nama)) {
                    $byName[strtoupper(trim($k->kat_izin_nama))] = $k;
                }
            }
        }

        $classifyLeave = function ($katObj, $rawTypeStr = '', $rawJenisId = null) {
            $katId = $katObj ? (int)$katObj->kat_izin_id : 0;
            $jenisId = $katObj ? (int)$katObj->izin_jenis_id : ($rawJenisId !== null ? (int)$rawJenisId : -1);
            $code = $katObj && !empty($katObj->kat_izin_code)
                ? strtoupper(trim($katObj->kat_izin_code))
                : strtoupper(trim((string)$rawTypeStr));
            $nama = $katObj && !empty($katObj->kat_izin_nama)
                ? trim($katObj->kat_izin_nama)
                : ($rawTypeStr ?: 'Izin / Cuti');

            // 1. Kategori Koreksi Absensi pada Master Permission (izin_jenis_id = 0)
            if ($katId === 1 || stripos($nama, 'DATANG TELAT') !== false) {
                return ['category' => 'CORRECTION_LATE', 'code' => $code ?: 'DATANG_TELAT', 'label' => $nama];
            }
            if ($katId === 2 || stripos($nama, 'CEPAT PULANG') !== false) {
                return ['category' => 'CORRECTION_EARLY_OUT', 'code' => $code ?: 'CEPAT_PULANG', 'label' => $nama];
            }
            if ($katId === 3 || stripos($nama, 'LUPA ABSEN') !== false) {
                return ['category' => 'CORRECTION_INCOMPLETE', 'code' => $code ?: 'LUPA_ABSEN', 'label' => $nama];
            }

            // 2. Kategori Alpha / Unpaid Leave (Potong Gaji / Unpaid)
            $unpaidCodes = [
                'ALPHA_TIDAK_HADIR',
                'ALPHA',
                'UNPAID_LEAVE',
                'CUTI_DILUAR_TANGGUNGAN',
                'IJIN_TIDAK_DIBAYAR',
                'IZIN_TIDAK_DIBAYAR',
            ];
            if (
                $jenisId === 4
                || in_array($code, $unpaidCodes, true)
                || stripos($nama, 'ALPHA') !== false
                || stripos($nama, 'UNPAID') !== false
                || stripos($nama, 'DILUAR TANGGUNGAN') !== false
                || stripos($nama, 'TIDAK DIBAYAR') !== false
            ) {
                return ['category' => 'LEAVE_UNPAID', 'code' => $code ?: 'ALPHA_TIDAK_HADIR', 'label' => $nama];
            }

            // 3. Seluruh Kategori Cuti, Izin Sakit, Izin Pribadi, & Dinas Luar Lainnya di Master Permission -> Paid Leave
            return ['category' => 'LEAVE_PAID', 'code' => $code ?: 'LEAVE_PAID', 'label' => $nama];
        };

        $leaveMap = [];

        // A. Baca dari tabel modern attendance_leave_requests (Status = APPROVED)
        if (Schema::hasTable('attendance_leave_requests')) {
            $leaves = DB::table('attendance_leave_requests')
                ->where('nik', $nik)
                ->where('status', 'APPROVED')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                      });
                })
                ->get();

            foreach ($leaves as $lv) {
                $rawType = trim((string)($lv->leave_type ?? ''));
                $upperType = strtoupper($rawType);
                $katObj = null;
                if (isset($byCode[$upperType])) {
                    $katObj = $byCode[$upperType];
                } elseif (isset($byName[$upperType])) {
                    $katObj = $byName[$upperType];
                } elseif (is_numeric($rawType) && isset($byId[(int)$rawType])) {
                    $katObj = $byId[(int)$rawType];
                }

                $classified = $classifyLeave($katObj, $rawType);
                $classified['clock_in_time'] = !empty($lv->clock_in_time) ? $lv->clock_in_time : null;
                $classified['clock_out_time'] = !empty($lv->clock_out_time) ? $lv->clock_out_time : null;

                $lStart = Carbon::parse(max($startDate, $lv->start_date));
                $lEnd = Carbon::parse(min($endDate, $lv->end_date));
                while ($lStart->lte($lEnd)) {
                    $leaveMap[$lStart->toDateString()] = $classified;
                    $lStart->addDay();
                }
            }
        }

        // B. Baca dari tabel legacy izin (izin_status = 1 -> Approved)
        if (Schema::hasTable('izin')) {
            $legacyIzins = DB::table('izin')
                ->where('NIK', $nik)
                ->where('izin_status', 1)
                ->whereBetween('izin_tgl', [$startDate, $endDate])
                ->get();

            foreach ($legacyIzins as $iz) {
                $dStr = $iz->izin_tgl;
                if (!$dStr || isset($leaveMap[$dStr])) {
                    continue;
                }
                $katId = isset($iz->kat_izin_id) ? (int)$iz->kat_izin_id : 0;
                $katObj = isset($byId[$katId]) ? $byId[$katId] : null;
                $fallbackText = $iz->izin_ket_lain ?: ($iz->izin_catatan ?: 'Izin');

                $leaveMap[$dStr] = $classifyLeave($katObj, $fallbackText, $iz->izin_jenis_id ?? null);
            }
        }

        // C. Baca dari tabel Cuti Bersama (attendance_collective_leaves) jika ada batch aktif
        if (Schema::hasTable('attendance_collective_leaves')) {
            $emp = DB::table('employment')->where('NIK', $nik)->first();
            $empLoc = $emp ? ($emp->Location ?? null) : null;
            $empDept = $emp ? ($emp->Department ?? $department) : $department;

            $colLeaves = DB::table('attendance_collective_leaves')
                ->whereIn('status', ['APPLIED', 'APPROVED', 'ACTIVE'])
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate)
                ->get();

            foreach ($colLeaves as $cl) {
                $scope = strtoupper(trim((string)($cl->target_scope ?? 'ALL')));
                $targetVal = trim((string)($cl->target_value ?? 'ALL'));
                if ($scope === 'LOCATION' && $targetVal !== 'ALL' && $empLoc && strcasecmp($empLoc, $targetVal) !== 0) {
                    continue;
                }
                if ($scope === 'DEPARTMENT' && $targetVal !== 'ALL' && $empDept && strcasecmp($empDept, $targetVal) !== 0) {
                    continue;
                }

                $cStart = Carbon::parse(max($startDate, $cl->start_date));
                $cEnd = Carbon::parse(min($endDate, $cl->end_date));
                while ($cStart->lte($cEnd)) {
                    $dStr = $cStart->toDateString();
                    if (!isset($leaveMap[$dStr])) {
                        $leaveMap[$dStr] = [
                            'category' => 'LEAVE_PAID',
                            'code'     => 'CUTI_BERSAMA',
                            'label'    => $cl->title ?: 'Cuti Bersama',
                        ];
                    }
                    $cStart->addDay();
                }
            }
        }

        return $leaveMap;
    }

    /**
     * Tarik dan Hitung Total Nominal & Jam Lembur (Overtime) yang Disetujui
     * Terintegrasi dengan Portal Overtime Matrix (attendance_overtime_requests) & Menu HRD Overtime (overtime_lists)
     *
     * @param string $nik
     * @param string $periodeYm
     * @param string $startDate
     * @param string $endDate
     * @return array ['total_hours' => float, 'total_amount' => float, 'task_ids' => array]
     */
    public function resolveApprovedOvertimeForPeriod($nik, $periodeYm, $startDate, $endDate)
    {
        $totalHours = 0.0;
        $totalAmount = 0.0;
        $taskIds = [];
        $processedOvertimeNos = [];
        $processedLegacyIds = [];

        // 1. Cek dari tabel modern attendance_overtime_requests (Hasil kalkulasi OvertimeMatrixService)
        if (Schema::hasTable('attendance_overtime_requests')) {
            $modernRows = DB::table('attendance_overtime_requests')
                ->where('nik', $nik)
                ->where('status', 'APPROVED')
                ->where(function ($q) use ($periodeYm, $startDate, $endDate) {
                    $q->where('salary_period', $periodeYm)
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->where(function ($s) {
                              $s->whereNull('salary_period')->orWhere('salary_period', '');
                          })
                          ->whereBetween('overtime_date', [$startDate, $endDate]);
                      });
                })
                ->get();

            foreach ($modernRows as $ot) {
                if (!empty($ot->overtime_no)) {
                    $processedOvertimeNos[$ot->overtime_no] = true;
                }
                if (!empty($ot->legacy_overtime_id)) {
                    $processedLegacyIds[(int)$ot->legacy_overtime_id] = true;
                }

                $compType = strtoupper(trim((string)($ot->compensation_type ?? 'PAID')));
                if ($compType === 'TIME_OFF') {
                    // Kompensasi Tukar Hari Libur (Day Off) -> nominal uang = 0
                    continue;
                }

                $mins = isset($ot->duration_minutes) ? (int)$ot->duration_minutes : 0;
                $hrs = isset($ot->total_hours) ? (float)$ot->total_hours : round($mins / 60, 2);
                $amt = isset($ot->calculated_amount)
                    ? (float)$ot->calculated_amount
                    : (isset($ot->total_calculated_amount) ? (float)$ot->total_calculated_amount : 0.0);

                $totalHours += $hrs;
                $totalAmount += $amt;
                if (!empty($ot->overtime_no)) {
                    $taskIds[] = $ot->overtime_no;
                }
            }
        }

        // 2. Cek dari tabel HRD Overtime (overtime_lists) dimana Submit_Approval = 1 (Approved)
        if (Schema::hasTable('overtime_lists')) {
            $legacyRows = DB::table('overtime_lists')
                ->where('NIK', $nik)
                ->where('Submit_Approval', 1)
                ->where(function ($q) use ($periodeYm, $startDate, $endDate) {
                    $q->where('Salary_Period', $periodeYm)
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->where(function ($s) {
                              $s->whereNull('Salary_ID')->orWhere('Salary_ID', '');
                          })
                          ->whereBetween('Start_Date', [$startDate, $endDate]);
                      });
                })
                ->get();

            foreach ($legacyRows as $ov) {
                $id = (int)$ov->ID;
                $tId = trim((string)($ov->Task_ID ?? ''));
                $ref = trim((string)($ov->Reference ?? ''));

                // Hindari double counting jika record ini sudah dihitung dari attendance_overtime_requests
                if (
                    isset($processedLegacyIds[$id])
                    || ($tId !== '' && isset($processedOvertimeNos[$tId]))
                    || ($ref !== '' && isset($processedOvertimeNos[$ref]))
                ) {
                    continue;
                }

                $totalHours += (float)($ov->Total_OT ?? 0);
                $totalAmount += (float)($ov->Amounts ?? 0);
                if ($tId !== '') {
                    $taskIds[] = $tId;
                }
            }
        }

        return [
            'total_hours'  => round($totalHours, 2),
            'total_amount' => round($totalAmount, 2),
            'task_ids'     => array_values(array_unique($taskIds)),
        ];
    }

    /**
     * Tarik dan Hitung Total Nominal KJK yang Disetujui dari Tabel kjk_lists
     * (Menu resources/views/humanresources/overtime/new-kjk & kjk-approval)
     *
     * @param string $nik
     * @param string $periodeYm
     * @param string $startDate
     * @param string $endDate
     * @return array ['total_amount' => float, 'task_ids' => array]
     */
    public function resolveApprovedKjkForPeriod($nik, $periodeYm, $startDate, $endDate)
    {
        $totalAmount = 0.0;
        $taskIds = [];

        if (Schema::hasTable('kjk_lists')) {
            // Di kjk-approval-update.blade.php, Submit_Approval = 2 adalah Approved (dan fallback 1 jika belum ada salary_id)
            $kjkRows = DB::table('kjk_lists')
                ->where('NIK', $nik)
                ->whereIn('Submit_Approval', [1, 2])
                ->where(function ($q) use ($periodeYm) {
                    $q->where('Salary_Period', $periodeYm)
                      ->orWhere(function ($sub) use ($periodeYm) {
                          $sub->where(function ($s) {
                              $s->whereNull('Salary_ID')->orWhere('Salary_ID', '');
                          })
                          ->where(function ($p) use ($periodeYm) {
                              $p->where('KJK_Period', $periodeYm)
                                ->orWhere('KJK_Period', 'LIKE', $periodeYm . '%')
                                ->orWhereNull('KJK_Period')
                                ->orWhere('KJK_Period', '');
                          });
                      });
                })
                ->get();

            foreach ($kjkRows as $kjk) {
                $totalAmount += (float)($kjk->Total ?? 0);
                if (!empty($kjk->Task_ID)) {
                    $taskIds[] = $kjk->Task_ID;
                }
            }
        }

        return [
            'total_amount' => round($totalAmount, 2),
            'task_ids'     => array_values(array_unique($taskIds)),
        ];
    }

    /**
     * Ambil Daftar Tanggal Merah / Hari Libur Nasional & Cuti Bersama
     * Terintegrasi dengan master_work_calendar, libur (hrd-master-holiday), dan master_holiday
     */
    protected function getHolidaysMap($startDate, $endDate, $department = null)
    {
        $map = [];

        // 1. Tabel libur (digunakan oleh menu hrd-master-holiday & permission-new)
        if (Schema::hasTable('libur')) {
            $liburRows = DB::table('libur')
                ->whereBetween('libur_tgl', [$startDate, $endDate])
                ->get();
            foreach ($liburRows as $l) {
                $map[$l->libur_tgl] = $l->libur_keterangan ?: ($l->libur_status == 2 ? 'Cuti Bersama' : 'Hari Libur Nasional');
            }
        }

        // 2. Tabel master_work_calendar
        if (Schema::hasTable('master_work_calendar')) {
            $query = DB::table('master_work_calendar')
                ->whereBetween('calendar_date', [$startDate, $endDate])
                ->where('is_holiday', 1);

            if ($department) {
                $query->where(function ($q) use ($department) {
                    $q->whereNull('department')
                      ->orWhere('department', 'ALL')
                      ->orWhere('department', $department);
                });
            }

            foreach ($query->get() as $r) {
                $map[$r->calendar_date] = $r->description ?: 'Hari Libur Nasional';
            }
        }

        // 3. Tabel master_holiday (fallback)
        if (Schema::hasTable('master_holiday')) {
            $mhRows = DB::table('master_holiday')
                ->whereBetween('Holiday_Date', [$startDate, $endDate])
                ->get();
            foreach ($mhRows as $mh) {
                $dStr = substr((string)$mh->Holiday_Date, 0, 10);
                $map[$dStr] = $mh->Description ?: 'Hari Libur';
            }
        }

        return $map;
    }

    /**
     * Kunci / Freeze Data Presensi Saat Payroll Diposting / Disetujui
     *
     * @param string $periodeYm Format YYYY-MM
     * @param string $lockStatus 'LOCKED' atau 'POSTED'
     * @param string $lockedBy Username pengeksekusi
     * @param array $nikList Daftar NIK yang dikunci (kosong = semua)
     * @return int Jumlah record yang dikunci
     */
    public function lockAttendancePeriod($periodeYm, $lockStatus = 'LOCKED', $lockedBy = 'SYSTEM', $nikList = [])
    {
        if (!Schema::hasTable('attendance_monthly_summaries')) {
            return 0;
        }

        // Normalisasi format periode: '2026-11' -> 202611 (unsignedInteger pada skema attendance_monthly_summaries)
        $periodeInt = (int)preg_replace('/[^0-9]/', '', (string)$periodeYm);
        $periodeVariants = array_values(array_unique(array_filter([
            $periodeInt > 0 ? $periodeInt : null,
            trim((string)$periodeYm),
        ])));

        $query = DB::table('attendance_monthly_summaries');
        if (!empty($periodeVariants)) {
            $query->whereIn('periode', $periodeVariants);
        }

        // Kolom identitas karyawan pada attendance_monthly_summaries adalah 'user_id' (dengan fallback 'nik' jika ada)
        $userCol = Schema::hasColumn('attendance_monthly_summaries', 'user_id')
            ? 'user_id'
            : (Schema::hasColumn('attendance_monthly_summaries', 'nik') ? 'nik' : null);

        if (!empty($nikList) && $userCol) {
            $targetIds = array_map('strval', $nikList);
            if (Schema::hasTable('users')) {
                $usernames = DB::table('users')
                    ->whereIn('Emp_ID', $nikList)
                    ->pluck('username')
                    ->filter()
                    ->toArray();
                $usernames = array_map('strval', $usernames);
                $targetIds = array_values(array_unique(array_merge($targetIds, $usernames)));
            }
            $query->whereIn($userCol, $targetIds);
        }

        $now = Carbon::now()->toDateTimeString();
        $updatePayload = [];

        if (Schema::hasColumn('attendance_monthly_summaries', 'payroll_post_status')) {
            $updatePayload['payroll_post_status'] = $lockStatus;
        }
        if (Schema::hasColumn('attendance_monthly_summaries', 'payroll_lock_status')) {
            $updatePayload['payroll_lock_status'] = $lockStatus;
        }
        if (Schema::hasColumn('attendance_monthly_summaries', 'payroll_posted_at')) {
            $updatePayload['payroll_posted_at'] = $now;
        }
        if (Schema::hasColumn('attendance_monthly_summaries', 'locked_at')) {
            $updatePayload['locked_at'] = $now;
        }
        if (Schema::hasColumn('attendance_monthly_summaries', 'updated_by')) {
            $updatePayload['updated_by'] = $lockedBy;
        }
        if (Schema::hasColumn('attendance_monthly_summaries', 'locked_by')) {
            $updatePayload['locked_by'] = $lockedBy;
        }
        if (Schema::hasColumn('attendance_monthly_summaries', 'updated_at')) {
            $updatePayload['updated_at'] = $now;
        }

        if (empty($updatePayload)) {
            return 0;
        }

        return $query->update($updatePayload);
    }
}
