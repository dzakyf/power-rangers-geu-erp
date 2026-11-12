<?php

namespace App\Services\CNS;

use App\CnsInventoryKemasan;
use App\CnsKemasanSiteLimit;
use App\CnsMasterKemasan;
use App\Services\ExternalApi\ApipiApiClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;

class KemasanInventoryService
{
    /**
     * Static fallback site list used only when external API and cache are unavailable.
     */
    public const SITES = [
        1 => 'Malang',
        2 => 'Samarinda',
        3 => 'Cakung',
        4 => 'Bekasi',
        5 => 'Aeropolis',
        6 => 'Cisoka',
        7 => 'Bandung',
        8 => 'Semarang',
    ];

    public static function getSites(): array
    {
        return self::fetchSiteMap(function (ApipiApiClient $client) {
            return $client->cachedWarehouses();
        });
    }

    public static function getReportSites(): array
    {
        return self::fetchSiteMap(function (ApipiApiClient $client) {
            return $client->reportWarehouses();
        });
    }

    private static function fetchSiteMap(callable $fetcher): array
    {
        try {
            $warehouses = $fetcher(app(ApipiApiClient::class));
            $sites = [];
            foreach ($warehouses as $w) {
                $name = trim((string) ($w['name'] ?? ''));
                if (isset($w['id']) && $name !== '') {
                    $sites[(int) $w['id']] = $name;
                }
            }
            if ($sites !== []) {
                asort($sites, SORT_NATURAL | SORT_FLAG_CASE);
                return $sites;
            }
        } catch (\Throwable $e) {
            // Fall back to static SITES when external API/cache is unavailable
        }

        return self::SITES;
    }

    public static function formatVolume($volume): string
    {
        return number_format((float) $volume, 2, '.', ',');
    }

    /**
     * Extract (qty_balance, excess_balance) with fallback for legacy rows created before bucket columns.
     */
    public static function extractBucketBalances(?CnsInventoryKemasan $record): array
    {
        if (!$record) {
            return [0.0, 0.0];
        }

        $qty = (float) $record->qty_balance;
        $excess = (float) $record->excess_balance;
        $total = (float) $record->current_balance;

        if ($qty == 0.0 && $excess == 0.0 && $total != 0.0) {
            $qty = $total;
        }

        return [$qty, $excess];
    }

    /**
     * Resolve effective Max_Stock for a (kemasan, site) pair.
     */
    public static function resolveEffectiveMaxStock(
        ?CnsMasterKemasan $kemasan,
        ?int $siteId = null,
        ?array $siteLimitMap = null
    ): ?float {
        if (!$kemasan || $kemasan->isAmpas()) {
            return null;
        }

        if ($siteId !== null) {
            if ($siteLimitMap !== null && isset($siteLimitMap[$kemasan->Kemasan_ID][$siteId])) {
                return (float) $siteLimitMap[$kemasan->Kemasan_ID][$siteId];
            }
            if ($siteLimitMap === null && Schema::hasTable('cns_kemasan_site_limits')) {
                $override = CnsKemasanSiteLimit::where('kemasan_id', $kemasan->Kemasan_ID)
                    ->where('site_id', $siteId)
                    ->value('max_stock');
                if ($override !== null) {
                    return (float) $override;
                }
            }
        }

        return $kemasan->Max_Stock !== null ? (float) $kemasan->Max_Stock : null;
    }

