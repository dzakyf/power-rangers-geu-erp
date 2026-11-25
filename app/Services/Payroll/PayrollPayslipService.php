<?php

namespace App\Services\Payroll;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollPayslipService
{
    /**
     * @var PayrollCalculationService
     */
    protected $calcService;

    public function __construct(PayrollCalculationService $calcService)
    {
        $this->calcService = $calcService;
    }

    /**
     * Siapkan Data Lengkap Payslip Digital (sesuai layout modern Talenta / PT GEU)
     * Mengakomodasi 2 skenario:
     * 1. Mode Recounting / Preview (di menu payroll-generator): simulasi kalkulasi terkini untuk pengecekan.
     * 2. Mode Final / Official (di menu payroll-report / approved): data definitif dari tabel salary & salary_lists.
     *
     * @param string $nik
     * @param string|null $period Format YYYY-MM
     * @param string|null $taskId
     * @param array $options ['source' => 'generator'|'recount'|'final', 'main_id' => ...]
     * @return array
     */
    public function getPayslipData($nik, $period = null, $taskId = null, array $options = [])
    {
        $period = $period ?: date('Y-m');
        $source = isset($options['source']) ? strtolower(trim($options['source'])) : '';
        $mainId = isset($options['main_id']) ? trim($options['main_id']) : null;

        // 1. Cek data definitif di tabel `salary` terlebih dahulu
        $historicalSalary = null;
        $isFinal = false;

        // Jika sumber BUKAN eksplisit generator/recount dan BUKAN task 'PREVIEW'
        if ($source !== 'generator' && $source !== 'recount' && $taskId !== 'PREVIEW') {
            if ($taskId) {
                $historicalSalary = DB::table('salary')
                    ->where('Task_ID', $taskId)
                    ->where('Group_ID', $nik)
                    ->first();
            } else {
                // Ambil dari salary_main terakhir pada periode tersebut
                $cleanPeriod = str_replace('-', '', $period);
                $historicalSalary = DB::table('salary')
                    ->join('salary_main', 'salary.Task_ID', '=', 'salary_main.Task_ID')
                    ->where('salary.Group_ID', $nik)
                    ->where(function($q) use ($period, $cleanPeriod) {
                        $q->where('salary.Salary_Period', $period)
                          ->orWhere('salary.Salary_Period', $cleanPeriod)
                          ->orWhere('salary_main.Salary_Period', $period);
                    })
                    ->orderBy('salary_main.id', 'desc')
                    ->select('salary.*', 'salary_main.Salary_Period as Main_Period', 'salary_main.Status as Batch_Status')
                    ->first();
            }
        }

        // 2. Ambil Profil Kepegawaian & Akun User
        $employee = DB::table('employment')->where('NIK', $nik)->first();
        $user = DB::table('users')->where('Emp_ID', $nik)->first();

        if (!$employee && !$user && !$historicalSalary) {
            throw new \InvalidArgumentException("Data karyawan dengan NIK {$nik} tidak ditemukan.");
        }

        $companyName = 'PT Green Energi Utama';
        $employeeName = $employee ? $employee->Name : ($user ? $user->username : ($historicalSalary ? $historicalSalary->Group_Name : $nik));
        $organization = $employee ? ($employee->Department ?: 'General') : ($user ? $user->Department : ($historicalSalary ? ($historicalSalary->Main_ID ?: 'General') : 'General'));
        $jobPosition = $employee ? ($employee->Position ?: '-') : ($user ? $user->Position : '-');
        $gradeLevel = $employee ? (($employee->Employment_Grade ?? null) ?: (($employee->Employment_Class ?? null) ?: '-')) : '-';
        $ptkp = $employee ? ($employee->PTKP_Status ?: 'TK/0') : 'TK/0';
        $npwp = ($employee && !empty($employee->NPWP)) ? $employee->NPWP : '-';
        $joinDateRaw = $employee ? $employee->Date_in : null;

        $joinDateFormatted = '-';
        $tenure = '';
        if ($joinDateRaw) {
            $joinCarbon = Carbon::parse($joinDateRaw);
            $joinDateFormatted = $joinCarbon->format('d M Y');
            $diff = $joinCarbon->diff(Carbon::now());
            $tenure = "({$diff->y}y {$diff->m}m {$diff->d}d)";
        }

        // =====================================================================
        // KASUS 1: FINAL / DEFINITIF DARI TABEL SALARY (HASIL PAYROLL REPORT)
        // =====================================================================
        if ($historicalSalary) {
            $isFinal = true;
            $batchStatus = $historicalSalary->Batch_Status ?? 'FINAL';

            // Decode metadata jika tersedia
            $meta = [];
            if (!empty($historicalSalary->calculation_metadata)) {
                $meta = is_string($historicalSalary->calculation_metadata)
                    ? json_decode($historicalSalary->calculation_metadata, true)
                    : (array)$historicalSalary->calculation_metadata;
            }
            $att = isset($meta['attendance_metrics']) ? $meta['attendance_metrics'] : [];
            $tax = isset($meta['tax_details']) ? $meta['tax_details'] : [];
            $ded = isset($meta['deduction_breakdown']) ? $meta['deduction_breakdown'] : [];

            $cutoffLabel = isset($att['cutoff_label']) ? $att['cutoff_label'] : ($period . '-01 s/d ' . $period . '-28');
            if (!empty($employee->PTKP_Status)) {
                $ptkp = $employee->PTKP_Status;
            } elseif (!empty($meta['ptkp_status'])) {
                $ptkp = $meta['ptkp_status'];
            }

            // Cek snapshot rincian komponen di salary_lists
            $savedLists = DB::table('salary_lists')
                ->where('Task_ID', $historicalSalary->Task_ID)
                ->where('Group_ID', $nik)
                ->get();

            $earningsItems = [];
            $additionalEarnings = [];
            $deductionsItems = [];
            $additionalDeductions = [];

            if ($savedLists->isNotEmpty()) {
                $basicSalary = 0.0;
                foreach ($savedLists as $sl) {
                    $type = strtoupper(trim((string)$sl->Type_Payroll));
                    $amt = (float)$sl->Amounts;
                    $name = trim((string)$sl->Name);
                    $pId = strtoupper(trim((string)$sl->Payroll_ID));

                    if ($type === 'BASIC') {
                        $basicSalary += $amt;
                    } elseif ($type === 'ALLOWANCE') {
                        if ($pId === 'PY000060' || stripos($name, 'THR') !== false || stripos($name, 'HARI RAYA') !== false) {
                            continue;
                        }
                        if ($pId === 'PYH00001' || stripos($name, 'TUNJANGAN PAJAK') !== false || stripos($name, 'TUNJANGAN PPH') !== false) {
                            $additionalEarnings[] = ['name' => $name, 'amount' => abs($amt)];
                        } else {
                            $earningsItems[] = ['name' => $name, 'amount' => $amt];
                        }
                    } elseif ($type === 'DEDUCTION') {
                        if ($pId === 'PY000000' || stripos($name, 'PPH') !== false || stripos($name, 'PAJAK') !== false) {
                            $terCat = isset($tax['ter_category']) ? " (TER {$tax['ter_category']})" : '';
                            $deductionsItems[] = ['name' => $name . $terCat, 'amount' => abs($amt)];
                        } elseif ($pId === 'PY000032') {
                            $bpjsMeta = $meta['deduction_breakdown']['bpjs_kes'] ?? [];
                            $dispMode = strtoupper(trim((string)($bpjsMeta['display_mode'] ?? 'SEPARATE')));
                            $extraCnt = (int)($bpjsMeta['extra_family_count'] ?? 0);
                            $label = ($dispMode === 'COMBINED' && $extraCnt > 0)
                                ? "IURAN BPJS KESEHATAN (1% + {$extraCnt} Kel. Tambahan)"
                                : $name;
                            $deductionsItems[] = ['name' => $label, 'amount' => abs($amt)];
                        } elseif ($pId === 'DED_BPJS_KES_EXTRA') {
                            if (abs($amt) > 0) {
                                $bpjsMeta = $meta['deduction_breakdown']['bpjs_kes'] ?? [];
                                $extraCnt = (int)($bpjsMeta['extra_family_count'] ?? 0);
                                $extraPct = isset($bpjsMeta['extra_family_rate']) ? (float)$bpjsMeta['extra_family_rate'] : 1.0;
                                $label = $extraCnt > 0
                                    ? "BPJS Kesehatan Keluarga Tambahan ({$extraCnt} org @ {$extraPct}%)"
                                    : $name;
                                $deductionsItems[] = ['name' => $label, 'amount' => abs($amt)];
                            }
                        } elseif (stripos($name, 'BPJS') !== false || stripos($name, 'JHT') !== false || stripos($name, 'PENSIUN') !== false) {
                            $deductionsItems[] = ['name' => $name, 'amount' => abs($amt)];
                        } else {
                            $additionalDeductions[] = ['name' => $name, 'amount' => abs($amt)];
                        }
                    }
                }

                $finalPokok = $basicSalary > 0 ? $basicSalary : (float)$historicalSalary->Pokok;
                array_unshift($earningsItems, ['name' => 'Basic salary', 'amount' => $finalPokok]);

                // Pastikan Overtime & KJK ada jika terdapat nilai pada kolom salary
                if ((float)$historicalSalary->Overtime > 0) {
                    $hasOt = false;
                    foreach ($earningsItems as $ei) {
                        if (stripos($ei['name'], 'Overtime') !== false || stripos($ei['name'], 'Lembur') !== false) {
                            $hasOt = true;
                            break;
                        }
                    }
                    if (!$hasOt) {
                        $earningsItems[] = ['name' => 'Overtime / Upah Lembur', 'amount' => (float)$historicalSalary->Overtime];
                    }
                }

                if (isset($historicalSalary->KJK) && (float)$historicalSalary->KJK > 0) {
                    $hasKjk = false;
                    foreach ($earningsItems as $ei) {
                        if (stripos($ei['name'], 'KJK') !== false) {
                            $hasKjk = true;
                            break;
                        }
                    }
                    if (!$hasKjk) {
                        $earningsItems[] = ['name' => 'Insentif KJK', 'amount' => (float)$historicalSalary->KJK];
                    }
                }
            } else {
                // Fallback struktur standar dari record salary
                $basicSalary = (float)$historicalSalary->Pokok;
                $dailyAllowance = (float)$historicalSalary->Tunjangan;
                $overtime = (float)$historicalSalary->Overtime;
                $kjk = isset($historicalSalary->KJK) ? (float)$historicalSalary->KJK : 0.0;
                $taxAllowance = (float)$historicalSalary->Tunjangan_PPH;

                $earningsItems[] = ['name' => 'Basic salary', 'amount' => $basicSalary];
                if ($dailyAllowance > 0) {
                    $earningsItems[] = ['name' => 'Daily allowance / Tunjangan', 'amount' => $dailyAllowance];
                }
                if ($overtime > 0) {
                    $earningsItems[] = ['name' => 'Overtime / Upah Lembur', 'amount' => $overtime];
                }
                if ($kjk > 0) {
                    $earningsItems[] = ['name' => 'Insentif KJK', 'amount' => $kjk];
                }

                if ($taxAllowance > 0) {
                    $additionalEarnings[] = ['name' => 'PPh 21 DTP / Tunjangan Pajak', 'amount' => $taxAllowance];
                }

                $bpjsKesEmployee = (float)$historicalSalary->bpjs_kes_amount;
                $jhtEmployee = (float)$historicalSalary->bpjs_tk_amount;
                $pph21Employee = abs((float)$historicalSalary->PPH);

                if ($bpjsKesEmployee > 0) {
                    $this->appendBpjsKesDeductionItems($deductionsItems, $bpjsKesEmployee, $meta);
                }
                if ($jhtEmployee > 0) {
                    $deductionsItems[] = ['name' => 'BPJS Ketenagakerjaan (JHT 2%)', 'amount' => $jhtEmployee];
                }
                if ($pph21Employee > 0) {
                    $terCat = isset($tax['ter_category']) ? " (TER {$tax['ter_category']})" : '';
                    $deductionsItems[] = ['name' => 'PPh 21' . $terCat, 'amount' => $pph21Employee];
                }

                if ((float)$historicalSalary->unpaid_leave_amount > 0) {
                    $additionalDeductions[] = [
                        'name' => 'Unpaid Leave / Alpha (' . ($historicalSalary->absent_days ?? 0) . ' hr)',
                        'amount' => (float)$historicalSalary->unpaid_leave_amount,
                    ];
                }
                if ((float)$historicalSalary->late_penalty_amount > 0) {
                    $additionalDeductions[] = [
                        'name' => 'Denda Keterlambatan (' . ($historicalSalary->late_occurrences ?? 0) . 'x)',
                        'amount' => (float)$historicalSalary->late_penalty_amount,
                    ];
                }

                $stdDeductions = $bpjsKesEmployee + $jhtEmployee + (float)$historicalSalary->unpaid_leave_amount + (float)$historicalSalary->late_penalty_amount;
                $customDed = (float)$historicalSalary->Deduction - $stdDeductions;
                if ($customDed > 0) {
                    $additionalDeductions[] = [
                        'name' => 'Other Deductions / Pinjaman',
                        'amount' => $customDed,
                    ];
                }
            }

            $totalEarnings = 0.0;
            foreach ($earningsItems as $e) $totalEarnings += $e['amount'];
            foreach ($additionalEarnings as $ae) $totalEarnings += $ae['amount'];

            $totalDeductions = 0.0;
            foreach ($deductionsItems as $d) $totalDeductions += $d['amount'];
            foreach ($additionalDeductions as $ad) $totalDeductions += $ad['amount'];

            $thp = (float)$historicalSalary->THP > 0 ? (float)$historicalSalary->THP : max(0.0, $totalEarnings - $totalDeductions);

            // Benefits* Porsi Perusahaan
            $baseForBenefit = (float)$historicalSalary->Pokok + (float)$historicalSalary->Tunjangan;
            $jpCap = 10042300.0;
            $jpSalary = min($baseForBenefit, $jpCap);
            $jpCompany = ceil($jpSalary * 0.02);
            $benefits = [
                ['name' => 'JKK Company (0.24%)', 'amount' => ceil($baseForBenefit * 0.0024)],
                ['name' => 'JKM Company (0.30%)', 'amount' => ceil($baseForBenefit * 0.0030)],
                ['name' => 'JHT Company (3.7%)', 'amount' => ceil($baseForBenefit * 0.037)],
                ['name' => 'Jaminan Pensiun Company (2%)', 'amount' => $jpCompany],
                ['name' => 'BPJS Kesehatan Company (4%)', 'amount' => ceil(min($baseForBenefit, 12000000.0) * 0.04)],
            ];

            $totalBenefits = 0.0;
            foreach ($benefits as $b) $totalBenefits += $b['amount'];

            // Attendance
            $scheduledDays = isset($att['scheduled_days']) ? (int)$att['scheduled_days'] : 26;
            $absentDays = (int)($historicalSalary->absent_days ?: ($att['absent_days'] ?? 0));
            $lateCount = (int)($historicalSalary->late_occurrences ?: ($att['late_count'] ?? 0));
            $presentDays = isset($att['present_days']) ? (int)$att['present_days'] : max(0, $scheduledDays - $absentDays);

            $attendanceSummary = [
                'actual_working_days'   => $presentDays,
                'schedule_working_days' => $scheduledDays,
                'dayoff'                => $att['dayoff_days'] ?? 4,
                'national_holiday'      => $att['holiday_days'] ?? 0,
                'absent_days'           => $absentDays,
                'late_count'            => $lateCount,
                'attendance_code'       => "H:{$presentDays} A:{$absentDays} L:{$lateCount}",
            ];

            $taskDisplayId = $historicalSalary->Task_ID;
            $noteDisplay = !empty($historicalSalary->Note) ? $historicalSalary->Note : 'Slip gaji digital resmi ini diterbitkan otomatis oleh sistem ERP HRIS HCGA.';
            $statusBadge = $batchStatus . ' (FINAL)';
        } else {
            // =====================================================================
            // KASUS 2: PREVIEW RECOUNTING (HASIL RECOUNTING PADA PAYROLL GENERATOR)
            // =====================================================================
            $isFinal = false;

            $calc = $this->calcService->calculateEmployeeSalary($nik, $period, ['main_id' => $mainId]);
            $meta = isset($calc['metadata']) ? $calc['metadata'] : [];
            $att = isset($meta['attendance_metrics']) ? $meta['attendance_metrics'] : [];
            $tax = isset($meta['tax_details']) ? $meta['tax_details'] : [];
            $ded = isset($meta['deduction_breakdown']) ? $meta['deduction_breakdown'] : [];

            $cutoffLabel = isset($att['cutoff_label']) ? $att['cutoff_label'] : ($period . '-01 s/d ' . $period . '-28');
            if (!empty($meta['ptkp_status'])) {
                $ptkp = $meta['ptkp_status'];
            }

            $basicSalary = (float)$calc['pokok'];
            $dailyAllowance = (float)$calc['tunjangan'];
            $overtime = (float)$calc['overtime'];
            $kjk = isset($calc['kjk']) ? (float)$calc['kjk'] : 0.0;
            $taxAllowance = (float)$calc['tunjangan_pph'];

            $earningsItems = [];
            $earningsItems[] = ['name' => 'Basic salary', 'amount' => $basicSalary];
            if ($dailyAllowance > 0) {
                $earningsItems[] = ['name' => 'Daily allowance / Tunjangan', 'amount' => $dailyAllowance];
            }
            if ($overtime > 0) {
                $earningsItems[] = ['name' => 'Overtime / Upah Lembur', 'amount' => $overtime];
            }
            if ($kjk > 0) {
                $earningsItems[] = ['name' => 'Insentif KJK', 'amount' => $kjk];
            }

            $additionalEarnings = [];
            if ($taxAllowance > 0) {
                $additionalEarnings[] = ['name' => 'PPh 21 DTP / Tunjangan Pajak', 'amount' => $taxAllowance];
            }

            $totalEarnings = $basicSalary + $dailyAllowance + $overtime + $kjk + $taxAllowance;

            $bpjsKesEmployee = (float)$calc['bpjs_kes_amount'];
            $jhtEmployee = (float)$calc['bpjs_tk_amount'];
            $pph21Employee = (float)$calc['pph'];
            $unpaidLeave = (float)$calc['unpaid_leave_amount'];
            $latePenalty = (float)$calc['late_penalty_amount'];

            $jpCap = 10042300.0;
            $jpSalary = min($basicSalary + $dailyAllowance, $jpCap);
            $jpEmployee = ceil($jpSalary * 0.01);

            $deductionsItems = [];
            if ($bpjsKesEmployee > 0) {
                $this->appendBpjsKesDeductionItems($deductionsItems, $bpjsKesEmployee, $meta);
            }
            if ($jhtEmployee > 0) {
                $deductionsItems[] = ['name' => 'BPJS Ketenagakerjaan (JHT 2%)', 'amount' => $jhtEmployee];
            }
            if ($jpEmployee > 0) {
                $deductionsItems[] = ['name' => 'Jaminan Pensiun Employee (1%)', 'amount' => $jpEmployee];
            }
            if ($pph21Employee > 0) {
                $terCat = isset($tax['ter_category']) ? " (TER {$tax['ter_category']})" : '';
                $deductionsItems[] = ['name' => 'PPh 21' . $terCat, 'amount' => $pph21Employee];
            }

            $additionalDeductions = [];
            if ($unpaidLeave > 0) {
                $additionalDeductions[] = ['name' => 'Unpaid Leave / Alpha (' . ($calc['absent_days'] ?? 0) . ' hr)', 'amount' => $unpaidLeave];
            }
            if ($latePenalty > 0) {
                $additionalDeductions[] = ['name' => 'Denda Keterlambatan (' . ($calc['late_occurrences'] ?? 0) . 'x)', 'amount' => $latePenalty];
            }

            if (!empty($meta['deduction_breakdown']['custom_ded'])) {
                $additionalDeductions[] = ['name' => 'Other Deductions / Pinjaman', 'amount' => (float)$meta['deduction_breakdown']['custom_ded']];
            }

            $advanceDeductionAmt = (float) ($meta['deduction_breakdown']['advance_deduction']['amount'] ?? 0);
            if ($advanceDeductionAmt > 0) {
                $additionalDeductions[] = [
                    'name'   => 'POTONGAN SPPD / KASBON / REIMBURSE',
                    'amount' => $advanceDeductionAmt,
                ];
            }

            $totalDeductions = 0.0;
            foreach ($deductionsItems as $d) $totalDeductions += $d['amount'];
            foreach ($additionalDeductions as $ad) $totalDeductions += $ad['amount'];

            $thp = max(0.0, $totalEarnings - $totalDeductions);

            $baseForBenefit = $basicSalary + $dailyAllowance;
            $benefits = [
                ['name' => 'JKK Company (0.24%)', 'amount' => ceil($baseForBenefit * 0.0024)],
                ['name' => 'JKM Company (0.30%)', 'amount' => ceil($baseForBenefit * 0.0030)],
                ['name' => 'JHT Company (3.7%)', 'amount' => ceil($baseForBenefit * 0.037)],
                ['name' => 'Jaminan Pensiun Company (2%)', 'amount' => ceil($jpSalary * 0.02)],
                ['name' => 'BPJS Kesehatan Company (4%)', 'amount' => ceil(min($baseForBenefit, 12000000.0) * 0.04)],
            ];

            $totalBenefits = 0.0;
            foreach ($benefits as $b) $totalBenefits += $b['amount'];

            $scheduledDays = isset($att['scheduled_days']) ? (int)$att['scheduled_days'] : 26;
            $absentDays    = (int)($calc['absent_days'] ?? 0);
            $lateCount     = (int)($calc['late_occurrences'] ?? 0);
            $presentDays   = isset($att['present_days']) ? (int)$att['present_days'] : max(0, $scheduledDays - $absentDays);

            $attendanceSummary = [
                'actual_working_days'  => $presentDays,
                'schedule_working_days'=> $scheduledDays,
                'dayoff'               => 4,
                'national_holiday'     => 0,
                'absent_days'          => $absentDays,
                'late_count'           => $lateCount,
                'attendance_code'      => "H:{$presentDays} A:{$absentDays} L:{$lateCount}",
            ];

            $taskDisplayId = 'PREVIEW-RECOUNT-' . str_replace('-', '', $period);
            $noteDisplay = 'Slip gaji digital ini merupakan simulasi perhitungan penggajian (Hasil Recounting). Belum difinalisasi.';
            $statusBadge = 'PREVIEW / RECOUNTING (DRAFT)';
        }

        // Email Karyawan
        $recipientEmail = $employee ? ($employee->Email ?: ($user ? $user->Email : null)) : null;

        return [
            'company_name'         => $companyName,
            'payroll_cutoff'       => $cutoffLabel,
            'id_name'              => $nik . ' / ' . $employeeName,
            'nik'                  => $nik,
            'name'                 => $employeeName,
            'email'                => $recipientEmail,
            'organization'         => $organization,
            'job_position'         => $jobPosition,
            'grade_level'          => $gradeLevel,
            'ptkp'                 => $ptkp,
            'npwp'                 => $npwp,
            'join_date'            => $joinDateFormatted . ' ' . $tenure,
            'period'               => $period,
            'task_id'              => $taskDisplayId,
            'source'               => $isFinal ? 'final' : 'generator',
            'is_final'             => $isFinal,
            'status_badge'         => $statusBadge,
            'earnings'             => $earningsItems,
            'additional_earnings'  => $additionalEarnings,
            'total_earnings'       => $totalEarnings,
            'deductions'           => $deductionsItems,
            'additional_deductions'=> $additionalDeductions,
            'total_deductions'     => $totalDeductions,
            'take_home_pay'        => $thp,
            'benefits'             => $benefits,
            'total_benefits'       => $totalBenefits,
            'attendance'           => $attendanceSummary,
            'note'                 => $noteDisplay,
            'main_id'              => $mainId,
        ];
    }

