<?php

namespace App\Services\Attendance;

use App\Exceptions\ApprovalWorkflowException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeaveEntitlementService
{
    /**
     * Dapatkan atau inisialisasi saldo cuti karyawan pada tahun tertentu.
     */
    public function getOrCreateUserBalance(string $nik, int $year = null): object
    {
        $year = $year ?: (int) date('Y');

        $balance = DB::table('user_leave_balances')
            ->where('nik', $nik)
            ->where('year', $year)
            ->first();

        if ($balance) {
            return $balance;
        }

        // Ambil data user untuk cek role / posisi
        $user = DB::table('users')->where('Emp_ID', $nik)->first();
        $defaultQuota = 12;
        $maxCarryForward = 6;
        $expireMonths = 6;

        if ($user) {
            $policy = DB::table('leave_entitlement_policies')
                ->where('level_or_role', $user->Priv_ID ?? null)
                ->where('status', 'ACTIVE')
                ->first();

            if ($policy) {
                $defaultQuota = (int) $policy->annual_quota;
                $maxCarryForward = (int) $policy->carry_forward_max;
                $expireMonths = (int) $policy->carry_forward_expire_months;
            }
        }

        // Cek sisa saldo dari tahun sebelumnya
        $prevYear = $year - 1;
        $prevBalance = DB::table('user_leave_balances')
            ->where('nik', $nik)
            ->where('year', $prevYear)
            ->first();

        $carryForward = 0;
        $expiredAt = Carbon::create($year, 1, 1)->addMonths($expireMonths)->endOfMonth()->toDateString();

        if ($prevBalance && $prevBalance->remaining_total > 0) {
            $carryForward = min($prevBalance->remaining_total, $maxCarryForward);
        }

        $totalRemaining = $defaultQuota + $carryForward;

        $id = DB::table('user_leave_balances')->insertGetId([
            'nik'                       => $nik,
            'year'                      => $year,
            'allocated_quota'           => $defaultQuota,
            'carry_forward_quota'       => $carryForward,
            'carry_forward_expired_at'  => $expiredAt,
            'used_carry_forward'        => 0,
            'used_current_year'         => 0,
            'remaining_total'           => $totalRemaining,
            'notes'                     => "Inisialisasi otomatis tahun {$year} (Akumulasi tahun lalu: {$carryForward} hari)",
            'created_at'                => Carbon::now(),
            'updated_at'                => Carbon::now(),
        ]);

        return DB::table('user_leave_balances')->where('id', $id)->first();
    }

    /**
     * Potong saldo cuti dengan prioritas saldo akumulasi tahun lalu sebelum masa reset.
     */
    public function deductLeaveBalance(string $nik, int $days, string $refNo, string $leaveCategoryCode, $adminUser = null): array
    {
        if ($days <= 0) {
            return ['status' => 'skipped', 'message' => 'Hari cuti 0. Tidak ada pemotongan.'];
        }

        // Cek apakah kategori ini memotong cuti tahunan
        $category = DB::table('kategori_izin')
            ->where('kat_izin_code', $leaveCategoryCode)
            ->orWhere('kat_izin_nama', $leaveCategoryCode)
            ->first();

        if ($category && $category->is_deduct_annual_leave !== 'YES') {
            return [
                'status'  => 'not_deducted',
                'message' => "Kategori {$category->kat_izin_nama} tidak memotong jatah cuti tahunan.",
            ];
        }

        $today = Carbon::now();
        $year = (int) $today->year;
        $balance = $this->getOrCreateUserBalance($nik, $year);

        if ($balance->remaining_total < $days) {
            throw new InvalidArgumentException("Sisa jatah cuti tidak mencukupi (Sisa: {$balance->remaining_total} hari, Diminta: {$days} hari).");
        }

        $isCarryValid = $balance->carry_forward_expired_at && $today->lte(Carbon::parse($balance->carry_forward_expired_at));
        $availableCarry = $isCarryValid ? ($balance->carry_forward_quota - $balance->used_carry_forward) : 0;

        $deductFromCarry = 0;
        $deductFromCurrent = 0;

        if ($availableCarry > 0) {
            $deductFromCarry = min($days, $availableCarry);
            $remainingNeeded = $days - $deductFromCarry;
            $deductFromCurrent = $remainingNeeded;
        } else {
            $deductFromCurrent = $days;
        }

        $newUsedCarry = $balance->used_carry_forward + $deductFromCarry;
        $newUsedCurrent = $balance->used_current_year + $deductFromCurrent;
        $newTotal = ($balance->allocated_quota - $newUsedCurrent) + ($isCarryValid ? ($balance->carry_forward_quota - $newUsedCarry) : 0);

        DB::transaction(function () use ($balance, $newUsedCarry, $newUsedCurrent, $newTotal, $nik, $refNo, $deductFromCarry, $deductFromCurrent, $days, $adminUser) {
            DB::table('user_leave_balances')->where('id', $balance->id)->update([
                'used_carry_forward' => $newUsedCarry,
                'used_current_year'  => $newUsedCurrent,
                'remaining_total'    => $newTotal,
                'updated_at'         => Carbon::now(),
            ]);

            if ($deductFromCarry > 0) {
                DB::table('leave_balance_ledgers')->insert([
                    'nik'              => $nik,
                    'reference_no'     => $refNo,
                    'transaction_type' => 'DEDUCTION',
                    'amount'           => -$deductFromCarry,
                    'source_balance'   => 'CARRY_FORWARD',
                    'balance_after'    => $newTotal,
                    'description'      => "Potong saldo cuti tahun lalu (Sebelum masa reset)",
                    'created_by'       => $adminUser ? $adminUser->username : 'system',
                ]);
            }

            if ($deductFromCurrent > 0) {
                DB::table('leave_balance_ledgers')->insert([
                    'nik'              => $nik,
                    'reference_no'     => $refNo,
                    'transaction_type' => 'DEDUCTION',
                    'amount'           => -$deductFromCurrent,
                    'source_balance'   => 'CURRENT_YEAR',
                    'balance_after'    => $newTotal,
                    'description'      => "Potong saldo cuti tahun berjalan",
                    'created_by'       => $adminUser ? $adminUser->username : 'system',
                ]);
            }
        });

        return [
            'status'               => 'deducted',
            'days_deducted'        => $days,
            'from_carry_forward'   => $deductFromCarry,
            'from_current_year'    => $deductFromCurrent,
            'remaining_balance'    => $newTotal,
            'message'              => "Berhasil memotong {$days} hari cuti (Tahun lalu: {$deductFromCarry}, Tahun berjalan: {$deductFromCurrent}).",
        ];
    }

    /**
     * Jalankan proses Reset Saldo Akumulasi Tahun Lalu setelah masa berlaku habis.
     */
    public function expireCarryForwardBalances(): int
    {
        $today = Carbon::now()->toDateString();

        $expiredList = DB::table('user_leave_balances')
            ->where('carry_forward_quota', '>', 0)
            ->where('carry_forward_expired_at', '<', $today)
            ->get();

        $count = 0;
        foreach ($expiredList as $bal) {
            $unclaimedCarry = $bal->carry_forward_quota - $bal->used_carry_forward;
            if ($unclaimedCarry > 0) {
                $newTotal = $bal->allocated_quota - $bal->used_current_year;

                DB::table('user_leave_balances')->where('id', $bal->id)->update([
                    'carry_forward_quota' => $bal->used_carry_forward, // kunci saldo yang hangus
                    'remaining_total'    => $newTotal,
                    'updated_at'         => Carbon::now(),
                ]);

                DB::table('leave_balance_ledgers')->insert([
                    'nik'              => $bal->nik,
                    'reference_no'     => 'RESET-' . date('Ymd'),
                    'transaction_type' => 'EXPIRED_RESET',
                    'amount'           => -$unclaimedCarry,
                    'source_balance'   => 'CARRY_FORWARD',
                    'balance_after'    => $newTotal,
                    'description'      => "Reset masa berlaku akumulasi cuti tahun lalu habis.",
                    'created_by'       => 'cron.system',
                ]);

                $count++;
            }
        }

        return $count;
    }
}

