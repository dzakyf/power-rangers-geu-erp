<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\ApipiApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Re-assigns the randomized POO names of SUPPLIER-delivery fragments.
 *
 * Rules:
 *  - pool = active suppliers with jenis != 'SUPPLIER'
 *  - per assigned name per do_date month: strictly < 5,000 kg
 *  - cascade: same city -> same province -> national (all non-SUPPLIER)
 *  - deterministic; only names + supplier doc fields change (qty/date/codes/evidence untouched)
 */
class RenameTraceabilityPool extends Command
{
    protected $signature = 'traceability:rename-pool
        {--from= : Start month (YYYY-MM)}
        {--to= : End month (YYYY-MM)}
        {--dry-run : Report only, do not write or fetch}';

    protected $description = 'Re-assign non-SUPPLIER POO names with a strictly <5000kg/month cap';

    const CAP = 5000.0;
    const CAP_INT = 500000; // 5,000.00 kg in 0.01 kg units (strict <)

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $from = $this->option('from');
        $to   = $this->option('to');
        if (!$from || !$to) {
            $this->error('--from and --to are required (YYYY-MM).');
            return 1;
        }

        $dir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');
        $suppliers = json_decode((string) @file_get_contents($dir . '/suppliers.json'), true) ?: [];
        $details   = json_decode((string) (@file_get_contents($dir . '/supplier_details.json') ?: '[]'), true) ?: [];

        // build eligible pool (jenis != SUPPLIER)
        $byId = []; $cityPool = []; $provPool = []; $national = [];
        $nameById = []; $idByName = [];
        foreach ($suppliers as $s) {
            if ((string) ($s['active'] ?? '1') !== '1') { continue; }
            if (strtoupper(trim((string) ($s['jenis'] ?? ''))) === 'SUPPLIER') { continue; }
            $id = $s['id'] ?? null;
            if (!$id) { continue; }
            $byId[$id] = $s;
            $nm = $this->normName($s['name'] ?? '');
            $nameById[$id] = $nm;
            $idByName[$nm][] = $id;
            $national[] = $id;
            if (!empty($s['kota_name']))     { $cityPool[$s['kota_name']][] = $id; }
            if (!empty($s['provinsi_name'])) { $provPool[$s['provinsi_name']][] = $id; }
        }
        $this->info('Eligible pool (non-SUPPLIER, active): ' . count($byId));

        $months = $this->monthsBetween($from, $to);
        $dry = (bool) $this->option('dry-run');

        $assignments = [];   // fragment_id => supplier_id
        $usedNames = [];     // supplier_id => true
        $monthStats = [];

        foreach ($months as $m) {
            [$start, $end] = $this->monthRange($m);

            $rows = DB::select(
                "SELECT tep.id, tep.quantity_kg, ted.city, ted.province
                 FROM traceability_external_poo_fragments tep
                 JOIN traceability_external_deliveries ted ON ted.id = tep.traceability_external_delivery_id
                 WHERE ted.poo_type = 'SUPPLIER' AND tep.do_date BETWEEN ? AND ?
                 ORDER BY tep.id",
                [$start, $end]
            );
            if (!$rows) { continue; }

            $used = [];                 // supplier_id => kg in this month
            $cityCursor = [];           // city => ['pool'=>[], 'i'=>0]
            $provCursor = [];           // prov => ['pool'=>[], 'i'=>0]
            $natCursor  = null;         // ['pool'=>[], 'i'=>0]

            $stat = ['fragments' => 0, 'kg' => 0.0, 'city' => 0, 'prov' => 0, 'national' => 0, 'minfallback' => 0];
            $maxNameKg = 0.0;

            foreach ($rows as $r) {
                $q      = (float) $r->quantity_kg;
                $qi     = (int) round($q * 100);
                $city   = $r->city;
                $prov   = $r->province;

                $chosen = null; $level = null;

                // 1) city
                if ($city && isset($cityPool[$city])) {
                    if (!isset($cityCursor[$city])) {
                        $cityCursor[$city] = ['pool' => $this->shuffleSeeded($cityPool[$city], "ren|{$m}|city|{$city}"), 'i' => 0];
                    }
                    $chosen = $this->takeFrom($cityCursor[$city], $used, $nameById, $qi);
                    if ($chosen) { $level = 'city'; }
                }
                // 2) province
                if (!$chosen && $prov && isset($provPool[$prov])) {
                    if (!isset($provCursor[$prov])) {
                        $provCursor[$prov] = ['pool' => $this->shuffleSeeded($provPool[$prov], "ren|{$m}|prov|{$prov}"), 'i' => 0];
                    }
                    $chosen = $this->takeFrom($provCursor[$prov], $used, $nameById, $qi);
                    if ($chosen) { $level = 'prov'; }
                }
                // 3) national
                if (!$chosen) {
                    if ($natCursor === null) {
                        $natCursor = ['pool' => $this->shuffleSeeded($national, "ren|{$m}|nat"), 'i' => 0];
                    }
                    $chosen = $this->takeFrom($natCursor, $used, $nameById, $qi);
                    if ($chosen) { $level = 'national'; }
                }
                // 4) rare fallback: least-used eligible name
                if (!$chosen) {
                    $minName = null; $min = PHP_FLOAT_MAX;
                    foreach ($used as $nm => $kg) {
                        if ($kg < $min) { $min = $kg; $minName = $nm; }
                    }
                    $chosen = ($minName !== null && !empty($idByName[$minName])) ? $idByName[$minName][0] : ($national[0] ?? null);
                    $level = 'minfallback';
                }

                if (!$chosen) { continue; }

                $nmChosen = $nameById[$chosen] ?? ('#' . $chosen);
                $used[$nmChosen] = ($used[$nmChosen] ?? 0) + $qi;
                $maxNameKg = max($maxNameKg, $used[$nmChosen] / 100);
                $assignments[$r->id] = $chosen;
                $usedNames[$chosen] = true;
                $stat['fragments']++;
                $stat['kg'] += $q;
                $stat[$level] = ($stat[$level] ?? 0) + 1;
            }

            $monthStats[$m] = $stat + ['names' => count($used), 'max_name_kg' => $maxNameKg];
        }

