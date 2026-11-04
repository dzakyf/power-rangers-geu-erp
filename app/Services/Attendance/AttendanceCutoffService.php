<?php

namespace App\Services\Attendance;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class AttendanceCutoffService
{
    /**
     * Resolusi aturan cutoff yang berlaku untuk karyawan atau departemen tertentu.
     * Prioritas:
     * 1. EMPLOYEE (berdasarkan NIK karyawan)
     * 2. DEPARTMENT (berdasarkan Departemen karyawan)
     * 3. EMPLOYEE_TYPE (berdasarkan tipe kerja karyawan, cth: SITE / OFFICE)
     * 4. GLOBAL (Default aktif)
     */
    public function resolveCutoffRule($employeeNik = null, $department = null, $employeeType = null)
    {
        if (!Schema::hasTable('master_attendance_cutoff')) {
            return $this->fallbackDefaultRule();
        }

        $query = DB::table('master_attendance_cutoff')->where('status', 'ACTIVE');

        // Jika ada employeeNik, cek target EMPLOYEE
        if (!empty($employeeNik)) {
            $ruleEmp = (clone $query)->where('target_type', 'EMPLOYEE')->where('target_value', $employeeNik)->first();
            if ($ruleEmp) {
                return $ruleEmp;
            }

            // Dapatkan detail employee jika department/employeeType belum diketahui
            if (empty($department) || empty($employeeType)) {
                $user = DB::table('users')->where('Emp_ID', $employeeNik)->first();
                if ($user) {
                    $department = $department ?: $user->Department;
                    $employeeType = $employeeType ?: ($user->Employee_Type ?? 'OFFICE');
                }
            }
        }

        // Cek target DEPARTMENT
        if (!empty($department) && $department !== 'ALL') {
            $ruleDept = (clone $query)->where('target_type', 'DEPARTMENT')->where('target_value', $department)->first();
            if ($ruleDept) {
                return $ruleDept;
            }
        }

        // Cek target EMPLOYEE_TYPE
        if (!empty($employeeType)) {
            $ruleType = (clone $query)->where('target_type', 'EMPLOYEE_TYPE')->where('target_value', $employeeType)->first();
            if ($ruleType) {
                return $ruleType;
            }
        }

        // Fallback ke GLOBAL default
        $ruleGlobal = (clone $query)->where('is_default', 1)->first();
        if (!$ruleGlobal) {
            $ruleGlobal = (clone $query)->where('target_type', 'GLOBAL')->first();
        }

        return $ruleGlobal ?: $this->fallbackDefaultRule();
    }

    /**
     * Fallback konfigurasi cutoff jika tabel belum dibuat atau data kosong.
     */
    protected function fallbackDefaultRule()
    {
        return (object) [
            'id'           => 0,
            'cutoff_code'  => 'DEFAULT_21_20',
            'name'         => 'Cutoff Standar (21 s/d 20 Bulan Berjalan)',
            'start_day'    => 21,
            'end_day'      => 20,
            'cutoff_cycle' => 'PREV_TO_CURRENT',
            'target_type'  => 'GLOBAL',
            'target_value' => null,
            'is_default'   => 1,
            'status'       => 'ACTIVE',
        ];
    }