    /**
     * Compute next (qty_balance, excess_balance, current_balance, normalized_out_type) for a mutation.
     */
    public static function computeNextBalances(
        float $prevQty,
        float $prevExcess,
        string $type,
        float $qty,
        ?CnsMasterKemasan $kemasan = null,
        ?string $outType = null,
        ?float $effectiveMaxStock = null
    ): array {
        $type = strtoupper($type);
        $hasMaxStock = $kemasan && !$kemasan->isAmpas() && ($effectiveMaxStock !== null || $kemasan->Max_Stock !== null);
        $maxStock = $hasMaxStock ? ($effectiveMaxStock !== null ? $effectiveMaxStock : (float) $kemasan->Max_Stock) : null;

        if ($type === 'IN') {
            if ($hasMaxStock) {
                $roomInQty = max(0.0, $maxStock - $prevQty);
                $addToQty = min($qty, $roomInQty);
                $newQty = $prevQty + $addToQty;
                $newExcess = $prevExcess + ($qty - $addToQty);
            } else {
                $newQty = $prevQty + $qty;
                $newExcess = $prevExcess;
            }

            return [$newQty, $newExcess, $newQty + $newExcess, null];
        }

        if ($type !== 'OUT') {
            throw new InvalidArgumentException('Tipe mutasi harus IN atau OUT.');
        }

        $normalizedOutType = strtoupper($outType ?: 'DROP');
        if (!in_array($normalizedOutType, ['DROP', 'PENJUALAN'], true)) {
            throw new InvalidArgumentException('Tipe OUT harus DROP atau PENJUALAN.');
        }

        if ($hasMaxStock && $normalizedOutType === 'PENJUALAN') {
            if ($prevExcess < $qty) {
                throw new RuntimeException("Stok Excess tidak mencukupi (tersedia: {$prevExcess}).");
            }
            $newQty = $prevQty;
            $newExcess = $prevExcess - $qty;
        } else {
            if ($prevQty < $qty) {
                throw new RuntimeException("Stok Qty tidak mencukupi (tersedia: {$prevQty}).");
            }
            $newQty = $prevQty - $qty;
            $newExcess = $prevExcess;
        }

        return [$newQty, $newExcess, $newQty + $newExcess, $normalizedOutType];
    }

    protected function buildScopedQuery(?int $siteId, ?int $packagingId, ?string $itemName)
    {
        $query = CnsInventoryKemasan::query();

        if ($siteId !== null) {
            $query->where('site_id', $siteId);
        } else {
            $query->whereNull('site_id');
        }

        if ($packagingId !== null) {
            $query->where('packaging_id', $packagingId);
        } else {
            $query->whereNull('packaging_id')->where('item_name', $itemName);
        }

        return $query;
    }

    /**
     * Record stock mutation (IN or OUT) for a packaging item.
     */
    public function recordStockMutation(
        string $itemName,
        string $unit,
        string $type,
        float $qty,
        string $referenceNo,
        ?string $notes = null,
        ?string $actor = null,
        ?int $packagingId = null,
        ?string $itemCode = null,
        ?string $transactionDate = null,
        ?string $outType = null,
        ?int $siteId = null,
        ?string $siteName = null
    ): CnsInventoryKemasan {
        $type = strtoupper($type);
        $qtyIn = $type === 'IN' ? $qty : 0.0;
        $qtyOut = $type === 'OUT' ? $qty : 0.0;
        $user = $actor ?: (auth()->check() ? auth()->user()->username : 'system');
        $date = $transactionDate ?: now()->toDateString();

        return DB::transaction(function () use (
            $itemName, $unit, $type, $qty, $qtyIn, $qtyOut,
            $referenceNo, $notes, $user, $packagingId,
            $itemCode, $date, $outType, $siteId, $siteName
        ) {
            $latest = $this->buildScopedQuery($siteId, $packagingId, $itemName)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            [$prevQty, $prevExcess] = self::extractBucketBalances($latest);
            $kemasan = $packagingId !== null ? CnsMasterKemasan::find($packagingId) : null;
            $effectiveMaxStock = self::resolveEffectiveMaxStock($kemasan, $siteId);

            [$newQty, $newExcess, $newBalance, $normalizedOutType] = self::computeNextBalances(
                $prevQty,
                $prevExcess,
                $type,
                $qty,
                $kemasan,
                $outType,
                $effectiveMaxStock
            );

            return CnsInventoryKemasan::create([
                'transaction_date' => $date,
                'site_id'          => $siteId,
                'site_name'        => $siteName,
                'packaging_id'     => $packagingId,
                'item_code'        => $itemCode,
                'item_name'        => $itemName,
                'unit'             => $unit ?: 'PCS',
                'reference_no'     => $referenceNo,
                'type'             => $type,
                'out_type'         => $normalizedOutType,
                'qty_in'           => $qtyIn,
                'qty_out'          => $qtyOut,
                'current_balance'  => $newBalance,
                'qty_balance'      => $newQty,
                'excess_balance'   => $newExcess,
                'notes'            => $notes,
                'created_by'       => $user,
            ]);
        });
    }

