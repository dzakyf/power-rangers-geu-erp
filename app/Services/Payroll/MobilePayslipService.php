<?php

namespace App\Services\Payroll;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobilePayslipService
{
    /**
     * @var PayrollPayslipService
     */
    protected $payslipService;

    public function __construct(PayrollPayslipService $payslipService)
    {
        $this->payslipService = $payslipService;
    }

    /**
     * Ambil daftar riwayat periode payslip karyawan (FINAL dari salary + PREVIEW periode berjalan).
     *
     * @param  string  $nik
     * @param  int     $monthsBack
     * @return array
     */
    public function getAvailablePeriodsForEmployee(string $nik, int $monthsBack = 12): array
    {
        $nik = trim($nik);
        if ($nik === '') {
            return [];
        }

        $periods = [];
        $seenPeriodKeys = [];

        // 1. Ambil riwayat FINAL/POSTED dari tabel salary + salary_main
        if (Schema::hasTable('salary') && Schema::hasTable('salary_main')) {
            $rows = DB::table('salary')
                ->join('salary_main', 'salary.Task_ID', '=', 'salary_main.Task_ID')
                ->where('salary.Group_ID', $nik)
                ->orderBy('salary_main.id', 'desc')
                ->limit($monthsBack)
                ->select(
                    'salary.Task_ID',
                    'salary.Salary_Period as row_period',
                    'salary.Pokok',
                    'salary.Tunjangan',
                    'salary.Overtime',
                    'salary.KJK',
                    'salary.THR',
                    'salary.Deduction',
                    'salary.PPH',
                    'salary.THP',
                    'salary_main.Salary_Period as main_period',
                    'salary_main.Status as batch_status',
                    'salary_main.Created_Date as created_date'
                )
                ->get();

            foreach ($rows as $row) {
                $normPeriod = $this->normalizePeriod((string) ($row->main_period ?: $row->row_period));
                if ($normPeriod === '' || isset($seenPeriodKeys[$normPeriod])) {
                    continue;
                }
                $seenPeriodKeys[$normPeriod] = true;

                $totalEarnings = (float) $row->Pokok + (float) $row->Tunjangan + (float) $row->Overtime + (float) $row->KJK + (float) $row->THR;
                $totalDeductions = (float) $row->Deduction + abs((float) $row->PPH);
                $thp = (float) $row->THP > 0 ? (float) $row->THP : max(0.0, $totalEarnings - $totalDeductions);

                $periods[] = [
                    'period'           => $normPeriod,
                    'period_label'     => $this->formatPeriodLabel($normPeriod),
                    'status'           => 'FINAL',
                    'status_label'     => strtoupper((string) ($row->batch_status ?: 'FINAL')),
                    'task_id'          => (string) $row->Task_ID,
                    'thp'              => round($thp, 2),
                    'total_earnings'   => round($totalEarnings, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'created_date'     => $row->created_date,
                ];
            }
        }

        // 2. Tambahkan PREVIEW untuk periode berjalan jika belum ada data FINAL
        $currentPeriod = date('Y-m');
        if (!isset($seenPeriodKeys[$currentPeriod])) {
            try {
                $preview = $this->payslipService->getPayslipData($nik, $currentPeriod, null, ['source' => 'generator']);
                if ((float) ($preview['total_earnings'] ?? 0) > 0 || (float) ($preview['take_home_pay'] ?? 0) > 0) {
                    array_unshift($periods, [
                        'period'           => $currentPeriod,
                        'period_label'     => $this->formatPeriodLabel($currentPeriod),
                        'status'           => 'PREVIEW',
                        'status_label'     => 'PREVIEW (DRAFT)',
                        'task_id'          => null,
                        'thp'              => round((float) ($preview['take_home_pay'] ?? 0), 2),
                        'total_earnings'   => round((float) ($preview['total_earnings'] ?? 0), 2),
                        'total_deductions' => round((float) ($preview['total_deductions'] ?? 0), 2),
                        'created_date'     => null,
                    ]);
                }
            } catch (\Throwable $e) {
                // Abaikan jika karyawan belum memiliki konfigurasi gaji untuk preview periode berjalan
            }
        }

        usort($periods, function ($a, $b) {
            return strcmp($b['period'], $a['period']);
        });

        return array_slice($periods, 0, $monthsBack);
    }

    /**
     * Ambil detail lengkap payslip mobile beserta rincian potongan SPPD / Kasbon / Reimburse.
     *
     * @param  string       $nik
     * @param  string       $period
     * @param  string|null  $taskId
     * @return array
     */
    public function getMobilePayslipDetail(string $nik, string $period, ?string $taskId = null): array
    {
        $period = $this->normalizePeriod($period) ?: date('Y-m');
        $payslip = $this->payslipService->getPayslipData($nik, $period, $taskId ?: null);
        $payslip['period_label'] = $this->formatPeriodLabel($period);

        $advanceItems = [];
        if (Schema::hasTable('payroll_employee_deductions')) {
            $query = DB::table('payroll_employee_deductions')
                ->where('nik', $nik)
                ->where('salary_period', $period)
                ->where('deducted_amount', '>', 0);

            if (!empty($payslip['is_final']) && !empty($payslip['task_id'])) {
                $query->where('status', 'POSTED')
                    ->where(function ($q) use ($payslip) {
                        $q->where('payroll_task_id', $payslip['task_id'])
                          ->orWhereNull('payroll_task_id');
                    });
            } else {
                $query->where('status', 'DRAFT')->where('is_included', 1);
            }

            $advanceItems = $query->orderBy('id', 'asc')->get()->toArray();
        }

        $payslip['advance_items'] = $advanceItems;

        return $payslip;
    }

    protected function normalizePeriod(string $raw): string
    {
        $raw = trim($raw);
        if (preg_match('/^\d{4}-\d{2}$/', $raw)) {
            return $raw;
        }
        if (preg_match('/^(\d{4})(\d{2})$/', $raw, $m)) {
            return $m[1] . '-' . $m[2];
        }
        return '';
    }

    protected function formatPeriodLabel(string $periodYm): string
    {
        if (!preg_match('/^(\d{4})-(\d{2})$/', $periodYm, $m)) {
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

        $monthName = $months[$m[2]] ?? $m[2];

        return $monthName . ' ' . $m[1];
    }
}
