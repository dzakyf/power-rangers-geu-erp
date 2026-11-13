<?php

namespace App\Console\Commands;

use App\Services\Partner\PickupUnitService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileUcoInstock extends Command
{
    protected $signature = 'uco:reconcile-instock {--apply : Write the matched results}';

    protected $description = 'Reconcile legacy UCO instock supplies with their source POs (dry-run by default)';

    public function handle()
    {
        $supplies = DB::table('warehouse_partner_supplies')
            ->whereNull('Instock_Batch_Ref')
            ->orderBy('created_date')
            ->orderBy('id')
            ->get();

        if ($supplies->isEmpty()) {
            $this->info('No unreconciled instock supplies found.');
            return 0;
        }

        $groups = [];
        foreach ($supplies as $supply) {
            $key = implode('|', [
                (string) $supply->warehouse_task_id,
                (string) $supply->received_date,
                (string) $supply->created_date,
                (string) ($supply->source ?? ''),
            ]);
            $groups[$key][] = $supply;
        }

        $matchedPoIds = [];
        $rows = [];
        $apply = (bool) $this->option('apply');

        foreach ($groups as $group) {
            $first = $group[0];
            $supplyIds = array_map(fn ($s) => (int) $s->id, $group);
            $qty = array_sum(array_map(fn ($s) => (float) $s->received_qty, $group));
            $warehouseTaskId = (string) $first->warehouse_task_id;
            $createdAt = $first->created_date ?: ($first->received_date . ' 23:59:59');

            $candidates = DB::table('pickup_order as po')
                ->join('work_order as wo', 'po.WO_Id', '=', 'wo.ID')
                ->leftJoin('master_poo as mp', 'wo.POO_Id', '=', 'mp.ID')
                ->leftJoin('delivery_order as d', 'po.DO_Id', '=', 'd.ID')
                ->leftJoin('warehouse_partner as wp', 'mp.Nearest_Warehouse', '=', 'wp.id')
                ->whereNull('po.Instock_At')
                ->where('wp.warehouse_task_id', $warehouseTaskId)
                ->where('d.DO_Status', 'DONE')
                ->where('po.Actual_Qty', '>', 0)
                ->where('po.Created_At', '<=', $createdAt)
                ->whereNotIn('po.ID', $matchedPoIds)
                ->select(
                    'po.ID',
                    'po.Code as PO_Code',
                    'po.Actual_Qty',
                    'po.Price',
                    'po.Price_Unit',
                    'po.Package',
                    'po.Package_Capacity',
                    'po.Created_At',
                    'd.Code as DO_Code',
                    'mp.Name as POO_Name',
                    'mp.Price as POO_Price',
                    'wp.warehouse_location'
                )
                ->orderByDesc('po.Created_At')
                ->orderByDesc('po.ID')
                ->get();

            $exact = $candidates->filter(function ($po) use ($qty) {
                return abs((float) $po->Actual_Qty - $qty) <= 0.01;
            })->values();

            if ($exact->isEmpty()) {
                $rows[] = [
                    implode(',', $supplyIds),
                    $warehouseTaskId,
                    number_format($qty, 2, '.', ','),
                    (string) $first->created_date,
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                    'Tidak ada PO yang cocok (qty/warehouse/DO DONE)',
                ];
                continue;
            }

            if ($exact->count() > 1) {
                $rows[] = [
                    implode(',', $supplyIds),
                    $warehouseTaskId,
                    number_format($qty, 2, '.', ','),
                    (string) $first->created_date,
                    $exact->count() . ' kandidat',
                    '-',
                    '-',
                    '-',
                    '-',
                    'Ambigu: lebh dari satu PO cocok',
                ];
                continue;
            }

            $po = $exact->first();
            $matchedPoIds[] = (int) $po->ID;
            $units = PickupUnitService::resolve($po, $po->POO_Price !== null ? (float) $po->POO_Price : null);
            $price = $units['price_per_liter'];
            $total = $qty * $price;
            $batchRef = 'LEGACY-' . $supplyIds[0];
            $note = 'OK';

            if ($apply) {
                DB::transaction(function () use ($group, $po, $batchRef, $price, $supplyIds) {
                    DB::table('pickup_order')->where('ID', $po->ID)->update([
                        'Instock_At'        => $group[0]->created_date ?: now(),
                        'Instock_By'        => $group[0]->created_by ?? 'legacy-reconcile',
                        'Instock_Batch_Ref' => $batchRef,
                    ]);

                    foreach ($group as $supply) {
                        DB::table('warehouse_partner_supplies')->where('id', $supply->id)->update([
                            'Instock_Batch_Ref' => $batchRef,
                            'price'             => $price,
                            'total'             => round((float) $supply->received_qty * $price, 2),
                        ]);
                    }
                });
                $note = 'UPDATED';
            }

            $rows[] = [
                implode(',', $supplyIds),
                $warehouseTaskId,
                number_format($qty, 2, '.', ','),
                (string) $first->created_date,
                '1',
                $po->PO_Code,
                $po->DO_Code,
                number_format((float) $first->price, 2, '.', ','),
                number_format($price, 2, '.', ',') . ' / L',
                $note . ' | total ' . number_format($total, 2, '.', ','),
            ];
        }

        $this->table([
            'Supply IDs',
            'Warehouse',
            'Qty',
            'Created',
            'Candidates',
            'PO',
            'DO',
            'Old Price',
            'New Price',
            'Result',
        ], $rows);

        if (!$apply) {
            $this->comment('Dry-run only. Re-run with --apply to write the high-confidence matches.');
        }

        return 0;
    }
}
