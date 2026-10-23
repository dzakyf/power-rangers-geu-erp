<?php

namespace App\Services\Traceability;

use App\Services\ISCCTraceabilityService;
use Illuminate\Support\Facades\DB;

/**
 * Ingests API-sourced UCO collections into the ISCC traceability tables.
 *
 * Standalone service for the API flow. It intentionally does NOT modify the
 * existing ImportTraceabilityCsv command (kept as the CSV fallback).
 *
 * Rules:
 *  - one DELIVERY per (supplier x surat jalan)
 *  - only suppliers with jenis == 'SUPPLIER' are split
 *  - split = day-spread (<= 5 t/day, offsets centred on the SJ date)
 *            -> range fragments within [300,1000] kg
 *            -> fragments named after random real suppliers of the SAME CITY
 *  - the print-doc supplier fields (address/city/lat-lng/phone/account/price)
 *    are taken from the fragment's ASSIGNED supplier
 *  - evidence (ttd + first photo + plat) is stored per delivery (from the SJ)
 *  - upsert by api_reference = "{sj_kode}-{supplier_id}"
 */
class TraceabilityApiIngestionService
{
    const DAILY_CAP_KG   = 5000;
    const SUPPLIER_JENIS = 'SUPPLIER';

    /** @var ISCCTraceabilityService */
    protected $split;

    protected $supplierById = [];
    protected $supPoolByCity = [];
    protected $supPoolByProvince = [];
    protected $anyPoolByCity = [];

    public function __construct(ISCCTraceabilityService $split)
    {
        $this->split = $split;
    }

