<?php

namespace App\Console\Commands;

use App\Services\WarehouseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecomputeWarehouseSystemQty extends Command
{
    protected $signature = 'warehouse:recompute-system-qty';

    protected $description = 'Recompute and cache warehouse_partner.System_Qty from the container event ledger';

    public function handle(WarehouseService $warehouseService): int
    {
        $ids = DB::table('warehouse_partner')->pluck('id');
        $count = 0;

        foreach ($ids as $id) {
            DB::transaction(function () use ($id, $warehouseService) {
                $warehouseService->recomputeWarehouseSystemQty($id);
            });
            $count++;
        }

        $this->info("Recomputed System_Qty for {$count} warehouse(s).");

        return 0;
    }
}
