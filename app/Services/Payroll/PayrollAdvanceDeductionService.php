<?php

namespace App\Services\Payroll;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayrollAdvanceDeductionService
{
    /**
     * Tarik otomatis kewajiban outstanding (SPPD belum settlement, Kasbon selisih regulasi SPPD, dan Reimburse)
     * untuk seluruh karyawan pada Salary Group & Periode tertentu.
     */
    public function autoPullOutstandingForPeriod(string $mainId, string $periodeYm, string $operator = 'SYSTEM'): array
    {
        if (!Schema::hasTable('payroll_employee_deductions')) {
            return ['inserted' => 0, 'skipped' => 0];
        }

        $niks = $this->resolveTargetNiks($mainId);
        if (empty($niks)) {
            return ['inserted' => 0, 'skipped' => 0];
        }

        $candidates = [];

        // 1. SPPD Belum Settlement (SPPD_UNSETTLED)
        if (Schema::hasTable('sppd')) {
            $sppds = DB::table('sppd')
                ->whereIn('Requester_NIK', $niks)
                ->whereIn('Status', ['APPROVED', 'ON_GOING', 'COMPLETED'])
                ->get();

            foreach ($sppds as $sppd) {
                $hasClosedSettlement = Schema::hasTable('settlement_sppd')
                    ? DB::table('settlement_sppd')
                        ->where('SPPD_ID', $sppd->ID)
                        ->whereIn('Status', ['APPROVED', 'CLOSED'])
                        ->exists()
                    : false;

                if ($hasClosedSettlement) {
                    continue;
                }

                $costSum = Schema::hasTable('sppd_cost')
                    ? (float) DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->sum('Cost')
                    : 0.0;
                $amount = $costSum > 0 ? $costSum : (float) ($sppd->Estimated_Cost ?? 0);

                if ($amount > 0) {
                    $candidates[] = [
                        'nik'             => $sppd->Requester_NIK,
                        'deduction_type'  => 'SPPD_UNSETTLED',
                        'source_table'    => 'sppd',
                        'source_id'       => (int) $sppd->ID,
                        'reference_no'    => $sppd->SPPD_No ?: ('SPPD-' . $sppd->ID),
                        'description'     => 'SPPD Belum Settlement: ' . ($sppd->SPPD_No ?: $sppd->ID) . ($sppd->Destination ? ' (' . $sppd->Destination . ')' : ''),
                        'original_amount' => $amount,
                    ];
                }
            }
        }

        // 2. Kasbon Selisih di Luar Matriks Regulasi SPPD (KASBON_SPPD_EXCESS)
        if (Schema::hasTable('settlement_sppd')) {
            $excessRows = DB::table('settlement_sppd')
                ->whereIn('Requester_NIK', $niks)
                ->where('Status', '!=', 'REJECTED')
                ->whereNotNull('Advance_Nominal')
                ->whereNotNull('Matrix_Amount')
                ->whereRaw('Advance_Nominal > Matrix_Amount')
                ->get();

            foreach ($excessRows as $sttl) {
                $excess = (float) $sttl->Advance_Nominal - (float) $sttl->Matrix_Amount;
                if ($excess > 0) {
                    $candidates[] = [
                        'nik'             => $sttl->Requester_NIK,
                        'deduction_type'  => 'KASBON_SPPD_EXCESS',
                        'source_table'    => 'settlement_sppd',
                        'source_id'       => (int) $sttl->ID,
                        'reference_no'    => $sttl->STTL_No ?: ($sttl->SPPD_No ?: ('STTL-' . $sttl->ID)),
                        'description'     => 'Kasbon Selisih Plafon SPPD: ' . ($sttl->STTL_No ?: $sttl->SPPD_No),
                        'original_amount' => $excess,
                    ];
                }
            }
        }

        // 3. Reimburse Belum Selesai / Ada Kewajiban Pengembalian (REIMBURSE)
        if (Schema::hasTable('reimbursement')) {
            $reimbs = DB::table('reimbursement')
                ->whereIn('User_Responsibility_ID', $niks)
                ->where('Remain', '>', 0)
                ->whereNotIn('Status', ['CLOSED', 'REJECTED', 'CANCELLED', 'PAID'])
                ->get();

            foreach ($reimbs as $rmb) {
                $remain = (float) ($rmb->Remain ?? 0);
                if ($remain > 0) {
                    $candidates[] = [
                        'nik'             => $rmb->User_Responsibility_ID,
                        'deduction_type'  => 'REIMBURSE',
                        'source_table'    => 'reimbursement',
                        'source_id'       => (int) $rmb->ID,
                        'reference_no'    => $rmb->Reimbursement_No ?: ('RMB-' . $rmb->ID),
                        'description'     => 'Potongan Reimburse Outstanding: ' . ($rmb->Reimbursement_No ?: $rmb->ID),
                        'original_amount' => $remain,
                    ];
                }
            }
        }

        $inserted = 0;
        $skipped = 0;
        $now = now();

        foreach ($candidates as $cand) {
            $existsDraft = DB::table('payroll_employee_deductions')
                ->where('nik', $cand['nik'])
                ->where('salary_period', $periodeYm)
                ->where('deduction_type', $cand['deduction_type'])
                ->where('source_table', $cand['source_table'])
                ->where('source_id', $cand['source_id'])
                ->where('status', 'DRAFT')
                ->exists();

            if ($existsDraft) {
                $skipped++;
                continue;
            }

            $alreadyPosted = (float) DB::table('payroll_employee_deductions')
                ->where('nik', $cand['nik'])
                ->where('deduction_type', $cand['deduction_type'])
                ->where('source_table', $cand['source_table'])
                ->where('source_id', $cand['source_id'])
                ->where('status', 'POSTED')
                ->sum('deducted_amount');

            $remaining = max(0.0, round($cand['original_amount'] - $alreadyPosted, 2));
            if ($remaining <= 0) {
                $skipped++;
                continue;
            }

            if (!isset($thpCache[$cand['nik']])) {
                $thpCache[$cand['nik']] = $this->getAvailableThpForAdvance($cand['nik'], $periodeYm, $mainId);
                $queuedCache[$cand['nik']] = (float) DB::table('payroll_employee_deductions')
                    ->where('nik', $cand['nik'])
                    ->where('salary_period', $periodeYm)
                    ->where('status', 'DRAFT')
                    ->where('is_included', 1)
                    ->sum('deducted_amount');
            }

            $remainingCapacity = max(0.0, round($thpCache[$cand['nik']] - $queuedCache[$cand['nik']], 2));
            $deductedForPeriod = min($remaining, $remainingCapacity);
            $queuedCache[$cand['nik']] = round($queuedCache[$cand['nik']] + $deductedForPeriod, 2);

            DB::table('payroll_employee_deductions')->insert([
                'nik'              => $cand['nik'],
                'salary_period'    => $periodeYm,
                'main_id'          => $mainId ?: null,
                'deduction_type'   => $cand['deduction_type'],
                'source_table'     => $cand['source_table'],
                'source_id'        => $cand['source_id'],
                'reference_no'     => $cand['reference_no'],
                'description'      => mb_substr($cand['description'], 0, 255),
                'original_amount'  => $cand['original_amount'],
                'remaining_amount' => $remaining,
                'deducted_amount'  => round($deductedForPeriod, 2),
                'is_auto_pulled'   => 1,
                'is_included'      => 1,
                'status'           => 'DRAFT',
                'created_by'       => $operator,
                'updated_by'       => $operator,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            $inserted++;
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    /**
     * Ambil daftar item potongan untuk ditampilkan di modal Payroll Generator.
     */
    public function listDeductions(string $mainId, string $periodeYm, ?string $nik = null): array
    {
        if (!Schema::hasTable('payroll_employee_deductions')) {
            return ['items' => [], 'total_included' => 0.0];
        }

        $query = DB::table('payroll_employee_deductions as ped')
            ->leftJoin('employment as e', 'e.NIK', '=', 'ped.nik')
            ->where('ped.salary_period', $periodeYm)
            ->whereIn('ped.status', ['DRAFT', 'POSTED'])
            ->select('ped.*', 'e.Name as employee_name');

        if (!empty($nik)) {
            $query->where('ped.nik', $nik);
        } elseif (!empty($mainId)) {
            $niks = $this->resolveTargetNiks($mainId);
            if (!empty($niks)) {
                $query->whereIn('ped.nik', $niks);
            } else {
                $query->where('ped.main_id', $mainId);
            }
        }

        $items = $query->orderBy('ped.status', 'ASC')->orderBy('ped.id', 'DESC')->get();
        $totalIncluded = 0.0;
        foreach ($items as $item) {
            if ($item->status === 'DRAFT' && (int) $item->is_included === 1) {
                $totalIncluded += (float) $item->deducted_amount;
            }
        }

        return [
            'items'          => $items,
            'total_included' => round($totalIncluded, 2),
        ];
    }

    /**
     * Ambil total & rincian potongan aktif (DRAFT & is_included = 1) untuk 1 karyawan pada periode tertentu.
     */
    public function getActiveDeductionsForEmployee(string $nik, string $periodeYm): array
    {
        if (!Schema::hasTable('payroll_employee_deductions')) {
            return ['total' => 0.0, 'by_type' => [], 'items' => []];
        }

        $items = DB::table('payroll_employee_deductions')
            ->where('nik', $nik)
            ->where('salary_period', $periodeYm)
            ->where('status', 'DRAFT')
            ->where('is_included', 1)
            ->where('deducted_amount', '>', 0)
            ->get();

        $total = 0.0;
        $byType = [];
        foreach ($items as $item) {
            $amt = (float) $item->deducted_amount;
            $total += $amt;
            $type = $item->deduction_type;
            if (!isset($byType[$type])) {
                $byType[$type] = 0.0;
            }
            $byType[$type] += $amt;
        }

        return [
            'total'   => round($total, 2),
            'by_type' => $byType,
            'items'   => $items->toArray(),
        ];
    }

    /**
     * Hitung THP karyawan sebelum pemotongan SPPD/Kasbon/Reimburse agar potongan tidak pernah melebihi THP.
     */
    public function getAvailableThpForAdvance(string $nik, string $periodeYm, string $mainId = ''): float
    {
        try {
            $calcService = app(\App\Services\Payroll\PayrollCalculationService::class);
            $calc = $calcService->calculateEmployeeSalary($nik, $periodeYm, [
                'main_id'                   => $mainId,
                'ignore_advance_deductions' => true,
            ]);

            return max(0.0, round((float) ($calc['thp'] ?? 0.0), 2));
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /**
     * Tambah item potongan manual atau update nominal cicilan / status ikutkan pada item DRAFT.
     */
    public function saveManualOrUpdateItem(array $data, string $operator = 'SYSTEM'): object
    {
        $now = now();

        if (!empty($data['id'])) {
            $existing = DB::table('payroll_employee_deductions')
                ->where('id', (int) $data['id'])
                ->where('status', 'DRAFT')
                ->first();

            if (!$existing) {
                throw new \InvalidArgumentException('Item potongan DRAFT tidak ditemukan atau sudah di-POSTED.');
            }

            $deducted = isset($data['deducted_amount']) ? max(0.0, (float) $data['deducted_amount']) : (float) $existing->deducted_amount;
            if ((int) $existing->is_auto_pulled === 1 && $deducted > (float) $existing->remaining_amount) {
                $deducted = (float) $existing->remaining_amount;
            }

            $newIncluded = isset($data['is_included']) ? ((int) $data['is_included'] ? 1 : 0) : (int) $existing->is_included;

            if ($newIncluded === 1 && $deducted > 0) {
                $availableThp = $this->getAvailableThpForAdvance(
                    (string) $existing->nik,
                    (string) $existing->salary_period,
                    (string) ($existing->main_id ?? '')
                );
                $otherIncludedTotal = (float) DB::table('payroll_employee_deductions')
                    ->where('nik', $existing->nik)
                    ->where('salary_period', $existing->salary_period)
                    ->where('status', 'DRAFT')
                    ->where('is_included', 1)
                    ->where('id', '!=', $existing->id)
                    ->sum('deducted_amount');

                if (round($otherIncludedTotal + $deducted, 2) > round($availableThp, 2)) {
                    $maxAllowed = max(0.0, round($availableThp - $otherIncludedTotal, 2));
                    throw new \InvalidArgumentException(sprintf(
                        'Total potongan (Rp %s) melebihi sisa THP karyawan (Rp %s). Maksimal nominal untuk item ini: Rp %s.',
                        number_format($otherIncludedTotal + $deducted, 0, ',', '.'),
                        number_format($availableThp, 0, ',', '.'),
                        number_format($maxAllowed, 0, ',', '.')
                    ));
                }
            }

            $updatePayload = [
                'deducted_amount' => round($deducted, 2),
                'is_included'     => $newIncluded,
                'updated_by'      => $operator,
                'updated_at'      => $now,
            ];

            if (!empty($data['description'])) {
                $updatePayload['description'] = mb_substr(trim((string) $data['description']), 0, 255);
            }

            if ((int) $existing->is_auto_pulled === 0 && isset($data['deducted_amount'])) {
                $updatePayload['original_amount'] = round($deducted, 2);
                $updatePayload['remaining_amount'] = round($deducted, 2);
            }

            DB::table('payroll_employee_deductions')->where('id', $existing->id)->update($updatePayload);

            return DB::table('payroll_employee_deductions')->where('id', $existing->id)->first();
        }

        $nik = trim((string) ($data['nik'] ?? ''));
        $periodeYm = trim((string) ($data['salary_period'] ?? date('Y-m')));
        $amount = max(0.0, (float) ($data['deducted_amount'] ?? 0));
        $type = $data['deduction_type'] ?? 'KASBON_MANUAL';
        if (!in_array($type, ['SPPD_UNSETTLED', 'KASBON_SPPD_EXCESS', 'KASBON_MANUAL', 'REIMBURSE', 'OTHER'], true)) {
            $type = 'KASBON_MANUAL';
        }

        if ($nik === '' || $amount <= 0) {
            throw new \InvalidArgumentException('NIK dan nominal potongan wajib diisi.');
        }

        $availableThp = $this->getAvailableThpForAdvance($nik, $periodeYm, (string) ($data['main_id'] ?? ''));
        $existingIncludedTotal = (float) DB::table('payroll_employee_deductions')
            ->where('nik', $nik)
            ->where('salary_period', $periodeYm)
            ->where('status', 'DRAFT')
            ->where('is_included', 1)
            ->sum('deducted_amount');

        if (round($existingIncludedTotal + $amount, 2) > round($availableThp, 2)) {
            $maxAllowed = max(0.0, round($availableThp - $existingIncludedTotal, 2));
            throw new \InvalidArgumentException(sprintf(
                'Total potongan (Rp %s) melebihi THP karyawan (Rp %s). Maksimal yang dapat ditambahkan: Rp %s.',
                number_format($existingIncludedTotal + $amount, 0, ',', '.'),
                number_format($availableThp, 0, ',', '.'),
                number_format($maxAllowed, 0, ',', '.')
            ));
        }

        $id = DB::table('payroll_employee_deductions')->insertGetId([
            'nik'              => $nik,
            'salary_period'    => $periodeYm,
            'main_id'          => $data['main_id'] ?? null,
            'deduction_type'   => $type,
            'source_table'     => 'MANUAL',
            'source_id'        => null,
            'reference_no'     => $data['reference_no'] ?? ('MAN-' . date('ymdHis')),
            'description'      => mb_substr(trim((string) ($data['description'] ?? 'Potongan Kasbon Manual')), 0, 255),
            'original_amount'  => round($amount, 2),
            'remaining_amount' => round($amount, 2),
            'deducted_amount'  => round($amount, 2),
            'is_auto_pulled'   => 0,
            'is_included'      => 1,
            'status'           => 'DRAFT',
            'created_by'       => $operator,
            'updated_by'       => $operator,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);

        return DB::table('payroll_employee_deductions')->where('id', $id)->first();
    }

    /**
     * Ambil daftar karyawan aktif dari tabel employment untuk dropdown modal.
     */
    public function getEmploymentOptions(string $mainId = ''): array
    {
        $query = DB::table('employment')
            ->whereNotNull('NIK')
            ->where('NIK', '!=', '')
            ->whereIn('Status', ['ACTIVE', 'Active', 'PROBATION', 'Probation', 'PKWT', 'PKWTT', 'TETAP']);

        if ($mainId !== '') {
            $niks = $this->resolveTargetNiks($mainId);
            if (!empty($niks)) {
                $query->whereIn('NIK', $niks);
            }
        }

        return $query->select('NIK', 'Name', 'Position', 'Department')
            ->orderBy('Name', 'ASC')
            ->get()
            ->toArray();
    }

    /**
     * Hapus item potongan manual yang masih berstatus DRAFT dan sudah di-uncheck.
     */
    public function deleteDraftItem(int $id): array
    {
        $row = DB::table('payroll_employee_deductions')
            ->where('id', $id)
            ->where('status', 'DRAFT')
            ->first();

        if (!$row) {
            return [
                'success' => false,
                'status'  => 404,
                'message' => 'Item tidak ditemukan atau sudah POSTED.',
            ];
        }

        if ((int) $row->is_auto_pulled === 1) {
            return [
                'success' => false,
                'status'  => 422,
                'message' => 'Item otomatis (AUTO) tidak dapat dihapus; cukup uncheck agar tidak dipotong dari gaji.',
            ];
        }

        if ((int) $row->is_included === 1) {
            return [
                'success' => false,
                'status'  => 422,
                'message' => 'Uncheck (keluarkan dari potongan) terlebih dahulu sebelum menghapus item ini.',
            ];
        }

        $deleted = DB::table('payroll_employee_deductions')->where('id', $id)->delete() > 0;

        return [
            'success' => $deleted,
            'status'  => $deleted ? 200 : 500,
            'message' => $deleted ? 'Item potongan DRAFT berhasil dihapus.' : 'Gagal menghapus item.',
        ];
    }

    /**
     * Tandai item potongan aktif sebagai POSTED setelah Submit Payroll berhasil.
     * Jika $maxDeductible diberikan, alokasikan maksimal sebesar THP dan carry-forward sisanya ke bulan berikutnya.
     */
    public function markDeductionsPosted(
        string $nik,
        string $periodeYm,
        string $taskId,
        string $operator = 'SYSTEM',
        ?float $maxDeductible = null
    ): void {
        if (!Schema::hasTable('payroll_employee_deductions')) {
            return;
        }

        $now = now();

        if ($maxDeductible === null) {
            DB::table('payroll_employee_deductions')
                ->where('nik', $nik)
                ->where('salary_period', $periodeYm)
                ->where('status', 'DRAFT')
                ->where('is_included', 1)
                ->where('deducted_amount', '>', 0)
                ->update([
                    'status'          => 'POSTED',
                    'payroll_task_id' => $taskId,
                    'updated_by'      => $operator,
                    'updated_at'      => $now,
                ]);
            return;
        }

        $rows = DB::table('payroll_employee_deductions')
            ->where('nik', $nik)
            ->where('salary_period', $periodeYm)
            ->where('status', 'DRAFT')
            ->where('is_included', 1)
            ->where('deducted_amount', '>', 0)
            ->orderBy('id', 'ASC')
            ->get();

        $remainingCap = max(0.0, round($maxDeductible, 2));
        $nextPeriod = date('Y-m', strtotime($periodeYm . '-01 +1 month'));

        foreach ($rows as $row) {
            $requested = (float) $row->deducted_amount;
            $actual = round(min($requested, $remainingCap), 2);
            $carryOver = round($requested - $actual, 2);
            $remainingCap = max(0.0, round($remainingCap - $actual, 2));

            if ($actual > 0) {
                DB::table('payroll_employee_deductions')
                    ->where('id', $row->id)
                    ->update([
                        'deducted_amount' => $actual,
                        'status'          => 'POSTED',
                        'payroll_task_id' => $taskId,
                        'updated_by'      => $operator,
                        'updated_at'      => $now,
                    ]);

                if ($carryOver > 0 && (int) $row->is_auto_pulled === 0) {
                    DB::table('payroll_employee_deductions')->insert([
                        'nik'              => $row->nik,
                        'salary_period'    => $nextPeriod,
                        'main_id'          => $row->main_id,
                        'deduction_type'   => $row->deduction_type,
                        'source_table'     => $row->source_table,
                        'source_id'        => $row->source_id,
                        'reference_no'     => $row->reference_no,
                        'description'      => mb_substr($row->description . ' (Sisa Carry-Forward)', 0, 255),
                        'original_amount'  => $row->original_amount,
                        'remaining_amount' => $carryOver,
                        'deducted_amount'  => $carryOver,
                        'is_auto_pulled'   => 0,
                        'is_included'      => 1,
                        'status'           => 'DRAFT',
                        'created_by'       => $operator,
                        'updated_by'       => $operator,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                }
            } else {
                if ((int) $row->is_auto_pulled === 0) {
                    DB::table('payroll_employee_deductions')
                        ->where('id', $row->id)
                        ->update([
                            'salary_period' => $nextPeriod,
                            'updated_by'    => $operator,
                            'updated_at'    => $now,
                        ]);
                } else {
                    DB::table('payroll_employee_deductions')
                        ->where('id', $row->id)
                        ->update([
                            'deducted_amount' => 0,
                            'is_included'     => 0,
                            'updated_by'      => $operator,
                            'updated_at'      => $now,
                        ]);
                }
            }
        }
    }

    /**
     * Resolve daftar NIK karyawan dalam suatu Main_ID Salary Group.
     */
    protected function resolveTargetNiks(string $mainId): array
    {
        if ($mainId === '') {
            return DB::table('employment')->whereNotNull('NIK')->pluck('NIK')->toArray();
        }

        $niks = DB::table('payrol_master_group as pmg')
            ->join('employment as e', 'e.NIK', '=', 'pmg.Group_ID')
            ->where('pmg.Main_ID', $mainId)
            ->pluck('e.NIK')
            ->toArray();

        if (empty($niks)) {
            $niks = DB::table('employment')
                ->where('Salary_Group', $mainId)
                ->pluck('NIK')
                ->toArray();
        }

        return array_values(array_unique(array_filter($niks)));
    }
}
