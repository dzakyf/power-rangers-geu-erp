<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairTraceabilityData extends Command
{
    protected $signature = 'traceability:repair
        {--step=all : all|site|location|supplier-type|photos}
        {--chunk=300 : Update chunk size}
        {--dry-run : Report only, do not write}';

    protected $description = 'Backfill site/location, remove SUPPLIER POO type, and restore cached surat jalan photos';

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $step = strtolower((string) $this->option('step'));
        $dry = (bool) $this->option('dry-run');

        if (in_array($step, ['all', 'site'], true)) {
            $this->stepSite($dry);
        }
        if (in_array($step, ['all', 'location'], true)) {
            $this->stepLocation($dry);
        }
        if (in_array($step, ['all', 'supplier-type'], true)) {
            $this->stepSupplierType($dry);
        }
        if (in_array($step, ['all', 'photos'], true)) {
            $this->stepPhotos($dry);
        }

        return 0;
    }

    protected function stepSite(bool $dry): void
    {
        $this->info('== step: site ==');
        $dir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');
        $suppliers = json_decode((string) @file_get_contents($dir . '/suppliers.json'), true) ?: [];
        $gudangByName = [];
        foreach ($suppliers as $supplier) {
            $name = $this->norm($supplier['name'] ?? '');
            $gudang = trim((string) ($supplier['gudang_name'] ?? ''));
            if ($name !== '' && $gudang !== '') {
                $gudangByName[$name] = $gudang;
            }
        }

        $deliverySupplier = DB::table('traceability_external_deliveries')
            ->pluck('supplier_name', 'id');

        $rows = DB::table('traceability_external_poo_fragments')
            ->where(function ($q) {
                $q->whereNull('site')->orWhere('site', '');
            })
            ->select('id', 'poo_name', 'traceability_external_delivery_id')
            ->get();

        $updates = [];
        foreach ($rows as $row) {
            $name = $this->basePooName($row->poo_name);
            $site = $gudangByName[$name] ?? null;
            if ($site === null) {
                $deliveryName = $this->norm($deliverySupplier[$row->traceability_external_delivery_id] ?? '');
                $site = $gudangByName[$deliveryName] ?? null;
            }
            if ($site !== null) {
                $updates[] = ['id' => $row->id, 'site' => $site];
            }
        }

        $this->line('  fragments without site: ' . count($rows));
        $this->line('  site updates resolved: ' . count($updates));
        if ($dry) { return; }

        foreach (array_chunk($updates, max(1, (int) $this->option('chunk'))) as $chunk) {
            $this->updateRows('traceability_external_poo_fragments', 'id', $chunk);
        }

        DB::statement(
            "UPDATE traceability_external_deliveries d
             SET d.site = (
                SELECT f.site FROM traceability_external_poo_fragments f
                WHERE f.traceability_external_delivery_id = d.id
                  AND f.site IS NOT NULL AND f.site <> ''
                ORDER BY f.id LIMIT 1
             )
             WHERE d.site IS NULL OR d.site = ''"
        );

        $this->line('  delivery sites synchronized');
    }

    protected function stepLocation(bool $dry): void
    {
        $this->info('== step: location ==');
        $count = DB::table('traceability_external_poo_fragments')
            ->where(function ($q) {
                $q->whereNull('city')->orWhere('city', '')
                  ->orWhereNull('province')->orWhere('province', '');
            })->count();
        $this->line('  fragments missing city/province: ' . $count);

        if ($dry) { return; }

        $updated = DB::update(
            "UPDATE traceability_external_poo_fragments f
             JOIN traceability_external_deliveries d ON d.id = f.traceability_external_delivery_id
             SET f.city = IF(f.city IS NULL OR f.city = '', d.city, f.city),
                 f.province = IF(f.province IS NULL OR f.province = '', d.province, f.province)
             WHERE (f.city IS NULL OR f.city = '' OR f.province IS NULL OR f.province = '')"
        );
        $this->line('  location rows updated: ' . $updated);
    }

    protected function stepSupplierType(bool $dry): void
    {
        $this->info('== step: supplier-type ==');
        $targets = DB::table('traceability_external_poo_fragments as f')
            ->join('traceability_external_deliveries as d', 'd.id', '=', 'f.traceability_external_delivery_id')
            ->where('f.poo_type', 'SUPPLIER')
            ->select('f.id', 'f.city', 'f.province', 'd.city as delivery_city', 'd.province as delivery_province')
            ->get();

        $this->line('  SUPPLIER fragments: ' . count($targets));

        $candidates = DB::table('traceability_external_poo_fragments')
            ->where('poo_type', '<>', 'SUPPLIER')
            ->whereNotNull('poo_name')->where('poo_name', '<>', '')
            ->select('poo_name', 'poo_type', 'city', 'province', 'full_address', 'lat', 'lng', 'phone', 'account_no', 'account_name')
            ->distinct()->get();

        $byCity = $byProvince = [];
        $national = [];
        foreach ($candidates as $candidate) {
            $row = (array) $candidate;
            $national[] = $row;
            if (!empty($candidate->city)) { $byCity[$candidate->city][] = $row; }
            if (!empty($candidate->province)) { $byProvince[$candidate->province][] = $row; }
        }

        $updates = [];
        foreach ($targets as $target) {
            $city = $target->city ?: $target->delivery_city;
            $province = $target->province ?: $target->delivery_province;

            $pool = [];
            if ($city && !empty($byCity[$city])) { $pool = $byCity[$city]; }
            elseif ($province && !empty($byProvince[$province])) { $pool = $byProvince[$province]; }
            else { $pool = $national; }

            if (!$pool) { continue; }

            mt_srand(crc32((string) $target->id));
            $pick = $pool[mt_rand(0, count($pool) - 1)];

            $updates[] = [
                'id' => $target->id,
                'poo_name' => $pick['poo_name'],
                'poo_type' => $pick['poo_type'],
                'city' => $pick['city'],
                'province' => $pick['province'],
                'full_address' => $pick['full_address'],
                'lat' => $pick['lat'],
                'lng' => $pick['lng'],
                'phone' => $pick['phone'],
                'account_no' => $pick['account_no'],
                'account_name' => $pick['account_name'],
            ];
        }

        $this->line('  remap updates prepared: ' . count($updates));
        if ($dry) { return; }

        foreach (array_chunk($updates, max(1, (int) $this->option('chunk'))) as $chunk) {
            $this->updateRows('traceability_external_poo_fragments', 'id', $chunk);
        }

        $remaining = DB::table('traceability_external_poo_fragments')->where('poo_type', 'SUPPLIER')->count();
        $this->line('  remaining SUPPLIER fragments: ' . $remaining);
    }

    protected function stepPhotos(bool $dry): void
    {
        $this->info('== step: photos ==');
        $dir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');
        $ndjson = $dir . '/surat_jalan_details.ndjson';
        if (!is_file($ndjson)) {
            $this->error('  cache ndjson missing');
            return;
        }

        $records = [];
        $fh = fopen($ndjson, 'r');
        while (($line = fgets($fh)) !== false) {
            $rec = json_decode($line, true);
            if (is_array($rec) && isset($rec['id'])) { $records[$rec['id']] = $rec; }
        }
        fclose($fh);

        $bySj = [];
        $bySjSupplier = [];
        foreach ($records as $rec) {
            $detail = is_array($rec['detail'] ?? null) ? $rec['detail'] : [];
            $kode = $rec['kode'] ?? ($detail['header']['kode'] ?? null);
            if (!$kode) { continue; }
            foreach (($detail['details'] ?? []) as $ln) {
                $photo = trim((string) ($ln['foto'] ?? ''));
                if ($photo === '') { continue; }
                if (!isset($bySj[$kode])) { $bySj[$kode] = $photo; }
                $name = $this->norm($ln['supplier_name'] ?? '');
                if ($name !== '') { $bySjSupplier[$kode][$name] = $photo; }
            }
        }

        $deliveries = DB::table('traceability_external_deliveries')
            ->select('id', 'api_reference', 'supplier_name')
            ->get();

        $deliveryUpdates = [];
        $photoByDelivery = [];
        foreach ($deliveries as $delivery) {
            $ref = (string) $delivery->api_reference;
            $pos = strrpos($ref, '-');
            $kode = $pos === false ? $ref : substr($ref, 0, $pos);
            $name = $this->norm($delivery->supplier_name);
            $photo = $bySjSupplier[$kode][$name] ?? ($bySj[$kode] ?? null);
            if (!$photo) { continue; }

            $deliveryUpdates[] = [
                'id' => $delivery->id,
                'photos' => json_encode([$photo], JSON_UNESCAPED_SLASHES),
            ];
            $photoByDelivery[$delivery->id] = $photo;
        }

        $this->line('  deliveries with cached photo: ' . count($deliveryUpdates));
        if ($dry) { return; }

        foreach (array_chunk($deliveryUpdates, max(1, (int) $this->option('chunk'))) as $chunk) {
            $this->updateRows('traceability_external_deliveries', 'id', $chunk);
        }

        $fragmentUpdates = [];
        foreach (array_chunk(array_keys($photoByDelivery), 500) as $ids) {
            $fragments = DB::table('traceability_external_poo_fragments')
                ->whereIn('traceability_external_delivery_id', $ids)
                ->select('id', 'traceability_external_delivery_id')
                ->get();
            foreach ($fragments as $fragment) {
                $photo = $photoByDelivery[$fragment->traceability_external_delivery_id] ?? null;
                if ($photo) {
                    $fragmentUpdates[] = ['id' => $fragment->id, 'oil_documentation' => $photo];
                }
            }
        }

        foreach (array_chunk($fragmentUpdates, max(1, (int) $this->option('chunk'))) as $chunk) {
            $this->updateRows('traceability_external_poo_fragments', 'id', $chunk);
        }

        $this->line('  fragments with restored oil documentation: ' . count($fragmentUpdates));
    }

    protected function updateRows(string $table, string $idColumn, array $rows): void
    {
        if (!$rows) { return; }

        $fields = array_keys($rows[0]);
        $fields = array_values(array_diff($fields, [$idColumn]));

        $cases = [];
        $bindings = [];
        foreach ($fields as $field) {
            $cases[$field] = [];
            foreach ($rows as $row) {
                $cases[$field][] = 'WHEN ? THEN ?';
                $bindings[] = $row[$idColumn];
                $bindings[] = $row[$field];
            }
        }

        $ids = array_column($rows, $idColumn);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $bindings = array_merge($bindings, $ids);

        $set = [];
        foreach ($fields as $field) {
            $set[] = "{$field} = CASE {$idColumn} " . implode(' ', $cases[$field]) . ' END';
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$idColumn} IN ({$placeholders})";
        DB::update($sql, $bindings);
    }

    protected function basePooName(?string $name): string
    {
        return $this->norm(preg_replace('/\s*\(\d+\)$/', '', (string) $name));
    }

    protected function norm(?string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $value)));
    }
}