    /**
     * @param array $suppliers   records: id,name,jenis,kota_name,provinsi_name,gps,alamat,phone,nama_rek,nomor_rek,price
     * @param array $collections records: supplier_id,sj_kode,sj_date,qty_real,wo_code,po_code,plat,ttd,photo
     */
    public function ingest(array $suppliers, array $collections, bool $purge = false, bool $skipExisting = false): array
    {
        $this->indexSuppliers($suppliers);

        // group by (sj_kode, supplier_id)
        $groups = [];
        foreach ($collections as $c) {
            $sid = $c['supplier_id'] ?? null;
            if ($sid === null || $sid === '') { continue; }
            $qty = (float) ($c['qty_real'] ?? 0);
            if ($qty <= 0) { continue; }

            $key = $c['sj_kode'] . '|' . $sid;
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'supplier_id' => $sid,
                    'sj_kode'     => $c['sj_kode'],
                    'sj_date'     => $c['sj_date'] ?? null,
                    'qty'         => 0.0,
                    'wo_codes'    => [],
                    'po_code'     => $c['po_code'] ?? null,
                    'plat'        => $c['plat'] ?? null,
                    'driver'      => $c['driver'] ?? null,
                    'ttd'         => null,
                    'photo'       => null,
                ];
            }
            $groups[$key]['qty'] = round($groups[$key]['qty'] + $qty, 2);
            if (!empty($c['wo_code'])) { $groups[$key]['wo_codes'][$c['wo_code']] = true; }
            if (empty($groups[$key]['po_code']) && !empty($c['po_code'])) { $groups[$key]['po_code'] = $c['po_code']; }
            if (empty($groups[$key]['plat']) && !empty($c['plat'])) { $groups[$key]['plat'] = $c['plat']; }
            if (empty($groups[$key]['driver']) && !empty($c['driver'])) { $groups[$key]['driver'] = $c['driver']; }
            if (empty($groups[$key]['ttd']) && !empty($c['ttd'])) { $groups[$key]['ttd'] = $c['ttd']; }
            if (empty($groups[$key]['photo']) && !empty($c['photo'])) { $groups[$key]['photo'] = $c['photo']; }
        }

        $counts = ['collections' => count($collections), 'groups' => count($groups), 'created' => 0, 'updated' => 0, 'skipped' => 0, 'fragments' => 0];

        if ($purge) {
            DB::table('traceability_external_poo_fragments')->delete();
            DB::table('traceability_external_deliveries')->delete();
            $existing = [];
        } else {
            $existing = [];
            foreach (DB::table('traceability_external_deliveries')->select('id', 'api_reference', 'total_quantity_kg')->get() as $r) {
                $existing[$r->api_reference] = $r;
            }
        }

        foreach ($groups as $g) {
            $sup = $this->supplierById[$g['supplier_id']] ?? null;
            if (!$sup) { continue; }

            $total  = $g['qty'];
            $apiRef = $g['sj_kode'] . '-' . $g['supplier_id'];
            $isSup  = trim((string) ($sup['jenis'] ?? '')) === self::SUPPLIER_JENIS;
            $label  = $isSup ? 'SUPPLIER' : (trim((string) ($sup['jenis'] ?? '')) ?: null);
            $sjDate = $g['sj_date'] ? substr($g['sj_date'], 0, 10) : null;

            $payload = [
                'supplier_name'     => $sup['name'] ?? null,
                'poo_type'          => $label,
                'city'              => $sup['kota_name'] ?? null,
                'province'          => $sup['provinsi_name'] ?? null,
                'delivery_date'     => $sjDate,
                'total_quantity_kg' => $total,
                'api_reference'     => $apiRef,
                'status'            => 'LOCKED',
                'plat_no'           => $g['plat'],
                'driver_name'       => $g['driver'],
                'ttd'               => $g['ttd'],
                'photos'            => $g['photo'] ? json_encode([$g['photo']]) : null,
            ];

            if (isset($existing[$apiRef])) {
                if ($skipExisting) { $counts['skipped']++; continue; }
                $ex = $existing[$apiRef];
                if (abs((float) $ex->total_quantity_kg - $total) < 0.01) { $counts['skipped']++; continue; }
                DB::table('traceability_external_poo_fragments')->where('traceability_external_delivery_id', $ex->id)->delete();
                DB::table('traceability_external_deliveries')->where('id', $ex->id)->update($payload + ['updated_at' => now()]);
                $deliveryId = $ex->id;
                $counts['updated']++;
            } else {
                $deliveryId = DB::table('traceability_external_deliveries')->insertGetId($payload + ['created_at' => now(), 'updated_at' => now()]);
                $counts['created']++;
            }

            // (qty, date) pairs
            $pairs = [];
            if (!$isSup) {
                $pairs[] = [$total, $sjDate];
            } else {
                $chunks = $this->spreadDays($total);
                $n = count($chunks);
                foreach ($chunks as $i => $chunk) {
                    $offset = $i - intdiv($n, 2);
                    $date   = $sjDate ? date('Y-m-d', strtotime($sjDate . ' ' . $offset . ' days')) : null;
                    foreach ($this->split->splitQuantity($chunk, 'pabrik') as $q) {
                        $pairs[] = [$q, $date];
                    }
                }
            }

            $applyPicks = $isSup && count($pairs) > 1;
            $pickIds    = $applyPicks ? $this->pickNames($sup, count($pairs)) : [];
            $woCodeTxt  = implode(', ', array_keys($g['wo_codes']));

            $rows = [];
            foreach ($pairs as $i => $pair) {
                list($qty, $date) = $pair;
                $seq  = $i + 1;
                $pick = $pickIds[$i] ?? null;
                $psup = $pick ? ($this->supplierById[$pick] ?? null) : null;
                $src  = $psup ?: $sup;                       // assigned supplier (doc fields)
                $repeat = $pick && $this->nameRepeated($pickIds, $pick, $i);

                $rows[] = [
                    'traceability_external_delivery_id' => $deliveryId,
                    'fragment_sequence'                 => $seq,
                    'poo_name'                          => trim((string) ($src['name'] ?? '')) . ($repeat ? ' (' . $seq . ')' : ''),
                    'poo_type'                          => $src['jenis'] ? (trim((string) $src['jenis']) ?: $label) : $label,
                    'quantity_kg'                       => $qty,
                    'site'                              => $src['gudang_name'] ?? null,
                    'city'                              => $src['kota_name'] ?? null,
                    'province'                          => $src['provinsi_name'] ?? null,
                    'full_address'                      => $src['alamat'] ?? null,
                    'lat'                               => $this->parseLat($src['gps'] ?? ''),
                    'lng'                               => $this->parseLng($src['gps'] ?? ''),
                    'phone'                             => $src['phone'] ?? null,
                    'account_no'                        => $src['nomor_rek'] ?? null,
                    'account_name'                      => $src['nama_rek'] ?? null,
                    'price'                             => isset($src['price']) && is_numeric($src['price']) ? $src['price'] : null,
                    'oil_documentation'                 => $g['photo'] ?? null,
                    'po_code'                           => $g['po_code'],
                    'po_date'                           => $date,
                    'po_qty'                            => $total,
                    'po_status'                         => 'DONE',
                    'wo_code'                           => $woCodeTxt ?: null,
                    'wo_date'                           => $date,
                    'wo_status'                         => 'DONE',
                    'do_code'                           => $g['sj_kode'],
                    'do_date'                           => $date,
                    'do_status'                         => 'DONE',
                    'created_at'                        => now(),
                    'updated_at'                        => now(),
                ];
            }

            foreach (array_chunk($rows, 500) as $batch) {
                DB::table('traceability_external_poo_fragments')->insert($batch);
            }
            $counts['fragments'] += count($rows);
        }

        return $counts;
    }

    /* ------------------------------------------------------------- helpers */

    protected function indexSuppliers(array $suppliers): void
    {
        $this->supplierById = $this->supPoolByCity = $this->supPoolByProvince = $this->anyPoolByCity = [];

        foreach ($suppliers as $s) {
            $id = $s['id'] ?? null;
            if ($id === null) { continue; }
            $this->supplierById[$id] = $s;

            $city  = $s['kota_name'] ?? null;
            $prov  = $s['provinsi_name'] ?? null;
            $isSup = trim((string) ($s['jenis'] ?? '')) === self::SUPPLIER_JENIS;

            if ($city) {
                if ($isSup) { $this->supPoolByCity[$city][] = $id; }
                $this->anyPoolByCity[$city][] = $id;
            }
            if ($isSup && $prov) {
                $this->supPoolByProvince[$prov][] = $id;
            }
        }
    }

    protected function spreadDays(float $total): array
    {
        $unit = 100;
        $totalInt = (int) round($total * $unit);
        $cap = self::DAILY_CAP_KG * $unit;
        $n = max(1, (int) ceil($totalInt / $cap));

        $chunks = []; $remaining = $totalInt;
        for ($i = 0; $i < $n; $i++) {
            $rest = $n - $i - 1;
            if ($i === $n - 1) {
                $c = $remaining;
            } else {
                $low = max(1, $remaining - $rest * $cap);
                $high = min($cap, $remaining - $rest);
                if ($high < $low) { $high = $low; }
                $c = mt_rand($low, $high);
            }
            $chunks[] = $c / $unit;
            $remaining -= $c;
        }
        return $chunks;
    }

    protected function pickNames(array $sup, int $n): array
    {
        $self = $sup['id'];
        $city = $sup['kota_name'] ?? '';
        $prov = $sup['provinsi_name'] ?? '';

        $pools = [];
        if ($city && isset($this->supPoolByCity[$city])) {
            $pools[] = array_values(array_diff($this->supPoolByCity[$city], [$self]));
        }
        if ($prov && isset($this->supPoolByProvince[$prov])) {
            $pools[] = array_values(array_diff($this->supPoolByProvince[$prov], [$self]));
        }
        if ($city && isset($this->anyPoolByCity[$city])) {
            $pools[] = array_values(array_diff($this->anyPoolByCity[$city], [$self]));
        }

        mt_srand(crc32($self . '|' . $city));
        $flat = [];
        foreach ($pools as $pool) {
            if (!$pool) { continue; }
            shuffle($pool);
            $flat = array_merge($flat, $pool);
        }
        $flat = array_values(array_unique($flat));
        if (!$flat) { $flat = [$self]; }

        $out = [];
        for ($i = 0; $i < $n; $i++) { $out[] = $flat[$i % count($flat)]; }
        return $out;
    }

    protected function nameRepeated(array $picks, $pick, int $index): bool
    {
        for ($i = 0; $i < $index; $i++) { if ($picks[$i] === $pick) { return true; } }
        return false;
    }

    protected function parseLat(string $gps): ?string
    {
        $parts = array_map('trim', explode(',', $gps));
        return isset($parts[0]) && is_numeric($parts[0]) ? $parts[0] : null;
    }

    protected function parseLng(string $gps): ?string
    {
        $parts = array_map('trim', explode(',', $gps));
        $lng = isset($parts[1]) ? preg_replace('/[^0-9\.\-]/', '', $parts[1]) : '';
        return ($lng !== '' && is_numeric($lng)) ? $lng : null;
    }
}
