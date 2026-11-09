<?php

namespace App\Services\Payroll;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PayrollCalculationService
{
    /**
     * @var PayrollTaxService
     */
    protected $taxService;

    /**
     * @var PayrollAttendanceSyncService
     */
    protected $attendanceSyncService;

    public function __construct(PayrollTaxService $taxService, PayrollAttendanceSyncService $attendanceSyncService)
    {
        $this->taxService = $taxService;
        $this->attendanceSyncService = $attendanceSyncService;
    }

    /**
     * Ambil Nilai Konfigurasi Deduction / Parameter Payroll dari Database
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig($key, $default = null)
    {
        if (Schema::hasTable('payroll_deduction_configs')) {
            $row = DB::table('payroll_deduction_configs')
                ->where('config_key', $key)
                ->where('is_active', 1)
                ->first();

            if ($row) {
                if ($row->value_type === 'NUMERIC' || $row->value_type === 'PERCENT') {
                    return (float)$row->value_data;
                }
                return $row->value_data;
            }
        }

        $fallbacks = [
            'unpaid_leave_divisor'        => 26.0,
            'late_penalty_per_occurrence' => 22000.0,
            'bpjs_tk_jht_employee_rate'   => 2.0,
            'bpjs_kes_employee_rate'      => 1.0,
            'bpjs_kes_max_salary_cap'     => 12000000.0,
            'bpjs_kes_max_deduction_cap'  => 120000.0,
            'ho_umk_nominal'              => 5067381.0,
            'biaya_jabatan_rate'          => 5.0,
            'biaya_jabatan_max_monthly'   => 500000.0,
        ];

        return isset($fallbacks[$key]) ? $fallbacks[$key] : $default;
    }

    /**
     * Update Nilai Konfigurasi Parameter Deduction
     *
     * @param string $key
     * @param mixed $value
     * @param string $updatedBy
     * @return bool
     */
    public function updateConfig($key, $value, $updatedBy = 'SYSTEM')
    {
        if (!Schema::hasTable('payroll_deduction_configs')) {
            return false;
        }

        return (bool)DB::table('payroll_deduction_configs')
            ->where('config_key', $key)
            ->update([
                'value_data' => (string)$value,
                'updated_by' => $updatedBy,
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]);
    }

