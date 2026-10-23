<?php

namespace App\Services\Traceability;

use App\Services\ExternalApi\ApipiApiClient;

/**
 * Stage A: pull raw data from the apipi API into a local JSON cache.
 *
 * Decoupled from ingestion so slow, throttled network fetching is resumable and
 * can be repeated without re-hitting the provider. No calls happen at build time.
 */
class TraceabilityApiFetcher
{
    /** @var ApipiApiClient */
    protected $client;
    /** @var string */
    protected $dir;

    public function __construct(ApipiApiClient $client)
    {
        $this->client = $client;
        $this->dir = config('services.apipi.cache_path') ?: storage_path('app/apipi/cache');
    }

    /* --------------------------------------------------------------- Files */

    public function cacheDir(): string
    {
        if (!is_dir($this->dir)) { @mkdir($this->dir, 0775, true); }
        return $this->dir;
    }

    protected function path(string $file): string
    {
        return $this->cacheDir() . '/' . $file;
    }

    protected function writeJson(string $file, $data): void
    {
        @file_put_contents($this->path($file), json_encode($data, JSON_UNESCAPED_SLASHES));
    }

    protected function readJson(string $file): array
    {
        $p = $this->path($file);
        if (!is_file($p)) { return []; }
        $d = json_decode((string) file_get_contents($p), true);
        return is_array($d) ? $d : [];
    }

    /* ------------------------------------------------------------- Fetches */

    public function fetchSuppliers(?callable $cb = null): int
    {
        $all = []; $cursor = null; $page = 0;
        do {
            $res = $this->client->suppliers(200, $cursor);
            foreach (($res['data'] ?? []) as $r) { $all[] = $r; }
            $cursor = $res['next_cursor'] ?? null;
            $hasNext = !empty($res['has_next']) && $cursor;
            $page++;
            if ($cb) { $cb('suppliers', $page, count($all), $res['total_records'] ?? null); }
        } while ($hasNext);

        $this->writeJson('suppliers.json', $all);
        return count($all);
    }

    public function fetchSuratJalanList(?string $from = null, ?string $to = null, ?callable $cb = null): int
    {
        $all = []; $page = 1;
        do {
            $res = $this->client->suratJalan($page, 100, 'done', $from, $to);
            foreach (($res['data'] ?? []) as $r) { $all[] = $r; }
            $pg = $res['pagination'] ?? [];
            $hasNext = !empty($pg['has_next']);
            if ($cb) { $cb('surat_jalan', $page, count($all), $pg['total_records'] ?? null); }
            $page++;
        } while ($hasNext);

        $this->writeJson('surat_jalan_list.json', $all);
        return count($all);
    }

    /**
     * Pull each surat jalan detail -> surat_jalan_details.ndjson (resumable).
     * When $withPhotos=true, each detail line gets a "_first_photo" URL.
     */
    public function fetchSuratJalanDetails(bool $withPhotos = false, ?callable $cb = null): int
    {
        $list = $this->readJson('surat_jalan_list.json');
        if (!$list) {
            throw new \RuntimeException('surat_jalan_list.json not found/empty. Run fetchSuratJalanList() first.');
        }

        $progress = $this->readJson('progress.json');
        $doneMap = array_flip($progress['sj_detail_done'] ?? []);

        $ndjson = $this->path('surat_jalan_details.ndjson');
        $fh = fopen($ndjson, 'a');
        if (!$fh) { throw new \RuntimeException('Cannot open ' . $ndjson); }

        $fetched = 0;
        foreach ($list as $sj) {
            $id = $sj['id'] ?? null;
            if ($id === null || isset($doneMap[$id])) { continue; }

            // per-SJ resilience: retry a few times, skip on persistent failure
            $detail = null;
            for ($try = 1; $try <= 3; $try++) {
                try {
                    $detail = $this->client->suratJalanDetail($id);
                    break;
                } catch (\Throwable $e) {
                    if ($try === 3) {
                        $progress['sj_detail_failed'] = array_values(array_unique(array_merge($progress['sj_detail_failed'] ?? [], [$id])));
                        $this->writeJson('progress.json', $progress);
                        $detail = null;
                        break;
                    }
                    sleep($try); // 1s, 2s backoff
                }
            }
            if ($detail === null) {
                if ($cb) { $cb('sj_details', $fetched, count($doneMap), count($list)); }
                continue;
            }

            if ($withPhotos && !empty($detail['details'])) {
                foreach ($detail['details'] as $k => $ln) {
                    $first = null;
                    if (!empty($ln['id']) && !empty($ln['photo_count'])) {
                        try {
                            $ph = $this->client->detailPhotos($ln['id']);
                            $first = $ph['photos'][0]['file_path'] ?? null;
                        } catch (\Throwable $e) { $first = null; }
                    }
                    $detail['details'][$k]['_first_photo'] = $first;
                }
            }

            fwrite($fh, json_encode([
                'id'     => $id,
                'kode'   => $sj['kode'] ?? ($detail['header']['kode'] ?? null),
                'tgl'    => $sj['tgl'] ?? ($detail['header']['tgl'] ?? null),
                'plat'   => $sj['fleet_plat'] ?? null,
                'driver' => $sj['driver_name'] ?? null,
                'kode_pickup' => $sj['kode_pickup'] ?? null,
                'detail' => $detail,
            ], JSON_UNESCAPED_SLASHES) . "\n");

            $doneMap[$id] = true;
            $fetched++;
            if ($fetched % 20 === 0) {
                fflush($fh);
                $progress['sj_detail_done'] = array_keys($doneMap);
                $this->writeJson('progress.json', $progress);
            }
            if ($cb) { $cb('sj_details', $fetched, count($doneMap), count($list)); }
        }

        fclose($fh);
        $progress['sj_detail_done'] = array_keys($doneMap);
        $this->writeJson('progress.json', $progress);
        return $fetched;
    }

