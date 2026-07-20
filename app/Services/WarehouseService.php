<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\WarehousePartnerSupplies;
use App\WarehouseStockRequest;
use App\WarehousePartnerJournal;
use App\MasterStockPrice;
use App\MasterWarehouseAccount;

class WarehouseService
{
    public function calculateMonthlyFinancialStatement(
        string $warehouseTaskId,
        ?string $dateStart = null,
        ?string $dateEnd = null
    ): array {
        return $this->calculateFinancialStatement($warehouseTaskId, $dateStart, $dateEnd);
    }

    public function calculateFinancialStatement(
        string $warehouseTaskId,
        ?string $dateStart = null,
        ?string $dateEnd = null
    ): array {
        $startDate  = $dateStart  ?? now()->startOfMonth()->format('Y-m-d');
        $endDate    = $dateEnd    ?? now()->endOfMonth()->format('Y-m-d');
        $startMonth = date('Y-m', strtotime($startDate));
        $endMonth   = date('Y-m', strtotime($endDate));

        $isSingleMonth = ($startMonth === $endMonth);

        $supplies = [];
        $defaultMonth = ['qty' => 0, 'avg' => 0, 'qty_out' => 0, 'avg_out' => 0, 'expenses' => []];

        $wh = DB::table('warehouse_partner as wp')
            ->select('wp.warehouse_task_id', 'wp.warehouse_type')
            ->where('wp.warehouse_task_id', $warehouseTaskId)
            ->first();

        $type = "BOOTH";
        if ($wh) {
            if ($wh->warehouse_type == "mobil") $type = "CAR";
        }

        $config = DB::table('config_system')
            ->whereIn('Constraint', ["TOTAL_HUB_INVESTATION_$type", 'CAPITAL_ALLOC_MULTIPLIER'])
            ->pluck('Value', 'Constraint');

        $totalInvestationConfig = $config["TOTAL_HUB_INVESTATION_$type"] ?? 0;
        $capitalAllocMultiplier = $config['CAPITAL_ALLOC_MULTIPLIER'] ?? 0.025;
        $transferPrice = MasterStockPrice::whereBetween('date_for', [$startDate, $endDate])->avg('price');

        // Stock IN
        WarehousePartnerSupplies::where("warehouse_task_id", $warehouseTaskId)
            ->whereBetween('received_date', [$startDate, $endDate])
            ->get()
            ->each(function ($item) use (&$supplies, $defaultMonth) {
                $month = date('Y-m', strtotime($item->received_date));
                $supplies[$month] ??= $defaultMonth;
                $prev   = $supplies[$month];
                $newQty = $prev['qty'] + $item->received_qty;
                $supplies[$month]['qty'] = $newQty;
                $supplies[$month]['avg'] = $newQty > 0
                    ? (($prev['avg'] * $prev['qty']) + $item->total) / $newQty
                    : 0;
            });

        // Stock OUT
        WarehouseStockRequest::where("warehouse_task_id", $warehouseTaskId)
            ->whereBetween('request_date', [$startDate, $endDate])
            ->get()
            ->each(function ($item) use (&$supplies, $defaultMonth) {
                $month = date('Y-m', strtotime($item->request_date));
                $supplies[$month] ??= $defaultMonth;
                $prev   = $supplies[$month];
                $newQty = $prev['qty_out'] + $item->request_qty;
                $supplies[$month]['qty_out'] = $newQty;
                $supplies[$month]['avg_out'] = $newQty > 0
                    ? (($prev['avg_out'] * $prev['qty_out']) + $item->total) / $newQty
                    : 0;
            });

        // Load account metadata once (source of truth)
        $accountMeta = MasterWarehouseAccount::all()->keyBy('account_no');

        // Warehouse journal / expenses
        WarehousePartnerJournal::where("warehouse_task_id", $warehouseTaskId)
            ->whereBetween('posting_date', [$startDate, $endDate])
            ->get()
            ->each(function ($item) use (&$supplies, $defaultMonth, $accountMeta) {
                $month = date('Y-m', strtotime($item->posting_date));
                $supplies[$month] ??= $defaultMonth;

                $meta = $accountMeta[$item->account_no] ?? null;
                $groupAccount = $meta ? $meta->group_account : $item->group_account;

                array_push($supplies[$month]['expenses'], [
                    'group_account' => $groupAccount,
                    'account_no'    => $item->account_no,
                    'nominal'       => $item->nominal,
                ]);
            });

        ksort($supplies);

        $result = collect($supplies)->mapWithKeys(function ($data, $month) use ($totalInvestationConfig, $capitalAllocMultiplier, $transferPrice) {
            $expenses = $data['expenses'] ?? [];
            $direct_cost = 0;
            $overhead_cost = 0;
            $employeeSalary = 0;
            $rentalCost = 0;
            $expenseDetails = [];

            foreach ($expenses as $e) {
                $ga = $e['group_account'];
                $an = $e['account_no'];
                $nominal = $e['nominal'];

                if ($ga == 502) {
                    $direct_cost += $nominal;
                } elseif ($ga == 601) {
                    $overhead_cost += $nominal;
                } elseif ($ga == 602 && $an == '62001') {
                    $employeeSalary += $nominal;
                } elseif ($ga == 602 && $an == '62002') {
                    $rentalCost += $nominal;
                }

                $expenseDetails[$ga][$an] = ($expenseDetails[$ga][$an] ?? 0) + $nominal;
            }

            $raw_material_price = ($data['avg'] ?? 0) * ($data['qty_out'] ?? 0);
            $cogs = $direct_cost + $raw_material_price;
            $opex = $direct_cost + $overhead_cost + $employeeSalary + $rentalCost;
            $totalInvestation = $totalInvestationConfig;
            $income = $data['qty_out'] * $data['avg_out'];
            $transfer_income = $data['qty_out'] * ($transferPrice ?? $data['avg_out']);
            $capitalAllocation = $income * $capitalAllocMultiplier;
            $netProfit = $income - $cogs - $overhead_cost - $capitalAllocation;
            $ebitda = $netProfit - $opex;

            return [$month => [
                'qty_out'            => $data['qty_out'] ?? 0,
                'avg_out'            => round($data['avg_out'] ?? 0),
                'raw_material_price' => $raw_material_price,
                'direct_cost'        => $direct_cost,
                'overhead_cost'      => $overhead_cost,
                'cogs'               => $cogs,
                'employee_salary'    => $employeeSalary,
                'rental_cost'        => $rentalCost,
                'opex'               => $opex,
                'total_investation'  => $totalInvestation,
                'ebitda'             => $ebitda,
                'net_profit'         => $netProfit,
                'total_roi'          => $totalInvestation > 0 ? ($ebitda / $totalInvestation) * 100 : 0,
                'qty_in'             => $data['qty'] ?? 0,
                'transfer_income'    => $transfer_income,
                'expense_details'    => $expenseDetails,
            ]];
        });

        // Ensure all months in range have a default entry
        $cursor = Carbon::parse($startDate)->startOfMonth();
        $endCursor = Carbon::parse($endDate)->startOfMonth();
        while ($cursor <= $endCursor) {
            $monthKey = $cursor->format('Y-m');
            $result[$monthKey] ??= [
                'qty_in'             => 0,
                'qty_out'            => 0,
                'avg_out'            => 0,
                'raw_material_price' => 0,
                'direct_cost'        => 0,
                'overhead_cost'      => 0,
                'cogs'               => 0,
                'employee_salary'    => 0,
                'rental_cost'        => 0,
                'opex'               => 0,
                'total_investation'  => $totalInvestationConfig,
                'ebitda'             => 0,
                'net_profit'         => 0,
                'total_roi'          => 0,
                'transfer_income'    => 0,
                'expense_details'    => [],
            ];
            $cursor->addMonth();
        }

        // Aggregate expense_details across all months in the range
        $allExpenseDetails = [];
        foreach ($result as $monthData) {
            foreach ($monthData['expense_details'] as $ga => $accounts) {
                foreach ($accounts as $an => $amount) {
                    $allExpenseDetails[$ga][$an] = ($allExpenseDetails[$ga][$an] ?? 0) + $amount;
                }
            }
        }

        if ($isSingleMonth) {
            $row = $result[$startMonth];

            $income         = $row['net_profit'] + $row['cogs'] + ($row['net_profit'] * $capitalAllocMultiplier);
            $nominalRoyalty = 0.6 * $row['net_profit'];
            $revenue        = $row['raw_material_price'] + $row['direct_cost'];

            $projectionRoyaltyHigh = 0;
            $projectionRoyaltyLow  = 0;
            $projectionNetProfitHigh = 0;
            $projectionNetProfitLow  = 0;

            $firstCreatedAt = DB::table('projections')
                ->where('warehouse_task_id', $warehouseTaskId)
                ->whereIn('Description', ['Bagi Hasil Mitra', 'Net Profit'])
                ->min('Created_At');

            if ($firstCreatedAt) {
                $origin     = Carbon::parse($firstCreatedAt)->startOfMonth();
                $monthIndex = $origin->diffInMonths(Carbon::parse($startDate)) + 1;
                if ($monthIndex >= 1) {
                    $yearCount = (int) floor(($monthIndex - 1) / 12) + 1;
                    $colIndex  = (($monthIndex - 1) % 12) + 1;
                    $col       = "Month_{$colIndex}_Total";

                    $allProjRows = DB::table('projections')
                        ->where('warehouse_task_id', $warehouseTaskId)
                        ->whereIn('Description', ['Bagi Hasil Mitra', 'Net Margin'])
                        ->where('Year', $yearCount)
                        ->whereIn('Projection_Type', ['high', 'low'])
                        ->get();

                    foreach ($allProjRows as $projRow) {
                        $type = strtolower($projRow->Projection_Type);
                        $val  = $projRow->$col ?? 0;
                        if ($projRow->Description === 'Bagi Hasil Mitra') {
                            if ($type === 'high') $projectionRoyaltyHigh = $val;
                            if ($type === 'low')  $projectionRoyaltyLow  = $val;
                        } elseif ($projRow->Description === 'Net Margin') {
                            if ($type === 'high') $projectionNetProfitHigh = $val;
                            if ($type === 'low')  $projectionNetProfitLow  = $val;
                        }
                    }
                }
            }

            return [
                'qty_in'                    => $row['qty_in'],
                'qty_out'                   => $row['qty_out'],
                'avg_out'                   => $row['avg_out'],
                'total_investation'         => $row['total_investation'],
                'total_transaction'         => 0,
                'opex'                      => $row['opex'],
                'transfer_income'           => $row['transfer_income'] ?? 0,
                'transfer_price'            => $transferPrice ?? 0,
                'transfer_variance'         => ($row['transfer_income'] ?? 0) - $income,
                'income'                    => $income,
                'net_profit'                => $row['net_profit'],
                'nominal_royalty'           => $nominalRoyalty,
                'raw_material_price'        => $row['raw_material_price'],
                'direct_cost'               => $row['direct_cost'],
                'overhead_cost'             => $row['overhead_cost'],
                'total_overhead_cost'       => $row['overhead_cost'],
                'cogs'                      => $row['cogs'],
                'ebitda'                    => $row['ebitda'],
                'gross_margin'              => $row['cogs'] - $income,
                'percent_profit_margin'     => $income > 0 ? round(($row['net_profit'] / $income) * 100, 2) : 0,
                'revenue'                   => $revenue,
                'total_roi'                 => $row['total_roi'],
                'rental_cost'               => $row['rental_cost'],
                'employee_salary'           => $row['employee_salary'],
                'projection_net_profit_high' => $projectionNetProfitHigh,
                'projection_net_profit_low'  => $projectionNetProfitLow,
                'projection_royalty_high'   => $projectionRoyaltyHigh,
                'projection_royalty_low'    => $projectionRoyaltyLow,
                'expense_details'           => $allExpenseDetails,
            ];
        }

        // Multi-month: aggregate
        $agg = [
            'qty_in'             => 0,
            'qty_out'            => 0,
            'raw_material_price' => 0,
            'direct_cost'        => 0,
            'overhead_cost'      => 0,
            'employee_salary'    => 0,
            'rental_cost'        => 0,
            'opex'               => 0,
            'transfer_income'    => 0,
            'total_investation'  => $totalInvestationConfig,
        ];

        foreach ($result as $monthData) {
            $agg['qty_in']             += $monthData['qty_in'];
            $agg['qty_out']            += $monthData['qty_out'];
            $agg['raw_material_price'] += $monthData['raw_material_price'];
            $agg['direct_cost']        += $monthData['direct_cost'];
            $agg['overhead_cost']      += $monthData['overhead_cost'];
            $agg['employee_salary']    += $monthData['employee_salary'];
            $agg['rental_cost']        += $monthData['rental_cost'];
            $agg['opex']               += $monthData['opex'];
            $agg['transfer_income']    += $monthData['transfer_income'];
        }

        $totalWeightedAvg = 0;
        $totalQtyForAvg = 0;
        foreach ($result as $md) {
            $totalWeightedAvg += $md['avg_out'] * $md['qty_out'];
            $totalQtyForAvg += $md['qty_out'];
        }
        $agg['avg_out'] = $totalQtyForAvg > 0 ? round($totalWeightedAvg / $totalQtyForAvg) : 0;

        $agg['cogs'] = $agg['direct_cost'] + $agg['raw_material_price'];

        $incomeAgg = $agg['transfer_income'];
        $capitalAllocationAgg = $incomeAgg * $capitalAllocMultiplier;
        $agg['net_profit'] = $incomeAgg - $agg['cogs'] - $agg['overhead_cost'] - $capitalAllocationAgg;
        $agg['ebitda'] = $agg['net_profit'] - $agg['opex'];
        $agg['total_roi'] = $agg['total_investation'] > 0
            ? ($agg['ebitda'] / $agg['total_investation']) * 100
            : 0;

        $incomeDerived = $agg['net_profit'] + $agg['cogs'] + ($agg['net_profit'] * $capitalAllocMultiplier);
        $nominalRoyaltyAgg = 0.6 * $agg['net_profit'];
        $revenueAgg = $agg['raw_material_price'] + $agg['direct_cost'];

        return [
            'qty_in'                    => $agg['qty_in'],
            'qty_out'                   => $agg['qty_out'],
            'avg_out'                   => $agg['avg_out'],
            'total_investation'         => $agg['total_investation'],
            'total_transaction'         => 0,
            'opex'                      => $agg['opex'],
            'transfer_income'           => $agg['transfer_income'] ?? 0,
            'transfer_price'            => $transferPrice ?? 0,
            'transfer_variance'         => ($agg['transfer_income'] ?? 0) - $incomeDerived,
            'income'                    => $incomeDerived,
            'net_profit'                => $agg['net_profit'],
            'nominal_royalty'           => $nominalRoyaltyAgg,
            'raw_material_price'        => $agg['raw_material_price'],
            'direct_cost'               => $agg['direct_cost'],
            'overhead_cost'             => $agg['overhead_cost'],
            'total_overhead_cost'       => $agg['overhead_cost'],
            'cogs'                      => $agg['cogs'],
            'ebitda'                    => $agg['ebitda'],
            'gross_margin'              => $agg['cogs'] - $incomeDerived,
            'percent_profit_margin'     => $incomeDerived > 0 ? round(($agg['net_profit'] / $incomeDerived) * 100, 2) : 0,
            'revenue'                   => $revenueAgg,
            'total_roi'                 => $agg['total_roi'],
            'rental_cost'               => $agg['rental_cost'],
            'employee_salary'           => $agg['employee_salary'],
            'projection_net_profit_high' => 0,
            'projection_net_profit_low'  => 0,
            'projection_royalty_high'    => 0,
            'projection_royalty_low'     => 0,
            'expense_details'            => $allExpenseDetails,
        ];
    }
}
