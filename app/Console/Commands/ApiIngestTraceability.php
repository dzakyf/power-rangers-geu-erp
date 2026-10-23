<?php

namespace App\Console\Commands;

use App\Services\Traceability\TraceabilityApiIngestionService;
use Illuminate\Console\Command;

class ApiIngestTraceability extends Command
{
    protected $signature = 'traceability:api-ingest
        {--purge : Delete existing EXTERNAL traceability rows before ingesting (full swap)}
        {--skip-existing : Skip deliveries whose api_reference already exists (incremental)}';

    protected $description = 'Ingest cached apipi JSON data into the ISCC traceability tables (Stage B)';

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

        // suppliers: list record + detail overlay (address/price/kategori)
        $suppliers = [];
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
            $nameMap[$this->norm($s['name'] ?? '')] = $id;
        }
        $this->info('  suppliers: ' . count($suppliers) . ' | with detail: ' . count($details));

        $woMap = [];
        foreach ($woLazy as $wo) {
            if (is_array($wo) && isset($wo['id'])) { $woMap[$wo['id']] = $wo['supplier_id'] ?? null; }
        }
        $this->info('  work orders (lazy): ' . count($woMap));

        // assemble collections
        $ndjson = "{$dir}/surat_jalan_details.ndjson";
        $collections = [];
        $lines = 0; $missing = 0;

        if (is_file($ndjson)) {
            // dedupe by SJ id (last occurrence wins) to avoid double counting
            $records = [];
            $fh = fopen($ndjson, 'r');
            while (($line = fgets($fh)) !== false) {
                $rec = json_decode($line, true);
                if (!$rec || !isset($rec['id'])) { continue; }
                $records[$rec['id']] = $rec;
            }
            fclose($fh);

            foreach ($records as $rec) {
                $header = $rec['detail']['header'] ?? [];
                $sjKode = $rec['kode'] ?? ($header['kode'] ?? null);
                $sjDate = $rec['tgl'] ?? ($header['tgl'] ?? null);
                $plat   = $rec['plat'] ?? null;
                $driver = $rec['driver'] ?? null;
                $poCode = $rec['kode_pickup'] ?? ($header['pickup']['kode'] ?? null);
                if (!$sjKode) { continue; }

                foreach (($rec['detail']['details'] ?? []) as $ln) {
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
                    if ($sid === null) { $missing++; continue; }

                    $collections[] = [
                        'supplier_id' => $sid,
                        'sj_kode'     => $sjKode,
                        'sj_date'     => $sjDate,
                        'qty_real'    => $qty,
                        'wo_code'     => $ln['wo_kode'] ?? null,
                        'po_code'     => $poCode,
                        'plat'        => $plat,
                        'driver'      => $driver,
                        'ttd'         => $ln['ttd'] ?? null,
                        'photo'       => $ln['_first_photo'] ?? null,
                    ];
                }
            }
        }

        $this->info('  lines: ' . $lines . ' | collections: ' . count($collections) . ' | unresolved supplier: ' . $missing);
        if (!$collections) { $this->error('No collections to ingest.'); return 1; }

        $this->warn($this->option('purge') ? 'Ingesting with PURGE...' : ($this->option('skip-existing') ? 'Ingesting skip-existing...' : 'Ingesting upsert...'));
        $counts = app(TraceabilityApiIngestionService::class)
            ->ingest($suppliers, $collections, (bool) $this->option('purge'), (bool) $this->option('skip-existing'));

        $this->info('--- Ingest summary ---');
        $this->line('  groups: ' . $counts['groups'] . ' | created: ' . $counts['created'] . ' | updated: ' . $counts['updated'] . ' | skipped: ' . $counts['skipped']);
        $this->line('  fragments: ' . $counts['fragments']);
        return 0;
    }

    protected function norm(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
