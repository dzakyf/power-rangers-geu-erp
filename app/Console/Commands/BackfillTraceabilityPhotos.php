<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\ApipiApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillTraceabilityPhotos extends Command
{
    protected $signature = 'traceability:backfill-photos
        {--only-missing : Only process deliveries whose photos are empty (default)}
        {--include-existing : Also process deliveries that already have photos}
        {--delivery-id= : Filter by delivery id}
        {--sj-code= : Filter by surat jalan code}
        {--from= : Filter delivery_date >= YYYY-MM-DD}
        {--to= : Filter delivery_date <= YYYY-MM-DD}
        {--limit=0 : Maximum deliveries to process (0 = all)}
        {--chunk=200 : Fragment update chunk size}
        {--all-photos : Always fetch all photos from live APIPI (otherwise cache first is used)}
        {--dry-run : Report only, do not write}';

    protected $description = 'Backfill delivery/fragment photos from cached SJ details or live APIPI';

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $cacheDir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');
        $cache = $this->loadCache($cacheDir);
        $client = app(ApipiApiClient::class);

        $query = DB::table('traceability_external_deliveries')
            ->when(!$this->option('include-existing'), function ($q) {
                $q->where(function ($w) {
                    $w->whereNull('photos')->orWhere('photos', '');
                });
            })
            ->when($this->option('delivery-id'), function ($q, $id) {
                $ids = array_filter(array_map('intval', explode(',', (string) $id)));
                if ($ids) { $q->whereIn('id', $ids); }
            })
            ->when($this->option('sj-code'), function ($q, $code) {
                $q->where('api_reference', 'like', trim((string) $code) . '-%');
            })
            ->when($this->option('from'), function ($q, $from) {
                $q->whereDate('delivery_date', '>=', $from);
            })
            ->when($this->option('to'), function ($q, $to) {
                $q->whereDate('delivery_date', '<=', $to);
            })
            ->orderBy('id');

        $limit = (int) $this->option('limit');
        if ($limit > 0) { $query->limit($limit); }

        $deliveries = $query->get();
        $this->info('Deliveries to process: ' . $deliveries->count());

        $dry = (bool) $this->option('dry-run');
        $allPhotos = (bool) $this->option('all-photos');
        $chunkSize = max(1, (int) $this->option('chunk'));

        $updated = 0;
        $skipped = 0;
        $fragmentCount = 0;

        foreach ($deliveries as $delivery) {
            $kode = $this->extractSjCode((string) $delivery->api_reference);
            if ($kode === '') {
                $skipped++;
                $this->warn("  skip delivery {$delivery->id}: cannot parse SJ code");
                continue;
            }

            $cacheEntry = $cache[$kode] ?? null;
            $photos = [];

            if (!$allPhotos && $cacheEntry && !empty($cacheEntry['first_photo'])) {
                $photos = [$cacheEntry['first_photo']];
            } else {
                $photos = $this->fetchLivePhotos($client, $delivery, $kode, $cacheEntry);
                if (!$photos && $cacheEntry && !empty($cacheEntry['first_photo'])) {
                    $photos = [$cacheEntry['first_photo']];
                }
            }

            $photos = array_values(array_unique(array_filter($photos)));
            if (!$photos) {
                $skipped++;
                $this->warn("  skip delivery {$delivery->id} ({$kode}): no photos found");
                continue;
            }

            $first = $photos[0];
            $fragments = DB::table('traceability_external_poo_fragments')
                ->where('traceability_external_delivery_id', $delivery->id)
                ->count();

            $this->line("  delivery {$delivery->id} ({$kode}) photos=" . count($photos) . " fragments={$fragments}");

            if ($dry) {
                continue;
            }

            DB::transaction(function () use ($delivery, $photos, $first, $chunkSize) {
                DB::table('traceability_external_deliveries')
                    ->where('id', $delivery->id)
                    ->update([
                        'photos' => json_encode($photos, JSON_UNESCAPED_SLASHES),
                        'updated_at' => now(),
                    ]);

                DB::table('traceability_external_poo_fragments')
                    ->where('traceability_external_delivery_id', $delivery->id)
                    ->orderBy('id')
                    ->chunkById($chunkSize, function ($rows) use ($first) {
                        DB::table('traceability_external_poo_fragments')
                            ->whereIn('id', $rows->pluck('id')->all())
                            ->update(['oil_documentation' => $first, 'updated_at' => now()]);
                    });
            });

            $updated++;
            $fragmentCount += $fragments;
        }

        if ($dry) {
            $this->info('DRY RUN: no data written.');
        }

        $this->info("Deliveries updated: {$updated} | fragments updated: {$fragmentCount} | skipped: {$skipped}");

        return 0;
    }

    protected function extractSjCode(string $ref): string
    {
        if (preg_match('/^(GEU-S[RJ]-\d{2}-[A-Z]{3}\d{4})/', $ref, $m)) {
            return $m[1];
        }

        return '';
    }

    protected function loadCache(string $dir): array
    {
        $cache = [];

        $listPath = $dir . '/surat_jalan_list.json';
        if (is_file($listPath)) {
            $list = json_decode((string) @file_get_contents($listPath), true) ?: [];
            foreach ($list as $row) {
                $kode = $row['kode'] ?? null;
                if (!$kode) { continue; }
                $cache[$kode]['sj_id'] = $row['id'] ?? null;
                $cache[$kode]['supplier_name'] = $row['supplier_name'] ?? null;
            }
        }

        $detailsPath = $dir . '/surat_jalan_details.ndjson';
        if (is_file($detailsPath)) {
            $fh = fopen($detailsPath, 'r');
            while (($line = fgets($fh)) !== false) {
                $rec = json_decode($line, true);
                if (!is_array($rec)) { continue; }

                $detail = is_array($rec['detail'] ?? null) ? $rec['detail'] : [];
                $header = $detail['header'] ?? [];
                $kode = $rec['kode'] ?? ($header['kode'] ?? null);
                if (!$kode) { continue; }

                $cache[$kode]['sj_id'] = $cache[$kode]['sj_id'] ?? ($rec['id'] ?? ($header['id'] ?? null));
                $cache[$kode]['supplier_name'] = $cache[$kode]['supplier_name'] ?? ($header['supplier_name'] ?? null);

                foreach (($detail['details'] ?? []) as $ln) {
                    $photo = trim((string) ($ln['_first_photo'] ?? ($ln['foto'] ?? '')));
                    if ($photo !== '' && empty($cache[$kode]['first_photo'])) {
                        $cache[$kode]['first_photo'] = $photo;
                    }
                    $cache[$kode]['lines'][] = [
                        'id' => $ln['id'] ?? null,
                        'supplier_name' => $ln['supplier_name'] ?? null,
                    ];
                }
            }
            fclose($fh);
        }

        return $cache;
    }

    protected function fetchLivePhotos(ApipiApiClient $client, $delivery, string $kode, ?array $cacheEntry): array
    {
        $sjId = $cacheEntry['sj_id'] ?? null;

        if (!$sjId) {
            try {
                $res = $client->suratJalan(1, 200, 'done', (string) $delivery->delivery_date, (string) $delivery->delivery_date);
                foreach (($res['data'] ?? []) as $row) {
                    if (($row['kode'] ?? null) === $kode) {
                        $sjId = $row['id'] ?? null;
                        break;
                    }
                }
            } catch (\Throwable $e) {
                return [];
            }
        }

        if (!$sjId) { return []; }

        try {
            $detail = $client->suratJalanDetail($sjId);
        } catch (\Throwable $e) {
            return [];
        }

        $lines = $detail['details'] ?? [];
        if (empty($lines) && !empty($cacheEntry['lines'])) {
            $lines = $cacheEntry['lines'];
        }

        $deliverySupplier = strtolower(trim((string) $delivery->supplier_name));
        $all = [];
        $matched = [];

        foreach ($lines as $line) {
            $lineId = $line['id'] ?? null;
            if (!$lineId) { continue; }

            try {
                $res = $client->detailPhotos($lineId);
            } catch (\Throwable $e) {
                continue;
            }

            $photos = $res['photos'] ?? ($res['data']['photos'] ?? ($res['data'] ?? []));
            if (!is_array($photos)) { continue; }

            $urls = [];
            foreach ($photos as $photo) {
                $url = is_array($photo) ? ($photo['file_path'] ?? null) : $photo;
                if (is_string($url) && trim($url) !== '') { $urls[] = trim($url); }
            }

            if (!$urls) { continue; }
            $all = array_merge($all, $urls);

            $lineSupplier = strtolower(trim((string) ($line['supplier_name'] ?? '')));
            if ($deliverySupplier !== '' && $lineSupplier !== '' && $lineSupplier === $deliverySupplier) {
                $matched = array_merge($matched, $urls);
            }
        }

        return $matched ?: $all;
    }
}
