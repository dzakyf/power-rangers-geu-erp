<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\WarehousePartnerSupplies;
use App\WarehouseStockRequest;
use App\WarehousePartnerJournal;
use App\MasterStockPrice;

class WarehouseService
{
    public function calculateMonthlyFinancialStatement(
        string $warehouseTaskId,
        ?string $dateStart = null,
        ?string $dateEnd = null
    ): array {
        $startOfMonth = date('Y-m-01', strtotime($dateStart ?? now()));
        $endOfMonth   = date('Y-m-t',  strtotime($dateEnd ?? now()));
        $currMonth    = date('Y-m', strtotime($startOfMonth));
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
        $transferPrice = MasterStockPrice::whereBetween('date_for', [$startOfMonth, $endOfMonth])->avg('price');

        // Stock IN
        WarehousePartnerSupplies::where("warehouse_task_id", $warehouseTaskId)
            ->whereBetween('received_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->each(function ($item) use (&$supplies, $defaultMonth) {
                $month  = date('Y-m', strtotime($item->received_date));
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
            ->whereBetween('request_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->each(function ($item) use (&$supplies, $defaultMonth) {
                $month  = date('Y-m', strtotime($item->request_date));
                $supplies[$month] ??= $defaultMonth;
                $prev   = $supplies[$month];
                $newQty = $prev['qty_out'] + $item->request_qty;
                $supplies[$month]['qty_out'] = $newQty;
                $supplies[$month]['avg_out'] = $newQty > 0
                    ? (($prev['avg_out'] * $prev['qty_out']) + $item->total) / $newQty
                    : 0;
            });

        // Overhead cost
        WarehousePartnerJournal::where("warehouse_task_id", $warehouseTaskId)
            ->whereBetween('posting_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->each(function ($item) use (&$supplies, $defaultMonth) {
                $month = date('Y-m', strtotime($item->posting_date));
                $supplies[$month] ??= $defaultMonth;
                array_push($supplies[$month]['expenses'], [
                    'item'    => $item->account_name,
                    'nominal' => $item->nominal,
                    'coa'     => $item->account_no,
                ]);
            });

        ksort($supplies);

        $result = collect($supplies)->mapWithKeys(function ($data, $month) use ($totalInvestationConfig, $capitalAllocMultiplier, $transferPrice) {
            $expenses = $data['expenses'] ?? [];
            $directCostItems = ['Bensin', 'Parkir'];
            $direct_cost = array_sum(array_column(
                array_filter($expenses, fn($e) => in_array($e['item'], $directCostItems)),
                'nominal'
            ));
            $overheadCostItems = [
                'Service Kendaraan',
                'Wifi',
                'Listrik',
                'Kebersihan & Keamanan',
                'Logistic Penjemputan',
                'Biaya ISCC',
                'Biaya Konversi',
                'Biaya Cadangan'
            ];
            $overhead_cost = array_sum(array_column(
                array_filter($expenses, fn($e) => in_array($e['item'], $overheadCostItems)),
                'nominal'
            ));
            $raw_material_price = ($data['avg'] ?? 0) * ($data['qty_out'] ?? 0);
            $cogs = $direct_cost + $raw_material_price;
            $empCoa = ['Gaji'];
            $employeeSalary = array_sum(array_column(array_filter($expenses, fn($e) => in_array($e['item'], $empCoa)), 'nominal'));
            $rentalCoa = ['Biaya Sewa'];
            $rentalCost = array_sum(array_column(array_filter($expenses, fn($e) => in_array($e['item'], $rentalCoa)), 'nominal'));
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
            ]];
        });

        $result[$currMonth] ??= [
            'qty_in' => 0,
            'qty_out' => 0,
            'avg_out' => 0,
            'raw_material_price' => 0,
            'direct_cost' => 0,
            'overhead_cost' => 0,
            'cogs' => 0,
            'employee_salary' => 0,
            'rental_cost' => 0,
            'opex' => 0,
            'total_investation' => $totalInvestationConfig,
            'ebitda' => 0,
            'net_profit' => 0,
            'total_roi' => 0,
            'transfer_income' => 0,
        ];

        $row = $result[$currMonth];

        $income         = $row['net_profit'] + $row['cogs'] + ($row['net_profit'] * $capitalAllocMultiplier);
        $nominalRoyalty = 0.6 * $row['net_profit'];
        $revenue        = $row['raw_material_price'] + $row['direct_cost'];

        $cursor = Carbon::parse($startOfMonth);
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
            $monthIndex = $origin->diffInMonths($cursor) + 1;
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
            'total_investation'         => $row['total_investation'],
            'total_transaction'         => 0,
            'opex'                      => $row['opex'],
            'transfer_income'           => $row['transfer_income'] ?? 0,
            'transfer_price'            => $transferPrice ?? 0,
            'transfer_variance'         => ($row['transfer_income'] ?? 0) - $income,
            'income'                    => $income,
            'net_profit'                => $row['net_profit'],  // net margin/profit
            'nominal_royalty'           => $nominalRoyalty, //bagi hasil mitra
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
            'rental_cost'                => $row['rental_cost'],
            'projection_net_profit_high' => $projectionNetProfitHigh,
            'projection_net_profit_low'  => $projectionNetProfitLow,
            'projection_royalty_high'    => $projectionRoyaltyHigh,
            'projection_royalty_low'     => $projectionRoyaltyLow,
        ];
    }
}