    /**
     * Resolve wo_id -> work order row for every wo_id referenced in the cached
     * surat jalan details -> work_orders_lazy.json (map id => row, resumable).
     */
    public function fetchWorkOrdersLazy(?callable $cb = null): int
    {
        $woIds = [];
        $ndjson = $this->path('surat_jalan_details.ndjson');
        if (is_file($ndjson)) {
            $fh = fopen($ndjson, 'r');
            while (($line = fgets($fh)) !== false) {
                $rec = json_decode($line, true);
                foreach (($rec['detail']['details'] ?? []) as $ln) {
                    if (!empty($ln['wo_id'])) { $woIds[$ln['wo_id']] = true; }
                }
            }
            fclose($fh);
        }

        $map = $this->readJson('work_orders_lazy.json');
        $fetched = 0; $i = 0;
        foreach (array_keys($woIds) as $wid) {
            $i++;
            if (isset($map[$wid])) { continue; }
            try {
                $map[$wid] = $this->client->workOrder($wid);
            } catch (\Throwable $e) {
                $map[$wid] = null;
            }
            $fetched++;
            if ($fetched % 25 === 0) { $this->writeJson('work_orders_lazy.json', $map); }
            if ($cb) { $cb('work_orders', $i, count($map), count($woIds)); }
        }
        $this->writeJson('work_orders_lazy.json', $map);
        return $fetched;
    }

    /**
     * Fetch supplier details for: the SUPPLIER-jenis pool (all) + original
     * suppliers referenced by the work orders -> supplier_details.json.
     */
    public function fetchSupplierDetails(?callable $cb = null): int
    {
        $ids = [];

        foreach ($this->readJson('suppliers.json') as $s) {
            if (trim((string) ($s['jenis'] ?? '')) === 'SUPPLIER' && !empty($s['id'])) {
                $ids[$s['id']] = true;
            }
        }
        foreach ($this->readJson('work_orders_lazy.json') as $w) {
            if (!empty($w['supplier_id'])) { $ids[$w['supplier_id']] = true; }
        }

        $map = $this->readJson('supplier_details.json');
        $fetched = 0; $i = 0;
        foreach (array_keys($ids) as $sid) {
            $i++;
            if (isset($map[$sid])) { continue; }
            try {
                $map[$sid] = $this->client->supplier($sid);
            } catch (\Throwable $e) {
                $map[$sid] = null;
            }
            $fetched++;
            if ($fetched % 25 === 0) { $this->writeJson('supplier_details.json', $map); }
            if ($cb) { $cb('supplier_details', $i, count($map), count($ids)); }
        }
        $this->writeJson('supplier_details.json', $map);
        return $fetched;
    }
}
