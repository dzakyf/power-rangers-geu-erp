<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service manajemen Chart of Account (branch_coa) & Trial Balance (branch_trial_balance)
 * beserta Master Default Mapping COA per sumber modul HR/GA.
 */
class FinanceCoaService
{
    public const SOURCE_MODULES = [
        'ASSET_MAINTENANCE' => 'Voucher Perawatan Aset',
        'GA_WORK_ORDER'     => 'Voucher GA Order Pekerjaan',
        'VEHICLE_TAX'       => 'Voucher Pajak STNK / KEUR',
        'HRGA_MANUAL'       => 'Voucher / AP Manual',
        'GLOBAL'            => 'Default Global (Fallback)',
    ];

    public const COMPONENT_KEYS = [
        'EXPENSE'        => ['label' => 'Beban Utama / Subtotal', 'side' => 'DEBET'],
        'DELIVERY'       => ['label' => 'Biaya Pengiriman (Delivery Costs)', 'side' => 'DEBET'],
        'MANAGEMENT_FEE' => ['label' => 'Management Fee', 'side' => 'DEBET'],
        'DP'             => ['label' => 'Uang Muka (DP)', 'side' => 'DEBET'],
        'MATERAI'        => ['label' => 'Biaya Materai', 'side' => 'DEBET'],
        'PPN'            => ['label' => 'PPN Masukan', 'side' => 'DEBET'],
        'PPH'            => ['label' => 'Hutang PPh (Potongan)', 'side' => 'CREDIT'],
        'AP_LIABILITY'   => ['label' => 'Hutang Dagang / Usaha (AP)', 'side' => 'CREDIT'],
        'CASH_DEFAULT'   => ['label' => 'Kas Default (Direct Cash)', 'side' => 'CREDIT'],
        'BANK_DEFAULT'   => ['label' => 'Bank Default (Direct Bank)', 'side' => 'CREDIT'],
    ];

    /**
     * Ambil daftar kelompok dari branch_trial_balance (Header_No & Header_Name).
     */
    public function getTrialBalanceGroups(?string $category = null): array
    {
        $query = DB::table('branch_trial_balance')
            ->where('Listed_On_Search', 1)
            ->whereNotNull('Header_No')
            ->where('Header_No', '!=', '');

        $cat = strtoupper(trim((string) $category));
        if ($cat === 'CASH_BANK') {
            $query->whereIn('Header_No', ['A1', 'A2', 'A3', 'A4']);
        } elseif ($cat === 'AP_LIABILITY') {
            $query->whereIn('Header_No', ['L1', 'L4', 'L6', 'L7']);
        } elseif ($cat === 'EXPENSE') {
            $query->where(function ($q) {
                $q->where('Header_No', 'LIKE', '5%')
                    ->orWhere('Header_No', 'LIKE', '6%');
            });
        }

        return $query
            ->select('Header_No', 'Header_Name', DB::raw('COUNT(*) as Account_Count'))
            ->groupBy('Header_No', 'Header_Name')
            ->orderBy('Header_No', 'ASC')
            ->get()
            ->all();
    }

