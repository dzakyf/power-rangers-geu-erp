<?php

namespace App\Console\Commands;

use App\Services\Traceability\TraceabilityApiFetcher;
use Illuminate\Console\Command;

class ApiFetchTraceability extends Command
{
    protected $signature = 'traceability:api-fetch
        {--from= : Surat jalan date_from (Y-m-d)}
        {--to= : Surat jalan date_to (Y-m-d)}
        {--with-photos : Also fetch each detail line\'s first photo}
        {--only=all : suppliers|sj|details|wo|supplier-details|all}';

    protected $description = 'Fetch raw data from the apipi API into the local JSON cache (Stage A, resumable)';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $f = app(TraceabilityApiFetcher::class);
        $only = strtolower($this->option('only'));

        $this->info('Cache dir: ' . $f->cacheDir());
        $cb = function ($stage, $page, $count, $total) {
            $this->line(sprintf('  %-16s idx/page %-6s fetched %-7s %s', $stage, $page, $count, $total ? "of {$total}" : ''));
        };
        $run = function ($label, callable $fn) use ($cb) {
            $this->info("--> {$label}");
            $n = $fn();
            $this->info("    {$label}: {$n}");
        };

        try {
            if (in_array($only, ['all', 'suppliers'])) {
                $run('suppliers', function () use ($f, $cb) { return $f->fetchSuppliers($cb); });
            }
            if (in_array($only, ['all', 'sj'])) {
                $run('surat_jalan_list', function () use ($f, $cb) { return $f->fetchSuratJalanList($this->option('from'), $this->option('to'), $cb); });
            }
            if (in_array($only, ['all', 'details'])) {
                $run('surat_jalan_details', function () use ($f, $cb) { return $f->fetchSuratJalanDetails((bool) $this->option('with-photos'), $cb); });
            }
            if (in_array($only, ['all', 'wo'])) {
                $run('work_orders_lazy', function () use ($f, $cb) { return $f->fetchWorkOrdersLazy($cb); });
            }
            if (in_array($only, ['all', 'supplier-details'])) {
                $run('supplier_details', function () use ($f, $cb) { return $f->fetchSupplierDetails($cb); });
            }
        } catch (\Throwable $e) {
            $this->error('Fetch failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