    /**
     * Get the latest balance for a specific packaging item.
     */
    public function getLatestBalance(string $itemName, ?int $packagingId = null, ?int $siteId = null): float
    {
        $latest = $this->buildScopedQuery($siteId, $packagingId, $itemName)
            ->orderBy('id', 'desc')
            ->first();

        return $latest ? (float) $latest->current_balance : 0.0;
    }

    protected function loadSiteLimitMap(): array
    {
        if (!Schema::hasTable('cns_kemasan_site_limits')) {
            return [];
        }

        $map = [];
        foreach (CnsKemasanSiteLimit::all() as $limit) {
            $map[$limit->kemasan_id][$limit->site_id] = (float) $limit->max_stock;
        }

        return $map;
    }

    /**
     * Get latest balances grouped by site, packaging_id, and item name.
     */
    public function getSummaryBalances(): array
    {
        $siteLimitMap = $this->loadSiteLimitMap();
        $items = CnsInventoryKemasan::with('packaging')
            ->select('site_id', 'site_name', 'packaging_id', 'item_name', 'unit')
            ->groupBy('site_id', 'site_name', 'packaging_id', 'item_name', 'unit')
            ->get();

        $summaries = [];
        foreach ($items as $item) {
            $latest = $this->buildScopedQuery(
                $item->site_id !== null ? (int) $item->site_id : null,
                $item->packaging_id !== null ? (int) $item->packaging_id : null,
                $item->item_name
            )->orderBy('id', 'desc')->first();

            [$qtyBalance, $excessBalance] = self::extractBucketBalances($latest);
            $isAmpas = $item->packaging ? $item->packaging->isAmpas() : false;
            $maxStock = self::resolveEffectiveMaxStock(
                $item->packaging,
                $item->site_id !== null ? (int) $item->site_id : null,
                $siteLimitMap
            );

            $label = $item->item_name;
            if ($item->packaging) {
                $label = $item->packaging->Name . ' (' . self::formatVolume($item->packaging->Volume) . ' L)';
            }

            $summaries[] = [
                'site_id'         => $item->site_id,
                'site_name'       => $item->site_name,
                'packaging_id'    => $item->packaging_id,
                'item_name'       => $item->item_name,
                'label'           => $label,
                'unit'            => $item->unit ?: 'PCS',
                'is_ampas'        => $isAmpas,
                'max_stock'       => $maxStock,
                'qty_balance'     => $qtyBalance,
                'excess_balance'  => $excessBalance,
                'current_balance' => $latest ? (float) $latest->current_balance : 0.0,
            ];
        }

        return $summaries;
    }

    public static function orderKemasansForReport($masterKemasanList)
    {
        [$ampasItems, $regularItems] = collect($masterKemasanList)->partition(function ($mk) {
            return $mk->isAmpas();
        });

        return $regularItems->concat($ampasItems)->values();
    }

