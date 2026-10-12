<?php

namespace App\Services\Traceability;

use App\Services\ExternalApi\ApipiApiClient;
use Illuminate\Support\Facades\File;

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

    protected function progress(): array
    {
        $f = $this->path('progress.json');
        if (!is_file($f)) { return []; }
        $d = json_decode((string) @file_get_contents($f), true);
        return is_array($d) ? $d : [];
    }

    protected function saveProgress(array $data): void
    {
        @file_put_contents($this->path('progress.json'), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function writeJson(string $file, $data): void
    {
        @file_put_contents($this->path($file), json_encode($data, JSON_UNESCAPED_SLASHES));
    }

    /* ------------------------------------------------------------- Fetches */

    /**
     * Pull all suppliers (cursor paginated) -> suppliers.json
     * @return int number of suppliers written
     */
    public function fetchSuppliers(?callable $onProgress = null): int
    {
        $all = [];
        $cursor = null;
        $page = 0;
        do {
            $res = $this->client->suppliers(200, $cursor);
            $rows = $res['data'] ?? [];
            foreach ($rows as $r) { $all[] = $r; }
            $cursor = $res['next_cursor'] ?? null;
            $hasNext = !empty($res['has_next']) && $cursor;
            $page++;
            if ($onProgress) { $onProgress('suppliers', $page, count($all), $res['total_records'] ?? null); }
        } while ($hasNext);

        $this->writeJson('suppliers.json', $all);
        return count($all);
    }

    /**
     * Pull all work orders (page paginated) -> work_orders.json
     * @return int
     */
    public function fetchWorkOrders(?callable $onProgress = null): int
    {
        $all = [];
        $page = 1;
        do {
            $res = $this->client->workOrders($page, 100);
            foreach (($res['data'] ?? []) as $r) { $all[] = $r; }
            $pg = $res['pagination'] ?? [];
            $hasNext = !empty($pg['has_next']);
            if ($onProgress) { $onProgress('work_orders', $page, count($all), $pg['total_records'] ?? null); }
            $page++;
        } while ($hasNext);

        $this->writeJson('work_orders.json', $all);
        return count($all);
    }

    /**
     * Pull surat jalan list (page paginated) -> surat_jalan_list.json
     * @return int
     */
    public function fetchSuratJalanList(?string $from = null, ?string $to = null, ?callable $onProgress = null): int
    {
        $all = [];
        $page = 1;
        do {
            $res = $this->client->suratJalan($page, 100, 'done', $from, $to);
            foreach (($res['data'] ?? []) as $r) { $all[] = $r; }
            $pg = $res['pagination'] ?? [];
            $hasNext = !empty($pg['has_next']);
            if ($onProgress) { $onProgress('surat_jalan', $page, count($all), $pg['total_records'] ?? null); }
            $page++;
        } while ($hasNext);

        $this->writeJson('surat_jalan_list.json', $all);
        return count($all);
    }

    /**
     * Pull each surat jalan detail -> surat_jalan_details.ndjson (resumable).
     * @return int number fetched in this run
     */
    public function fetchSuratJalanDetails(?callable $onProgress = null): int
    {
        $listFile = $this->path('surat_jalan_list.json');
        if (!is_file($listFile)) {
            throw new \RuntimeException('surat_jalan_list.json not found. Run fetchSuratJalanList() first.');
        }

        $list = json_decode((string) file_get_contents($listFile), true) ?: [];
        $progress = $this->progress();
        $done = $progress['sj_detail_done'] ?? [];
        $doneMap = array_flip($done);

        $ndjson = $this->path('surat_jalan_details.ndjson');
        $fh = fopen($ndjson, 'a');
        if (!$fh) { throw new \RuntimeException('Cannot open ' . $ndjson); }

        $fetched = 0;
        foreach ($list as $sj) {
            $id = $sj['id'] ?? null;
            if ($id === null || isset($doneMap[$id])) { continue; }

            $detail = $this->client->suratJalanDetail($id);
            fwrite($fh, json_encode([
                'id'     => $id,
                'kode'   => $sj['kode'] ?? ($detail['header']['kode'] ?? null),
                'tgl'    => $sj['tgl'] ?? ($detail['header']['tgl'] ?? null),
                'detail' => $detail,
            ], JSON_UNESCAPED_SLASHES) . "\n");

            $doneMap[$id] = true;
            $fetched++;

            if ($fetched % 20 === 0) {
                fflush($fh);
                $progress['sj_detail_done'] = array_keys($doneMap);
                $this->saveProgress($progress);
            }
            if ($onProgress) { $onProgress('sj_details', $fetched, count($doneMap), count($list)); }
        }

        fclose($fh);
        $progress['sj_detail_done'] = array_keys($doneMap);
        $this->saveProgress($progress);

        return $fetched;
    }
}
