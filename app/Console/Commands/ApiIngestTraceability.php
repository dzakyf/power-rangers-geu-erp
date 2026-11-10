<?php

namespace App\Console\Commands;

use App\Services\Traceability\TraceabilityApiIngestionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApiIngestTraceability extends Command
{
    protected $signature = 'traceability:api-ingest
        {--purge : Delete existing EXTERNAL traceability rows before ingesting (full swap)}
        {--purge-range= : Delete existing EXTERNAL rows in YYYY-MM-DD,YYYY-MM-DD before ingesting}
        {--skip-existing : Skip deliveries whose api_reference already exists (incremental)}
        {--from= : Only ingest SJ with date >= YYYY-MM-DD}
        {--to= : Only ingest SJ with date <= YYYY-MM-DD}
        {--bulk : Use chunked bulk inserts (requires purge/purge-range)}
        {--dry-run : Report only, do not write or purge}';

    protected $description = 'Ingest cached apipi SJ data into the ISCC traceability tables (Stage B)';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $dir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');

        if (!is_file("{$dir}/suppliers.json")) {
            $this->error("Missing {$dir}/suppliers.json. Run traceability:api-fetch first.");
            return 1;
        }

        $this->info('Loading cache...');
        $suppliersRaw = json_decode((string) file_get_contents("{$dir}/suppliers.json"), true) ?: [];
        $details      = json_decode((string) (@file_get_contents("{$dir}/supplier_details.json") ?: '[]'), true) ?: [];
        $woLazy       = json_decode((string) (@file_get_contents("{$dir}/work_orders_lazy.json") ?: '[]'), true) ?: [];

        $suppliers = [];
        $supplierById = [];
        $nameMap = [];
        foreach ($suppliersRaw as $s) {
            $id = $s['id'] ?? null;
            if ($id === null) { continue; }
            $d = $details[$id] ?? [];
            $rec = [
                'id'            => $id,
                'name'          => $s['name'] ?? ($d['name'] ?? null),
                'jenis'         => $s['jenis'] ?? ($d['jenis'] ?? null),
                'kota_name'     => ($s['kota_name'] ?? '') !== '' ? $s['kota_name'] : ($d['kota_name'] ?? null),
                'provinsi_name' => ($s['provinsi_name'] ?? '') !== '' ? $s['provinsi_name'] : ($d['provinsi_name'] ?? null),
                'gps'           => $s['gps'] ?? ($d['gps'] ?? null),
                'phone'         => $s['phone'] ?? ($d['phone'] ?? null),
                'nama_rek'      => $s['nama_rek'] ?? ($d['nama_rek'] ?? null),
                'nomor_rek'     => $s['nomor_rek'] ?? ($d['nomor_rek'] ?? null),
                'alamat'        => $d['alamat'] ?? ($s['alamat'] ?? null),
                'gudang_name'   => $s['gudang_name'] ?? ($d['gudang_name'] ?? null),
                'price'         => $d['price'] ?? null,
                'active'        => $s['active'] ?? 1,
            ];
            $suppliers[] = $rec;
            $supplierById[(string) $id] = $rec;
            $nameMap[$this->norm($s['name'] ?? '')] = $id;
        }
        $this->info('  suppliers: ' . count($suppliers) . ' | with detail: ' . count($details));

        $woMap = [];
        foreach ($woLazy as $wo) {
            if (is_array($wo) && isset($wo['id'])) { $woMap[$wo['id']] = $wo['supplier_id'] ?? null; }
        }
        $this->info('  work orders (lazy): ' . count($woMap));

        $ndjson = "{$dir}/surat_jalan_details.ndjson";
        $collections = [];
        $lines = 0;
        $missingSupplier = 0;
        $missingDetails = [];
        $zeroQty = [];
        $from = $this->option('from');
        $to = $this->option('to');

        if (is_file($ndjson)) {
            $records = [];
            $fh = fopen($ndjson, 'r');
            while (($line = fgets($fh)) !== false) {
                $rec = json_decode($line, true);
                if (!$rec || !isset($rec['id'])) { continue; }
                $records[$rec['id']] = $rec;
            }
            fclose($fh);

            foreach ($records as $rec) {
                $detail = is_array($rec['detail'] ?? null) ? $rec['detail'] : [];
                $header = $detail['header'] ?? [];
                $sjKode = $rec['kode'] ?? ($header['kode'] ?? null);
                $sjDate = substr((string) ($rec['tgl'] ?? ($header['tgl'] ?? '')), 0, 10);
                if (!$sjKode) { continue; }
                if ($from && (!$sjDate || $sjDate < $from)) { continue; }
                if ($to && (!$sjDate || $sjDate > $to)) { continue; }

                $sjDetails = $detail['details'] ?? null;
                if (!is_array($sjDetails) || empty($sjDetails)) {
                    $missingDetails[] = ['id' => $rec['id'], 'kode' => $sjKode, 'date' => $sjDate];
                    continue;
                }

                $sjQtyKg = $rec['qty_real_kg'] ?? null;
                $sjQtyKg = $sjQtyKg === null ? 0.0 : (float) $sjQtyKg;
                if ($sjQtyKg <= 0) {
                    $zeroQty[] = ['id' => $rec['id'], 'kode' => $sjKode, 'date' => $sjDate];
                    continue;
                }

                $valid = [];
                $weightTotal = 0.0;
                foreach ($sjDetails as $ln) {
                    $lines++;
                    if (($ln['status'] ?? 'done') !== 'done') { continue; }
                    $weight = (float) ($ln['qty_real'] ?? 0);
                    $valid[] = ['line' => $ln, 'weight' => $weight];
                    $weightTotal += $weight;
                }

                if (empty($valid)) {
                    $missingDetails[] = ['id' => $rec['id'], 'kode' => $sjKode, 'date' => $sjDate];
                    continue;
                }

                $allocations = $this->allocateKg($sjQtyKg, $valid, $weightTotal);
                $plat = $rec['plat'] ?? null;
                $driver = $rec['driver'] ?? null;
                $gudang = $rec['gudang'] ?? null;
                $pickupId = $rec['pickup_id'] ?? ($header['pickup_id'] ?? null);
                $poCode = $rec['kode_pickup'] ?? ($header['pickup']['kode'] ?? null);

                foreach ($valid as $i => $entry) {
                    $ln = $entry['line'];
                    $supplierName = $ln['supplier_name'] ?? null;

                    $sid = null;
                    if (!empty($ln['wo_id']) && isset($woMap[$ln['wo_id']])) {
                        $sid = $woMap[$ln['wo_id']];
                    }
                    if ($sid === null && $supplierName) {
                        $sid = $nameMap[$this->norm($supplierName)] ?? null;
                    }
                    if ($sid === null && $supplierName) {
                        $sid = 'NAME-' . substr(md5($this->norm($supplierName)), 0, 12);
                    }
                    if ($sid === null) {
                        $missingSupplier++;
                        continue;
                    }

                    if (!$supplierName && isset($supplierById[(string) $sid])) {
                        $supplierName = $supplierById[(string) $sid]['name'] ?? null;
                    }

                    $collections[] = [
                        'supplier_id'      => $sid,
                        'supplier_name'    => $supplierName,
                        'supplier_alamat'  => $ln['supplier_alamat'] ?? null,
                        'supplier_phone'   => $ln['supplier_phone'] ?? null,
                        'supplier_gps'     => $ln['supplier_gps'] ?? null,
                        'supplier_jenis'   => $ln['supplier_jenis'] ?? null,
                        'sj_kode'          => $sjKode,
                        'sj_date'          => $sjDate,
                        'qty_real_kg'      => $allocations[$i],
                        'qty_real'         => $entry['weight'],
                        'wo_code'          => $ln['wo_kode'] ?? null,
                        'po_code'          => $poCode,
                        'plat'             => $plat,
                        'driver'           => $driver,
                        'gudang'           => $gudang,
                        'pickup_id'        => $pickupId,
                        'unit'             => 'Kg',
                        'ttd'              => $ln['ttd'] ?? null,
                        'photo'            => $ln['_first_photo'] ?? null,
                    ];
                }
            }
        }

        $this->info('  lines: ' . $lines . ' | collections: ' . count($collections));
        $this->info('  SJ missing details: ' . count($missingDetails) . ' | zero qty: ' . count($zeroQty) . ' | unresolved supplier: ' . $missingSupplier);
        foreach (array_slice($missingDetails, 0, 20) as $row) {
            $this->warn("  skip SJ {$row['id']} {$row['kode']} {$row['date']}: details is null/empty");
        }

        if ($this->option('dry-run')) {
            $this->info('DRY RUN: no data written.');
            return 0;
        }

        if (!$collections) { $this->error('No collections to ingest.'); return 1; }

        $purgeRange = $this->option('purge-range');
        if ($purgeRange) {
            $range = array_map('trim', explode(',', $purgeRange));
            if (count($range) !== 2) {
                $this->error('--purge-range must be YYYY-MM-DD,YYYY-MM-DD');
                return 1;
            }
            $this->purgeRange($range[0], $range[1]);
        }

        $bulk = (bool) $this->option('bulk');
        if ($bulk && !$this->option('purge') && !$purgeRange) {
            $this->error('--bulk requires --purge or --purge-range.');
            return 1;
        }

        $this->warn($this->option('purge') ? 'Ingesting with PURGE...' : ($this->option('skip-existing') ? 'Ingesting skip-existing...' : 'Ingesting upsert...'));
        $counts = app(TraceabilityApiIngestionService::class)
            ->ingest($suppliers, $collections, (bool) $this->option('purge'), (bool) $this->option('skip-existing'), $bulk);

        $this->info('--- Ingest summary ---');
        $this->line('  groups: ' . $counts['groups'] . ' | created: ' . $counts['created'] . ' | updated: ' . $counts['updated'] . ' | skipped: ' . $counts['skipped']);
        $this->line('  fragments: ' . $counts['fragments']);
        return 0;
    }

    protected function purgeRange(string $from, string $to): void
    {
        $ids = DB::table('traceability_external_deliveries')
            ->whereDate('delivery_date', '>=', $from)
            ->whereDate('delivery_date', '<=', $to)
            ->pluck('id')->all();

        if (!$ids) {
            $this->info("  purge range {$from}..{$to}: nothing to delete");
            return;
        }

        $fragments = 0;
        foreach (array_chunk($ids, 500) as $chunk) {
            $fragments += DB::table('traceability_external_poo_fragments')
                ->whereIn('traceability_external_delivery_id', $chunk)->delete();
        }
        $deliveries = 0;
        foreach (array_chunk($ids, 500) as $chunk) {
            $deliveries += DB::table('traceability_external_deliveries')->whereIn('id', $chunk)->delete();
        }

        $this->info("  purged {$from}..{$to}: deliveries={$deliveries}, fragments={$fragments}");
    }

    /**
     * Allocate the SJ-level kg total across its detail lines.
     * When the line weights are unusable, split equally.
     */
    protected function allocateKg(float $totalKg, array $valid, float $weightTotal): array
    {
        $count = count($valid);
        if ($count === 1) {
            return [round($totalKg, 2)];
        }

        $totalCents = (int) round($totalKg * 100);
        $allocations = [];
        $used = 0;

        foreach ($valid as $i => $entry) {
            if ($i === $count - 1) {
                $allocations[] = round(($totalCents - $used) / 100, 2);
                break;
            }

            $weight = $weightTotal > 0 ? $entry['weight'] / $weightTotal : 1 / $count;
            $cents = (int) round($totalCents * $weight);
            $allocations[] = round($cents / 100, 2);
            $used += $cents;
        }

        return $allocations;
    }

    protected function norm(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