    /**
     * Kalkulasi Penggajian Lengkap untuk Seorang Karyawan pada Periode Tertentu
     *
     * @param string $nik NIK Karyawan
     * @param string $periodeYm Format YYYY-MM
     * @param array $options Opsi override (main_id, is_month_end, dll.)
     * @return array
     */
    public function calculateEmployeeSalary($nik, $periodeYm, $options = [])
    {
        // 1. Ambil Profil Kepegawaian Karyawan
        $employee = DB::table('employment')->where('NIK', $nik)->first();
        $user = DB::table('users')->where('Emp_ID', $nik)->first();

        if (!$employee && !$user) {
            throw new \InvalidArgumentException("Data karyawan dengan NIK {$nik} tidak ditemukan.");
        }

        $mainId = isset($options['main_id']) ? $options['main_id'] : ($employee ? $employee->Salary_Group : null);
        $ptkpStatus = $employee ? ($employee->PTKP_Status ?: 'TK/0') : 'TK/0';
        $isGrossUp = ($employee && $employee->PPH_Allowance == 1);
        $userDept = $employee ? $employee->Department : ($user ? $user->Department : 'HO');
        $location = $user ? ($user->Location ?: 'HO') : 'HO';

        // 2. Ambil Komponen Master Gaji Karyawan dari payrol_group_lists
        $itemsQuery = DB::table('payrol_group_lists')
            ->where('Group_ID', $nik);

        if ($mainId) {
            $itemsQuery->where('Main_ID', $mainId);
        }

        $salaryItems = $itemsQuery->get();

        $gajiPokok = 0.0;
        $totalTunjangan = 0.0;
        $customDeductions = 0.0;
        $activeComponents = [];

        foreach ($salaryItems as $item) {
            $val = (float)$item->Amounts;
            $type = strtoupper(trim($item->Type_Payroll));

            if ($type === 'BASIC') {
                $gajiPokok += $val;
            } elseif ($type === 'ALLOWANCE') {
                // Abaikan tunjangan pajak sistem (PYH00001) yang akan dihitung ulang secara dinamis
                if ($item->Payroll_ID !== 'PYH00001') {
                    $totalTunjangan += $val;
                }
            } elseif ($type === 'DEDUCTION') {
                // Abaikan komponen deduction standar yang akan dihitung formula baru
                if (!in_array($item->Payroll_ID, ['PY000000', 'DED_UNPAID', 'DED_LATE', 'DED_BPJS_TK', 'DED_BPJS_KES'])) {
                    $customDeductions += abs($val);
                }
            }

            $activeComponents[] = [
                'payroll_id'    => $item->Payroll_ID,
                'name'          => $item->Name,
                'type'          => $type,
                'amount'        => $val,
                'kena_potongan' => $item->Kena_Potongan,
                'default_pph'   => $item->Default_PPH,
            ];
        }

        // 3. Tarik Rekapitulasi Presensi Karyawan dari Modul Attendant
        $attendance = $this->attendanceSyncService->getEmployeeAttendanceForPeriod($nik, $periodeYm, [
            'department' => $userDept,
        ]);

        $absentDays = (int)$attendance['total_absent_days']; // Termasuk Alpha & Absensi Tidak Lengkap
        $lateCount = (int)$attendance['total_late_days'];    // Frekuensi Terlambat
        $overtimeAmount = (float)$attendance['approved_overtime_amount'];

        // 4. Kalkulasi Komponen Deductions Dinamis Sesuai Aturan Klien
        $baseTotalSalary = $gajiPokok + $totalTunjangan;

        // A. Unpaid Leave (Alpha): (Total Gaji / 26) * Jumlah Alpha
        $unpaidDivisor = (float)$this->getConfig('unpaid_leave_divisor', 26.0);
        $unpaidLeaveAmount = 0.0;
        if ($absentDays > 0 && $unpaidDivisor > 0) {
            $unpaidLeaveAmount = ceil(($baseTotalSalary / $unpaidDivisor) * $absentDays);
        }

        // B. Keterlambatan: 22.000 * Berapa Kali Keterlambatan
        $latePenaltyRate = (float)$this->getConfig('late_penalty_per_occurrence', 22000.0);
        $latePenaltyAmount = ceil($latePenaltyRate * $lateCount);

        // C. BPJS Ketenagakerjaan: Total Salary * 2%
        $bpjsTkRate = (float)$this->getConfig('bpjs_tk_jht_employee_rate', 2.0);
        $bpjsTkAmount = ceil(($bpjsTkRate / 100.0) * $baseTotalSalary);

        // D. BPJS Kesehatan (Site HO):
        // Jika Total Salary < UMK HO -> UMK * 1%
        // Jika Total Salary >= UMK HO -> Total Salary * 1%
        // Jika Total Salary > 12 juta -> Cap maksimal 120.000 fix
        $umkHO = (float)$this->getConfig('ho_umk_nominal', 5067381.0);
        $bpjsKesRate = (float)$this->getConfig('bpjs_kes_employee_rate', 1.0) / 100.0;
        $maxSalaryCap = (float)$this->getConfig('bpjs_kes_max_salary_cap', 12000000.0);
        $maxDeductionCap = (float)$this->getConfig('bpjs_kes_max_deduction_cap', 120000.0);

        if ($baseTotalSalary > $maxSalaryCap) {
            $bpjsKesAmount = $maxDeductionCap;
        } elseif ($baseTotalSalary < $umkHO) {
            $bpjsKesAmount = ceil($umkHO * $bpjsKesRate);
        } else {
            $bpjsKesAmount = ceil($baseTotalSalary * $bpjsKesRate);
        }

        // Total Potongan Selain Pajak
        $totalNonTaxDeductions = $unpaidLeaveAmount + $latePenaltyAmount + $bpjsTkAmount + $bpjsKesAmount + $customDeductions;

        // 5. Kalkulasi PPh 21 (TER Bulanan PP 58/2023 atau Tahunan Pasal 17 UU HPP)
        $monthNum = (int)date('m', strtotime($periodeYm . '-01'));
        $isYearEnd = ($monthNum == 12 || !empty($options['is_year_end']));

        // Bruto Kena Pajak Sebulan = Pokok + Tunjangan + Lembur
        $grossTaxable = $baseTotalSalary + $overtimeAmount;

        $taxResult = $this->taxService->calculateMonthlyTax(
            $grossTaxable,
            $ptkpStatus,
            $monthNum,
            $grossTaxable * $monthNum, // Estimasi YTD
            0.0,
            [
                'is_gross_up'        => $isGrossUp,
                'nik'                => $nik,
                'npwp'               => $employee ? $employee->NPWP : '',
                'jht_employee_yearly'=> $bpjsTkAmount * 12,
            ]
        );

        $pph21Amount = (float)$taxResult['tax_to_deduct'];
        $tunjanganPph = (float)$taxResult['tax_allowance'];

        // 6. Kalkulasi Take Home Pay (THP)
        // THP = (Pokok + Tunjangan + Lembur + Tunjangan PPh) - (Total Potongan + PPh 21)
        $totalIncome = $gajiPokok + $totalTunjangan + $overtimeAmount + $tunjanganPph;
        $totalDeductions = $totalNonTaxDeductions + $pph21Amount;
        $thp = max(0.0, $totalIncome - $totalDeductions);

        // 7. Bentuk Audit Metadata (JSON) Lengkap
        $calculationMetadata = [
            'nik'                 => $nik,
            'name'                => $employee ? $employee->Name : ($user ? $user->username : ''),
            'periode'             => $periodeYm,
            'ptkp_status'         => $ptkpStatus,
            'is_gross_up'         => $isGrossUp,
            'base_components'     => [
                'pokok'           => $gajiPokok,
                'tunjangan'       => $totalTunjangan,
                'overtime'        => $overtimeAmount,
                'total_base'      => $baseTotalSalary,
            ],
            'attendance_metrics'  => [
                'cutoff_start'    => $attendance['cutoff_start_date'],
                'cutoff_end'      => $attendance['cutoff_end_date'],
                'cutoff_label'    => $attendance['cutoff_label'],
                'scheduled_days'  => $attendance['total_scheduled_days'],
                'present_days'    => $attendance['total_present_days'],
                'absent_days'     => $absentDays,
                'late_count'      => $lateCount,
                'late_minutes'    => $attendance['total_late_minutes'],
                'incomplete_logs' => $attendance['incomplete_logs_count'],
            ],
            'deduction_breakdown' => [
                'unpaid_leave'    => [
                    'days'        => $absentDays,
                    'divisor'     => $unpaidDivisor,
                    'amount'      => $unpaidLeaveAmount,
                    'formula'     => "({$baseTotalSalary} / {$unpaidDivisor}) * {$absentDays}",
                ],
                'late_penalty'    => [
                    'occurrences' => $lateCount,
                    'rate'        => $latePenaltyRate,
                    'amount'      => $latePenaltyAmount,
                    'formula'     => "{$latePenaltyRate} * {$lateCount}",
                ],
                'bpjs_tk'         => [
                    'rate'        => $bpjsTkRate,
                    'amount'      => $bpjsTkAmount,
                    'formula'     => "{$baseTotalSalary} * {$bpjsTkRate}%",
                ],
                'bpjs_kes'        => [
                    'rate'        => $bpjsKesRate * 100,
                    'umk_ho'      => $umkHO,
                    'max_cap'     => $maxDeductionCap,
                    'amount'      => $bpjsKesAmount,
                ],
                'custom_ded'      => $customDeductions,
                'total_deduction' => $totalNonTaxDeductions,
            ],
            'tax_details'         => $taxResult,
            'final_summary'       => [
                'gross_income'    => $totalIncome,
                'total_deductions'=> $totalDeductions,
                'pph21'           => $pph21Amount,
                'thp'             => $thp,
            ],
            'calculated_at'       => Carbon::now()->toDateTimeString(),
        ];

        return [
            'nik'                   => $nik,
            'name'                  => $employee ? $employee->Name : ($user ? $user->username : ''),
            'periode'               => $periodeYm,
            'pokok'                 => $gajiPokok,
            'tunjangan'             => $totalTunjangan,
            'overtime'              => $overtimeAmount,
            'tunjangan_pph'         => $tunjanganPph,
            'deduction'             => $totalNonTaxDeductions,
            'pph'                   => $pph21Amount,
            'thp'                   => $thp,
            'absent_days'           => $absentDays,
            'late_occurrences'      => $lateCount,
            'unpaid_leave_amount'   => $unpaidLeaveAmount,
            'late_penalty_amount'   => $latePenaltyAmount,
            'bpjs_tk_amount'        => $bpjsTkAmount,
            'bpjs_kes_amount'       => $bpjsKesAmount,
            'metadata'              => $calculationMetadata,
            'active_components'     => $activeComponents,
        ];
    }