    /**
     * Compute per-site (row), per-kemasan (column), and grand totals for Tab 2 report.
     */
    public static function computeReportTotals(array $rows, $kemasans): array
    {
        $columnTotals = [];
        foreach ($kemasans as $mk) {
            $columnTotals[$mk->Kemasan_ID] = [
                'max_stock' => 0.0,
                'qty'       => 0.0,
                'excess'    => 0.0,
            ];
        }

        $rowTotals = [];
        $grandTotals = [
            'max_stock' => 0.0,
            'qty'       => 0.0,
            'excess'    => 0.0,
        ];

        foreach ($rows as $row) {
            $siteId = $row['site_id'];
            $siteSum = ['max_stock' => 0.0, 'qty' => 0.0, 'excess' => 0.0];

            foreach ($kemasans as $mk) {
                $cell = $row['cells'][$mk->Kemasan_ID] ?? ['max_stock' => null, 'qty' => 0.0, 'excess' => 0.0];
                $maxStock = $mk->isAmpas() ? 0.0 : (float) ($cell['max_stock'] ?? 0.0);
                $qty = (float) ($cell['qty'] ?? 0.0);
                $excess = $mk->isAmpas() ? 0.0 : (float) ($cell['excess'] ?? 0.0);

                $columnTotals[$mk->Kemasan_ID]['max_stock'] += $maxStock;
                $columnTotals[$mk->Kemasan_ID]['qty'] += $qty;
                $columnTotals[$mk->Kemasan_ID]['excess'] += $excess;

                $siteSum['max_stock'] += $maxStock;
                $siteSum['qty'] += $qty;
                $siteSum['excess'] += $excess;
            }

            $rowTotals[$siteId] = $siteSum;
            $grandTotals['max_stock'] += $siteSum['max_stock'];
            $grandTotals['qty'] += $siteSum['qty'];
            $grandTotals['excess'] += $siteSum['excess'];
        }

        return [
            'row_totals'    => $rowTotals,
            'column_totals' => $columnTotals,
            'grand_totals'  => $grandTotals,
        ];
    }

    /**
     * Build Site x Master Kemasan report matrix for Tab 2.
     */
    public function getSiteReportMatrix($masterKemasanList = null): array
    {
        $sites = self::getReportSites();
        if ($masterKemasanList === null) {
            $masterKemasanList = Schema::hasTable('cns_master_kemasan')
                ? CnsMasterKemasan::orderBy('Name', 'asc')->orderBy('Volume', 'asc')->get()
                : collect();
        }
        $masterKemasanList = self::orderKemasansForReport($masterKemasanList);

        $siteLimitMap = $this->loadSiteLimitMap();
        $latestMap = [];

        if (Schema::hasTable('cns_inventory_kemasan')) {
            $records = CnsInventoryKemasan::whereNotNull('site_id')
                ->whereNotNull('packaging_id')
                ->orderBy('id', 'desc')
                ->get();

            foreach ($records as $rec) {
                if (!isset($latestMap[$rec->site_id][$rec->packaging_id])) {
                    $latestMap[$rec->site_id][$rec->packaging_id] = $rec;
                }
            }
        }

        $rows = [];
        foreach ($sites as $siteId => $siteName) {
            $cells = [];
            foreach ($masterKemasanList as $mk) {
                $rec = $latestMap[$siteId][$mk->Kemasan_ID] ?? null;
                [$qty, $excess] = self::extractBucketBalances($rec);
                $maxStock = self::resolveEffectiveMaxStock($mk, (int) $siteId, $siteLimitMap);

                $cells[$mk->Kemasan_ID] = [
                    'max_stock' => $maxStock,
                    'qty'       => $qty,
                    'excess'    => $excess,
                ];
            }

            $rows[] = [
                'site_id'   => $siteId,
                'site_name' => $siteName,
                'cells'     => $cells,
            ];
        }

        $totals = self::computeReportTotals($rows, $masterKemasanList);

        return [
            'sites'         => $sites,
            'kemasans'      => $masterKemasanList,
            'rows'          => $rows,
            'limits'        => $siteLimitMap,
            'row_totals'    => $totals['row_totals'],
            'column_totals' => $totals['column_totals'],
            'grand_totals'  => $totals['grand_totals'],
        ];
    }
}