    /**
     * Hitung rentang tanggal (start_date, end_date) untuk sebuah Periode Bulan (format Y-m, misal '2026-11').
     *
     * Contoh:
     * Periode '2026-11', start_day=21, end_day=20:
     * Maka:
     * - start_date = 2026-10-21 (21 Oktober)
     * - end_date   = 2026-11-20 (20 November)
     *
     * Contoh 2:
     * Periode '2026-11', start_day=26, end_day=25:
     * Maka:
     * - start_date = 2026-10-26
     * - end_date   = 2026-11-25
     */
    public function getCutoffPeriodRange($periodeMonth, $cutoffRule = null): array
    {
        if (empty($periodeMonth)) {
            $periodeMonth = date('Y-m');
        }

        // Normalize periodeMonth (e.g. 202611 or 2026-11)
        if (strpos($periodeMonth, '-') === false && strlen($periodeMonth) === 6) {
            $periodeMonth = substr($periodeMonth, 0, 4) . '-' . substr($periodeMonth, 4, 2);
        }

        if (is_numeric($cutoffRule)) {
            $cutoffRule = DB::table('master_attendance_cutoff')->where('id', $cutoffRule)->first();
        }

        if (!$cutoffRule) {
            $cutoffRule = $this->resolveCutoffRule();
        }

        $startDay = (int) ($cutoffRule->start_day ?? 21);
        $endDay = (int) ($cutoffRule->end_day ?? 20);
        $cycle = $cutoffRule->cutoff_cycle ?? 'PREV_TO_CURRENT';

        $carbonPeriode = Carbon::createFromFormat('Y-m', $periodeMonth)->startOfMonth();

        if ($cycle === 'SAME_MONTH') {
            // Cutoff dalam bulan kalender yang sama (misal tgl 1 s/d tgl 30)
            $actualStartDay = min($startDay, $carbonPeriode->daysInMonth);
            $startDate = $carbonPeriode->copy()->day($actualStartDay)->toDateString();

            $actualEndDay = min($endDay, $carbonPeriode->daysInMonth);
            $endDate = $carbonPeriode->copy()->day($actualEndDay)->toDateString();
        } else {
            // PREV_TO_CURRENT (Default paling umum):
            // Periode November: Mulai dari 21 Oktober s/d 20 November
            $prevMonth = $carbonPeriode->copy()->subMonth();
            $actualStartDay = min($startDay, $prevMonth->daysInMonth);
            $startDate = $prevMonth->day($actualStartDay)->toDateString();

            $actualEndDay = min($endDay, $carbonPeriode->daysInMonth);
            $endDate = $carbonPeriode->copy()->day($actualEndDay)->toDateString();
        }

        $formattedLabel = date('d M Y', strtotime($startDate)) . ' s/d ' . date('d M Y', strtotime($endDate));

        return [
            'periode'         => $periodeMonth,
            'periode_int'     => (int) str_replace('-', '', $periodeMonth),
            'start_date'      => $startDate,
            'end_date'        => $endDate,
            'start_day'       => $startDay,
            'end_day'         => $endDay,
            'cutoff_rule'     => $cutoffRule,
            'formatted_label' => $formattedLabel,
            'cycle_type'      => $cycle,
        ];
    }

    /**
     * Tentukan Periode Bulan (Ym / Y-m) dari suatu tanggal spesifik berdasarkan aturan Cutoff.
     *
     * Contoh:
     * Cutoff 21 s/d 20:
     * - Tanggal 2026-10-20 -> Masuk Periode 2026-10 (Oktober)
     * - Tanggal 2026-10-21 -> Masuk Periode 2026-11 (November)
     * - Tanggal 2026-11-20 -> Masuk Periode 2026-11 (November)
     * - Tanggal 2026-11-21 -> Masuk Periode 2026-12 (Desember)
     */
    public function getPeriodeByDate($dateString, $cutoffRule = null): string
    {
        $dt = Carbon::parse($dateString);
        $day = $dt->day;

        if (!$cutoffRule) {
            $cutoffRule = $this->resolveCutoffRule();
        }

        $startDay = (int) ($cutoffRule->start_day ?? 21);
        $cycle = $cutoffRule->cutoff_cycle ?? 'PREV_TO_CURRENT';

        if ($cycle === 'SAME_MONTH') {
            return $dt->format('Y-m');
        }

        // PREV_TO_CURRENT: Jika tanggal >= startDay, maka masuk ke periode bulan berikutnya
        if ($day >= $startDay) {
            return $dt->copy()->addMonth()->format('Y-m');
        }

        // Jika belum mencapai startDay (misal tgl 1..20), masuk ke periode bulan ini
        return $dt->format('Y-m');
    }

    /**
     * Dapatkan daftar seluruh master cutoff aktif untuk dropdown select.
     */
    public function getAllActiveRules()
    {
        if (!Schema::hasTable('master_attendance_cutoff')) {
            return collect([$this->fallbackDefaultRule()]);
        }

        return DB::table('master_attendance_cutoff')
            ->orderBy('is_default', 'desc')
            ->orderBy('id', 'asc')
            ->get();
    }
}