    /**
     * Recount dan Simpan Hasil Kalkulasi ke Database untuk Suatu Group
     *
     * @param string $mainId
     * @param string $periodeYm
     * @param string $operatorUsername
     * @return array
     */
    public function recountSalaryGroup($mainId, $periodeYm, $operatorUsername = 'SYSTEM')
    {
        $employees = DB::table('payrol_master_group')
            ->where('Main_ID', $mainId)
            ->get();

        $results = [];
        $totalGroupThp = 0.0;
        $totalGroupTax = 0.0;

        foreach ($employees as $emp) {
            $nik = $emp->Group_ID;
            $calc = $this->calculateEmployeeSalary($nik, $periodeYm, ['main_id' => $mainId]);

            // Sinkronkan ke payrol_group_lists
            $this->syncGroupListsComponents($mainId, $nik, $calc, $operatorUsername);

            $totalGroupThp += $calc['thp'];
            $totalGroupTax += $calc['pph'];
            $results[] = $calc;
        }

        return [
            'main_id'         => $mainId,
            'periode'         => $periodeYm,
            'total_employees' => count($results),
            'total_thp'       => $totalGroupThp,
            'total_tax'       => $totalGroupTax,
            'employees'       => $results,
        ];
    }

    /**
     * Update / Insert Komponen Dinamis ke Tabel payrol_group_lists
     */
    protected function syncGroupListsComponents($mainId, $nik, array $calc, $operatorUsername)
    {
        $now = Carbon::now()->toDateTimeString();

        // 1. Update / Insert Komponen Pajak PPh 21 (PY000000)
        $pajakRow = DB::table('payrol_group_lists')
            ->where('Main_ID', $mainId)
            ->where('Group_ID', $nik)
            ->where('Payroll_ID', 'PY000000')
            ->first();

        $taxNote = json_encode($calc['metadata']['tax_details']);

        if ($pajakRow) {
            DB::table('payrol_group_lists')
                ->where('ID', $pajakRow->ID)
                ->update([
                    'Amounts'       => -$calc['pph'],
                    'Note'          => $taxNote,
                    'Modified_By'   => $operatorUsername,
                    'Modified_Date' => $now,
                ]);
        } else {
            DB::table('payrol_group_lists')->insert([
                'Main_ID'       => $mainId,
                'Group_ID'      => $nik,
                'Payroll_ID'    => 'PY000000',
                'Name'          => 'PPh 21',
                'Kena_Potongan' => 0,
                'Default_PPH'   => 0,
                'Type_Payroll'  => 'DEDUCTION',
                'Amounts'       => -$calc['pph'],
                'Note'          => $taxNote,
                'Created_By'    => $operatorUsername,
                'Created_Date'  => $now,
            ]);
        }

        // 2. Jika Gross-Up, Update / Insert Tunjangan Pajak (PYH00001)
        if ($calc['tunjangan_pph'] > 0) {
            $tunjRow = DB::table('payrol_group_lists')
                ->where('Main_ID', $mainId)
                ->where('Group_ID', $nik)
                ->where('Payroll_ID', 'PYH00001')
                ->first();

            if ($tunjRow) {
                DB::table('payrol_group_lists')
                    ->where('ID', $tunjRow->ID)
                    ->update([
                        'Amounts'       => $calc['tunjangan_pph'],
                        'Note'          => 'Tunjangan PPh 21 (Gross-Up)',
                        'Modified_By'   => $operatorUsername,
                        'Modified_Date' => $now,
                    ]);
            } else {
                DB::table('payrol_group_lists')->insert([
                    'Main_ID'       => $mainId,
                    'Group_ID'      => $nik,
                    'Payroll_ID'    => 'PYH00001',
                    'Name'          => 'TUNJANGAN PAJAK PPH',
                    'Kena_Potongan' => 0,
                    'Default_PPH'   => 0,
                    'Type_Payroll'  => 'ALLOWANCE',
                    'Amounts'       => $calc['tunjangan_pph'],
                    'Note'          => 'Tunjangan PPh 21 (Gross-Up)',
                    'Created_By'    => $operatorUsername,
                    'Created_Date'  => $now,
                ]);
            }
        }
    }
}

