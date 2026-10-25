<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\ApipiApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 2026-only backfill:
 *  sjmeta : site/driver/plat/pickup_id from SJ list (+ propagate site to fragments)
 *  delete : hard delete deliverables of excluded sites
 *  unit   : fragments.unit from cached SJ details (satuan_name)
 *  price  : fragments.price from /api-tms/pickups/{id} items[].harga
 */
class BackfillTraceability2026 extends Command
{
    protected $signature = 'traceability:backfill-2026 {--step=all : all|sjmeta|delete|unit|price}';
    protected $description = 'Backfill 2026 traceability (site/driver/plat, delete excluded, unit, price)';

    const YEAR = 2026;
    const EXCLUDED_SITES = ['Final Storage - Pelindo', 'Gresik - PTU', 'Mirah - Pelindo', 'Transit - Sidoarjo'];

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $step = strtolower($this->option('step'));
        $client = app(ApipiApiClient::class);

        if (in_array($step, ['all', 'sjmeta'])) { $this->stepSjMeta($client); }
        if (in_array($step, ['all', 'delete'])) { $this->stepDelete(); }
        if (in_array($step, ['all', 'unit']))   { $this->stepUnit(); }
        if (in_array($step, ['all', 'price']))  { $this->stepPrice($client); }

        return 0;
    }

    protected function yearStart(): string { return self::YEAR . '-01-01'; }
    protected function yearEnd(): string { return self::YEAR . '-12-31'; }

    /** site/driver/plat/pickup_id from SJ list, and propagate site to fragments */
    protected function stepSjMeta(ApipiApiClient $client): void
    {
        $this->info('== step: sjmeta ==');
        $updated = 0;
        for ($m = 1; $m <= 12; $m++) {
            $start = sprintf('%d-%02d-01', self::YEAR, $m);
            $end = date('Y-m-t', strtotime($start));
            $page = 1;
            do {
                $res = $client->suratJalan($page, 100, 'done', $start, $end);
                $rows = $res['data'] ?? [];
                foreach ($rows as $sj) {
                    $kode = $sj['kode'] ?? null;
                    if (!$kode) { continue; }
                    $updated += DB::table('traceability_external_deliveries')
                        ->where('api_reference', 'like', $kode . '-%')
                        ->update([
                            'site'        => $sj['gudang_name'] ?? null,
                            'driver_name' => $sj['driver_name'] ?? null,
                            'plat_no'     => $sj['fleet_plat'] ?? null,
                            'pickup_id'   => $sj['pickup_id'] ?? null,
                        ]);
                }
                $page++;
                $hasNext = !empty($res['pagination']['has_next']);
            } while ($hasNext && $page <= 100);
            $this->line("  {$start} .. {$end} done");
        }
        // propagate delivery site -> fragment site
        DB::statement(
            "UPDATE traceability_external_poo_fragments f
             JOIN traceability_external_deliveries d ON d.id = f.traceability_external_delivery_id
             SET f.site = d.site
             WHERE d.delivery_date BETWEEN ? AND ?",
            [$this->yearStart(), $this->yearEnd()]
        );
        $this->info("Deliveries updated: {$updated}");
    }

    /** hard delete excluded-site deliveries + fragments (2026 only) */
    protected function stepDelete(): void
    {
        $this->info('== step: delete excluded sites ==');
        $ids = DB::table('traceability_external_deliveries')
            ->whereBetween('delivery_date', [$this->yearStart(), $this->yearEnd()])
            ->whereIn('site', self::EXCLUDED_SITES)
            ->pluck('id')->all();

        if (!$ids) { $this->info('  nothing to delete'); return; }

        $frag = 0;
        foreach (array_chunk($ids, 500) as $chunk) {
            $frag += DB::table('traceability_external_poo_fragments')->whereIn('traceability_external_delivery_id', $chunk)->delete();
        }
        $del = 0;
        foreach (array_chunk($ids, 500) as $chunk) {
            $del += DB::table('traceability_external_deliveries')->whereIn('id', $chunk)->delete();
        }
        $this->info("  deleted fragments: {$frag} | deliveries: {$del}");
    }

    /** unit from cached SJ details (satuan_name) */
    protected function stepUnit(): void
    {
        $this->info('== step: unit ==');
        $dir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');
        $nd = $dir . '/surat_jalan_details.ndjson';
        if (!is_file($nd)) { $this->error('  cache ndjson missing'); return; }

        $map = []; // sj kode => satuan_name (first non-empty)
        $fh = fopen($nd, 'r');
        while (($line = fgets($fh)) !== false) {
            $rec = json_decode($line, true);
            if (!$rec) { continue; }
            $kode = $rec['kode'] ?? ($rec['detail']['header']['kode'] ?? null);
            if (!$kode || isset($map[$kode])) { continue; }
            foreach (($rec['detail']['details'] ?? []) as $ln) {
                $u = $ln['satuan_name'] ?? null;
                if ($u) { $map[$kode] = $u; break; }
            }
        }
        fclose($fh);

        $updated = 0; $n = 0;
        foreach ($map as $kode => $unit) {
            $n++;
            $updated += DB::table('traceability_external_poo_fragments')
                ->whereIn('traceability_external_delivery_id', function ($q) use ($kode) {
                    $q->select('id')->from('traceability_external_deliveries')
                      ->where('api_reference', 'like', $kode . '-%')
                      ->whereBetween('delivery_date', [$this->yearStart(), $this->yearEnd()]);
                })
                ->update(['unit' => $unit]);
        }
        $this->info("  SJ units mapped: {$n} | fragments updated: {$updated}");
    }

    /** price from pickup API items[].harga */
    protected function stepPrice(ApipiApiClient $client): void
    {
        $this->info('== step: price (pickup api) ==');
        $deliveries = DB::table('traceability_external_deliveries')
            ->whereBetween('delivery_date', [$this->yearStart(), $this->yearEnd()])
            ->whereNotNull('pickup_id')
            ->select('id', 'pickup_id', 'api_reference')
            ->get();

        // group deliveries by pickup
        $byPickup = [];
        foreach ($deliveries as $d) {
            $byPickup[$d->pickup_id][] = $d;
        }
        $this->info('  pickups to fetch: ' . count($byPickup));

        // resumable progress
        $dir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');
        $progFile = $dir . '/backfill2026_price_done.json';
        $done = is_file($progFile) ? (json_decode((string) file_get_contents($progFile), true) ?: []) : [];
        $doneSet = array_flip($done);
        $this->info('  already done: ' . count($doneSet));

        $updatedFrags = 0; $fetched = 0; $failed = 0; $sinceSave = 0;
        foreach ($byPickup as $pickupId => $dels) {
            if (isset($doneSet[$pickupId])) { continue; }
            try {
                $data = $client->pickup($pickupId);
                $items = $data['items'] ?? [];
                $fetched++;
            } catch (\Throwable $e) {
                $failed++;
                continue;
            }
            // mark done after a successful fetch (even if no price found)
            $doneSet[$pickupId] = true;
            $sinceSave++;
            if ($sinceSave >= 50) {
                file_put_contents($progFile, json_encode(array_keys($doneSet)));
                $sinceSave = 0;
                $this->line('  progress: ' . count($doneSet) . '/' . count($byPickup));
            }

            $bySupplier = [];
            $firstHarga = null;
            foreach ($items as $i => $it) {
                $h = $it['harga'] ?? null;
                if ($firstHarga === null && $h !== null) { $firstHarga = $h; }
                if (!empty($it['supplier_id'])) { $bySupplier[(string) $it['supplier_id']] = $h; }
            }

            foreach ($dels as $d) {
                // original supplier id = suffix of api_reference
                $sid = null;
                if ($d->api_reference && strpos($d->api_reference, '-') !== false) {
                    $parts = explode('-', $d->api_reference);
                    $sid = end($parts);
                }
                $harga = ($sid !== null && isset($bySupplier[(string) $sid])) ? $bySupplier[(string) $sid] : $firstHarga;
                if ($harga === null) { continue; }
                $updatedFrags += DB::table('traceability_external_poo_fragments')
                    ->where('traceability_external_delivery_id', $d->id)
                    ->update(['price' => $harga]);
            }
        }
        file_put_contents($progFile, json_encode(array_keys($doneSet)));
        $this->info("  pickups fetched: {$fetched} | failed: {$failed} | fragments price updated: {$updatedFrags}");
    }
}
