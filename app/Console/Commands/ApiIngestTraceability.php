<?php

namespace App\Console\Commands;

use App\Services\Traceability\TraceabilityApiIngestionService;
use Illuminate\Console\Command;

class ApiIngestTraceability extends Command
{
    protected $signature = 'traceability:api-ingest
        {--purge : Delete existing EXTERNAL traceability rows before ingesting (full swap)}';

    protected $description = 'Ingest cached apipi JSON data into the ISCC traceability tables (Stage B)';

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $dir = config('services.apipi.cache_path') ?: storage_path('app/apipi/cache');
        $dir = rtrim($dir, '/');

        foreach (['suppliers.json', 'work_orders.json'] as $f) {
            if (!is_file("{$dir}/{$f}")) {
                $this->error("Missing cache file {$dir}/{$f}. Run traceability:api-fetch first.");
                return 1;
            }
        }

        $this->info('Loading cache...');
        $suppliersRaw = json_decode((string) file_get_contents("{$dir}/suppliers.json"), true) ?: [];
        $workOrders   = json_decode((string) file_get_contents("{$dir}/work_orders.json"), true) ?: [];

        // normalize suppliers
        $suppliers = [];
        $nameMap = [];
        foreach ($suppliersRaw as $s) {
            $id = $s['id'] ?? null;
            if ($id === null) { continue; }
            $suppliers[] = [
                'id'            => $id,
                'name'          => $s['name'] ?? null,
                'jenis'         => $s['jenis'] ?? null,
                'kota_name'     => $s['kota_name'] ?? null,
                'provinsi_name' => $s['provinsi_name'] ?? null,
                'gps'           => $s['gps'] ?? null,
                'alamat'        => $s['alamat'] ?? null,
                'active'        => $s['active'] ?? 1,
            ];
            $nameMap[$this->norm($s['name'] ?? '')] = $id;
        }
        $this->info('  suppliers: ' . count($suppliers));

        // wo_id -> supplier_id
        $woMap = [];
        foreach ($workOrders as $w) {
            if (isset($w['id'])) { $woMap[$w['id']] = $w['supplier_id'] ?? null; }
        }
        $this->info('  work orders: ' . count($workOrders) . ' (wo_id map: ' . count($woMap) . ')');

        // assemble collections from surat jalan details
        $ndjson = "{$dir}/surat_jalan_details.ndjson";
        $collections = [];
        $missingSupplier = 0;
        $lines = 0;

        if (is_file($ndjson)) {
            $fh = fopen($ndjson, 'r');
            while (($line = fgets($fh)) !== false) {
                $rec = json_decode($line, true);
                if (!$rec) { continue; }
                $detail = $rec['detail'] ?? [];
                $header = $detail['header'] ?? [];
                $sjKode = $rec['kode'] ?? ($header['kode'] ?? null);
                $sjDate = $rec['tgl'] ?? ($header['tgl'] ?? null);
                if (!$sjKode) { continue; }
                $poCode = $header['pickup']['kode'] ?? null;

                foreach (($detail['details'] ?? []) as $ln) {
                    $lines++;
                    if (($ln['status'] ?? 'done') !== 'done') { continue; }
                    $qty = (float) ($ln['qty_real'] ?? 0);
                    if ($qty <= 0) { continue; }

                    $sid = null;
                    if (!empty($ln['wo_id']) && isset($woMap[$ln['wo_id']])) {
                        $sid = $woMap[$ln['wo_id']];
                    }
                    if ($sid === null && !empty($ln['supplier_name'])) {
                        $sid = $nameMap[$this->norm($ln['supplier_name'])] ?? null;
                    }
                    if ($sid === null) { $missingSupplier++; continue; }

                    $collections[] = [
                        'supplier_id' => $sid,
                        'sj_kode'     => $sjKode,
                        'sj_date'     => $sjDate,
                        'qty_real'    => $qty,
                        'wo_code'     => $ln['wo_kode'] ?? null,
                        'po_code'     => $poCode,
                    ];
                }
            }
            fclose($fh);
        }

        $this->info('  collection lines: ' . $lines . ' | resolved collections: ' . count($collections) . ' | unresolved supplier: ' . $missingSupplier);

        if (!$collections) {
            $this->error('No collections to ingest (no surat_jalan_details.ndjson or all unresolved).');
            return 1;
        }

        $this->warn($this->option('purge') ? 'Ingesting with PURGE (existing EXTERNAL rows removed)...' : 'Ingesting (upsert)...');
        $counts = app(TraceabilityApiIngestionService::class)->ingest($suppliers, $collections, (bool) $this->option('purge'));

        $this->info('--- Ingest summary ---');
        $this->line('  groups (deliveries): ' . $counts['groups']);
        $this->line('  created: ' . $counts['created'] . ' | updated: ' . $counts['updated'] . ' | skipped: ' . $counts['skipped']);
        $this->line('  fragments: ' . $counts['fragments']);

        return 0;
    }

    protected function norm(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