        // report per month
        $this->line('');
        $this->line(str_pad('Month', 9) . str_pad('Frag', 7) . str_pad('Kg', 12) . str_pad('Names', 8) . str_pad('MaxNameKg', 12) . 'city/prov/nat/fallback');
        foreach ($monthStats as $m => $st) {
            $this->line(str_pad($m, 9) . str_pad((string) $st['fragments'], 7) . str_pad(number_format($st['kg'], 0, ',', '.'), 12) . str_pad((string) $st['names'], 8) . str_pad(number_format($st['max_name_kg'], 2, ',', '.'), 12)
                . ($st['city'] ?? 0) . '/' . ($st['prov'] ?? 0) . '/' . ($st['national'] ?? 0) . '/' . ($st['minfallback'] ?? 0));
        }
        $this->info('Assignments: ' . count($assignments) . ' | distinct names: ' . count($usedNames));

        if ($dry) {
            $missing = 0;
            foreach (array_keys($usedNames) as $sid) { if (!isset($details[$sid])) { $missing++; } }
            $this->warn('DRY-RUN: no writes. Names without cached detail: ' . $missing . ' (would be fetched on apply).');
            return 0;
        }

        // fetch missing supplier details for assigned names
        $client = app(ApipiApiClient::class);
        $fetched = 0; $failed = 0;
        foreach (array_keys($usedNames) as $sid) {
            if (isset($details[$sid])) { continue; }
            try {
                $d = $client->supplier($sid);
                if (is_array($d) && !empty($d)) { $details[$sid] = $d; $fetched++; }
                else { $failed++; }
            } catch (\Throwable $e) { $failed++; }
        }
        if ($fetched) {
            @file_put_contents($dir . '/supplier_details.json', json_encode($details, JSON_UNESCAPED_SLASHES));
        }
        $this->info("Supplier details fetched: {$fetched} | failed: {$failed}");

        // apply updates
        $updated = 0;
        DB::beginTransaction();
        try {
            $i = 0;
            foreach ($assignments as $fid => $sid) {
                $s = $byId[$sid] ?? null;
                if (!$s) { continue; }
                $d = $details[$sid] ?? [];
                $gps = (string) ($s['gps'] ?? '');
                $parts = array_map('trim', explode(',', $gps));
                $lat = (isset($parts[0]) && is_numeric($parts[0])) ? $parts[0] : null;
                $lng = (isset($parts[1]) && is_numeric($parts[1])) ? preg_replace('/[^0-9\.\-]/', '', $parts[1]) : null;

                DB::table('traceability_external_poo_fragments')->where('id', $fid)->update([
                    'poo_name'     => $s['name'] ?? null,
                    'poo_type'     => trim((string) ($s['jenis'] ?? '')) ?: null,
                    'site'         => $s['gudang_name'] ?? null,
                    'full_address' => $d['alamat'] ?? ($s['alamat'] ?? null),
                    'lat'          => $lat,
                    'lng'          => $lng,
                    'phone'        => $s['phone'] ?? null,
                    'account_no'   => $s['nomor_rek'] ?? null,
                    'account_name' => $s['nama_rek'] ?? null,
                    'price'        => $d['price'] ?? null,
                    'updated_at'   => now(),
                ]);
                $updated++;
                if ($updated % 500 === 0) { DB::commit(); DB::beginTransaction(); }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Update failed: ' . $e->getMessage());
            return 1;
        }

        $this->info("Fragments updated: {$updated}");
        return 0;
    }

    /** pick a name from a cursor pool with used+q < CAP; advances cursor when full */
    protected function takeFrom(array &$cursor, array $used, array $nameById, int $q): ?int
    {
        $n = count($cursor['pool']);
        while ($cursor['i'] < $n) {
            $id = $cursor['pool'][$cursor['i']];
            $nm = $nameById[$id] ?? ('#' . $id);
            if (($used[$nm] ?? 0) + $q < self::CAP_INT) {
                return $id;
            }
            $cursor['i']++;
        }
        return null;
    }

    protected function normName($name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $name)));
    }

    protected function shuffleSeeded(array $arr, string $seed): array
    {
        mt_srand(crc32($seed));
        // Fisher-Yates
        for ($i = count($arr) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $tmp = $arr[$i]; $arr[$i] = $arr[$j]; $arr[$j] = $tmp;
        }
        return $arr;
    }

    protected function monthsBetween(string $from, string $to): array
    {
        $out = [];
        $d = new \DateTime($from . '-01');
        $end = new \DateTime($to . '-01');
        while ($d <= $end) {
            $out[] = $d->format('Y-m');
            $d->modify('+1 month');
        }
        return $out;
    }

    protected function monthRange(string $ym): array
    {
        $d = new \DateTime($ym . '-01');
        $start = $d->format('Y-m-01');
        $d->modify('last day of this month');
        return [$start, $d->format('Y-m-d')];
    }
}
