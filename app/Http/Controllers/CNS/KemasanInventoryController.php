<?php

namespace App\Http\Controllers\CNS;

use App\CnsInventoryKemasan;
use App\CnsKemasanSiteLimit;
use App\CnsMasterKemasan;
use App\Http\Controllers\Controller;
use App\Services\CNS\KemasanInventoryService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class KemasanInventoryController extends Controller
{
    protected $service;

    public function __construct(KemasanInventoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Index page for Inventory Kemasan.
     */
    public function index(Request $request)
    {
        $summaries = $this->service->getSummaryBalances();
        $existingItems = CnsInventoryKemasan::whereNull('packaging_id')
            ->distinct()
            ->pluck('item_name')
            ->filter()
            ->sort()
            ->values();

        $masterPackagings = collect();
        if (Schema::hasTable('cns_master_kemasan')) {
            $masterPackagings = CnsMasterKemasan::orderBy('Name', 'asc')
                ->orderBy('Volume', 'asc')
                ->get();
        }

        $sites = KemasanInventoryService::getSites();
        $reportMatrix = $this->service->getSiteReportMatrix($masterPackagings);

        return view('cns.inventory-kemasan.index', compact(
            'summaries',
            'existingItems',
            'masterPackagings',
            'sites',
            'reportMatrix'
        ));
    }

    protected function applyItemFilter($query, $packagingId, ?string $itemName, bool $manualOnly = false, $siteId = null): void
    {
        if ($siteId !== null && $siteId !== '') {
            $query->where('site_id', (int) $siteId);
        }

        if ($packagingId !== null && $packagingId !== '') {
            $query->where('packaging_id', (int) $packagingId);
        } elseif ($itemName !== null && $itemName !== '') {
            if ($manualOnly) {
                $query->whereNull('packaging_id');
            }
            $query->where('item_name', $itemName);
        }
    }

    /**
     * DHTMLX Grid XML data feed.
     */
    public function xmlData(Request $request)
    {
        $startDate   = $request->query('start_date');
        $endDate     = $request->query('end_date');
        $siteId      = $request->query('site_id');
        $packagingId = $request->query('packaging_id');
        $itemName    = $request->query('item_name');
        $manualOnly  = $request->boolean('manual');
        $isItemFilter = ($packagingId !== null && $packagingId !== '') || ($itemName !== null && $itemName !== '');

        $query = CnsInventoryKemasan::orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        $this->applyItemFilter($query, $packagingId, $itemName, $manualOnly, $siteId);

        $records = $query->get();

        header('Content-Type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        echo "<rows>";

        $no = 1;
        $runningBalance = 0.0;

        // If filtering by item and start_date, get prior initial balance
        if ($startDate && $isItemFilter) {
            $priorQuery = CnsInventoryKemasan::query();
            $this->applyItemFilter($priorQuery, $packagingId, $itemName, $manualOnly, $siteId);
            $priorRecord = $priorQuery->whereDate('transaction_date', '<', $startDate)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($priorRecord) {
                [$priorQty, $priorExcess] = KemasanInventoryService::extractBucketBalances($priorRecord);
                $runningBalance = (float) $priorRecord->current_balance;
                echo '<row id="init_0">';
                echo '<cell>' . $no++ . '</cell>';
                echo '<cell>' . Carbon::parse($startDate)->format('d/m/Y') . '</cell>';
                echo '<cell>' . htmlspecialchars($priorRecord->site_name ?: '-') . '</cell>';
                echo '<cell>SALDO AWAL</cell>';
                echo '<cell>' . htmlspecialchars($priorRecord->item_name) . '</cell>';
                echo '<cell>-</cell>';
                echo '<cell>-</cell>';
                echo '<cell>-</cell>';
                echo '<cell>-</cell>';
                echo '<cell>-</cell>';
                echo '<cell>' . number_format($priorQty, 2) . '</cell>';
                echo '<cell>' . number_format($priorExcess, 2) . '</cell>';
                echo '<cell>' . number_format($runningBalance, 2) . '</cell>';
                echo '<cell>Saldo sebelum ' . Carbon::parse($startDate)->format('d/m/Y') . '</cell>';
                echo '<cell>system</cell>';
                echo '</row>';
            }
        }

        foreach ($records as $r) {
            [$qtyBalance, $excessBalance] = KemasanInventoryService::extractBucketBalances($r);
            $dateFormatted = $r->transaction_date ? Carbon::parse($r->transaction_date)->format('d/m/Y') : '-';

            echo '<row id="' . $r->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $dateFormatted . '</cell>';
            echo '<cell>' . htmlspecialchars($r->site_name ?: '-') . '</cell>';
            echo '<cell>' . htmlspecialchars($r->reference_no ?: '-') . '</cell>';
            echo '<cell>' . htmlspecialchars($r->item_name) . '</cell>';
            echo '<cell>' . htmlspecialchars($r->unit ?: 'PCS') . '</cell>';
            echo '<cell>' . htmlspecialchars($r->type) . '</cell>';
            echo '<cell>' . htmlspecialchars($r->out_type ?: '-') . '</cell>';
            echo '<cell>' . ($r->qty_in > 0 ? number_format((float) $r->qty_in, 2) : '-') . '</cell>';
            echo '<cell>' . ($r->qty_out > 0 ? number_format((float) $r->qty_out, 2) : '-') . '</cell>';
            echo '<cell>' . number_format($qtyBalance, 2) . '</cell>';
            echo '<cell>' . number_format($excessBalance, 2) . '</cell>';
            echo '<cell>' . number_format((float) $r->current_balance, 2) . '</cell>';
            echo '<cell>' . htmlspecialchars($r->notes ?: '-') . '</cell>';
            echo '<cell>' . htmlspecialchars($r->created_by ?: '-') . '</cell>';
            echo '</row>';
        }

        echo "</rows>";
        exit;
    }

    /**
     * Return balance JSON.
     */
    public function balanceJson(Request $request)
    {
        $siteId       = $request->query('site_id');
        $packagingId  = $request->query('packaging_id');
        $itemName     = $request->query('item_name');
        $startDate    = $request->query('start_date');
        $manualOnly   = $request->boolean('manual');
        $isItemFilter = ($packagingId !== null && $packagingId !== '') || ($itemName !== null && $itemName !== '');
        $initialBalance = 0.0;

        if ($isItemFilter && $startDate) {
            $priorQuery = CnsInventoryKemasan::query();
            $this->applyItemFilter($priorQuery, $packagingId, $itemName, $manualOnly, $siteId);
            $priorRecord = $priorQuery->whereDate('transaction_date', '<', $startDate)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $initialBalance = $priorRecord ? (float) $priorRecord->current_balance : 0.0;
        }

        $pkgIdInt = ($packagingId !== null && $packagingId !== '') ? (int) $packagingId : null;
        $siteIdInt = ($siteId !== null && $siteId !== '') ? (int) $siteId : null;

        return response()->json([
            'status'          => 'success',
            'site_id'         => $siteIdInt,
            'packaging_id'    => $pkgIdInt,
            'item_name'       => $itemName,
            'balance'         => $isItemFilter ? $this->service->getLatestBalance((string) $itemName, $pkgIdInt, $siteIdInt) : 0,
            'initial_balance' => $initialBalance,
            'summaries'       => $this->service->getSummaryBalances(),
        ]);
    }

    /**
     * Manual mutation (IN / OUT).
     */
    public function manualMutation(Request $request)
    {
        $sites = KemasanInventoryService::getSites();

        $request->validate([
            'site_id'          => ['required', 'integer', Rule::in(array_keys($sites))],
            'item_name'        => 'required|string',
            'unit'             => 'required|string',
            'type'             => 'required|in:IN,OUT',
            'out_type'         => 'required_if:type,OUT|nullable|in:DROP,PENJUALAN',
            'qty'              => 'required|numeric|min:0.01',
            'reference_no'     => 'required|string|max:100',
            'notes'            => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'packaging_id'     => 'nullable|integer|exists:cns_master_kemasan,Kemasan_ID',
        ]);

        $siteId = (int) $request->site_id;
        $siteName = $sites[$siteId] ?? null;

        try {
            $this->service->recordStockMutation(
                $request->item_name,
                $request->unit,
                $request->type,
                (float) $request->qty,
                $request->reference_no,
                $request->notes,
                Auth::user()->username ?? 'admin',
                $request->packaging_id ? (int) $request->packaging_id : null,
                null,
                $request->transaction_date,
                $request->type === 'OUT' ? $request->out_type : null,
                $siteId,
                $siteName
            );

            return back()->with('success', 'Mutasi stok kemasan berhasil dicatat.')->with('active_tab', 'report');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal mencatat mutasi: ' . $e->getMessage())->with('active_tab', 'report');
        }
    }

    /**
     * Save Max Stock overrides per (Master Kemasan, Site).
     */
    public function saveSiteLimits(Request $request)
    {
        $sites = KemasanInventoryService::getSites();
        $limits = $request->input('limits', []);

        $request->validate([
            'limits'     => 'nullable|array',
            'limits.*'   => 'nullable|array',
            'limits.*.*' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($limits, $sites) {
                foreach ($limits as $kemasanId => $siteValues) {
                    $kemasan = CnsMasterKemasan::find($kemasanId);
                    if (!$kemasan || $kemasan->isAmpas() || !is_array($siteValues)) {
                        continue;
                    }

                    foreach ($siteValues as $siteId => $maxStock) {
                        if (!array_key_exists((int) $siteId, $sites)) {
                            continue;
                        }

                        if ($maxStock === null || $maxStock === '') {
                            CnsKemasanSiteLimit::where('kemasan_id', (int) $kemasanId)
                                ->where('site_id', (int) $siteId)
                                ->delete();
                        } else {
                            CnsKemasanSiteLimit::updateOrCreate(
                                ['kemasan_id' => (int) $kemasanId, 'site_id' => (int) $siteId],
                                ['max_stock' => (float) $maxStock]
                            );
                        }
                    }
                }
            });

            return back()->with('success', 'Pengaturan Max Stok per Site berhasil disimpan.')->with('active_tab', 'report');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menyimpan Max Stok per Site: ' . $e->getMessage())->with('active_tab', 'report');
        }
    }
}
