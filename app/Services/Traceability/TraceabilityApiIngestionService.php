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
    protected $sjPoolByIdentity = [];
    protected $sjPoolByCity = [];
    protected $sjPoolByProvince = [];
    protected $sjPoolNational = [];

    public function __construct(ISCCTraceabilityService $split)
    {
        $this->split = $split;
    }

    /**
     * @param array $suppliers   records: id,name,jenis,kota_name,provinsi_name,gps,alamat,phone,nama_rek,nomor_rek,price
     * @param array $collections records: supplier_id,supplier_name,supplier_alamat,supplier_phone,supplier_gps,supplier_jenis,sj_kode,sj_date,qty_real_kg,wo_code,po_code,plat,ttd,photo
     */
    public function ingest(array $suppliers, array $collections, bool $purge = false, bool $skipExisting = false, bool $bulk = false): array
    {
        $this->indexSuppliers($suppliers, $collections);

        // group by (sj_kode, supplier_id)
        $groups = [];
        foreach ($collections as $c) {
            $sid = $c['supplier_id'] ?? null;
            if ($sid === null || $sid === '') { continue; }
            $qty = (float) ($c['qty_real_kg'] ?? 0);
            if ($qty <= 0) { continue; }

            $key = $c['sj_kode'] . '|' . $sid;
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'supplier_id'     => $sid,
                    'supplier_name'   => $c['supplier_name'] ?? null,
                    'supplier_alamat' => $c['supplier_alamat'] ?? null,
                    'supplier_phone'  => $c['supplier_phone'] ?? null,
                    'supplier_gps'    => $c['supplier_gps'] ?? null,
                    'supplier_jenis'  => $c['supplier_jenis'] ?? null,
                    'sj_kode'         => $c['sj_kode'],
                    'sj_date'         => $c['sj_date'] ?? null,
                    'qty'             => 0.0,
                    'wo_codes'        => [],
                    'po_code'         => $c['po_code'] ?? null,
                    'plat'            => $c['plat'] ?? null,
                    'driver'          => $c['driver'] ?? null,
                    'gudang'          => $c['gudang'] ?? null,
                    'pickup_id'       => $c['pickup_id'] ?? null,
                    'unit'            => 'Kg',
                    'ttd'             => null,
                    'photo'           => null,
                ];
            }
            $groups[$key]['qty'] = round($groups[$key]['qty'] + $qty, 2);
            if (!empty($c['wo_code'])) { $groups[$key]['wo_codes'][$c['wo_code']] = true; }
            if (empty($groups[$key]['po_code']) && !empty($c['po_code'])) { $groups[$key]['po_code'] = $c['po_code']; }
            if (empty($groups[$key]['plat']) && !empty($c['plat'])) { $groups[$key]['plat'] = $c['plat']; }
            if (empty($groups[$key]['driver']) && !empty($c['driver'])) { $groups[$key]['driver'] = $c['driver']; }
            if (empty($groups[$key]['gudang']) && !empty($c['gudang'])) { $groups[$key]['gudang'] = $c['gudang']; }
            if (empty($groups[$key]['pickup_id']) && !empty($c['pickup_id'])) { $groups[$key]['pickup_id'] = $c['pickup_id']; }
            if (empty($groups[$key]['supplier_name']) && !empty($c['supplier_name'])) { $groups[$key]['supplier_name'] = $c['supplier_name']; }
            if (empty($groups[$key]['supplier_alamat']) && !empty($c['supplier_alamat'])) { $groups[$key]['supplier_alamat'] = $c['supplier_alamat']; }
            if (empty($groups[$key]['supplier_phone']) && !empty($c['supplier_phone'])) { $groups[$key]['supplier_phone'] = $c['supplier_phone']; }
            if (empty($groups[$key]['supplier_gps']) && !empty($c['supplier_gps'])) { $groups[$key]['supplier_gps'] = $c['supplier_gps']; }
            if (empty($groups[$key]['supplier_jenis']) && !empty($c['supplier_jenis'])) { $groups[$key]['supplier_jenis'] = $c['supplier_jenis']; }
            if (empty($groups[$key]['ttd']) && !empty($c['ttd'])) { $groups[$key]['ttd'] = $c['ttd']; }
            if (empty($groups[$key]['photo']) && !empty($c['photo'])) { $groups[$key]['photo'] = $c['photo']; }
        }

        $counts = ['collections' => count($collections), 'groups' => count($groups), 'created' => 0, 'updated' => 0, 'skipped' => 0, 'fragments' => 0];
        $bulkDeliveries = [];
        $bulkFragments = [];

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
            $sup = $this->sjPoolByIdentity[$g['supplier_id']] ?? $this->supplierById[$g['supplier_id']] ?? null;
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
                'site'              => !empty($g['gudang']) ? $g['gudang'] : ($sup['gudang_name'] ?? null),
                'pickup_id'         => $g['pickup_id'] ?? null,
                'total_quantity_kg' => $total,
                'api_reference'     => $apiRef,
                'status'            => 'LOCKED',
                'plat_no'           => $g['plat'],
                'driver_name'       => $g['driver'],
                'ttd'               => $g['ttd'],
                'photos'            => $g['photo'] ? json_encode([$g['photo']]) : null,
            ];

            if ($bulk) {
                $deliveryId = $apiRef;
                $bulkDeliveries[$apiRef] = $payload + ['created_at' => now(), 'updated_at' => now()];
                $counts['created']++;
            } elseif (isset($existing[$apiRef])) {
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

            $applyPicks = $isSup;
            $pickIds    = $applyPicks ? $this->pickNames($sup, count($pairs)) : [];
            $woCodeTxt  = implode(', ', array_keys($g['wo_codes']));

            $rows = [];
            foreach ($pairs as $i => $pair) {
                list($qty, $date) = $pair;
                $seq  = $i + 1;
                $pick = $pickIds[$i] ?? null;
                $psup = $pick ? ($this->sjPoolByIdentity[$pick] ?? null) : null;
                $src  = $psup ?: $sup;                       // assigned SJ supplier (doc fields)
                $repeat = $pick && $this->nameRepeated($pickIds, $pick, $i);

                $rows[] = [
                    'traceability_external_delivery_id' => $deliveryId,
                    'fragment_sequence'                 => $seq,
                    'poo_name'                          => trim((string) ($src['name'] ?? '')) . ($repeat ? ' (' . $seq . ')' : ''),
                    'poo_type'                          => $src['jenis'] ? (trim((string) $src['jenis']) ?: $label) : $label,
                    'quantity_kg'                       => $qty,
                    'unit'                              => 'Kg',
                    'site'                              => !empty($g['gudang']) ? $g['gudang'] : ($src['gudang_name'] ?? ($sup['gudang_name'] ?? null)),
                    'city'                              => !empty($src['kota_name']) ? $src['kota_name'] : ($sup['kota_name'] ?? null),
                    'province'                          => !empty($src['provinsi_name']) ? $src['provinsi_name'] : ($sup['provinsi_name'] ?? null),
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

            if ($bulk) {
                $bulkFragments[$apiRef] = $rows;
            } else {
                foreach (array_chunk($rows, 500) as $batch) {
                    DB::table('traceability_external_poo_fragments')->insert($batch);
                }
            }
            $counts['fragments'] += count($rows);
        }

        if ($bulk) {
            $this->flushBulk($bulkDeliveries, $bulkFragments);
        }

        return $counts;
    }

    protected function flushBulk(array $deliveries, array $fragments): void
    {
        foreach (array_chunk($deliveries, 200, true) as $chunk) {
            DB::table('traceability_external_deliveries')->insert(array_values($chunk));

            $refs = array_keys($chunk);
            $ids = DB::table('traceability_external_deliveries')
                ->whereIn('api_reference', $refs)
                ->pluck('id', 'api_reference');

            $fragmentRows = [];
            foreach ($refs as $ref) {
                $deliveryId = $ids[$ref] ?? null;
                if (!$deliveryId) { continue; }
                foreach (($fragments[$ref] ?? []) as $row) {
                    $row['traceability_external_delivery_id'] = $deliveryId;
                    $fragmentRows[] = $row;
                }
            }

            foreach (array_chunk($fragmentRows, 500) as $batch) {
                DB::table('traceability_external_poo_fragments')->insert($batch);
            }
        }
    }

    /* ------------------------------------------------------------- helpers */

    protected function indexSuppliers(array $suppliers, array $collections = []): void
    {
        $this->supplierById = [];
        $this->sjPoolByIdentity = $this->sjPoolByCity = $this->sjPoolByProvince = $this->sjPoolNational = [];

        foreach ($suppliers as $s) {
            $id = $s['id'] ?? null;
            if ($id === null) { continue; }
            $this->supplierById[$id] = $s;
            $this->supplierById[(string) $id] = $s;
        }

        // The randomized POO pool is limited to suppliers that actually appear in SJ.
        foreach ($collections as $c) {
            $id = $c['supplier_id'] ?? null;
            if ($id === null || $id === '') { continue; }

            $identity = (string) $id;
            if (isset($this->sjPoolByIdentity[$identity])) { continue; }

            $s = $this->supplierById[$id] ?? $this->supplierById[$identity] ?? [];
            $record = [
                'id'            => $id,
                'name'          => $c['supplier_name'] ?? ($s['name'] ?? null),
                'jenis'         => $c['supplier_jenis'] ?? ($s['jenis'] ?? null),
                'kota_name'     => $s['kota_name'] ?? ($c['supplier_kota_name'] ?? null),
                'provinsi_name' => $s['provinsi_name'] ?? ($c['supplier_provinsi_name'] ?? null),
                'gps'           => $c['supplier_gps'] ?? ($s['gps'] ?? null),
                'alamat'        => $c['supplier_alamat'] ?? ($s['alamat'] ?? null),
                'phone'         => $c['supplier_phone'] ?? ($s['phone'] ?? null),
                'nama_rek'      => $s['nama_rek'] ?? null,
                'nomor_rek'     => $s['nomor_rek'] ?? null,
                'price'         => $s['price'] ?? null,
                'gudang_name'   => $s['gudang_name'] ?? null,
                'active'        => $s['active'] ?? 1,
            ];

            $this->sjPoolByIdentity[$identity] = $record;

            if (trim((string) ($record['jenis'] ?? '')) === self::SUPPLIER_JENIS) {
                continue;
            }

            $this->sjPoolNational[] = $identity;
            if (!empty($record['kota_name'])) {
                $this->sjPoolByCity[$record['kota_name']][] = $identity;
            }
            if (!empty($record['provinsi_name'])) {
                $this->sjPoolByProvince[$record['provinsi_name']][] = $identity;
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
        $self = (string) $sup['id'];
        $city = $sup['kota_name'] ?? '';
        $prov = $sup['provinsi_name'] ?? '';

        $pools = [];
        if ($city && isset($this->sjPoolByCity[$city])) {
            $pools[] = array_values(array_diff($this->sjPoolByCity[$city], [$self]));
        }
        if ($prov && isset($this->sjPoolByProvince[$prov])) {
            $pools[] = array_values(array_diff($this->sjPoolByProvince[$prov], [$self]));
        }
        $pools[] = array_values(array_diff($this->sjPoolNational, [$self]));

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
