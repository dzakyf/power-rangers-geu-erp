<?php

namespace App\Console\Commands;

use App\Services\SettlementJournalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillSettlementJournals extends Command
{
    protected $signature = 'warehouse:backfill-settlement-journals {--apply : Actually write journal rows (default is dry-run)}';

    protected $description = 'Backfill warehouse_partner_journal from historical UCO settlements (settled routes only)';

    public function handle(SettlementJournalService $service): int
    {
        $apply = (bool) $this->option('apply');
        $settlements = DB::table('settlement')->orderBy('ID')->get();

        $posted = 0;
        $skipped = [];
        $routeCache = [];

        foreach ($settlements as $s) {
            $routeId = (string) $s->Route_Id;

            if (strpos($routeId, ',') !== false) {
                $skipped[] = "settlement {$s->ID}: multi-route";
                continue;
            }

            if (!array_key_exists($routeId, $routeCache)) {
                $routeCache[$routeId] = DB::table('pickup_routes')->where('ID', $routeId)->first();
            }
            $route = $routeCache[$routeId];

            if (!$route) {
                $skipped[] = "settlement {$s->ID}: route {$routeId} not found";
                continue;
            }
            if ($route->Status !== 'SETTLED') {
                $skipped[] = "settlement {$s->ID}: route not settled ({$route->Status})";
                continue;
            }

            $warehouseTaskId = $service->resolveWarehouseTaskId($route);
            $status = $service->postSettlement($s, $warehouseTaskId, !$apply);

            if ($status === 'posted' || $status === 'would_post') {
                $posted++;
            } else {
                $skipped[] = "settlement {$s->ID}: {$status}";
            }
        }

        $label = $apply ? 'Posted' : 'Would post';
        $this->info("{$label}: {$posted} row(s).");
        foreach ($skipped as $reason) {
            $this->line("  skipped - {$reason}");
        }

        if (!$apply) {
            $this->comment('Dry run. Re-run with --apply to write.');
        }

        return 0;
    }
}