    /**
     * Format item potongan BPJS Kesehatan (dipisah atau digabung sesuai setting display_mode).
     *
     * @param array $deductionsItems
     * @param float $bpjsKesEmployee
     * @param array $meta
     * @return void
     */
    private function appendBpjsKesDeductionItems(array &$deductionsItems, float $bpjsKesEmployee, array $meta): void
    {
        $bpjsMeta = $meta['deduction_breakdown']['bpjs_kes'] ?? [];
        $dispMode = strtoupper(trim((string)($bpjsMeta['display_mode'] ?? 'SEPARATE')));
        $extraCnt = (int)($bpjsMeta['extra_family_count'] ?? 0);
        $extraAmt = (float)($bpjsMeta['extra_family_amount'] ?? 0);
        $baseAmt = isset($bpjsMeta['base_amount'])
            ? (float)$bpjsMeta['base_amount']
            : max(0.0, $bpjsKesEmployee - $extraAmt);
        $extraPct = isset($bpjsMeta['extra_family_rate']) ? (float)$bpjsMeta['extra_family_rate'] : 1.0;

        if ($extraCnt > 0 && $extraAmt > 0 && $dispMode === 'SEPARATE') {
            if ($baseAmt > 0) {
                $deductionsItems[] = ['name' => 'BPJS Kesehatan Employee (1%)', 'amount' => $baseAmt];
            }
            $deductionsItems[] = [
                'name'   => "BPJS Kesehatan Keluarga Tambahan ({$extraCnt} org @ {$extraPct}%)",
                'amount' => $extraAmt,
            ];
            return;
        }

        $label = $extraCnt > 0
            ? "BPJS Kesehatan Employee (1% + {$extraCnt} Kel. Tambahan)"
            : 'BPJS Kesehatan Employee (1%)';
        $deductionsItems[] = ['name' => $label, 'amount' => $bpjsKesEmployee];
    }
}
