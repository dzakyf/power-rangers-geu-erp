<?php

namespace App\Http\Controllers\CNS;

use App\CnsInventoryKemasan;
use App\CnsKemasanSiteLimit;
use App\CnsMasterKemasan;
use App\Http\Controllers\Controller;
use App\Services\CNS\KemasanInventoryService;
use App\Support\DhtmlxGridPagination;
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
                ->where('product_type', 'Kemasan')
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

        // Column filters from dhtmlx header
        if ($request->has('filters') && is_array($request->query('filters'))) {
            foreach ($request->query('filters') as $colIdx => $val) {
                $val = trim((string) $val);
                if ($val === '') continue;
                if ($colIdx == 1) $query->whereDate('transaction_date', $val);
                if ($colIdx == 2) $query->where('site_name', 'like', "%{$val}%");
                if ($colIdx == 3) $query->where('reference_no', 'like', "%{$val}%");
                if ($colIdx == 4) $query->where('item_name', 'like', "%{$val}%");
                if ($colIdx == 5) $query->where('unit', $val);
                if ($colIdx == 6) $query->where('type', $val);
                if ($colIdx == 7) $query->where('out_type', $val);
                if ($colIdx == 13) $query->where('notes', 'like', "%{$val}%");
                if ($colIdx == 14) $query->where('created_by', 'like', "%{$val}%");
            }
        }

        $colMap = [
            1 => 'transaction_date',
            2 => 'site_name',
            3 => 'reference_no',
            4 => 'item_name',
            5 => 'unit',
            6 => 'type',
            7 => 'out_type',
            8 => 'qty_in',
            9 => 'qty_out',
            12 => 'current_balance',
            13 => 'notes',
            14 => 'created_by',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $records = $paginated['rows'];
        $total = $paginated['total'];
        $posStart = $paginated['posStart'];

        $bodyXml = "";
        $no = $posStart + 1;
        $runningBalance = 0.0;

        // If filtering by item and start_date, get prior initial balance
        if ($startDate && $isItemFilter && $posStart === 0) {
            $priorQuery = CnsInventoryKemasan::query();
            $this->applyItemFilter($priorQuery, $packagingId, $itemName, $manualOnly, $siteId);
            $priorRecord = $priorQuery->whereDate('transaction_date', '<', $startDate)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($priorRecord) {
                [$priorQty, $priorExcess] = KemasanInventoryService::extractBucketBalances($priorRecord);
                $runningBalance = (float) $priorRecord->current_balance;
                $bodyXml .= '<row id="init_0">';
                $bodyXml .= '<cell>' . $no++ . '</cell>';
                $bodyXml .= '<cell>' . Carbon::parse($startDate)->format('d/m/Y') . '</cell>';
                $bodyXml .= '<cell>' . htmlspecialchars($priorRecord->site_name ?: '-') . '</cell>';
                $bodyXml .= '<cell>SALDO AWAL</cell>';
                $bodyXml .= '<cell>' . htmlspecialchars($priorRecord->item_name) . '</cell>';
                $bodyXml .= '<cell>-</cell>';
                $bodyXml .= '<cell>-</cell>';
                $bodyXml .= '<cell>-</cell>';
                $bodyXml .= '<cell>-</cell>';
                $bodyXml .= '<cell>-</cell>';
                $bodyXml .= '<cell>' . number_format($priorQty, 2) . '</cell>';
                $bodyXml .= '<cell>' . number_format($priorExcess, 2) . '</cell>';
                $bodyXml .= '<cell>' . number_format($runningBalance, 2) . '</cell>';
                $bodyXml .= '<cell>Saldo sebelum ' . Carbon::parse($startDate)->format('d/m/Y') . '</cell>';
                $bodyXml .= '<cell>system</cell>';
                $bodyXml .= '</row>' . "\n";
                $total++;
            }
        }

        foreach ($records as $r) {
            [$qtyBalance, $excessBalance] = KemasanInventoryService::extractBucketBalances($r);
            $dateFormatted = $r->transaction_date ? Carbon::parse($r->transaction_date)->format('d/m/Y') : '-';

            $bodyXml .= '<row id="' . $r->id . '">';
            $bodyXml .= '<cell>' . $no++ . '</cell>';
            $bodyXml .= '<cell>' . $dateFormatted . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($r->site_name ?: '-') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($r->reference_no ?: '-') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($r->item_name) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($r->unit ?: 'PCS') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($r->type) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($r->out_type ?: '-') . '</cell>';
            $bodyXml .= '<cell>' . ($r->qty_in > 0 ? number_format((float) $r->qty_in, 2) : '-') . '</cell>';
            $bodyXml .= '<cell>' . ($r->qty_out > 0 ? number_format((float) $r->qty_out, 2) : '-') . '</cell>';
            $bodyXml .= '<cell>' . number_format($qtyBalance, 2) . '</cell>';
            $bodyXml .= '<cell>' . number_format($excessBalance, 2) . '</cell>';
            $bodyXml .= '<cell>' . number_format((float) $r->current_balance, 2) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($r->notes ?: '-') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($r->created_by ?: '-') . '</cell>';
            $bodyXml .= '</row>' . "\n";
        }

        return DhtmlxGridPagination::response($total, $posStart, $bodyXml);
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
            'type'             => 'required|in:OUT',
            'out_type'         => 'required|in:DROP,PENJUALAN',
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
                'OUT',
                (float) $request->qty,
                $request->reference_no,
                $request->notes,
                Auth::user()->username ?? 'admin',
                $request->packaging_id ? (int) $request->packaging_id : null,
                null,
                $request->transaction_date,
                $request->out_type,
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
