<?php

namespace App\Services\Payroll;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayrollThrService
{
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
     * Ambil basis upah THR karyawan (Gaji Pokok + Tunjangan Tetap / Kena_Potongan = 1)
     * sesuai Permenaker No. 6 Tahun 2016.
     *
     * @param string $nik
     * @param string|null $mainId
     * @return float
     */
    public function getThrBaseSalary($nik, $mainId = null)
    {
        $query = DB::table('payrol_group_lists')
            ->where('Group_ID', $nik);

        if (!empty($mainId)) {
            $query->where('Main_ID', $mainId);
        }

        $items = $query->get();
        $base = 0.0;

        foreach ($items as $item) {
            $type = strtoupper(trim((string)$item->Type_Payroll));
            $val = (isset($item->Prorate) && (float)$item->Prorate > 0)
                ? (float)$item->Prorate
                : (float)$item->Amounts;

            if ($type === 'BASIC') {
                $base += $val;
            } elseif ($type === 'ALLOWANCE' && (int)$item->Kena_Potongan === 1) {
                $base += $val;
            }
        }

        return max(0.0, $base);
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
        $religionFilter = trim((string)($params['religion_filter'] ?? 'ALL'));
        $mainIdFilter = !empty($params['main_id_filter']) ? trim((string)$params['main_id_filter']) : null;
        $prorateMethod = strtoupper(trim((string)($params['prorate_method'] ?? 'MONTH_FLOOR')));
        $note = trim((string)($params['note'] ?? ''));

        if (!in_array($prorateMethod, ['MONTH_FLOOR', 'DAY_365'], true)) {
            $prorateMethod = 'MONTH_FLOOR';
        }

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
                'e.Status'
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
            $prorateMethod,
            $note,
            $employees,
            $operator,
            $now
        ) {
            $batchId = DB::table('payroll_thr_batches')->insertGetId([
                'batch_no'        => $batchNo,
                'salary_period'   => $salaryPeriod,
                'thr_event_name'  => $eventName,
                'reference_date'  => $referenceDate,
                'religion_filter' => $religionFilter ?: 'ALL',
                'main_id_filter'  => $mainIdFilter,
                'prorate_method'  => $prorateMethod,
                'status'          => 'DRAFT',
                'total_employees' => 0,
                'total_amount'    => 0,
                'note'            => $note,
                'created_by'      => $operator,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $eligibleCount = 0;
            $totalAmount = 0.0;

            foreach ($employees as $emp) {
                $nik = trim((string)$emp->NIK);
                if ($nik === '') {
                    continue;
                }

                $tenure = $this->calculateTenureAndProrate($emp->Date_in, $referenceDate, $prorateMethod);
                $baseSalary = $this->getThrBaseSalary($nik, $emp->Salary_Group);

                $isEligible = $tenure['is_tenure_valid'] ? 1 : 0;
                $reason = $tenure['ineligible_reason'];

                // Cek status resign / keluar sebelum tanggal acuan -> flag default 0 agar bisa ditinjau HCGA
                $resignDate = null;
                if (!empty($emp->Resigned_Date) && $emp->Resigned_Date !== '0000-00-00') {
                    $resignDate = $emp->Resigned_Date;
                } elseif (!empty($emp->Date_Out) && $emp->Date_Out !== '0000-00-00') {
                    $resignDate = $emp->Date_Out;
                }

                if ($resignDate && $resignDate < $referenceDate) {
                    $isEligible = 0;
                    $reason = 'Resign/Keluar sebelum tgl acuan (' . $resignDate . ')';
                } elseif (!empty($emp->Status) && strtoupper(trim((string)$emp->Status)) !== 'ACTIVE') {
                    $isEligible = 0;
                    $reason = 'Status kepegawaian: ' . $emp->Status;
                }

                if ($baseSalary <= 0 && $isEligible === 1) {
                    $isEligible = 0;
                    $reason = 'Komponen gaji pokok/tetap belum disetup';
                }

                $calculated = ceil($baseSalary * $tenure['prorate_ratio']);
                $finalAmount = ($isEligible === 1) ? $calculated : 0.0;

                if ($isEligible === 1) {
                    $eligibleCount++;
                    $totalAmount += $finalAmount;
                }

                DB::table('payroll_thr_items')->insert([
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
                    'prorate_ratio'         => $tenure['prorate_ratio'],
                    'base_salary_amount'    => $baseSalary,
                    'thr_calculated_amount' => $calculated,
                    'thr_adjustment_amount' => 0,
                    'thr_final_amount'      => $finalAmount,
                    'is_eligible'           => $isEligible,
                    'ineligible_reason'     => $reason,
                    'is_payroll_processed'  => 0,
                    'salary_period'         => $salaryPeriod,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            }

            DB::table('payroll_thr_batches')
                ->where('id', $batchId)
                ->update([
                    'total_employees' => $eligibleCount,
                    'total_amount'    => $totalAmount,
                    'updated_at'      => $now,
                ]);

            return [
                'batch_id'        => $batchId,
                'batch_no'        => $batchNo,
                'total_employees' => $eligibleCount,
                'total_amount'    => $totalAmount,
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

        $isEligible = isset($data['is_eligible']) ? (int)$data['is_eligible'] : (int)$item->is_eligible;
        $adjustment = isset($data['thr_adjustment_amount']) ? (float)$data['thr_adjustment_amount'] : (float)$item->thr_adjustment_amount;
        $note = array_key_exists('note', $data) ? trim((string)$data['note']) : $item->note;

        $finalAmount = ($isEligible === 1)
            ? max(0.0, (float)$item->thr_calculated_amount + $adjustment)
            : 0.0;

        $now = Carbon::now()->toDateTimeString();

        return DB::transaction(function () use ($itemId, $item, $isEligible, $adjustment, $finalAmount, $note, $now) {
            DB::table('payroll_thr_items')
                ->where('id', $itemId)
                ->update([
                    'is_eligible'           => $isEligible,
                    'thr_adjustment_amount' => $adjustment,
                    'thr_final_amount'      => $finalAmount,
                    'note'                  => $note,
                    'updated_at'            => $now,
                ]);

            $summary = $this->recalculateBatchTotals($item->batch_id);

            return array_merge([
                'item_id'          => $itemId,
                'is_eligible'      => $isEligible,
                'thr_final_amount' => $finalAmount,
            ], $summary);
        });
    }

    /**
     * Hitung ulang total header batch THR.
     *
     * @param int $batchId
     * @return array
     */
    public function recalculateBatchTotals($batchId)
    {
        $agg = DB::table('payroll_thr_items')
            ->where('batch_id', $batchId)
            ->where('is_eligible', 1)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(thr_final_amount), 0) as total_amt')
            ->first();

        $totalEmp = $agg ? (int)$agg->cnt : 0;
        $totalAmt = $agg ? (float)$agg->total_amt : 0.0;

        DB::table('payroll_thr_batches')
            ->where('id', $batchId)
            ->update([
                'total_employees' => $totalEmp,
                'total_amount'    => $totalAmt,
                'updated_at'      => Carbon::now()->toDateTimeString(),
            ]);

        return [
            'total_employees' => $totalEmp,
            'total_amount'    => $totalAmt,
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
     * Ambil total THR yang siap digabungkan ke payslip bulan berjalan untuk seorang karyawan.
     * Hanya menarik batch berstatus LOCKED dan belum diproses ke salary_main.
     *
     * @param string $nik
     * @param string $periodeYm
     * @return array
     */
    public function getApprovedThrForEmployeePeriod($nik, $periodeYm)
    {
        if (!Schema::hasTable('payroll_thr_batches') || !Schema::hasTable('payroll_thr_items')) {
            return [
                'amount'    => 0.0,
                'item_ids'  => [],
                'batch_nos' => [],
            ];
        }

        $rows = DB::table('payroll_thr_items as i')
            ->join('payroll_thr_batches as b', 'b.id', '=', 'i.batch_id')
            ->where('i.nik', $nik)
            ->where('i.salary_period', $periodeYm)
            ->where('i.is_eligible', 1)
            ->where('i.is_payroll_processed', 0)
            ->where('b.status', 'LOCKED')
            ->select('i.id', 'i.thr_final_amount', 'b.batch_no')
            ->get();

        $total = 0.0;
        $itemIds = [];
        $batchNos = [];

        foreach ($rows as $r) {
            $amt = (float)$r->thr_final_amount;
            if ($amt > 0) {
                $total += $amt;
                $itemIds[] = (int)$r->id;
                $batchNos[] = $r->batch_no;
            }
        }

        return [
            'amount'    => $total,
            'item_ids'  => $itemIds,
            'batch_nos' => array_values(array_unique($batchNos)),
        ];
    }

    /**
     * Tandai item THR karyawan sudah masuk ke batch penggajian (idempotency guard).
     *
     * @param string $nik
     * @param string $periodeYm
     * @param string $taskId
     * @return void
     */
    public function markThrAsProcessed($nik, $periodeYm, $taskId)
    {
        if (!Schema::hasTable('payroll_thr_batches') || !Schema::hasTable('payroll_thr_items')) {
            return;
        }

        $items = DB::table('payroll_thr_items as i')
            ->join('payroll_thr_batches as b', 'b.id', '=', 'i.batch_id')
            ->where('i.nik', $nik)
            ->where('i.salary_period', $periodeYm)
            ->where('i.is_eligible', 1)
            ->where('i.is_payroll_processed', 0)
            ->where('b.status', 'LOCKED')
            ->select('i.id', 'i.batch_id')
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $itemIds = $items->pluck('id')->all();
        $batchIds = $items->pluck('batch_id')->unique()->all();
        $now = Carbon::now()->toDateTimeString();

        DB::table('payroll_thr_items')
            ->whereIn('id', $itemIds)
            ->update([
                'is_payroll_processed' => 1,
                'salary_id'            => $taskId,
                'updated_at'           => $now,
            ]);

        foreach ($batchIds as $bId) {
            $remaining = DB::table('payroll_thr_items')
                ->where('batch_id', $bId)
                ->where('is_eligible', 1)
                ->where('thr_final_amount', '>', 0)
                ->where('is_payroll_processed', 0)
                ->exists();

            if (!$remaining) {
                DB::table('payroll_thr_batches')
                    ->where('id', $bId)
                    ->where('status', 'LOCKED')
                    ->update([
                        'status'     => 'POSTED',
                        'updated_at' => $now,
                    ]);
            }
        }
    }
}
