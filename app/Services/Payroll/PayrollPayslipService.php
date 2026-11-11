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
     *
     * @param string $nik
     * @param string|null $period Format YYYY-MM
     * @param string|null $taskId
     * @return array
     */
    public function getPayslipData($nik, $period = null, $taskId = null)
    {
        $period = $period ?: date('Y-m');

        // 1. Ambil Profil Kepegawaian & Akun User
        $employee = DB::table('employment')->where('NIK', $nik)->first();
        $user = DB::table('users')->where('Emp_ID', $nik)->first();

        if (!$employee && !$user) {
            throw new \InvalidArgumentException("Data karyawan dengan NIK {$nik} tidak ditemukan.");
        }

        // Cek apakah ada data historis yang sudah di-generate di tabel `salary`
        $historicalSalary = null;
        if ($taskId) {
            $historicalSalary = DB::table('salary')
                ->where('Task_ID', $taskId)
                ->where('Group_ID', $nik)
                ->first();
        } else {
            // Ambil dari salary_main terakhir pada periode tersebut (kolom Salary_Period)
            $historicalSalary = DB::table('salary')
                ->join('salary_main', 'salary.Task_ID', '=', 'salary_main.Task_ID')
                ->where('salary.Group_ID', $nik)
                ->where(function($q) use ($period) {
                    $q->where('salary_main.Salary_Period', $period);
                })
                ->orderBy('salary_main.id', 'desc')
                ->select('salary.*', 'salary_main.Salary_Period as Main_Period')
                ->first();
        }

        // Jalankan kalkulasi detail jika metadata belum ada
        $calc = $this->calcService->calculateEmployeeSalary($nik, $period);
        $meta = isset($calc['metadata']) ? $calc['metadata'] : [];
        $att = isset($meta['attendance_metrics']) ? $meta['attendance_metrics'] : [];
        $tax = isset($meta['tax_details']) ? $meta['tax_details'] : [];
        $ded = isset($meta['deduction_breakdown']) ? $meta['deduction_breakdown'] : [];

        // 2. Data Header Karyawan
        $companyName = 'PT Green Energi Utama';
        $employeeName = $employee ? $employee->Name : ($user ? $user->username : $nik);
        $organization = $employee ? ($employee->Department ?: 'General') : ($user ? $user->Department : 'General');
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

        $cutoffLabel = isset($att['cutoff_label']) ? $att['cutoff_label'] : ($period . '-01 s/d ' . $period . '-28');

        // 3. Earnings (Penghasilan)
        $basicSalary = (float)$calc['pokok'];
        $dailyAllowance = (float)$calc['tunjangan'];
        $overtime = (float)$calc['overtime'];
        $taxAllowance = (float)$calc['tunjangan_pph']; // PPh 21 DTP / Gross-Up Allowance

        $earningsItems = [];
        $earningsItems[] = ['name' => 'Basic salary', 'amount' => $basicSalary];
        if ($dailyAllowance > 0) {
            $earningsItems[] = ['name' => 'Daily allowance / Tunjangan', 'amount' => $dailyAllowance];
        }
        if ($overtime > 0) {
            $earningsItems[] = ['name' => 'Overtime', 'amount' => $overtime];
        }

        $additionalEarnings = [];
        if ($taxAllowance > 0) {
            $additionalEarnings[] = ['name' => 'PPh 21 DTP / Tunjangan Pajak', 'amount' => $taxAllowance];
        }

        $totalEarnings = $basicSalary + $dailyAllowance + $overtime + $taxAllowance;

        // 4. Deductions (Potongan Karyawan)
        $bpjsKesEmployee = (float)$calc['bpjs_kes_amount'];
        $jhtEmployee = (float)$calc['bpjs_tk_amount'];
        
        // Jaminan Pensiun Karyawan 1% (Cap gaji maks 10.042.300 / PP 45/2015)
        $jpCap = 10042300.0;
        $jpSalary = min($basicSalary + $dailyAllowance, $jpCap);
        $jpEmployee = ceil($jpSalary * 0.01);

        $pph21Employee = (float)$calc['pph'];
        $unpaidLeave = (float)$calc['unpaid_leave_amount'];
        $latePenalty = (float)$calc['late_penalty_amount'];

        $deductionsItems = [];
        if ($bpjsKesEmployee > 0) {
            $deductionsItems[] = ['name' => 'BPJS Kesehatan Employee (1%)', 'amount' => $bpjsKesEmployee];
        }
        if ($jhtEmployee > 0) {
            $deductionsItems[] = ['name' => 'JHT Employee (2%)', 'amount' => $jhtEmployee];
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

        // Custom deductions dari salary items jika ada
        if (!empty($meta['deduction_breakdown']['custom_ded'])) {
            $additionalDeductions[] = ['name' => 'Other Deductions / Pinjaman', 'amount' => (float)$meta['deduction_breakdown']['custom_ded']];
        }

        $totalDeductions = 0.0;
        foreach ($deductionsItems as $d) $totalDeductions += $d['amount'];
        foreach ($additionalDeductions as $ad) $totalDeductions += $ad['amount'];

        // 5. Take Home Pay (THP)
        $thp = max(0.0, $totalEarnings - $totalDeductions);

        // 6. Benefits* (Porsi Ditanggung Perusahaan - Not included in THP)
        $baseForBenefit = $basicSalary + $dailyAllowance;
        $jkkCompany = ceil($baseForBenefit * 0.0024);     // JKK 0.24%
        $jkmCompany = ceil($baseForBenefit * 0.0030);     // JKM 0.30%
        $jhtCompany = ceil($baseForBenefit * 0.037);      // JHT Perusahaan 3.7%
        $jpCompany  = ceil($jpSalary * 0.02);             // JP Perusahaan 2%
        $bpjsKesCompany = ceil(min($baseForBenefit, 12000000.0) * 0.04); // BPJS Kes Perusahaan 4%

        $benefits = [
            ['name' => 'JKK Company (0.24%)', 'amount' => $jkkCompany],
            ['name' => 'JKM Company (0.30%)', 'amount' => $jkmCompany],
            ['name' => 'JHT Company (3.7%)', 'amount' => $jhtCompany],
            ['name' => 'Jaminan Pensiun Company (2%)', 'amount' => $jpCompany],
            ['name' => 'BPJS Kesehatan Company (4%)', 'amount' => $bpjsKesCompany],
        ];

        $totalBenefits = 0.0;
        foreach ($benefits as $b) {
            $totalBenefits += $b['amount'];
        }

        // 7. Attendance Summary
        $scheduledDays = isset($att['scheduled_days']) ? (int)$att['scheduled_days'] : 26;
        $presentDays   = isset($att['present_days']) ? (int)$att['present_days'] : max(0, $scheduledDays - (int)($calc['absent_days'] ?? 0));
        $absentDays    = (int)($calc['absent_days'] ?? 0);
        $lateCount     = (int)($calc['late_occurrences'] ?? 0);

        $attendanceSummary = [
            'actual_working_days'  => $presentDays,
            'schedule_working_days'=> $scheduledDays,
            'dayoff'               => 4,
            'national_holiday'     => 0,
            'company_holiday'      => 0,
            'attendance_code'      => "H:{$presentDays} A:{$absentDays} L:{$lateCount}",
        ];

        return [
            'company_name'         => $companyName,
            'payroll_cutoff'       => $cutoffLabel,
            'id_name'              => $nik . ' / ' . $employeeName,
            'nik'                  => $nik,
            'name'                 => $employeeName,
            'email'                => $employee ? ($employee->Email ?: ($user ? $user->Email : null)) : null,
            'organization'         => $organization,
            'job_position'         => $jobPosition,
            'grade_level'          => $gradeLevel,
            'ptkp'                 => $ptkp,
            'npwp'                 => $npwp,
            'join_date'            => $joinDateFormatted . ' ' . $tenure,
            'period'               => $period,
            'task_id'              => $taskId ?: ($historicalSalary ? $historicalSalary->Task_ID : 'PREVIEW'),
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
            'note'                 => 'Slip gaji digital ini diterbitkan otomatis oleh sistem ERP HRIS HCGA.',
        ];
    }
}
