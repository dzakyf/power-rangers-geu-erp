<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\WarehousePartnerSupplies;
use App\WarehouseStockRequest;
use App\WarehousePartnerJournal;
use App\MasterWarehouseAccount;

class WarehouseService
{
    /**
     * Event-sourced system quantity for a container:
     * stock-in (+) and outstock loading (-) replayed in timestamp order,
     * rebaselined to the physical count at each CLOSED stock opname.
     *
     * Adjustments are approval artifacts of an opname, never additive terms -
     * the CLOSED opname itself is the rebaseline. Pending/rejected counts
     * never rebaseline. The balance may go negative before an opname: an
     * honest signal of unattributed stock that the next physical count heals.
     *
     * This is the single source of truth for "System Quantity" everywhere.
     */
    public function containerSystemQty($containerId): float
    {
        return $this->foldEvents($this->containerEvents($containerId), null);
    }

    /**
     * The container balance as of (strictly before) a point in time - used for
     * a document's pre-document baseline without later events (e.g. a CLOSED
     * opname rebaseline) contaminating it.
     */
    public function containerSystemQtyAsOf($containerId, ?string $asOf, array $excludeDetailIds = []): float
    {
        return $this->foldEvents($this->containerEvents($containerId), $asOf, $excludeDetailIds);
    }

    private function containerEvents($containerId): array
    {
        $events = [];

        foreach (DB::table('warehouse_partner_supplies')
            ->where('Container_ID', $containerId)
            ->get(['received_qty', 'created_date']) as $s) {
            $events[] = ['ts' => (string) $s->created_date, 'qty' => (float) $s->received_qty, 'reset' => false];
        }

        foreach (DB::table('outstock_details')
            ->where('Container_ID', $containerId)
            ->get(['ID', 'Actual_Qty', 'Created_At']) as $l) {
            $events[] = ['id' => (int) $l->ID, 'ts' => (string) $l->Created_At, 'qty' => -(float) $l->Actual_Qty, 'reset' => false];
        }

        foreach (DB::table('warehouse_stock_opnames')
            ->where('Container_ID', $containerId)
            ->where('Status', 'CLOSED')
            ->get(['Physical_Qty', 'Created_At']) as $o) {
            $events[] = ['ts' => (string) $o->Created_At, 'qty' => (float) $o->Physical_Qty, 'reset' => true];
        }

        usort($events, function ($a, $b) {
            $cmp = strcmp($a['ts'], $b['ts']);
            if ($cmp !== 0) {
                return $cmp;
            }
            // same timestamp: events sort first - the snapshot the count was
            // taken against already included them, so the rebaseline supersedes them
            return $a['reset'] <=> $b['reset'];
        });

        return $events;
    }

    private function foldEvents(array $events, ?string $asOf, array $excludeDetailIds = []): float
    {
        $balance = 0.0;
        foreach ($events as $e) {
            if (!empty($e['id']) && in_array($e['id'], $excludeDetailIds, true)) {
                continue; // this document's own loading lines
            }
            if ($asOf !== null && strcmp($e['ts'], $asOf) > 0) {
                continue; // events strictly after the document's loading time
            }
            $balance = $e['reset'] ? $e['qty'] : $balance + $e['qty'];
        }
        return $balance;
    }

    /**
     * Recompute and cache the warehouse-level system quantity. Call inside a
     * transaction; locks the warehouse row to serialise concurrent updates.
     */
    public function recomputeWarehouseSystemQty($warehouseId): float
    {
        $wh = DB::table('warehouse_partner')->where('id', $warehouseId)->lockForUpdate()->first(['id']);
        if (!$wh) {
            return 0.0;
        }

        $total = 0.0;
        foreach (DB::table('master_container')->where('Warehouse_ID', $warehouseId)->pluck('ID') as $cid) {
            $total += $this->containerSystemQty($cid);
        }

        DB::table('warehouse_partner')->where('id', $warehouseId)->update([
            'System_Qty'            => $total,
            'System_Qty_Updated_At' => now(),
        ]);

        return $total;
    }

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

