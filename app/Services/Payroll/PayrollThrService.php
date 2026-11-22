<?php

namespace App\Services\Payroll;

use App\Services\HCGA\EmployeeThrSchemeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayrollThrService
{
    public const TAX_MODE_NONE = 'NONE';
    public const TAX_MODE_TER_STANDALONE = 'TER_STANDALONE';

    /**
     * @var PayrollTaxService
     */
    protected $taxService;

    public function __construct(PayrollTaxService $taxService = null)
    {
        $this->taxService = $taxService ?: app(PayrollTaxService::class);
    }

    /**
     * Normalisasi mode pajak batch THR.
     *
     * @param string|null $mode
     * @return string
     */
    public function normalizeTaxMode($mode): string
    {
        $upper = strtoupper(trim((string)$mode));
        if ($upper === self::TAX_MODE_TER_STANDALONE) {
            return self::TAX_MODE_TER_STANDALONE;
        }
        return self::TAX_MODE_NONE;
    }

    /**
     * Hitung PPh 21 mandiri atas THR sesuai mode pajak batch ('NONE' atau 'TER_STANDALONE').
     *
     * @param float $thrFinalAmount
     * @param string $taxMode
     * @param object|null $employee
     * @return array
     */
    public function calculateItemTax(float $thrFinalAmount, string $taxMode, ?object $employee = null): array
    {
        $taxMode = $this->normalizeTaxMode($taxMode);
        $ptkpStatus = $employee
            ? (trim((string)($employee->PTKP_Status ?? '')) ?: (trim((string)($employee->Marital_Status ?? '')) ?: 'TK/0'))
            : 'TK/0';
        $isGrossUp = $employee && ((int)($employee->PPH_Allowance ?? 0) === 1);
        $ktp = $employee ? trim((string)($employee->KTP ?? '')) : '';
        $npwp = $employee ? trim((string)($employee->NPWP ?? '')) : '';
        if ($npwp === '' && $ktp !== '') {
            $npwp = $ktp;
        }
        $empNik = $employee ? trim((string)($employee->NIK ?? '')) : '';

        $bruto = max(0.0, $thrFinalAmount);

        if ($taxMode !== self::TAX_MODE_TER_STANDALONE || $bruto <= 0.0) {
            return [
                'ptkp_status'       => $ptkpStatus,
                'is_gross_up'       => $isGrossUp ? 1 : 0,
                'tax_ter_category'  => null,
                'tax_rate_percent'  => 0.0,
                'thr_tax_allowance' => 0.0,
                'thr_pph21_amount'  => 0.0,
                'thr_net_amount'    => $bruto,
                'tax_metadata'      => [
                    'mode'          => self::TAX_MODE_NONE,
                    'ptkp_code'     => $ptkpStatus,
                    'is_gross_up'   => $isGrossUp,
                    'gross_thr'     => $bruto,
                    'tax_amount'    => 0.0,
                    'tax_allowance' => 0.0,
                    'net_thr'       => $bruto,
                ],
            ];
        }

        $ter = $this->taxService->calculatePph21Ter($bruto, $ptkpStatus, [
            'is_gross_up'            => $isGrossUp,
            'nik'                    => $ktp !== '' ? $ktp : $empNik,
            'npwp'                   => $npwp,
            'apply_non_npwp_penalty' => false,
        ]);

        $taxAmount = (float)($ter['tax_amount'] ?? 0.0);
        $taxAllowance = (float)($ter['tax_allowance'] ?? 0.0);
        $netAmount = max(0.0, ($bruto + $taxAllowance) - $taxAmount);

        return [
            'ptkp_status'       => $ptkpStatus,
            'is_gross_up'       => $isGrossUp ? 1 : 0,
            'tax_ter_category'  => $ter['ter_category'] ?? null,
            'tax_rate_percent'  => (float)($ter['rate_percent'] ?? 0.0),
            'thr_tax_allowance' => $taxAllowance,
            'thr_pph21_amount'  => $taxAmount,
            'thr_net_amount'    => $netAmount,
            'tax_metadata'      => array_merge([
                'mode'    => self::TAX_MODE_TER_STANDALONE,
                'net_thr' => $netAmount,
            ], $ter),
        ];
    }
    /**
     * Hitung rasio prorata & masa kerja karyawan terhadap tanggal acuan Hari Raya.
     *
     * @param string|null $dateIn
     * @param string $referenceDate
     * @param string $prorateMethod 'MONTH_FLOOR' | 'DAY_365'
     * @return array
     */
    public function calculateTenureAndProrate($dateIn, $referenceDate, $prorateMethod = 'MONTH_FLOOR')
    {
        if (empty($dateIn) || $dateIn === '0000-00-00') {
            return [
                'tenure_months'     => 0,
                'tenure_days'       => 0,
                'prorate_ratio'     => 0.0,
                'is_tenure_valid'   => false,
                'ineligible_reason' => 'Tanggal masuk (Date_in) belum diisi',
            ];
        }

        $start = Carbon::parse($dateIn)->startOfDay();
        $ref = Carbon::parse($referenceDate)->startOfDay();

        if ($start->gt($ref)) {
            return [
                'tenure_months'     => 0,
                'tenure_days'       => 0,
                'prorate_ratio'     => 0.0,
                'is_tenure_valid'   => false,
                'ineligible_reason' => 'Tanggal masuk setelah tanggal acuan Hari Raya',
            ];
        }

        $diff = $start->diff($ref);
        $tenureMonths = ($diff->y * 12) + $diff->m;
        $tenureDays = $start->diffInDays($ref);

        if ($tenureMonths < 1) {
            return [
                'tenure_months'     => 0,
                'tenure_days'       => $tenureDays,
                'prorate_ratio'     => 0.0,
                'is_tenure_valid'   => false,
                'ineligible_reason' => 'Masa kerja kurang dari 1 bulan',
            ];
        }

        if ($tenureMonths >= 12) {
            $ratio = 1.0;
        } else {
            if (strtoupper($prorateMethod) === 'DAY_365') {
                $ratio = min(1.0, round($tenureDays / 365.0, 4));
            } else {
                $ratio = min(1.0, round($tenureMonths / 12.0, 4));
            }
        }

        return [
            'tenure_months'     => $tenureMonths,
            'tenure_days'       => $tenureDays,
            'prorate_ratio'     => $ratio,
            'is_tenure_valid'   => true,
            'ineligible_reason' => null,
        ];
    }

    /**
     * Ambil komponen gaji THR karyawan:
     * - gapok      : BASIC saja
     * - upah_bruto : BASIC + semua ALLOWANCE (termasuk COLA & seluruh tunjangan)
     * - upah_tetap : BASIC + ALLOWANCE dengan Kena_Potongan = 1 (standar Permenaker)
     *
     * @param string $nik
     * @param string|null $mainId
     * @return array
     */
    public function getEmployeeSalaryComponents($nik, $mainId = null)
    {
        $query = DB::table('payrol_group_lists')
            ->where('Group_ID', $nik);

        if (!empty($mainId)) {
            $query->where('Main_ID', $mainId);
        }

        $items = $query->get();
        $gapok = 0.0;
        $upahBruto = 0.0;
        $upahTetap = 0.0;

        foreach ($items as $item) {
            $type = strtoupper(trim((string)$item->Type_Payroll));
            $val = (isset($item->Prorate) && (float)$item->Prorate > 0)
                ? (float)$item->Prorate
                : (float)$item->Amounts;

            if ($type === 'BASIC') {
                $gapok += $val;
                $upahBruto += $val;
                $upahTetap += $val;
            } elseif ($type === 'ALLOWANCE') {
                $upahBruto += $val;
                if ((int)$item->Kena_Potongan === 1) {
                    $upahTetap += $val;
                }
            }
        }

        return [
            'gapok'      => max(0.0, $gapok),
            'upah_bruto' => max(0.0, $upahBruto),
            'upah_tetap' => max(0.0, $upahTetap),
        ];
    }

    /**
     * Ambil basis upah THR karyawan (Gaji Pokok + Tunjangan Tetap / Kena_Potongan = 1)
     * sesuai Permenaker No. 6 Tahun 2016.
     *
     * @param string $nik
     * @param string|null $mainId
     * @return float
     */
    public function getThrBaseSalary($nik, $mainId = null)
    {
        $components = $this->getEmployeeSalaryComponents($nik, $mainId);
        return $components['upah_tetap'];
    }

    /**
     * Hitung nominal dan eligibilitas THR karyawan berdasarkan skema efektif.
     *
     * @param array $components
     * @param array $tenure
     * @param string|null $effectiveScheme
     * @param string $referenceDate
     * @param string|null $resignDate
     * @param string|null $empStatus
     * @return array
     */
    protected function resolveEmployeeThrCalculation(
        array $components,
        array $tenure,
        ?string $effectiveScheme,
        string $referenceDate,
        ?string $resignDate,
        ?string $empStatus
    ): array {
        $isEligible = $tenure['is_tenure_valid'] ? 1 : 0;
        $reason = $tenure['ineligible_reason'];
        $effectiveRatio = $tenure['prorate_ratio'];
        $baseSalary = $components['upah_tetap'];

        $schemeKey = !empty($effectiveScheme) ? strtoupper(trim($effectiveScheme)) : 'DEFAULT_PERMENAKER';

        if ($schemeKey === 'FULL_UPAH') {
            $baseSalary = $components['upah_bruto'];
            $effectiveRatio = $tenure['is_tenure_valid'] ? 1.0 : 0.0;
        } elseif ($schemeKey === 'PRORATE_UPAH') {
            $baseSalary = $components['upah_bruto'];
            $effectiveRatio = $tenure['prorate_ratio'];
        } elseif ($schemeKey === 'FULL_GAPOK') {
            $baseSalary = $components['gapok'];
            $effectiveRatio = $tenure['is_tenure_valid'] ? 1.0 : 0.0;
        } elseif ($schemeKey === 'PRORATE_GAPOK') {
            $baseSalary = $components['gapok'];
            $effectiveRatio = $tenure['prorate_ratio'];
        } elseif ($schemeKey === 'NO_THR_UNDER_12M') {
            $baseSalary = $components['upah_bruto'];
            if ($tenure['tenure_months'] < 12) {
                $isEligible = 0;
                $effectiveRatio = 0.0;
                $reason = 'Kontrak < 12 bulan tidak dapat THR (Sesuai PKWT/MoU)';
            } else {
                $effectiveRatio = 1.0;
            }
        }

        if ($resignDate && $resignDate < $referenceDate) {
            $isEligible = 0;
            $reason = 'Resign/Keluar sebelum tgl acuan (' . $resignDate . ')';
        } elseif (!empty($empStatus) && strtoupper(trim((string)$empStatus)) !== 'ACTIVE') {
            $isEligible = 0;
            $reason = 'Status kepegawaian: ' . $empStatus;
        }

        if ($baseSalary <= 0 && $isEligible === 1) {
            $isEligible = 0;
            $reason = 'Komponen gaji pokok/tetap belum disetup';
        }

        $calculated = ($isEligible === 1) ? ceil($baseSalary * $effectiveRatio) : 0.0;

        return [
            'thr_scheme'            => ($schemeKey === 'DEFAULT_PERMENAKER') ? null : $schemeKey,
            'prorate_ratio'         => $effectiveRatio,
            'base_salary_amount'    => $baseSalary,
            'thr_calculated_amount' => $calculated,
            'is_eligible'           => $isEligible,
            'ineligible_reason'     => $reason,
        ];
    }

    /**
     * Generate atau regenerate draft batch THR untuk periode payslip tertentu.
     *
     * @param array $params
     * @param string $operator
     * @return array
     */
    public function generateBatchDraft(array $params, $operator = 'SYSTEM')
    {
        $salaryPeriod = trim((string)($params['salary_period'] ?? date('Y-m')));
        $eventName = trim((string)($params['thr_event_name'] ?? 'THR ' . $salaryPeriod));
        $referenceDate = trim((string)($params['reference_date'] ?? date('Y-m-d')));
        $religionFilter = trim((string)($params['religion_filter'] ?? 'ALL')) ?: 'ALL';
        $mainIdFilter = !empty($params['main_id_filter']) ? trim((string)$params['main_id_filter']) : null;
        $schemeMode = strtoupper(trim((string)($params['prorate_method'] ?? 'FOLLOW_EMPLOYEE')));
        $fallbackScheme = strtoupper(trim((string)($params['fallback_thr_scheme'] ?? 'DEFAULT_PERMENAKER')));
        $taxMode = $this->normalizeTaxMode($params['tax_mode'] ?? self::TAX_MODE_NONE);
        $note = trim((string)($params['note'] ?? ''));

        $validSchemes = array_merge(['FOLLOW_EMPLOYEE', 'DEFAULT_PERMENAKER', 'MONTH_FLOOR', 'DAY_365'], array_keys(EmployeeThrSchemeService::schemeOptions()));
        if (!in_array($schemeMode, $validSchemes, true)) {
            $schemeMode = 'FOLLOW_EMPLOYEE';
        }

        $validFallbacks = array_merge(['DEFAULT_PERMENAKER'], array_keys(EmployeeThrSchemeService::schemeOptions()));
        if (!in_array($fallbackScheme, $validFallbacks, true)) {
            $fallbackScheme = 'DEFAULT_PERMENAKER';
        }

        $prorateCalcMethod = ($schemeMode === 'DAY_365') ? 'DAY_365' : 'MONTH_FLOOR';

        $empQuery = DB::table('employment as e')
            ->select(
                'e.NIK',
                'e.Name',
                'e.Department',
                'e.Salary_Group',
                'e.Religion',
                'e.Date_in',
                'e.Date_Out',
                'e.Resigned_Date',
                'e.Status',
                'e.thr_scheme',
                'e.PTKP_Status',
                'e.Marital_Status',
                'e.PPH_Allowance',
                'e.KTP',
                'e.NPWP'
            )
            ->orderBy('e.Name', 'ASC');

        if (!empty($mainIdFilter)) {
            $empQuery->where('e.Salary_Group', $mainIdFilter);
        } else {
            $empQuery->whereNotNull('e.Salary_Group')->where('e.Salary_Group', '!=', '');
        }

        if (strtoupper($religionFilter) !== 'ALL' && $religionFilter !== '') {
            $empQuery->whereRaw('UPPER(TRIM(COALESCE(e.Religion, ""))) = ?', [strtoupper($religionFilter)]);
        }

        $employees = $empQuery->get();
        $now = Carbon::now()->toDateTimeString();
        $batchNo = 'THR-' . str_replace('-', '', $salaryPeriod) . '-' . date('His');

        return DB::transaction(function () use (
            $batchNo,
            $salaryPeriod,
            $eventName,
            $referenceDate,
            $religionFilter,
            $mainIdFilter,
            $schemeMode,
            $fallbackScheme,
            $taxMode,
            $prorateCalcMethod,
            $note,
            $employees,
            $operator,
            $now
        ) {
            $batchInsert = [
                'batch_no'            => $batchNo,
                'salary_period'       => $salaryPeriod,
                'thr_event_name'      => $eventName,
                'reference_date'      => $referenceDate,
                'religion_filter'     => $religionFilter ?: 'ALL',
                'main_id_filter'      => $mainIdFilter,
                'prorate_method'      => $schemeMode,
                'status'              => 'DRAFT',
                'total_employees'     => 0,
                'total_amount'        => 0,
                'note'                => $note,
                'created_by'          => $operator,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];

            if (Schema::hasColumn('payroll_thr_batches', 'fallback_thr_scheme')) {
                $batchInsert['fallback_thr_scheme'] = ($schemeMode === 'FOLLOW_EMPLOYEE') ? $fallbackScheme : null;
            }
            if (Schema::hasColumn('payroll_thr_batches', 'tax_mode')) {
                $batchInsert['tax_mode'] = $taxMode;
                $batchInsert['total_pph21'] = 0;
                $batchInsert['total_tax_allowance'] = 0;
                $batchInsert['total_net_amount'] = 0;
            }

            $batchId = DB::table('payroll_thr_batches')->insertGetId($batchInsert);

            $eligibleCount = 0;
            $totalAmount = 0.0;
            $totalPph21 = 0.0;
            $totalTaxAllowance = 0.0;
            $totalNetAmount = 0.0;

            foreach ($employees as $emp) {
                $nik = trim((string)$emp->NIK);
                if ($nik === '') {
                    continue;
                }

                $tenure = $this->calculateTenureAndProrate($emp->Date_in, $referenceDate, $prorateCalcMethod);
                $components = $this->getEmployeeSalaryComponents($nik, $emp->Salary_Group);
                $empScheme = !empty($emp->thr_scheme) ? strtoupper(trim((string)$emp->thr_scheme)) : null;

                if ($schemeMode === 'FOLLOW_EMPLOYEE' || $schemeMode === 'MONTH_FLOOR' || $schemeMode === 'DAY_365') {
                    $effectiveScheme = $empScheme ?: (($fallbackScheme === 'DEFAULT_PERMENAKER') ? null : $fallbackScheme);
                } elseif ($schemeMode === 'DEFAULT_PERMENAKER') {
                    $effectiveScheme = null;
                } else {
                    $effectiveScheme = $schemeMode;
                }

                // Cek status resign / keluar sebelum tanggal acuan -> flag default 0 agar bisa ditinjau HCGA
                $resignDate = null;
                if (!empty($emp->Resigned_Date) && $emp->Resigned_Date !== '0000-00-00') {
                    $resignDate = $emp->Resigned_Date;
                } elseif (!empty($emp->Date_Out) && $emp->Date_Out !== '0000-00-00') {
                    $resignDate = $emp->Date_Out;
                }

                $calc = $this->resolveEmployeeThrCalculation(
                    $components,
                    $tenure,
                    $effectiveScheme,
                    $referenceDate,
                    $resignDate,
                    $emp->Status
                );

                $finalAmount = ($calc['is_eligible'] === 1) ? $calc['thr_calculated_amount'] : 0.0;
                $taxCalc = $this->calculateItemTax($finalAmount, $taxMode, $emp);

                if ($calc['is_eligible'] === 1) {
                    $eligibleCount++;
                    $totalAmount += $finalAmount;
                    $totalPph21 += (float)$taxCalc['thr_pph21_amount'];
                    $totalTaxAllowance += (float)$taxCalc['thr_tax_allowance'];
                    $totalNetAmount += (float)$taxCalc['thr_net_amount'];
                }

                $itemInsert = [
                    'batch_id'              => $batchId,
                    'nik'                   => $nik,
                    'employee_name'         => $emp->Name,
                    'department'            => $emp->Department,
                    'main_id'               => $emp->Salary_Group,
                    'religion'              => $emp->Religion,
                    'date_in'               => (!empty($emp->Date_in) && $emp->Date_in !== '0000-00-00') ? $emp->Date_in : null,
                    'resigned_date'         => $resignDate,
                    'tenure_months'         => $tenure['tenure_months'],
                    'tenure_days'           => $tenure['tenure_days'],
                    'prorate_ratio'         => $calc['prorate_ratio'],
                    'thr_scheme'            => $calc['thr_scheme'],
                    'base_salary_amount'    => $calc['base_salary_amount'],
                    'thr_calculated_amount' => $calc['thr_calculated_amount'],
                    'thr_adjustment_amount' => 0,
                    'thr_final_amount'      => $finalAmount,
                    'is_eligible'           => $calc['is_eligible'],
                    'ineligible_reason'     => $calc['ineligible_reason'],
                    'is_payroll_processed'  => 0,
                    'salary_period'         => $salaryPeriod,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];

                if (Schema::hasColumn('payroll_thr_items', 'thr_net_amount')) {
                    $itemInsert['ptkp_status'] = $taxCalc['ptkp_status'];
                    $itemInsert['is_gross_up'] = $taxCalc['is_gross_up'];
                    $itemInsert['tax_ter_category'] = $taxCalc['tax_ter_category'];
                    $itemInsert['tax_rate_percent'] = $taxCalc['tax_rate_percent'];
                    $itemInsert['thr_tax_allowance'] = $taxCalc['thr_tax_allowance'];
                    $itemInsert['thr_pph21_amount'] = $taxCalc['thr_pph21_amount'];
                    $itemInsert['thr_net_amount'] = $taxCalc['thr_net_amount'];
                    $itemInsert['tax_metadata'] = json_encode($taxCalc['tax_metadata']);
                }

                DB::table('payroll_thr_items')->insert($itemInsert);
            }

            $batchTotals = [
                'total_employees' => $eligibleCount,
                'total_amount'    => $totalAmount,
                'updated_at'      => $now,
            ];
            if (Schema::hasColumn('payroll_thr_batches', 'total_net_amount')) {
                $batchTotals['total_pph21'] = $totalPph21;
                $batchTotals['total_tax_allowance'] = $totalTaxAllowance;
                $batchTotals['total_net_amount'] = $totalNetAmount;
            }

            DB::table('payroll_thr_batches')
                ->where('id', $batchId)
                ->update($batchTotals);

            return [
                'batch_id'            => $batchId,
                'batch_no'            => $batchNo,
                'tax_mode'            => $taxMode,
                'total_employees'     => $eligibleCount,
                'total_amount'        => $totalAmount,
                'total_pph21'         => $totalPph21,
                'total_tax_allowance' => $totalTaxAllowance,
                'total_net_amount'    => $totalNetAmount,
            ];
        });
    }

    /**
     * Update flag eligibilitas, adjustment manual, dan catatan pada item THR (khusus batch DRAFT).
     *
     * @param int $itemId
     * @param array $data
     * @return array
     */
    public function updateItem($itemId, array $data)
    {
        $item = DB::table('payroll_thr_items')->where('id', $itemId)->first();
        if (!$item) {
            throw new \InvalidArgumentException('Item THR tidak ditemukan.');
        }

        $batch = DB::table('payroll_thr_batches')->where('id', $item->batch_id)->first();
        if (!$batch || $batch->status !== 'DRAFT') {
            throw new \RuntimeException('Hanya batch berstatus DRAFT yang dapat diubah.');
        }

        $adjustment = isset($data['thr_adjustment_amount']) ? (float)$data['thr_adjustment_amount'] : (float)$item->thr_adjustment_amount;
        $note = array_key_exists('note', $data) ? trim((string)$data['note']) : $item->note;

        $updatePayload = [
            'thr_adjustment_amount' => $adjustment,
            'note'                  => $note,
        ];

        if (array_key_exists('thr_scheme', $data)) {
            $rawScheme = strtoupper(trim((string)($data['thr_scheme'] ?? '')));
            if ($rawScheme !== '' && $rawScheme !== 'DEFAULT_PERMENAKER' && !array_key_exists($rawScheme, EmployeeThrSchemeService::schemeOptions())) {
                throw new \InvalidArgumentException('Skema THR tidak valid.');
            }

            $emp = DB::table('employment')->where('NIK', $item->nik)->first();
            $prorateCalcMethod = ($batch->prorate_method === 'DAY_365') ? 'DAY_365' : 'MONTH_FLOOR';
            $dateIn = $item->date_in ?: ($emp->Date_in ?? null);
            $tenure = $this->calculateTenureAndProrate($dateIn, $batch->reference_date, $prorateCalcMethod);
            $components = $this->getEmployeeSalaryComponents($item->nik, $item->main_id ?: ($emp->Salary_Group ?? null));

            $calc = $this->resolveEmployeeThrCalculation(
                $components,
                $tenure,
                ($rawScheme === '' || $rawScheme === 'DEFAULT_PERMENAKER') ? null : $rawScheme,
                $batch->reference_date,
                $item->resigned_date,
                $emp ? $emp->Status : 'ACTIVE'
            );

            $isEligible = isset($data['is_eligible']) ? (int)$data['is_eligible'] : $calc['is_eligible'];
            $calculatedAmount = $calc['thr_calculated_amount'];
            $baseSalaryAmount = $calc['base_salary_amount'];
            $prorateRatio = $calc['prorate_ratio'];

            $updatePayload['thr_scheme'] = $calc['thr_scheme'];
            $updatePayload['prorate_ratio'] = $prorateRatio;
            $updatePayload['base_salary_amount'] = $baseSalaryAmount;
            $updatePayload['thr_calculated_amount'] = $calculatedAmount;
            $updatePayload['ineligible_reason'] = $calc['ineligible_reason'];
        } else {
            $isEligible = isset($data['is_eligible']) ? (int)$data['is_eligible'] : (int)$item->is_eligible;
            $calculatedAmount = (float)$item->thr_calculated_amount;
            $baseSalaryAmount = (float)$item->base_salary_amount;
            $prorateRatio = (float)$item->prorate_ratio;
        }

        $finalAmount = ($isEligible === 1)
            ? max(0.0, $calculatedAmount + $adjustment)
            : 0.0;

        $empForTax = DB::table('employment')
            ->where('NIK', $item->nik)
            ->select('NIK', 'PTKP_Status', 'Marital_Status', 'PPH_Allowance', 'KTP', 'NPWP')
            ->first();
        $taxMode = $this->normalizeTaxMode($batch->tax_mode ?? self::TAX_MODE_NONE);
        $taxCalc = $this->calculateItemTax($finalAmount, $taxMode, $empForTax);

        $updatePayload['is_eligible'] = $isEligible;
        $updatePayload['thr_final_amount'] = $finalAmount;
        if (Schema::hasColumn('payroll_thr_items', 'thr_net_amount')) {
            $updatePayload['ptkp_status'] = $taxCalc['ptkp_status'];
            $updatePayload['is_gross_up'] = $taxCalc['is_gross_up'];
            $updatePayload['tax_ter_category'] = $taxCalc['tax_ter_category'];
            $updatePayload['tax_rate_percent'] = $taxCalc['tax_rate_percent'];
            $updatePayload['thr_tax_allowance'] = $taxCalc['thr_tax_allowance'];
            $updatePayload['thr_pph21_amount'] = $taxCalc['thr_pph21_amount'];
            $updatePayload['thr_net_amount'] = $taxCalc['thr_net_amount'];
            $updatePayload['tax_metadata'] = json_encode($taxCalc['tax_metadata']);
        }

        $now = Carbon::now()->toDateTimeString();
        $updatePayload['updated_at'] = $now;

        return DB::transaction(function () use ($itemId, $item, $updatePayload, $isEligible, $baseSalaryAmount, $prorateRatio, $calculatedAmount, $finalAmount, $taxCalc) {
            DB::table('payroll_thr_items')
                ->where('id', $itemId)
                ->update($updatePayload);

            $summary = $this->recalculateBatchTotals($item->batch_id);

            return array_merge([
                'item_id'               => $itemId,
                'is_eligible'           => $isEligible,
                'prorate_ratio'         => $prorateRatio,
                'base_salary_amount'    => $baseSalaryAmount,
                'thr_calculated_amount' => $calculatedAmount,
                'thr_final_amount'      => $finalAmount,
                'ptkp_status'           => $taxCalc['ptkp_status'],
                'is_gross_up'           => $taxCalc['is_gross_up'],
                'tax_ter_category'      => $taxCalc['tax_ter_category'],
                'tax_rate_percent'      => $taxCalc['tax_rate_percent'],
                'thr_tax_allowance'     => $taxCalc['thr_tax_allowance'],
                'thr_pph21_amount'      => $taxCalc['thr_pph21_amount'],
                'thr_net_amount'        => $taxCalc['thr_net_amount'],
                'ineligible_reason'     => $updatePayload['ineligible_reason'] ?? $item->ineligible_reason,
            ], $summary);
        });
    }

    /**
     * Hitung ulang total header batch THR (Bruto, PPh 21, Tunjangan PPh, Nett).
     *
     * @param int $batchId
     * @return array
     */
    public function recalculateBatchTotals($batchId)
    {
        $hasTaxCols = Schema::hasColumn('payroll_thr_items', 'thr_net_amount');
        $selectRaw = $hasTaxCols
            ? 'COUNT(*) as cnt, COALESCE(SUM(thr_final_amount), 0) as total_amt, COALESCE(SUM(thr_pph21_amount), 0) as total_pph21, COALESCE(SUM(thr_tax_allowance), 0) as total_tax_allowance, COALESCE(SUM(thr_net_amount), 0) as total_net'
            : 'COUNT(*) as cnt, COALESCE(SUM(thr_final_amount), 0) as total_amt';

        $agg = DB::table('payroll_thr_items')
            ->where('batch_id', $batchId)
            ->where('is_eligible', 1)
            ->selectRaw($selectRaw)
            ->first();

        $totalEmp = $agg ? (int)$agg->cnt : 0;
        $totalAmt = $agg ? (float)$agg->total_amt : 0.0;
        $totalPph21 = ($hasTaxCols && $agg) ? (float)$agg->total_pph21 : 0.0;
        $totalTaxAllowance = ($hasTaxCols && $agg) ? (float)$agg->total_tax_allowance : 0.0;
        $totalNet = ($hasTaxCols && $agg) ? (float)$agg->total_net : $totalAmt;

        $updateBatch = [
            'total_employees' => $totalEmp,
            'total_amount'    => $totalAmt,
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];
        if (Schema::hasColumn('payroll_thr_batches', 'total_net_amount')) {
            $updateBatch['total_pph21'] = $totalPph21;
            $updateBatch['total_tax_allowance'] = $totalTaxAllowance;
            $updateBatch['total_net_amount'] = $totalNet;
        }

        DB::table('payroll_thr_batches')
            ->where('id', $batchId)
            ->update($updateBatch);

        return [
            'total_employees'     => $totalEmp,
            'total_amount'        => $totalAmt,
            'total_pph21'         => $totalPph21,
            'total_tax_allowance' => $totalTaxAllowance,
            'total_net_amount'    => $totalNet,
        ];
    }

    /**
     * Ubah mode PPh 21 pada batch THR berstatus DRAFT dan hitung ulang pajak seluruh item.
     *
     * @param int $batchId
     * @param string $taxMode 'NONE' | 'TER_STANDALONE'
     * @return array
     */
    public function updateBatchTaxMode(int $batchId, string $taxMode): array
    {
        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            throw new \InvalidArgumentException('Batch THR tidak ditemukan.');
        }
        if (strtoupper((string)$batch->status) !== 'DRAFT') {
            throw new \RuntimeException('Mode pajak hanya dapat diubah saat batch berstatus DRAFT.');
        }

        $normalizedMode = $this->normalizeTaxMode($taxMode);

        return DB::transaction(function () use ($batchId, $normalizedMode) {
            $now = Carbon::now()->toDateTimeString();

            DB::table('payroll_thr_batches')
                ->where('id', $batchId)
                ->update([
                    'tax_mode'   => $normalizedMode,
                    'updated_at' => $now,
                ]);

            $items = DB::table('payroll_thr_items')->where('batch_id', $batchId)->get();
            $niks = $items->pluck('nik')->filter()->unique()->values()->all();
            $employeesByNik = !empty($niks)
                ? DB::table('employment')
                    ->whereIn('NIK', $niks)
                    ->select('NIK', 'PTKP_Status', 'Marital_Status', 'PPH_Allowance', 'KTP', 'NPWP')
                    ->get()
                    ->keyBy('NIK')
                : collect([]);

            foreach ($items as $it) {
                $finalAmt = ((int)$it->is_eligible === 1) ? (float)$it->thr_final_amount : 0.0;
                $emp = $employeesByNik->get($it->nik);
                $taxCalc = $this->calculateItemTax($finalAmt, $normalizedMode, $emp);

                DB::table('payroll_thr_items')
                    ->where('id', $it->id)
                    ->update([
                        'ptkp_status'       => $taxCalc['ptkp_status'],
                        'is_gross_up'       => $taxCalc['is_gross_up'],
                        'tax_ter_category'  => $taxCalc['tax_ter_category'],
                        'tax_rate_percent'  => $taxCalc['tax_rate_percent'],
                        'thr_tax_allowance' => $taxCalc['thr_tax_allowance'],
                        'thr_pph21_amount'  => $taxCalc['thr_pph21_amount'],
                        'thr_net_amount'    => $taxCalc['thr_net_amount'],
                        'tax_metadata'      => json_encode($taxCalc['tax_metadata']),
                        'updated_at'        => $now,
                    ]);
            }

            $summary = $this->recalculateBatchTotals($batchId);

            return array_merge([
                'batch_id' => $batchId,
                'tax_mode' => $normalizedMode,
            ], $summary);
        });
    }

    /**
     * Kembalikan batch berstatus REJECTED, CANCELLED, atau LOCKED (legacy) ke status DRAFT untuk direvisi.
     *
     * @param int $batchId
     * @return bool
     */
    public function revertToDraft(int $batchId): bool
    {
        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            throw new \InvalidArgumentException('Batch THR tidak ditemukan.');
        }

        $statusUpper = strtoupper((string)$batch->status);
        if (!in_array($statusUpper, ['REJECTED', 'CANCELLED', 'CANCELED', 'LOCKED'], true)) {
            throw new \RuntimeException('Hanya batch berstatus REJECTED, CANCELLED, atau LOCKED yang dapat dikembalikan ke DRAFT.');
        }

        $update = [
            'status'     => 'DRAFT',
            'locked_by'  => null,
            'locked_at'  => null,
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
        if (Schema::hasColumn('payroll_thr_batches', 'approval_request_id')) {
            $update['approval_request_id'] = null;
            $update['submitted_by'] = null;
            $update['submitted_at'] = null;
            $update['rejected_by'] = null;
            $update['rejected_at'] = null;
            $update['rejection_reason'] = null;
        }

        return (bool)DB::table('payroll_thr_batches')->where('id', $batchId)->update($update);
    }

    /**
     * Ambil baris rekap THR & Bank Transfer Summary untuk export Excel Finance.
     *
     * @param int $batchId
     * @return array
     */
    public function getExportRowsForBatch(int $batchId): array
    {
        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            throw new \InvalidArgumentException('Batch THR tidak ditemukan.');
        }

        $items = DB::table('payroll_thr_items')
            ->where('batch_id', $batchId)
            ->where('is_eligible', 1)
            ->orderBy('department', 'ASC')
            ->orderBy('employee_name', 'ASC')
            ->get();

        $niks = $items->pluck('nik')->filter()->unique()->values()->all();
        $employeesByNik = collect();
        if (!empty($niks)) {
            $employeesByNik = DB::table('employment')
                ->whereIn('NIK', $niks)
                ->select(
                    'NIK',
                    'Position',
                    'Location',
                    'Work_Area',
                    'Payment_Reference',
                    'Payment_Reference2',
                    'Payment_Reference3'
                )
                ->get()
                ->keyBy(function ($emp) {
                    return strtoupper(trim((string)$emp->NIK));
                });
        }

        $taxMode = strtoupper(trim((string)($batch->tax_mode ?? 'NONE')));
        $rows = [];
        $no = 1;

        foreach ($items as $it) {
            $emp = $employeesByNik->get(strtoupper(trim((string)$it->nik)));
            $empName = $it->employee_name ?: $it->nik;
            $bruto = (float)$it->thr_final_amount;
            $pph21 = isset($it->thr_pph21_amount) ? (float)$it->thr_pph21_amount : 0.0;
            $taxAllow = isset($it->thr_tax_allowance) ? (float)$it->thr_tax_allowance : 0.0;
            $net = isset($it->thr_net_amount) && (float)$it->thr_net_amount > 0
                ? (float)$it->thr_net_amount
                : max(0.0, ($bruto + $taxAllow) - $pph21);

            $bankName1 = strtoupper(trim((string)($emp->Payment_Reference ?? '')));
            $bankAcc1 = $emp->Payment_Reference2 ?? null;
            $bankAn1 = $emp->Payment_Reference3 ?? null;
            $isBca = ($bankName1 !== '' && (strpos($bankName1, 'BCA') !== false || strpos($bankName1, 'CENTRAL ASIA') !== false));

            $bcaNorek = $isBca ? ($bankAcc1 ?: '-') : '-';
            $bcaAn = $isBca ? ($bankAn1 ?: $empName) : '-';
            $nonBcaBank = (!$isBca && $bankName1 !== '') ? $bankName1 : '-';
            $nonBcaNorek = !$isBca ? ($bankAcc1 ?: '-') : '-';
            $nonBcaAn = !$isBca ? ($bankAn1 ?: $empName) : '-';

            $empPosition = $emp->Position ?? null;
            $empLocation = $emp->Location ?? null;
            $empWorkArea = $emp->Work_Area ?? null;

            $rows[] = [
                'no'                  => $no++,
                'nik'                 => $it->nik,
                'nama'                => $empName,
                'jabatan'             => $empPosition ?: '-',
                'departemen'          => $it->department ?: '-',
                'site'                => $empLocation ?: ($empWorkArea ?: 'HO'),
                'agama'               => $it->religion ?: '-',
                'tanggal_join'        => $it->date_in ?: '-',
                'masa_kerja'          => $it->tenure_months . ' bln (' . $it->tenure_days . ' hr)',
                'skema_thr'           => EmployeeThrSchemeService::label($it->thr_scheme ?? null),
                'prorate_percent'     => round(((float)$it->prorate_ratio) * 100, 2),
                'basis_upah'          => (float)$it->base_salary_amount,
                'thr_sistem'          => (float)$it->thr_calculated_amount,
                'adjustment'          => (float)$it->thr_adjustment_amount,
                'thr_bruto'           => $bruto,
                'tax_mode'            => $taxMode,
                'ptkp'                => $it->ptkp_status ?: '-',
                'is_gross_up'         => !empty($it->is_gross_up) ? 'YA' : 'TIDAK',
                'tunjangan_pph'       => $taxAllow,
                'pph21_thr'           => $pph21,
                'nett_thr'            => $net,
                'terbilang_id'        => function_exists('terbilang_id') ? terbilang_id($net) : '',
                'terbilang_en'        => function_exists('terbilang_en') ? (terbilang_en($net) . ' Rupiah') : '',
                'bca_norek'           => $bcaNorek,
                'bca_atas_nama'       => $bcaAn,
                'selain_bca_bank'     => $nonBcaBank,
                'selain_bca_norek'    => $nonBcaNorek,
                'selain_bca_atas_nama'=> $nonBcaAn,
                'keterangan'          => $it->note ?: '-',
            ];
        }

        return [
            'batch' => $batch,
            'rows'  => $rows,
        ];
    }

    /**
     * Lock batch THR agar siap digabung ke Payroll Generator bulan berjalan.
     *
     * @param int $batchId
     * @param string $operator
     * @return bool
     */
    public function lockBatch($batchId, $operator = 'SYSTEM')
    {
        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            throw new \InvalidArgumentException('Batch THR tidak ditemukan.');
        }
        if ($batch->status !== 'DRAFT') {
            throw new \RuntimeException('Hanya batch DRAFT yang dapat di-lock.');
        }

        $now = Carbon::now()->toDateTimeString();

        return (bool)DB::table('payroll_thr_batches')
            ->where('id', $batchId)
            ->update([
                'status'     => 'LOCKED',
                'locked_by'  => $operator,
                'locked_at'  => $now,
                'updated_at' => $now,
            ]);
    }

    /**
     * Unlock batch THR kembali ke DRAFT (selama belum POSTED ke payroll).
     *
     * @param int $batchId
     * @return bool
     */
    public function unlockBatch($batchId)
    {
        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            throw new \InvalidArgumentException('Batch THR tidak ditemukan.');
        }
        if ($batch->status !== 'LOCKED') {
            throw new \RuntimeException('Hanya batch berstatus LOCKED yang dapat di-unlock.');
        }

        $hasProcessed = DB::table('payroll_thr_items')
            ->where('batch_id', $batchId)
            ->where('is_payroll_processed', 1)
            ->exists();

        if ($hasProcessed) {
            throw new \RuntimeException('Sebagian/seluruh item THR sudah diproses pada batch Payroll.');
        }

        return (bool)DB::table('payroll_thr_batches')
            ->where('id', $batchId)
            ->update([
                'status'     => 'DRAFT',
                'locked_by'  => null,
                'locked_at'  => null,
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]);
    }

    /**
     * Hapus batch THR berstatus DRAFT.
     *
     * @param int $batchId
     * @return bool
     */
    public function deleteDraftBatch($batchId)
    {
        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            throw new \InvalidArgumentException('Batch THR tidak ditemukan.');
        }
        if ($batch->status !== 'DRAFT') {
            throw new \RuntimeException('Hanya batch berstatus DRAFT yang dapat dihapus.');
        }

        return DB::transaction(function () use ($batchId) {
            DB::table('payroll_thr_items')->where('batch_id', $batchId)->delete();
            return (bool)DB::table('payroll_thr_batches')->where('id', $batchId)->delete();
        });
    }

    /**
     * DEPRECATED: THR kini berdiri mandiri dan tidak lagi digabungkan ke payroll bulanan.
     * Selalu mengembalikan nominal 0 agar tidak pernah ditarik ke salary_main/salary.
     *
     * @param string $nik
     * @param string $periodeYm
     * @return array
     */
    public function getApprovedThrForEmployeePeriod($nik, $periodeYm)
    {
        return [
            'amount'    => 0.0,
            'item_ids'  => [],
            'batch_nos' => [],
        ];
    }

    /**
     * DEPRECATED: No-op karena THR tidak lagi di-post ke batch payroll reguler.
     *
     * @param string $nik
     * @param string $periodeYm
     * @param string $taskId
     * @return void
     */
    public function markThrAsProcessed($nik, $periodeYm, $taskId)
    {
        return;
    }
}
