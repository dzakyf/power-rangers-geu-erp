<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\ApipiApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfills delivery.driver_name / plat_no from the SJ list, and
 * fragment.site from the supplier API's gudang_name (by fragment poo_name),
 * for a given year.
 */
class BackfillTraceabilitySjMeta extends Command
{
    protected $signature = 'traceability:backfill-sjmeta {--year=2026}';
    protected $description = 'Backfill driver/plat/site columns for traceability (given year)';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $year = (int) $this->option('year');
        if ($year < 2000) { $this->error('Invalid --year'); return 1; }

        $dir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');
        $client = app(ApipiApiClient::class);

        // ---- 1) driver_name + plat_no from SJ list
        $updatedDeliveries = 0;
        for ($m = 1; $m <= 12; $m++) {
            $start = sprintf('%04d-%02d-01', $year, $m);
            $end   = date('Y-m-t', strtotime($start));
            $page = 1;
            do {
                $res = $client->suratJalan($page, 100, 'done', $start, $end);
                $rows = $res['data'] ?? [];
                foreach ($rows as $sj) {
                    $kode = $sj['kode'] ?? null;
                    if (!$kode) { continue; }
                    $n = DB::table('traceability_external_deliveries')
                        ->where('api_reference', 'like', $kode . '-%')
                        ->update([
                            'driver_name' => $sj['driver_name'] ?? null,
                            'plat_no'     => $sj['fleet_plat'] ?? null,
                        ]);
                    $updatedDeliveries += $n;
                }
                $hasNext = !empty($res['pagination']['has_next']);
                $page++;
            } while ($hasNext && $page <= 100);
            $this->line("  {$year}-" . str_pad($m, 2, '0', STR_PAD_LEFT) . " SJ meta done");
        }
        $this->info("Deliveries updated (driver/plat): {$updatedDeliveries}");

        // ---- 2) site from supplier gudang_name (by poo_name)
        $suppliers = json_decode((string) @file_get_contents($dir . '/suppliers.json'), true) ?: [];
        $nameToGudang = [];
        foreach ($suppliers as $s) {
            $nm = strtolower(trim(preg_replace('/\s+/', ' ', (string) ($s['name'] ?? ''))));
            if ($nm === '') { continue; }
            $g = $s['gudang_name'] ?? null;
            if ($g === null || $g === '') { continue; }
            if (!isset($nameToGudang[$nm])) { $nameToGudang[$nm] = $g; }
        }

        $from = sprintf('%04d-01-01', $year);
        $to   = sprintf('%04d-12-31', $year);
        $names = DB::table('traceability_external_poo_fragments')
            ->whereBetween('do_date', [$from, $to])
            ->distinct()->pluck('poo_name');

        $updatedFrags = 0; $matched = 0;
        foreach ($names as $nmRaw) {
            $nm = strtolower(trim(preg_replace('/\s+/', ' ', (string) $nmRaw)));
            if (!isset($nameToGudang[$nm])) { continue; }
            $matched++;
            $updatedFrags += DB::table('traceability_external_poo_fragments')
                ->where('poo_name', $nmRaw)
                ->whereBetween('do_date', [$from, $to])
                ->update(['site' => $nameToGudang[$nm]]);
        }
        $this->info("Fragment site updated: {$updatedFrags} (names matched: {$matched})");

        return 0;
    }
}