        $result = collect($supplies)->mapWithKeys(function ($data, $month) use ($totalInvestationConfig, $capitalAllocMultiplier) {
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
            $transfer_income = $data['qty_out'] * ($data['avg_out'] ?? 0);
            $capitalAllocation = $transfer_income * $capitalAllocMultiplier;
            $netProfit = $transfer_income - $cogs - $overhead_cost - $capitalAllocation;
            $ebitda = ($netProfit * 0.6) - $employeeSalary - $rentalCost;

            return [$month => [
                'qty_out'            => $data['qty_out'] ?? 0,
                'avg_out'            => round($data['avg_out'] ?? 0),
                'raw_avg_out'        => $data['avg_out'] ?? 0,
                'avg_in'             => round($data['avg'] ?? 0),
                'raw_avg_in'         => $data['avg'] ?? 0,
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
                'raw_avg_out'        => 0,
                'avg_in'             => 0,
                'raw_avg_in'         => 0,
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

            $income            = $row['transfer_income'];
            $capitalAllocVal  = $income * $capitalAllocMultiplier;
            $row['net_profit'] = $income - $row['cogs'] - $row['overhead_cost'] - $capitalAllocVal;
            $nominalRoyalty    = $row['net_profit'] * 0.6;
            $row['ebitda']     = $nominalRoyalty - $row['employee_salary'] - $row['rental_cost'];
            $row['total_roi']  = $row['total_investation'] > 0 ? ($row['ebitda'] / $row['total_investation']) * 100 : 0;
            $revenue           = $income;

            $projectionRoyaltyHigh = 0;
            $projectionRoyaltyLow  = 0;
            $projectionNetProfitHigh = 0;
            $projectionNetProfitLow  = 0;

            $operationalDate = DB::table('warehouse_partner as wp')
                ->join('opportunity_partner as op', 'op.lead_id', '=', 'wp.partner_task_id')
                ->where('wp.warehouse_task_id', $warehouseTaskId)
                ->where('op.onboarding_stage', 'ACTIVE')
                ->orderBy('op.id')
                ->value('op.onboarding_stage_updated_at');

            if ($operationalDate) {
                $origin  = Carbon::parse($operationalDate)->startOfMonth();
                $current = Carbon::parse($startDate)->startOfMonth();

                if ($current->greaterThanOrEqualTo($origin)) {
                    $monthIndex = $origin->diffInMonths($current) + 1;
                    $yearCount  = (int) floor(($monthIndex - 1) / 12) + 1;
                    $colIndex   = (($monthIndex - 1) % 12) + 1;
                    $col        = "Month_{$colIndex}_Total";

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
                'raw_avg_out'               => $row['raw_avg_out'] ?? 0,
                'avg_in'                    => $row['avg_in'],
                'raw_avg_in'                => $row['raw_avg_in'],
                'total_investation'         => $row['total_investation'],
                'total_transaction'         => 0,
                'opex'                      => $row['opex'],
                'transfer_income'           => $row['transfer_income'] ?? 0,
                'transfer_price'            => $row['raw_avg_out'] ?? 0,
                'transfer_variance'         => ($row['transfer_income'] ?? 0) - $income,
                'income'                    => $income,
                'net_profit'                => $row['net_profit'],
                'nominal_royalty'           => $nominalRoyalty,
                'company_profit_share'      => $row['net_profit'] * 0.4,
                'raw_material_price'        => $row['raw_material_price'],
                'direct_cost'               => $row['direct_cost'],
                'overhead_cost'             => $row['overhead_cost'],
                'total_overhead_cost'       => $row['overhead_cost'],
                'cogs'                      => $row['cogs'],
                'ebitda'                    => $row['ebitda'],
                'gross_margin'              => $income - $row['cogs'],
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
            $totalWeightedAvg += ($md['raw_avg_out'] ?? 0) * $md['qty_out'];
            $totalQtyForAvg += $md['qty_out'];
        }
        $agg['avg_out'] = $totalQtyForAvg > 0 ? round($totalWeightedAvg / $totalQtyForAvg) : 0;
        $agg['raw_avg_out'] = $totalQtyForAvg > 0 ? ($totalWeightedAvg / $totalQtyForAvg) : 0;

        $totalInboundValue = 0;
        $totalInboundQty = 0;
        foreach ($result as $md) {
            $totalInboundValue += ($md['raw_avg_in'] ?? 0) * $md['qty_in'];
            $totalInboundQty += $md['qty_in'];
        }
        $agg['avg_in'] = $totalInboundQty > 0 ? round($totalInboundValue / $totalInboundQty) : 0;
        $agg['raw_avg_in'] = $totalInboundQty > 0 ? ($totalInboundValue / $totalInboundQty) : 0;

        $agg['cogs'] = $agg['direct_cost'] + $agg['raw_material_price'];

        $incomeAgg = $agg['transfer_income'];
        $capitalAllocationAgg = $incomeAgg * $capitalAllocMultiplier;
        $agg['net_profit'] = $incomeAgg - $agg['cogs'] - $agg['overhead_cost'] - $capitalAllocationAgg;
        $nominalRoyaltyAgg = $agg['net_profit'] * 0.6;
        $agg['ebitda'] = $nominalRoyaltyAgg - $agg['employee_salary'] - $agg['rental_cost'];
        $agg['total_roi'] = $agg['total_investation'] > 0 ? ($agg['ebitda'] / $agg['total_investation']) * 100 : 0;

        $incomeDerived = $incomeAgg;
        $revenueAgg = $incomeAgg;
        return [
            'qty_in'                    => $agg['qty_in'],
            'qty_out'                   => $agg['qty_out'],
            'avg_out'                   => $agg['avg_out'],
            'raw_avg_out'               => $agg['raw_avg_out'] ?? 0,
            'avg_in'                    => $agg['avg_in'],
            'raw_avg_in'                => $agg['raw_avg_in'],
            'total_investation'         => $agg['total_investation'],
            'total_transaction'         => 0,
            'opex'                      => $agg['opex'],
            'transfer_income'           => $agg['transfer_income'] ?? 0,
            'transfer_price'            => $agg['raw_avg_out'] ?? 0,
            'transfer_variance'         => ($agg['transfer_income'] ?? 0) - $incomeDerived,
            'income'                    => $incomeDerived,
            'net_profit'                => $agg['net_profit'],
            'nominal_royalty'           => $nominalRoyaltyAgg,
            'company_profit_share'      => $agg['net_profit'] * 0.4,
            'raw_material_price'        => $agg['raw_material_price'],
            'direct_cost'               => $agg['direct_cost'],
            'overhead_cost'             => $agg['overhead_cost'],
            'total_overhead_cost'       => $agg['overhead_cost'],
            'cogs'                      => $agg['cogs'],
            'ebitda'                    => $agg['ebitda'],
            'gross_margin'              => $incomeDerived - $agg['cogs'],
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