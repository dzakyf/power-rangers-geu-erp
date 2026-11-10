<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\ApipiApiClient;
use App\Services\Traceability\TraceabilityPriceAllocator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillTraceabilityPrice extends Command
{
    protected $signature = 'traceability:backfill-price
        {--from=2025-01-01 : Start delivery date (Y-m-d)}
        {--to=2026-12-31 : End delivery date (Y-m-d)}
        {--chunk=100 : Number of pickups processed per progress/update chunk}
        {--limit=0 : Limit pickups for testing; 0 means all}
        {--dry-run : Fetch and report only, do not update}';

    protected $description = 'Backfill fragment price/total_price from pickup item total_harga in resumable chunks';

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $from = $this->option('from');
        $to = $this->option('to');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $limit = max(0, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $deliveries = DB::table('traceability_external_deliveries')
            ->whereBetween('delivery_date', [$from, $to])
            ->whereNotNull('pickup_id')
            ->select('id', 'pickup_id', 'api_reference', 'supplier_name', 'delivery_date')
            ->get();

        $byPickup = [];
        foreach ($deliveries as $delivery) {
            $byPickup[$delivery->pickup_id][] = $delivery;
        }

        $fragmentsByDelivery = [];
        $fragments = DB::table('traceability_external_poo_fragments as f')
            ->join('traceability_external_deliveries as d', 'd.id', '=', 'f.traceability_external_delivery_id')
            ->whereBetween('d.delivery_date', [$from, $to])
            ->select('f.id', 'f.traceability_external_delivery_id', 'f.quantity_kg')
            ->get();

        foreach ($fragments as $fragment) {
            $fragmentsByDelivery[$fragment->traceability_external_delivery_id][] = $fragment;
        }

        $this->info('Deliveries: ' . count($deliveries) . ' | pickups: ' . count($byPickup) . ' | fragments: ' . count($fragments));

        $progressFile = $this->progressFile($from, $to);
        $done = $this->readProgress($progressFile);
        $this->info('Already processed pickups: ' . count($done));

        $client = app(ApipiApiClient::class);
        $processed = 0;
        $fetched = 0;
        $failed = 0;
        $matchedDeliveries = 0;
        $updatedFragments = 0;
        $skippedPickups = 0;
        $allocatedTotal = 0.0;
        $pendingUpdates = [];
        $pendingDone = [];

        foreach ($byPickup as $pickupId => $pickupDeliveries) {
            if (isset($done[$pickupId])) { continue; }
            if ($limit > 0 && $processed >= $limit) { break; }

            $processed++;

            try {
                $data = $client->pickup($pickupId);
                $items = $data['items'] ?? [];
                $fetched++;
            } catch (\Throwable $e) {
                $failed++;
                continue;
            }

            $groups = $this->matchGroups($pickupDeliveries, $items);

            if (!$groups) {
                $skippedPickups++;
            }

            foreach ($groups as $group) {
                $item = $group['item'];
                $total = $this->itemTotal($item);
                if ($total === null) { continue; }

                $groupFragments = [];
                foreach ($group['deliveries'] as $delivery) {
                    foreach (($fragmentsByDelivery[$delivery->id] ?? []) as $fragment) {
                        $groupFragments[] = $fragment;
                    }
                }

                if (!$groupFragments) { continue; }

                $quantities = array_map(function ($f) { return (float) $f->quantity_kg; }, $groupFragments);
                $allocations = TraceabilityPriceAllocator::allocate($total, $quantities);

                foreach ($groupFragments as $i => $fragment) {
                    $allocated = $allocations[$i];
                    $qty = (float) $fragment->quantity_kg;
                    $price = $qty > 0 ? round($allocated / $qty, 2) : 0;

                    $pendingUpdates[] = [
                        'id' => $fragment->id,
                        'total_price' => $allocated,
                        'price' => $price,
                    ];

                    $allocatedTotal += $allocated;
                    $updatedFragments++;
                }

                $matchedDeliveries += count($group['deliveries']);
            }

            $pendingDone[$pickupId] = true;

            if (count($pendingDone) >= $chunkSize) {
                if (!$dryRun) {
                    $this->flushUpdates($pendingUpdates);
                    $this->saveProgress($progressFile, $done + $pendingDone);
                }
                $this->line(sprintf(
                    '  processed %d pickups | fetched %d | failed %d | fragments %d',
                    $processed,
                    $fetched,
                    $failed,
                    $updatedFragments
                ));
                $pendingUpdates = [];
                $pendingDone = [];
            }
        }

        if (!$dryRun) {
            $this->flushUpdates($pendingUpdates);
            $this->saveProgress($progressFile, $done + $pendingDone);
        }

        $this->info('--- Price backfill summary ---');
        $this->line('  processed pickups: ' . $processed);
        $this->line('  fetched: ' . $fetched . ' | failed: ' . $failed . ' | skipped/no item: ' . $skippedPickups);
        $this->line('  matched deliveries: ' . $matchedDeliveries);
        $this->line('  fragments updated: ' . $updatedFragments);
        $this->line('  allocated total: ' . number_format($allocatedTotal, 2, '.', ','));
        if ($dryRun) {
            $this->warn('DRY RUN: no updates written and no progress saved.');
        }

        return 0;
    }

    protected function matchGroups(array $deliveries, array $items): array
    {
        $bySupplier = [];
        $byName = [];
        foreach ($items as $item) {
            if (!empty($item['supplier_id'])) {
                $bySupplier[(string) $item['supplier_id']] = $item;
            }
            if (!empty($item['supplier_name'])) {
                $byName[$this->norm($item['supplier_name'])] = $item;
            }
        }
        $single = count($items) === 1 ? $items[0] : null;

        $groups = [];
        foreach ($deliveries as $delivery) {
            $key = $this->deliverySupplierKey($delivery);
            $item = null;

            if (ctype_digit($key) && isset($bySupplier[$key])) {
                $item = $bySupplier[$key];
            } elseif (isset($byName[$this->norm($delivery->supplier_name)])) {
                $item = $byName[$this->norm($delivery->supplier_name)];
            } elseif ($single) {
                $item = $single;
            }

            if (!$item) { continue; }

            $itemKey = (string) ($item['id'] ?? $key);
            if (!isset($groups[$itemKey])) {
                $groups[$itemKey] = ['item' => $item, 'deliveries' => []];
            }
            $groups[$itemKey]['deliveries'][] = $delivery;
        }

        return array_values($groups);
    }

    protected function deliverySupplierKey($delivery): string
    {
        $ref = (string) ($delivery->api_reference ?? '');
        if (strpos($ref, '-') !== false) {
            $parts = explode('-', $ref);
            $suffix = end($parts);
            if (ctype_digit((string) $suffix)) {
                return (string) $suffix;
            }
        }

        return 'name:' . $this->norm($delivery->supplier_name);
    }

    protected function itemTotal(array $item): ?float
    {
        if (isset($item['total_harga']) && is_numeric($item['total_harga'])) {
            return (float) $item['total_harga'];
        }
        if (isset($item['harga'], $item['qty']) && is_numeric($item['harga']) && is_numeric($item['qty'])) {
            return (float) $item['harga'] * (float) $item['qty'];
        }

        return null;
    }

    protected function flushUpdates(array $updates): void
    {
        foreach (array_chunk($updates, 300) as $chunk) {
            if (!$chunk) { continue; }

            $totalCases = [];
            $priceCases = [];
            $totalBindings = [];
            $priceBindings = [];
            $ids = [];

            foreach ($chunk as $u) {
                $ids[] = $u['id'];
                $totalCases[] = 'WHEN ? THEN ?';
                $totalBindings[] = $u['id'];
                $totalBindings[] = $u['total_price'];
                $priceCases[] = 'WHEN ? THEN ?';
                $priceBindings[] = $u['id'];
                $priceBindings[] = $u['price'];
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $bindings = array_merge($totalBindings, $priceBindings, $ids);

            $sql = 'UPDATE traceability_external_poo_fragments
                    SET total_price = CASE id ' . implode(' ', $totalCases) . ' END,
                        price = CASE id ' . implode(' ', $priceCases) . ' END
                    WHERE id IN (' . $placeholders . ')';

            DB::update($sql, $bindings);
        }
    }

    protected function norm(?string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $value)));
    }

    protected function progressFile(string $from, string $to): string
    {
        $dir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }

        return $dir . '/backfill_price_done_' . preg_replace('/[^0-9]/', '', $from) . '_' . preg_replace('/[^0-9]/', '', $to) . '.json';
    }

    protected function readProgress(string $file): array
    {
        if (!is_file($file)) { return []; }
        $data = json_decode((string) file_get_contents($file), true);

        return is_array($data) ? array_flip($data) : [];
    }

    protected function saveProgress(string $file, array $done): void
    {
        @file_put_contents($file, json_encode(array_keys($done), JSON_UNESCAPED_SLASHES));
    }
}
