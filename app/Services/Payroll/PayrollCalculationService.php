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

    /**
     * @var PayrollThrService
     */
    protected $thrService;

    /**
     * @var PayrollAdvanceDeductionService
     */
    protected $advanceDeductionService;

    public function __construct(
        PayrollTaxService $taxService,
        PayrollAttendanceSyncService $attendanceSyncService,
        PayrollThrService $thrService = null,
        PayrollAdvanceDeductionService $advanceDeductionService = null
    ) {
        $this->taxService = $taxService;
        $this->attendanceSyncService = $attendanceSyncService;
        $this->thrService = $thrService ?: app(PayrollThrService::class);
        $this->advanceDeductionService = $advanceDeductionService ?: app(PayrollAdvanceDeductionService::class);
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
     * Wajib terdaftar di tabel employment.
     *
     * @param string $nik NIK Karyawan
     * @param string $periodeYm Format YYYY-MM
     * @param array $options Opsi override (main_id, is_year_end, dll.)
     * @return array
     */
    public function calculateEmployeeSalary($nik, $periodeYm, $options = [])
    {
        // 1. Ambil Profil Kepegawaian Karyawan (Wajib ada di tabel employment)
        $employee = DB::table('employment')->where('NIK', $nik)->first();
        if (!$employee) {
            throw new \InvalidArgumentException("Data karyawan dengan NIK {$nik} tidak ditemukan pada tabel employment.");
        }

        $user = DB::table('users')->where('Emp_ID', $nik)->first();

        $mainId = isset($options['main_id']) ? $options['main_id'] : $employee->Salary_Group;
        $ptkpStatus = $employee->PTKP_Status ?: ($employee->Marital_Status ?: 'TK/0');
        $isGrossUp = ($employee->PPH_Allowance == 1);
        $userDept = $employee->Department ?: ($user ? $user->Department : 'HO');
        $empDisplayName = $employee->Name ?: $nik;

        // Preload formula master lists untuk membedakan komponen dinamis OVERTIME (PY000045) & KJK (PY000044)
        $masterFormulas = [];
        if (Schema::hasTable('payrol_master_lists')) {
            foreach (DB::table('payrol_master_lists')->get(['Payroll_ID', 'Formula_Desc']) as $ml) {
                $masterFormulas[strtoupper(trim($ml->Payroll_ID))] = strtoupper(trim((string)$ml->Formula_Desc));
            }
        }

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
            $val = (isset($item->Prorate) && (float)$item->Prorate > 0) ? (float)$item->Prorate : (float)$item->Amounts;
            $type = strtoupper(trim((string)$item->Type_Payroll));
            $pId = strtoupper(trim((string)$item->Payroll_ID));
            $formula = isset($masterFormulas[$pId]) ? $masterFormulas[$pId] : '';

            if ($type === 'BASIC') {
                $gajiPokok += $val;
            } elseif ($type === 'ALLOWANCE') {
                // Abaikan tunjangan pajak sistem (PYH00001/PYH00000) serta komponen dinamis OVERTIME (PY000045), KJK (PY000044), & THR (PY000060)
                // karena dihitung secara dinamis dari modul terkait per periode
                if (
                    !in_array($pId, ['PYH00001', 'PYH00000', 'PY000045', 'PY000044', 'PY000060'], true)
                    && !in_array($formula, ['OVERTIME', 'KJK', 'THR'], true)
                ) {
                    $totalTunjangan += $val;
                }
            } elseif ($type === 'DEDUCTION') {
                // Abaikan komponen deduction standar yang akan dihitung formula baru
                if (!in_array($pId, ['PY000000', 'PY000037', 'PY000032', 'DED_UNPAID', 'DED_LATE', 'DED_BPJS_TK', 'DED_BPJS_KES', 'DED_SPPD_KASBON'], true)) {
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

        // 3. Tarik Rekapitulasi Presensi, Izin/Cuti (Master Permission), Overtime (Matrix + Legacy), & KJK
        $attendance = $this->attendanceSyncService->getEmployeeAttendanceForPeriod($nik, $periodeYm, [
            'department' => $userDept,
        ]);

        $absentDays = (int)$attendance['total_absent_days']; // Alpha + Unpaid Leave + Absensi Tidak Lengkap
        $lateCount = (int)$attendance['total_late_days'];    // Frekuensi Terlambat (setelah dikurangi dispensasi Datang Telat)
        $overtimeAmount = (float)$attendance['approved_overtime_amount'];
        $kjkAmount = (float)($attendance['approved_kjk_amount'] ?? 0.0);

        // 3b. Tarik Komponen THR (Tunjangan Hari Raya) yang sudah di-lock untuk periode berjalan
        $thrData = $this->thrService->getApprovedThrForEmployeePeriod($nik, $periodeYm);
        $thrAmount = (float)($thrData['amount'] ?? 0.0);

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

        // ---------------------------------------------------------------------
        // Pengecekan Kebijakan BPJS: Karyawan Tidak Dipotong & Masa Probation
        // ---------------------------------------------------------------------
        $isProbationExempt = false;
        $probationReason = '';

        // Cek flag pembebasan/penundaan potongan BPJS selama masa probation
        $flagProbationExempt = isset($employee->bpjs_probation_exempt) ? (int)$employee->bpjs_probation_exempt : 0;
        if ($flagProbationExempt === 1) {
            $periodStartDate = $periodeYm . '-01';

            if (!empty($employee->probation_end_date) && $employee->probation_end_date !== '0000-00-00') {
                $probEndDate = $employee->probation_end_date;
                // Jika periode penggajian masih berada pada atau sebelum tanggal akhir masa probation
                if ($periodStartDate <= $probEndDate) {
                    $isProbationExempt = true;
                    $probationReason = 'Masa probation hingga ' . date('d M Y', strtotime($probEndDate));
                }
            } else {
                // Fallback jika probation_end_date belum diisi: Cek Employment_Status atau Date_in (+3 bulan)
                $empStatus = strtoupper(trim((string)($employee->Employment_Status ?? '')));
                if (strpos($empStatus, 'PROBATION') !== false) {
                    $isProbationExempt = true;
                    $probationReason = 'Status kepegawaian PROBATION';
                } elseif (!empty($employee->Date_in) && $employee->Date_in !== '0000-00-00') {
                    $estProbEnd = date('Y-m-d', strtotime($employee->Date_in . ' +3 months'));
                    if ($periodStartDate <= $estProbEnd) {
                        $isProbationExempt = true;
                        $probationReason = 'Masa probation (3 bulan sejak ' . date('d M Y', strtotime($employee->Date_in)) . ')';
                    }
                }
            }
        }

        // C. BPJS Ketenagakerjaan: Total Salary * 2%
        $bpjsTkRate = (float)$this->getConfig('bpjs_tk_jht_employee_rate', 2.0);
        $isTkConfigDeducted = !isset($employee->bpjs_tk_deduction) || ((int)$employee->bpjs_tk_deduction === 1);

        $bpjsTkExemptReason = null;
        if (!$isTkConfigDeducted) {
            $bpjsTkAmount = 0.0;
            $bpjsTkExemptReason = 'Setup Karyawan: Bebas/Tidak Dipotong BPJS Ketenagakerjaan';
        } elseif ($isProbationExempt) {
            $bpjsTkAmount = 0.0;
            $bpjsTkExemptReason = 'Bebas Potongan Selama Masa Probation (' . $probationReason . ')';
        } else {
            $bpjsTkAmount = ceil(($bpjsTkRate / 100.0) * $baseTotalSalary);
        }

        // D. BPJS Kesehatan (Site HO):
        // Jika Total Salary < UMK HO -> UMK * 1%
        // Jika Total Salary >= UMK HO -> Total Salary * 1%
        // Jika Total Salary > 12 juta -> Cap maksimal 120.000 fix
        $umkHO = (float)$this->getConfig('ho_umk_nominal', 5067381.0);
        $bpjsKesRate = (float)$this->getConfig('bpjs_kes_employee_rate', 1.0) / 100.0;
        $maxSalaryCap = (float)$this->getConfig('bpjs_kes_max_salary_cap', 12000000.0);
        $maxDeductionCap = (float)$this->getConfig('bpjs_kes_max_deduction_cap', 120000.0);

        $kesDeductionSetting = isset($employee->bpjs_kes_deduction) ? (int)$employee->bpjs_kes_deduction : 1;
        $isKesConfigDeducted = ($kesDeductionSetting === 1);

        $bpjsKesExemptReason = null;
        if ($kesDeductionSetting === 2) {
            // Subsidi Pemerintah berupa Kartu Indonesia Sehat (KIS / PBI)
            $bpjsKesAmount = 0.0;
            $bpjsKesExemptReason = 'Tidak Dipotong: Menerima Subsidi Pemerintah (Kartu Indonesia Sehat / KIS PBI)';
        } elseif ($kesDeductionSetting === 0) {
            $bpjsKesAmount = 0.0;
            $bpjsKesExemptReason = 'Setup Karyawan: Bebas/Tidak Dipotong BPJS Kesehatan';
        } elseif ($isProbationExempt) {
            $bpjsKesAmount = 0.0;
            $bpjsKesExemptReason = 'Bebas Potongan Selama Masa Probation (' . $probationReason . ')';
        } else {
            if ($baseTotalSalary > $maxSalaryCap) {
                $bpjsKesAmount = $maxDeductionCap;
            } elseif ($baseTotalSalary < $umkHO) {
                $bpjsKesAmount = ceil($umkHO * $bpjsKesRate);
            } else {
                $bpjsKesAmount = ceil($baseTotalSalary * $bpjsKesRate);
            }
        }

        // 4e. Tarik Potongan SPPD Belum Settlement, Kasbon, & Reimburse (Non-Tax Deduction)
        $ignoreAdvance = !empty($options['ignore_advance_deductions']);
        $advanceDeductionData = $ignoreAdvance
            ? ['total' => 0.0, 'by_type' => [], 'items' => []]
            : $this->advanceDeductionService->getActiveDeductionsForEmployee($nik, $periodeYm);
        $rawAdvanceDeductionsTotal = (float) ($advanceDeductionData['total'] ?? 0.0);

        // Total Potongan Wajib Selain Pajak (sebelum potongan SPPD/Kasbon/Manual)
        $mandatoryNonTaxDeductions = $unpaidLeaveAmount + $latePenaltyAmount + $bpjsTkAmount + $bpjsKesAmount + $customDeductions;

        // 5. Kalkulasi PPh 21 (TER Bulanan PP 58/2023 atau Tahunan Pasal 17 UU HPP)
        $monthNum = (int)date('m', strtotime($periodeYm . '-01'));

        // Bruto Kena Pajak Sebulan = Pokok + Tunjangan + Lembur + KJK + THR
        $grossTaxable = $baseTotalSalary + $overtimeAmount + $kjkAmount + $thrAmount;

        $ktpNumber = trim((string) ($employee->KTP ?? ''));
        $npwpNumber = trim((string) ($employee->NPWP ?? ''));
        if ($npwpNumber === '' && $ktpNumber !== '') {
            $npwpNumber = $ktpNumber;
        }

        $taxResult = $this->taxService->calculateMonthlyTax(
            $grossTaxable,
            $ptkpStatus,
            $monthNum,
            $grossTaxable * $monthNum, // Estimasi YTD
            0.0,
            [
                'is_gross_up'           => $isGrossUp,
                'nik'                   => $ktpNumber !== '' ? $ktpNumber : $nik,
                'npwp'                  => $npwpNumber,
                'jht_employee_yearly'   => $bpjsTkAmount * 12,
                'apply_non_npwp_penalty' => false,
            ]
        );

        $pph21Amount = (float)$taxResult['tax_to_deduct'];
        $tunjanganPph = (float)$taxResult['tax_allowance'];

        // 6. Kalkulasi Take Home Pay (THP) & Safety Cap Potongan SPPD/Kasbon/Manual agar tidak melebihi THP
        $totalIncome = $gajiPokok + $totalTunjangan + $overtimeAmount + $kjkAmount + $thrAmount + $tunjanganPph;
        $thpBeforeAdvance = max(0.0, $totalIncome - ($mandatoryNonTaxDeductions + $pph21Amount));
        $advanceDeductionsTotal = $ignoreAdvance ? 0.0 : min($rawAdvanceDeductionsTotal, $thpBeforeAdvance);
        $totalNonTaxDeductions = $mandatoryNonTaxDeductions + $advanceDeductionsTotal;
        $totalDeductions = $totalNonTaxDeductions + $pph21Amount;
        $thp = max(0.0, $totalIncome - $totalDeductions);

        // 7. Bentuk Audit Metadata (JSON) Lengkap
        $calculationMetadata = [
            'nik'                 => $nik,
            'name'                => $empDisplayName,
            'periode'             => $periodeYm,
            'ptkp_status'         => $ptkpStatus,
            'is_gross_up'         => $isGrossUp,
            'base_components'     => [
                'pokok'           => $gajiPokok,
                'tunjangan'       => $totalTunjangan,
                'overtime'        => $overtimeAmount,
                'kjk'             => $kjkAmount,
                'thr'             => $thrAmount,
                'total_base'      => $baseTotalSalary,
            ],
            'attendance_metrics'  => [
                'cutoff_start'      => $attendance['cutoff_start_date'],
                'cutoff_end'        => $attendance['cutoff_end_date'],
                'cutoff_label'      => $attendance['cutoff_label'],
                'scheduled_days'    => $attendance['total_scheduled_days'],
                'present_days'      => $attendance['total_present_days'],
                'paid_leave_days'   => $attendance['total_leave_paid_days'],
                'unpaid_leave_days' => $attendance['total_leave_unpaid_days'],
                'alpha_days'        => $attendance['total_alpha_days'],
                'absent_days'       => $absentDays,
                'late_count'        => $lateCount,
                'late_minutes'      => $attendance['total_late_minutes'],
                'incomplete_logs'   => $attendance['incomplete_logs_count'],
                'change_shift_days' => $attendance['change_shift_days'] ?? 0,
                'corrections_count' => $attendance['approved_corrections_count'] ?? 0,
                'overtime_hours'    => $attendance['approved_overtime_hours'],
                'overtime_tasks'    => $attendance['overtime_task_ids'] ?? [],
                'kjk_tasks'         => $attendance['kjk_task_ids'] ?? [],
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
                    'rate'          => $bpjsTkRate,
                    'amount'        => $bpjsTkAmount,
                    'formula'       => $bpjsTkAmount > 0 ? "{$baseTotalSalary} * {$bpjsTkRate}%" : '0 (Bebas / Dikecualikan)',
                    'is_exempt'     => ($bpjsTkAmount == 0.0),
                    'exempt_reason' => $bpjsTkExemptReason,
                ],
                'bpjs_kes'        => [
                    'rate'          => $bpjsKesRate * 100,
                    'umk_ho'        => $umkHO,
                    'max_cap'       => $maxDeductionCap,
                    'amount'        => $bpjsKesAmount,
                    'formula'       => $bpjsKesAmount > 0 ? "Kalkulasi BPJS Kes HO" : '0 (Bebas / Dikecualikan)',
                    'is_exempt'     => ($bpjsKesAmount == 0.0),
                    'exempt_reason' => $bpjsKesExemptReason,
                ],
                'custom_ded'        => $customDeductions,
                'advance_deduction' => [
                    'amount'             => $advanceDeductionsTotal,
                    'raw_requested'      => $rawAdvanceDeductionsTotal,
                    'thp_before_advance' => $thpBeforeAdvance,
                    'is_capped_by_thp'   => ($rawAdvanceDeductionsTotal > $thpBeforeAdvance),
                    'by_type'            => $advanceDeductionData['by_type'] ?? [],
                    'items_count'        => count($advanceDeductionData['items'] ?? []),
                ],
                'total_deduction'   => $totalNonTaxDeductions,
            ],
            'bpjs_policy'         => [
                'bpjs_tk_deduction'     => $isTkConfigDeducted,
                'bpjs_kes_deduction'    => $isKesConfigDeducted,
                'bpjs_kes_setting'      => $kesDeductionSetting,
                'is_kis_subsidized'     => ($kesDeductionSetting === 2),
                'bpjs_probation_exempt' => (bool)$flagProbationExempt,
                'is_in_probation'       => $isProbationExempt,
                'probation_reason'      => $probationReason,
            ],
            'tax_details'         => $taxResult,
            'final_summary'       => [
                'gross_income'     => $totalIncome,
                'total_deductions' => $totalDeductions,
                'pph21'            => $pph21Amount,
                'thp'              => $thp,
            ],
            'calculated_at'       => Carbon::now()->toDateTimeString(),
        ];

        return [
            'nik'                   => $nik,
            'name'                  => $empDisplayName,
            'periode'               => $periodeYm,
            'pokok'                 => $gajiPokok,
            'tunjangan'             => $totalTunjangan,
            'overtime'              => $overtimeAmount,
            'kjk'                   => $kjkAmount,
            'thr'                   => $thrAmount,
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
            'overtime_task_ids'     => $attendance['overtime_task_ids'] ?? [],
            'kjk_task_ids'          => $attendance['kjk_task_ids'] ?? [],
            'thr_item_ids'          => $thrData['item_ids'] ?? [],
            'thr_batch_nos'         => $thrData['batch_nos'] ?? [],
            'metadata'              => $calculationMetadata,
            'active_components'     => $activeComponents,
        ];
    }

    /**
     * Recount dan Simpan Hasil Kalkulasi ke Database untuk Suatu Group
     * HANYA memproses karyawan yang terdaftar di tabel employment.
     *
     * @param string $mainId
     * @param string $periodeYm
     * @param string $operatorUsername
     * @return array
     */
    public function recountSalaryGroup($mainId, $periodeYm, $operatorUsername = 'SYSTEM')
    {
        // Wajib INNER JOIN dengan tabel employment agar hanya karyawan valid di employment yang diproses
        $employees = DB::table('payrol_master_group as pmg')
            ->join('employment as e', 'e.NIK', '=', 'pmg.Group_ID')
            ->where('pmg.Main_ID', $mainId)
            ->select('pmg.*', 'e.Name as Emp_Name')
            ->orderBy('e.Name', 'ASC')
            ->get();

        $results = [];
        $totalGroupThp = 0.0;
        $totalGroupTax = 0.0;
        $seenNiks = [];

        foreach ($employees as $emp) {
            $nik = trim((string)$emp->Group_ID);
            if ($nik === '' || isset($seenNiks[$nik])) {
                continue;
            }
            $seenNiks[$nik] = true;

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
     * Meliputi PPh 21, Tunjangan PPh (Gross-Up), Overtime (PY000045), KJK (PY000044), dan Deductions Dinamis.
     */
    protected function syncGroupListsComponents($mainId, $nik, array $calc, $operatorUsername)
    {
        $now = Carbon::now()->toDateTimeString();

        // 1. Update / Insert Komponen Pajak PPh 21 (PY000000)
        $pajakRow = DB::table('payrol_group_lists')
            ->where('Main_ID', $mainId)
            ->where('Group_ID', $nik)
            ->where(function ($q) {
                $q->where('Payroll_ID', 'PY000000')
                  ->orWhere('Name', 'LIKE', '%PPH%')
                  ->orWhere('Name', 'LIKE', '%PAJAK%');
            })
            ->where('Type_Payroll', 'DEDUCTION')
            ->first();

        $taxNote = json_encode($calc['metadata']['tax_details']);

        if ($pajakRow) {
            $updateData = [
                'Amounts' => -$calc['pph'],
                'Note'    => $taxNote,
            ];
            if (Schema::hasColumn('payrol_group_lists', 'Modified_By')) {
                $updateData['Modified_By'] = $operatorUsername;
            }
            if (Schema::hasColumn('payrol_group_lists', 'Modified_Date')) {
                $updateData['Modified_Date'] = $now;
            }

            DB::table('payrol_group_lists')
                ->where('ID', $pajakRow->ID)
                ->update($updateData);
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
        $tunjRow = DB::table('payrol_group_lists')
            ->where('Main_ID', $mainId)
            ->where('Group_ID', $nik)
            ->where(function ($q) {
                $q->whereIn('Payroll_ID', ['PYH00001', 'PYH00000'])
                  ->orWhere('Name', 'LIKE', '%TUNJANGAN PAJAK%')
                  ->orWhere('Name', 'LIKE', '%TUNJANGAN PPH%');
            })
            ->where('Type_Payroll', 'ALLOWANCE')
            ->first();

        if ($tunjRow) {
            $updateTunjData = [
                'Amounts' => $calc['tunjangan_pph'],
                'Note'    => 'Tunjangan PPh 21 (Gross-Up)',
            ];
            if (Schema::hasColumn('payrol_group_lists', 'Modified_By')) {
                $updateTunjData['Modified_By'] = $operatorUsername;
            }
            if (Schema::hasColumn('payrol_group_lists', 'Modified_Date')) {
                $updateTunjData['Modified_Date'] = $now;
            }

            DB::table('payrol_group_lists')
                ->where('ID', $tunjRow->ID)
                ->update($updateTunjData);
        } elseif ($calc['tunjangan_pph'] > 0) {
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

        // 3. Sinkronkan Komponen Dinamis OVERTIME (PY000045), KJK (PY000044), & THR (PY000060)
        $otTasks = isset($calc['overtime_task_ids']) ? $calc['overtime_task_ids'] : [];
        $kjkTasks = isset($calc['kjk_task_ids']) ? $calc['kjk_task_ids'] : [];
        $thrBatches = isset($calc['thr_batch_nos']) ? $calc['thr_batch_nos'] : [];

        $dynamicAllowances = [
            'PY000045' => [
                'name'   => 'OVERTIME',
                'amount' => (float)($calc['overtime'] ?? 0.0),
                'note'   => 'Number of Overtime: ' . count($otTasks) . '; Task LIST = ' . implode(',', $otTasks),
            ],
            'PY000044' => [
                'name'   => 'KJK',
                'amount' => (float)($calc['kjk'] ?? 0.0),
                'note'   => 'Number of KJK: ' . count($kjkTasks) . '; Task LIST = ' . implode(',', $kjkTasks),
            ],
            'PY000060' => [
                'name'   => 'THR (TUNJANGAN HARI RAYA)',
                'amount' => (float)($calc['thr'] ?? 0.0),
                'note'   => !empty($thrBatches) ? ('Batch THR: ' . implode(', ', $thrBatches)) : 'Tidak ada THR pada periode ini',
            ],
        ];

        foreach ($dynamicAllowances as $alwCode => $alwInfo) {
            $alwRow = DB::table('payrol_group_lists')
                ->where('Main_ID', $mainId)
                ->where('Group_ID', $nik)
                ->where(function ($q) use ($alwCode, $alwInfo) {
                    $q->where('Payroll_ID', $alwCode)
                      ->orWhere('Name', $alwInfo['name']);
                })
                ->first();

            if ($alwRow) {
                $upAlw = [
                    'Amounts' => $alwInfo['amount'],
                    'Note'    => $alwInfo['note'],
                ];
                if (Schema::hasColumn('payrol_group_lists', 'Modified_By')) {
                    $upAlw['Modified_By'] = $operatorUsername;
                }
                if (Schema::hasColumn('payrol_group_lists', 'Modified_Date')) {
                    $upAlw['Modified_Date'] = $now;
                }
                DB::table('payrol_group_lists')->where('ID', $alwRow->ID)->update($upAlw);
            } elseif ($alwInfo['amount'] > 0) {
                DB::table('payrol_group_lists')->insert([
                    'Main_ID'       => $mainId,
                    'Group_ID'      => $nik,
                    'Payroll_ID'    => $alwCode,
                    'Name'          => $alwInfo['name'],
                    'Kena_Potongan' => 0,
                    'Default_PPH'   => 1,
                    'Type_Payroll'  => 'ALLOWANCE',
                    'Amounts'       => $alwInfo['amount'],
                    'Note'          => $alwInfo['note'],
                    'Created_By'    => $operatorUsername,
                    'Created_Date'  => $now,
                ]);
            }
        }

        // 4. Sinkronkan Deductions Dinamis (BPJS TK, BPJS KES, Late Penalty, Unpaid Leave)
        $dynamicDeductions = [
            'PY000037' => [
                'name'    => 'IURAN JHT (BPJS TK)',
                'amount'  => -$calc['bpjs_tk_amount'],
                'aliases' => ['JHT', 'BPJS TK', 'KETENAGAKERJAAN'],
            ],
            'PY000032' => [
                'name'    => 'IURAN BPJS KESEHATAN',
                'amount'  => -$calc['bpjs_kes_amount'],
                'aliases' => ['BPJS KESEHATAN', 'BPJS KES', 'KESEHATAN'],
            ],
            'DED_LATE' => [
                'name'    => 'POTONGAN KETERLAMBATAN',
                'amount'  => -$calc['late_penalty_amount'],
                'aliases' => ['LATE', 'KETERLAMBATAN', 'TERLAMBAT'],
            ],
            'DED_UNPAID' => [
                'name'    => 'POTONGAN UNPAID LEAVE (ALPHA)',
                'amount'  => -$calc['unpaid_leave_amount'],
                'aliases' => ['UNPAID', 'ALPHA', 'ABSENSI'],
            ],
            'DED_SPPD_KASBON' => [
                'name'    => 'POTONGAN SPPD / KASBON / REIMBURSE',
                'amount'  => -((float) ($calc['metadata']['deduction_breakdown']['advance_deduction']['amount'] ?? 0.0)),
                'aliases' => ['SPPD', 'KASBON', 'REIMBURSE'],
            ],
        ];

        foreach ($dynamicDeductions as $dedCode => $dedInfo) {
            $dedRow = DB::table('payrol_group_lists')
                ->where('Main_ID', $mainId)
                ->where('Group_ID', $nik)
                ->where(function ($q) use ($dedCode, $dedInfo) {
                    $q->where('Payroll_ID', $dedCode);
                    foreach ($dedInfo['aliases'] as $alias) {
                        $q->orWhere('Name', 'LIKE', "%{$alias}%");
                    }
                })
                ->first();

            if ($dedRow) {
                $upData = ['Amounts' => $dedInfo['amount']];
                if (Schema::hasColumn('payrol_group_lists', 'Modified_By')) {
                    $upData['Modified_By'] = $operatorUsername;
                }
                if (Schema::hasColumn('payrol_group_lists', 'Modified_Date')) {
                    $upData['Modified_Date'] = $now;
                }
                DB::table('payrol_group_lists')->where('ID', $dedRow->ID)->update($upData);
            } elseif (abs($dedInfo['amount']) > 0) {
                DB::table('payrol_group_lists')->insert([
                    'Main_ID'       => $mainId,
                    'Group_ID'      => $nik,
                    'Payroll_ID'    => $dedCode,
                    'Name'          => $dedInfo['name'],
                    'Kena_Potongan' => 0,
                    'Default_PPH'   => 0,
                    'Type_Payroll'  => 'DEDUCTION',
                    'Amounts'       => $dedInfo['amount'],
                    'Note'          => 'Kalkulasi Otomatis',
                    'Created_By'    => $operatorUsername,
                    'Created_Date'  => $now,
                ]);
            }
        }
    }
}
