<?php

namespace App\Console\Commands;

use App\Services\Traceability\TraceabilityApiFetcher;
use Illuminate\Console\Command;

class ApiFetchTraceability extends Command
{
    protected $signature = 'traceability:api-fetch
        {--from= : Surat jalan date_from (Y-m-d)}
        {--to= : Surat jalan date_to (Y-m-d)}
        {--only=all : suppliers|wo|sj|details|all}';

    protected $description = 'Fetch raw data from the apipi API into the local JSON cache (Stage A, resumable)';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $fetcher = app(TraceabilityApiFetcher::class);
        $only = strtolower($this->option('only'));

        $this->info('Cache dir: ' . $fetcher->cacheDir());
        $progress = function ($stage, $page, $count, $total) {
            $this->line(sprintf('  %-11s page/idx %-6s  fetched %-7s  %s', $stage, $page, $count, $total ? "of {$total}" : ''));
        };

        try {
            if (in_array($only, ['all', 'suppliers'])) {
                $n = $fetcher->fetchSuppliers($progress);
                $this->info("suppliers.json: {$n} rows");
            }
            if (in_array($only, ['all', 'wo'])) {
                $n = $fetcher->fetchWorkOrders($progress);
                $this->info("work_orders.json: {$n} rows");
            }
            if (in_array($only, ['all', 'sj'])) {
                $n = $fetcher->fetchSuratJalanList($this->option('from'), $this->option('to'), $progress);
                $this->info("surat_jalan_list.json: {$n} rows");
            }
            if (in_array($only, ['all', 'details'])) {
                $n = $fetcher->fetchSuratJalanDetails($progress);
                $this->info("surat_jalan_details.ndjson: +{$n} rows this run");
            }
        } catch (\Throwable $e) {
            $this->error('Fetch failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
