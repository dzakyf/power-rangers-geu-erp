<?php

namespace App\Console\Commands;

use App\Services\GA\GaInventoryService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GaInventoryOpeningBalance extends Command
{
    protected $signature = 'ga:inventory-opening-balance {--date= : Cutover date (Y-m-d)} {--location= : Location filter} {--wh= : Warehouse filter}';

    protected $description = 'Backfill GA stock ledger opening balances from the legacy supplies table.';

    public function handle(GaInventoryService $inventory)
    {
        $date = $this->option('date') ?: Carbon::now()->toDateString();

        try {
            $result = $inventory->backfillOpeningBalance($date, $this->option('location'), $this->option('wh'), null);
            $this->info('Created: ' . $result['created'] . ', skipped: ' . $result['skipped'] . ' (cutover ' . $result['date'] . ')');

            return 0;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return 1;
        }
    }
}