    /**
     * Ambil opsi COA dari branch_coa yang terhubung ke branch_trial_balance.
     */
    public function getCoaOptions(?string $headerNo = null, ?string $keyword = null, int $limit = 1000): array
    {
        $query = DB::table('branch_coa as c')
            ->leftJoin('branch_trial_balance as tb', function ($join) {
                $join->on(
                    DB::raw('CONVERT(c.Acc_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('CONVERT(tb.Account_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                );
            })
            ->where('c.Listed_OnSearch', 1);

        $header = trim((string) $headerNo);
        if ($header !== '' && strtoupper($header) !== 'ALL') {
            $query->where(function ($q) use ($header) {
                $q->where('tb.Header_No', $header)
                    ->orWhere('c.Group_Bal', $header);
            });
        }

        $kw = trim((string) $keyword);
        if ($kw !== '') {
            $like = '%' . $kw . '%';
            $query->where(function ($q) use ($like) {
                $q->where('c.Acc_No', 'LIKE', $like)
                    ->orWhere('c.Description', 'LIKE', $like)
                    ->orWhere('tb.Header_Name', 'LIKE', $like);
            });
        }

        return $query
            ->select(
                'c.ID',
                'c.Acc_No',
                'c.Description as Acc_Name',
                'c.Normal',
                'c.Group_Bal',
                'c.Group_Report',
                DB::raw('COALESCE(NULLIF(tb.Header_No, ""), c.Group_Bal) as Trial_Balance_Header_No'),
                'tb.Header_Name as Trial_Balance_Header_Name'
            )
            ->orderBy('c.Acc_No', 'ASC')
            ->limit(max(1, min($limit, 2000)))
            ->get()
            ->all();
    }

    /**
     * Katalog COA ramah non-accounting: mengembalikan kelompok branch_trial_balance
     * dan akun branch_coa yang diurutkan berdasarkan Header_Name & Description.
     */
    public function getNonAccountingCoaCatalog(): array
    {
        $groups = $this->getTrialBalanceGroups();
        $accounts = DB::table('branch_coa as c')
            ->leftJoin('branch_trial_balance as tb', function ($join) {
                $join->on(
                    DB::raw('CONVERT(c.Acc_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('CONVERT(tb.Account_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                );
            })
            ->where('c.Listed_OnSearch', 1)
            ->select(
                'c.Acc_No',
                'c.Description as Acc_Name',
                'c.Normal',
                'c.Group_Report',
                DB::raw('COALESCE(NULLIF(tb.Header_No, ""), c.Group_Bal) as Header_No'),
                DB::raw('COALESCE(NULLIF(tb.Header_Name, ""), c.Group_Report, "Lainnya") as Header_Name')
            )
            ->orderBy('Header_Name', 'ASC')
            ->orderBy('c.Description', 'ASC')
            ->get()
            ->all();

        foreach ($accounts as $acc) {
            $h = strtoupper((string) ($acc->Header_No ?? ''));
            $no = (string) ($acc->Acc_No ?? '');
            if (in_array($h, ['A1', 'A2', 'A3', 'A4'], true) || strpos($no, '1-11') === 0 || strpos($no, '1-14') === 0) {
                $acc->Category_Tag = 'CASH_BANK';
            } elseif (in_array($h, ['L1', 'L4', 'L6', 'L7'], true) || strpos($no, '2-') === 0) {
                $acc->Category_Tag = 'LIABILITY_AP';
            } elseif (strpos($h, '5') === 0 || strpos($h, '6') === 0 || strpos($no, '5-') === 0 || strpos($no, '6-') === 0) {
                $acc->Category_Tag = 'EXPENSE';
            } else {
                $acc->Category_Tag = 'OTHER';
            }
        }

        return [
            'groups'   => $groups,
            'accounts' => $accounts,
        ];
    }

    /**
     * Ambil satu COA berdasarkan Acc_No beserta info kelompok Trial Balance.
     */
    public function findCoaByAccNo(string $accNo): ?object
    {
        $clean = trim($accNo);
        if ($clean === '') {
            return null;
        }

        return DB::table('branch_coa as c')
            ->leftJoin('branch_trial_balance as tb', function ($join) {
                $join->on(
                    DB::raw('CONVERT(c.Acc_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('CONVERT(tb.Account_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                );
            })
            ->where('c.Acc_No', $clean)
            ->select(
                'c.ID',
                'c.Acc_No',
                'c.Description as Acc_Name',
                'c.Normal',
                'c.Group_Bal',
                'c.Group_Report',
                'c.Listed_OnSearch',
                DB::raw('COALESCE(NULLIF(tb.Header_No, ""), c.Group_Bal) as Trial_Balance_Header_No'),
                'tb.Header_Name as Trial_Balance_Header_Name'
            )
            ->first();
    }

    /**
     * Ambil daftar bank internal aktif dari master bank_acc.
     */
    public function getBankAccountOptions(): array
    {
        return DB::table('bank_acc')
            ->where('Status', 'ACTIVE')
            ->select('ID', 'Location_ID', 'Bank_Acc', 'Bank_ID', 'Bank_Name', 'Bank_Branch', 'Currency', 'Acc_No', 'Acc_Hold')
            ->orderBy('Urutan', 'ASC')
            ->orderBy('ID', 'ASC')
            ->get()
            ->all();
    }

    /**
     * Ambil semua konfigurasi default mapping COA.
     */
    public function getAllDefaultMappings(): array
    {
        return DB::table('finance_coa_mapping')
            ->where('Is_Active', 1)
            ->orderBy('Source_Module', 'ASC')
            ->orderBy('Component_Key', 'ASC')
            ->get()
            ->all();
    }

    /**
     * Ambil mapping COA efektif untuk suatu modul (digabung dengan fallback GLOBAL).
     */
    public function getEffectiveMappings(string $sourceModule): array
    {
        $mod = strtoupper(trim($sourceModule));
        $rows = DB::table('finance_coa_mapping')
            ->where('Is_Active', 1)
            ->whereIn('Source_Module', [$mod, 'GLOBAL'])
            ->get();

        $result = [];
        foreach ($rows as $row) {
            if ($row->Source_Module === 'GLOBAL' && !isset($result[$row->Component_Key])) {
                $result[$row->Component_Key] = $row;
            }
        }
        foreach ($rows as $row) {
            if ($row->Source_Module === $mod) {
                $result[$row->Component_Key] = $row;
            }
        }

        return $result;
    }

    /**
     * Simpan atau perbarui default mapping COA per Source_Module & Component_Key.
     */
    public function saveDefaultMapping(array $data, string $username): object
    {
        $sourceModule = strtoupper(trim((string) ($data['Source_Module'] ?? '')));
        $componentKey = strtoupper(trim((string) ($data['Component_Key'] ?? '')));
        $accNo = trim((string) ($data['Acc_No'] ?? ''));

        if (!isset(self::SOURCE_MODULES[$sourceModule])) {
            throw new InvalidArgumentException('Source_Module tidak valid.');
        }
        if (!isset(self::COMPONENT_KEYS[$componentKey])) {
            throw new InvalidArgumentException('Component_Key tidak valid.');
        }

        $coa = $this->findCoaByAccNo($accNo);
        if (!$coa) {
            throw new InvalidArgumentException('Kode Akun COA (' . $accNo . ') tidak ditemukan di branch_coa.');
        }

        $defaultSide = strtoupper(trim((string) ($data['Default_Side'] ?? self::COMPONENT_KEYS[$componentKey]['side'])));
        if (!in_array($defaultSide, ['DEBET', 'CREDIT'], true)) {
            $defaultSide = self::COMPONENT_KEYS[$componentKey]['side'];
        }

        $now = date('Y-m-d H:i:s');
        DB::table('finance_coa_mapping')->updateOrInsert(
            [
                'Source_Module' => $sourceModule,
                'Component_Key' => $componentKey,
            ],
            [
                'Trial_Balance_Header_No'   => $coa->Trial_Balance_Header_No ?: null,
                'Trial_Balance_Header_Name' => $coa->Trial_Balance_Header_Name ?: null,
                'Acc_No'                    => $coa->Acc_No,
                'Acc_Name'                  => $coa->Acc_Name,
                'Default_Side'              => $defaultSide,
                'Is_Active'                 => 1,
                'Created_By'                => $username,
                'Updated_By'                => $username,
                'Updated_At'                => $now,
                'Created_At'                => $now,
            ]
        );

        return DB::table('finance_coa_mapping')
            ->where('Source_Module', $sourceModule)
            ->where('Component_Key', $componentKey)
            ->first();
    }

    /**
     * Hapus salah satu mapping default COA.
     */
    public function deleteDefaultMapping(int $id): void
    {
        DB::table('finance_coa_mapping')->where('ID', $id)->delete();
    }

    /**
     * Bangun saran baris COA multi-debet & multi-kredit yang langsung seimbang
     * berdasarkan komponen biaya voucher dan mapping default / fallback branch_coa.
     */
    public function buildSuggestedLines(
        string $sourceModule,
        array $amounts,
        string $channel = 'DIRECT',
        string $paymentMethod = 'BANK',
        ?string $overrideCreditAccNo = null,
        ?string $department = null
    ): array {
        $mappings = $this->getEffectiveMappings($sourceModule);
        $channel = strtoupper(trim($channel)) === 'AP' ? 'AP' : 'DIRECT';
        $paymentMethod = strtoupper(trim($paymentMethod)) === 'CASH' ? 'CASH' : 'BANK';

        $resolveCoa = function (string $componentKey, array $fallbackHeaders, array $fallbackPrefixes) use ($mappings) {
            if (isset($mappings[$componentKey])) {
                $m = $mappings[$componentKey];
                $found = $this->findCoaByAccNo((string) $m->Acc_No);
                if ($found) {
                    return $found;
                }
            }

            if (!empty($fallbackHeaders)) {
                $row = DB::table('branch_coa as c')
                    ->leftJoin('branch_trial_balance as tb', function ($join) {
                        $join->on(
                            DB::raw('CONVERT(c.Acc_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                            '=',
                            DB::raw('CONVERT(tb.Account_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                        );
                    })
                    ->where('c.Listed_OnSearch', 1)
                    ->whereIn('tb.Header_No', $fallbackHeaders)
                    ->select(
                        'c.Acc_No',
                        'c.Description as Acc_Name',
                        DB::raw('COALESCE(NULLIF(tb.Header_No, ""), c.Group_Bal) as Trial_Balance_Header_No'),
                        'tb.Header_Name as Trial_Balance_Header_Name'
                    )
                    ->orderBy('c.Acc_No', 'ASC')
                    ->first();
                if ($row) {
                    return $row;
                }
            }

            foreach ($fallbackPrefixes as $prefix) {
                $row = DB::table('branch_coa as c')
                    ->leftJoin('branch_trial_balance as tb', function ($join) {
                        $join->on(
                            DB::raw('CONVERT(c.Acc_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                            '=',
                            DB::raw('CONVERT(tb.Account_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                        );
                    })
                    ->where('c.Listed_OnSearch', 1)
                    ->where('c.Acc_No', 'LIKE', $prefix . '%')
                    ->select(
                        'c.Acc_No',
                        'c.Description as Acc_Name',
                        DB::raw('COALESCE(NULLIF(tb.Header_No, ""), c.Group_Bal) as Trial_Balance_Header_No'),
                        'tb.Header_Name as Trial_Balance_Header_Name'
                    )
                    ->orderBy('c.Acc_No', 'ASC')
                    ->first();
                if ($row) {
                    return $row;
                }
            }

            return null;
        };

        $lines = [];
        $subtotal = round((float) ($amounts['Subtotal'] ?? 0), 2);
        $delivery = round((float) ($amounts['Delivery_Costs'] ?? 0), 2);
        $mgmtFee = round((float) ($amounts['Management_Fee'] ?? 0), 2);
        $dp = round((float) ($amounts['DP'] ?? 0), 2);
        $materai = round((float) ($amounts['Materai'] ?? 0), 2);
        $ppn = round((float) ($amounts['PPN'] ?? 0), 2);
        $pph = round((float) ($amounts['PPH'] ?? 0), 2);
        $total = round((float) ($amounts['Total'] ?? 0), 2);

        $expenseHeaders = ['607', '516'];
        if ($sourceModule === 'VEHICLE_TAX') {
            $expenseHeaders = ['618', '607'];
        } elseif ($sourceModule === 'ASSET_MAINTENANCE') {
            $expenseHeaders = ['516', '607'];
        } elseif ($sourceModule === 'GA_WORK_ORDER') {
            $expenseHeaders = ['607', '605', '516'];
        }

        if ($subtotal <= 0 && $total > 0 && $delivery <= 0 && $mgmtFee <= 0 && $materai <= 0 && $ppn <= 0) {
            $subtotal = $total;
        }

        $pushLine = function (?object $coa, string $side, string $tag, float $amount, string $remark) use (&$lines, $department) {
            if ($amount <= 0) {
                return;
            }
            $lines[] = [
                'Trial_Balance_Header_No'   => $coa ? ($coa->Trial_Balance_Header_No ?? '') : '',
                'Trial_Balance_Header_Name' => $coa ? ($coa->Trial_Balance_Header_Name ?? '') : '',
                'Acc_No'                    => $coa ? ($coa->Acc_No ?? '') : '',
                'Acc_Name'                  => $coa ? ($coa->Acc_Name ?? '') : '',
                'Line_Side'                 => $side,
                'Component_Tag'             => $tag,
                'Amount'                    => round($amount, 2),
                'Department'                => $department ?: '',
                'Remark'                    => $remark,
            ];
        };

        $pushLine($resolveCoa('EXPENSE', $expenseHeaders, ['6-', '5-']), 'DEBET', 'EXPENSE', $subtotal, 'Beban / Subtotal Voucher');
        $pushLine($resolveCoa('DELIVERY', ['606', '517'], ['6-', '5-']), 'DEBET', 'DELIVERY', $delivery, 'Biaya Pengiriman');
        $pushLine($resolveCoa('MANAGEMENT_FEE', ['608', '605'], ['6-']), 'DEBET', 'MANAGEMENT_FEE', $mgmtFee, 'Management Fee');
        $pushLine($resolveCoa('DP', ['A11'], ['1-']), 'DEBET', 'DP', $dp, 'Uang Muka (DP)');
        $pushLine($resolveCoa('MATERAI', ['605', '620'], ['6-']), 'DEBET', 'MATERAI', $materai, 'Biaya Materai');
        $pushLine($resolveCoa('PPN', ['A12', 'A19'], ['1-']), 'DEBET', 'PPN', $ppn, 'PPN Masukan');

        $debetSum = 0.0;
        foreach ($lines as $ln) {
            if ($ln['Line_Side'] === 'DEBET') {
                $debetSum += $ln['Amount'];
            }
        }

        if ($pph > 0 && $pph < $debetSum) {
            $pushLine($resolveCoa('PPH', ['L7'], ['2-']), 'CREDIT', 'PPH', $pph, 'Potongan PPh');
        } else {
            $pph = 0.0;
        }

        $balancingAmount = round($debetSum - $pph, 2);
        if ($balancingAmount > 0) {
            $creditCoa = $overrideCreditAccNo ? $this->findCoaByAccNo($overrideCreditAccNo) : null;
            if (!$creditCoa) {
                if ($channel === 'AP') {
                    $creditCoa = $resolveCoa('AP_LIABILITY', ['L1'], ['2-1', '2-']);
                } elseif ($paymentMethod === 'CASH') {
                    $creditCoa = $resolveCoa('CASH_DEFAULT', ['A1', 'A2'], ['1-11']);
                } else {
                    $creditCoa = $resolveCoa('BANK_DEFAULT', ['A3', 'A4'], ['1-12', '1-1']);
                }
            }

            $creditTag = $channel === 'AP' ? 'AP' : $paymentMethod;
            $creditRemark = $channel === 'AP'
                ? 'Pengakuan Hutang Dagang (AP)'
                : ($paymentMethod === 'CASH' ? 'Pembayaran Langsung Kas' : 'Pembayaran Langsung Bank');

            $pushLine($creditCoa, 'CREDIT', $creditTag, $balancingAmount, $creditRemark);
        }

        return $lines;
    }
}
