<?php

namespace App\Services\Payroll;

use App\Services\HCGA\EmployeeThrSchemeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayrollThrPayslipService
{
    /**
     * Siapkan data lengkap Payslip THR digital (Web, Email, PDF, & Mobile ESS).
     *
     * @param int $batchId
     * @param string $nik
     * @return array
     */
    public function getThrPayslipData(int $batchId, string $nik): array
    {
        $nik = trim($nik);
        if ($batchId <= 0 || $nik === '') {
            throw new \InvalidArgumentException('Parameter batch THR dan NIK wajib diisi.');
        }

        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            throw new \InvalidArgumentException('Batch THR tidak ditemukan.');
        }

        $item = DB::table('payroll_thr_items')
            ->where('batch_id', $batchId)
            ->where('nik', $nik)
            ->first();

        if (!$item) {
            throw new \InvalidArgumentException("Data THR untuk NIK {$nik} pada batch {$batch->batch_no} tidak ditemukan.");
        }

        $employee = DB::table('employment')->where('NIK', $nik)->first();
        $user = DB::table('users')->where('Emp_ID', $nik)->first();

        $companyName = 'PT Green Energi Utama';
        $employeeName = $item->employee_name ?: ($employee ? $employee->Name : ($user ? $user->Name : $nik));
        $organization = $item->department ?: ($employee ? ($employee->Department ?: 'General') : 'General');
        $jobPosition = $employee ? ($employee->Position ?: '-') : '-';
        $gradeLevel = $employee ? (($employee->Employment_Grade ?? null) ?: (($employee->Employment_Class ?? null) ?: '-')) : '-';
        $ptkp = !empty($item->ptkp_status)
            ? $item->ptkp_status
            : ($employee ? ($employee->PTKP_Status ?: ($employee->Marital_Status ?: 'TK/0')) : 'TK/0');
        $npwp = ($employee && !empty($employee->NPWP)) ? $employee->NPWP : '-';
        $recipientEmail = $employee ? ($employee->Email ?: ($user ? $user->Email : null)) : ($user ? $user->Email : null);

        $joinDateRaw = $item->date_in ?: ($employee ? $employee->Date_in : null);
        $joinDateFormatted = '-';
        $tenureDisplay = $item->tenure_months . ' bln (' . $item->tenure_days . ' hr)';
        if ($joinDateRaw && $joinDateRaw !== '0000-00-00') {
            try {
                $joinCarbon = Carbon::parse($joinDateRaw);
                $joinDateFormatted = $joinCarbon->format('d M Y');
            } catch (\Throwable $e) {
                $joinDateFormatted = (string)$joinDateRaw;
            }
        }

        $refDateFormatted = '-';
        if (!empty($batch->reference_date)) {
            try {
                $refDateFormatted = Carbon::parse($batch->reference_date)->format('d M Y');
            } catch (\Throwable $e) {
                $refDateFormatted = (string)$batch->reference_date;
            }
        }

        $statusUpper = strtoupper(trim((string)$batch->status));
        $isFinal = ($statusUpper === 'APPROVED');
        $statusBadge = $isFinal ? 'FINAL' : ($statusUpper === 'DRAFT' ? 'PREVIEW / DRAFT' : $statusUpper);

        $schemeLabel = EmployeeThrSchemeService::label($item->thr_scheme ?? null);
        $proratePercent = round(((float)$item->prorate_ratio) * 100, 2);

        $thrCalculated = (float)$item->thr_calculated_amount;
        $thrAdjustment = (float)$item->thr_adjustment_amount;
        $thrBruto = (float)$item->thr_final_amount;
        $taxAllowance = isset($item->thr_tax_allowance) ? (float)$item->thr_tax_allowance : 0.0;
        $pph21Amount = isset($item->thr_pph21_amount) ? (float)$item->thr_pph21_amount : 0.0;
        $netThr = isset($item->thr_net_amount) && (float)$item->thr_net_amount > 0
            ? (float)$item->thr_net_amount
            : ($thrBruto > 0 ? max(0.0, ($thrBruto + $taxAllowance) - $pph21Amount) : 0.0);

        $taxMode = strtoupper(trim((string)($batch->tax_mode ?? 'NONE')));
        $isGrossUp = !empty($item->is_gross_up);

        $earnings = [];
        $earnings[] = [
            'name'   => "THR (Tunjangan Hari Raya) - {$schemeLabel} (Prorata {$proratePercent}%)",
            'amount' => $thrCalculated,
        ];
        if ($thrAdjustment != 0.0) {
            $earnings[] = [
                'name'   => 'Adjustment THR (+/-)',
                'amount' => $thrAdjustment,
            ];
        }

        $additionalEarnings = [];
        if ($taxAllowance > 0) {
            $additionalEarnings[] = [
                'name'   => 'Tunjangan PPh 21 THR (Gross-Up)',
                'amount' => $taxAllowance,
            ];
        }

        $deductions = [];
        if ($pph21Amount > 0) {
            $terCat = !empty($item->tax_ter_category) ? $item->tax_ter_category : '';
            $rateStr = isset($item->tax_rate_percent) && (float)$item->tax_rate_percent > 0
                ? rtrim(rtrim(number_format((float)$item->tax_rate_percent, 2, '.', ''), '0'), '.') . '%'
                : '';
            $suffix = trim($terCat . ' ' . $rateStr);
            $deductions[] = [
                'name'   => 'PPh 21 THR' . ($suffix !== '' ? " (TER {$suffix})" : ''),
                'amount' => $pph21Amount,
            ];
        }

        $totalEarnings = $thrBruto + $taxAllowance;
        $totalDeductions = $pph21Amount;
        $takeHomePay = ((int)$item->is_eligible === 1) ? max(0.0, $netThr) : 0.0;
        $terbilangId = function_exists('terbilang_id') ? terbilang_id($takeHomePay) : '';

        $noteDisplay = trim((string)($item->note ?? ''));
        if ($noteDisplay === '') {
            $noteDisplay = $isFinal
                ? 'Slip THR digital resmi ini diterbitkan oleh sistem ERP HRIS HCGA.'
                : 'Slip THR ini masih berstatus PREVIEW / DRAFT dan menunggu persetujuan final.';
        }

        return [
            'slip_type'                => 'THR',
            'company_name'             => $companyName,
            'batch_id'                 => (int)$batch->id,
            'batch_no'                 => $batch->batch_no,
            'event_name'               => $batch->thr_event_name,
            'period'                   => $batch->salary_period,
            'period_label'             => $this->formatPeriodLabel((string)$batch->salary_period),
            'reference_date'           => $batch->reference_date,
            'reference_date_formatted' => $refDateFormatted,
            'id_name'                  => $nik . ' / ' . $employeeName,
            'nik'                      => $nik,
            'name'                     => $employeeName,
            'email'                    => $recipientEmail,
            'organization'             => $organization,
            'job_position'             => $jobPosition,
            'grade_level'              => $gradeLevel,
            'ptkp'                     => $ptkp,
            'npwp'                     => $npwp,
            'join_date'                => $joinDateFormatted . ' (' . $tenureDisplay . ')',
            'tenure_label'             => $tenureDisplay,
            'thr_scheme_label'         => $schemeLabel,
            'prorate_ratio'            => (float)$item->prorate_ratio,
            'prorate_percent'          => $proratePercent,
            'base_salary_amount'       => (float)$item->base_salary_amount,
            'tax_mode'                 => $taxMode,
            'is_gross_up'              => $isGrossUp,
            'is_eligible'              => (int)$item->is_eligible,
            'is_final'                 => $isFinal,
            'batch_status'             => $statusUpper,
            'status_badge'             => $statusBadge,
            'approved_at'              => $batch->approved_at ?? null,
            'earnings'                 => $earnings,
            'additional_earnings'      => $additionalEarnings,
            'total_earnings'           => $totalEarnings,
            'deductions'               => $deductions,
            'total_deductions'         => $totalDeductions,
            'take_home_pay'            => $takeHomePay,
            'terbilang_id'             => $terbilangId,
            'note'                     => $noteDisplay,
        ];
    }

    /**
     * Ambil daftar Slip THR untuk Mobile ESS karyawan (STRICTLY batch DRAFT & APPROVED).
     *
     * @param string $nik
     * @param int $limit
     * @return array
     */
    public function getAvailableThrSlipsForEmployee(string $nik, int $limit = 12): array
    {
        $nik = trim($nik);
        if ($nik === '' || !Schema::hasTable('payroll_thr_batches') || !Schema::hasTable('payroll_thr_items')) {
            return [];
        }

        $rows = DB::table('payroll_thr_items as i')
            ->join('payroll_thr_batches as b', 'b.id', '=', 'i.batch_id')
            ->where('i.nik', $nik)
            ->where('i.is_eligible', 1)
            ->where('i.thr_final_amount', '>', 0)
            ->whereIn('b.status', ['DRAFT', 'APPROVED'])
            ->orderBy('b.reference_date', 'DESC')
            ->orderBy('b.id', 'DESC')
            ->limit($limit)
            ->select(
                'b.id as batch_id',
                'b.batch_no',
                'b.thr_event_name',
                'b.salary_period',
                'b.reference_date',
                'b.status as batch_status',
                'b.tax_mode',
                'b.approved_at',
                'i.thr_final_amount',
                'i.thr_pph21_amount',
                'i.thr_net_amount'
            )
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $statusUpper = strtoupper(trim((string)$row->batch_status));
            $isFinal = ($statusUpper === 'APPROVED');
            $net = isset($row->thr_net_amount) && (float)$row->thr_net_amount > 0
                ? (float)$row->thr_net_amount
                : max(0.0, (float)$row->thr_final_amount - (float)($row->thr_pph21_amount ?? 0));

            $result[] = [
                'batch_id'       => (int)$row->batch_id,
                'batch_no'       => (string)$row->batch_no,
                'event_name'     => (string)$row->thr_event_name,
                'period'         => (string)$row->salary_period,
                'period_label'   => $this->formatPeriodLabel((string)$row->salary_period),
                'reference_date' => (string)$row->reference_date,
                'status'         => $isFinal ? 'FINAL' : 'PREVIEW',
                'status_label'   => $isFinal ? 'FINAL' : 'PREVIEW (DRAFT)',
                'batch_status'   => $statusUpper,
                'tax_mode'       => (string)($row->tax_mode ?? 'NONE'),
                'thr_bruto'      => round((float)$row->thr_final_amount, 2),
                'pph21'          => round((float)($row->thr_pph21_amount ?? 0), 2),
                'thp'            => round($net, 2),
                'approved_at'    => $row->approved_at,
            ];
        }

        return $result;
    }

    /**
     * Ambil detail Slip THR untuk Mobile ESS (strictly scoped by NIK, status DRAFT atau APPROVED).
     *
     * @param string $nik
     * @param int $batchId
     * @return array
     */
    public function getMobileThrPayslipDetail(string $nik, int $batchId): array
    {
        $nik = trim($nik);
        $batch = DB::table('payroll_thr_batches')
            ->where('id', $batchId)
            ->whereIn('status', ['DRAFT', 'APPROVED'])
            ->first();

        if (!$batch) {
            throw new \InvalidArgumentException('Slip THR tidak ditemukan atau belum tersedia.');
        }

        $item = DB::table('payroll_thr_items')
            ->where('batch_id', $batchId)
            ->where('nik', $nik)
            ->where('is_eligible', 1)
            ->first();

        if (!$item) {
            throw new \InvalidArgumentException('Data Slip THR untuk akun Anda tidak ditemukan.');
        }

        return $this->getThrPayslipData($batchId, $nik);
    }

    protected function formatPeriodLabel(string $periodYm): string
    {
        if (!preg_match('/^(\d{4})-(\d{2})$/', trim($periodYm), $m)) {
            return $periodYm;
        }

        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        return ($months[$m[2]] ?? $m[2]) . ' ' . $m[1];
    }
}
