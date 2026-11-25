<?php

namespace App\Http\Controllers\CNS;

use App\CnsInventoryNonUco;
use App\Http\Controllers\Controller;
use App\Services\CNS\NonUcoInventoryService;
use App\Support\DhtmlxGridPagination;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class NonUcoInventoryController extends Controller
{
    protected $service;

    public function __construct(NonUcoInventoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Index page for Non-UCO inventory.
     */
    public function index(Request $request)
    {
        $summaries = $this->service->getSummaryBalances();
        $categories = CnsInventoryNonUco::distinct()->pluck('category')->filter()->values();
        $items = CnsInventoryNonUco::distinct()->pluck('item_name')->filter()->values();
        $currentBalance = $this->service->getLatestBalance('Flexibag 24KL');

        return view('cns.inventory-non-uco.index', compact('summaries', 'categories', 'items', 'currentBalance'));
    }

    /**
     * DHTMLX Grid XML data feed.
     */
    public function xmlData(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $category  = $request->query('category');
        $itemName  = $request->query('item_name');

        $query = CnsInventoryNonUco::with(['vesselSchedule', 'goodsReceipt'])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        if ($category) {
            $query->where('category', $category);
        }
        if ($itemName) {
            $query->where('item_name', $itemName);
        }

        // Column filters from dhtmlx header
        if ($request->has('filters') && is_array($request->query('filters'))) {
            foreach ($request->query('filters') as $colIdx => $val) {
                $val = trim((string) $val);
                if ($val === '') continue;
                if ($colIdx == 1) $query->whereDate('transaction_date', $val);
                if ($colIdx == 2) $query->where('category', $val);
                if ($colIdx == 3) $query->where('item_name', 'like', "%{$val}%");
                if ($colIdx == 4) $query->where('reference_no', 'like', "%{$val}%");
                if ($colIdx == 5) $query->where('type', $val);
                if ($colIdx == 9) $query->where('unit', $val);
                if ($colIdx == 10) $query->where('notes', 'like', "%{$val}%");
                if ($colIdx == 11) $query->where('created_by', 'like', "%{$val}%");
            }
        }

        $colMap = [
            1 => 'transaction_date',
            2 => 'category',
            3 => 'item_name',
            4 => 'reference_no',
            5 => 'type',
            6 => 'qty_in',
            7 => 'qty_out',
            8 => 'current_balance',
            9 => 'unit',
            10 => 'notes',
            11 => 'created_by',
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

        if ($startDate && $itemName && $posStart === 0) {
            $priorRecord = CnsInventoryNonUco::where('item_name', $itemName)
                ->whereDate('transaction_date', '<', $startDate)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $initialBalance = $priorRecord ? (float) $priorRecord->current_balance : 0.0;
            $virtualDate = Carbon::parse($startDate)->format('d/m/Y');
            $formattedInitial = number_format((float) $initialBalance, 2, '.', ',');

            $bodyXml .= '<row id="virtual_saldo_awal" style="font-weight:bold; background-color:#e8f4fd;">';
            $bodyXml .= '<cell>' . $no++ . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($virtualDate) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($priorRecord->category ?? '-') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($itemName) . '</cell>';
            $bodyXml .= '<cell>SALDO AWAL PERIODE</cell>';
            $bodyXml .= '<cell>SALDO AWAL</cell>';
            $bodyXml .= '<cell>0.00</cell>';
            $bodyXml .= '<cell>0.00</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($formattedInitial) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($priorRecord->unit ?? 'PCS') . '</cell>';
            $bodyXml .= '<cell>Saldo awal sebelum periode filter</cell>';
            $bodyXml .= '<cell>SYSTEM</cell>';
            $bodyXml .= '</row>' . "\n";
            $total++;
        }

        foreach ($records as $row) {
            $date = $row->transaction_date ? Carbon::parse($row->transaction_date)->format('d/m/Y') : '-';
            $ref = $row->reference_no ?: '-';
            $type = $row->type;
            $qtyIn = (float) $row->qty_in > 0 ? number_format((float) $row->qty_in, 2, '.', ',') : '0.00';
            $qtyOut = (float) $row->qty_out > 0 ? number_format((float) $row->qty_out, 2, '.', ',') : '0.00';
            $balance = number_format((float) $row->current_balance, 2, '.', ',');
            $notes = $row->notes ?: '-';
            $createdBy = $row->created_by ?: '-';

            $bodyXml .= '<row id="' . $row->id . '">';
            $bodyXml .= '<cell>' . $no++ . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($date) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($row->category ?: 'General') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($row->item_name) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($ref) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($type) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($qtyIn) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($qtyOut) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($balance) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($row->unit ?: 'PCS') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($notes) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($createdBy) . '</cell>';
            $bodyXml .= '</row>' . "\n";
        }

        return DhtmlxGridPagination::response($total, $posStart, $bodyXml);
    }

    /**
     * Return balance JSON.
     */
    public function balanceJson(Request $request)
    {
        $itemName  = $request->query('item_name', 'Flexibag 24KL');
        $startDate = $request->query('start_date');
        $initialBalance = 0.0;

        if ($startDate) {
            $priorRecord = CnsInventoryNonUco::where('item_name', $itemName)
                ->whereDate('transaction_date', '<', $startDate)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $initialBalance = $priorRecord ? (float) $priorRecord->current_balance : 0.0;
        }

        return response()->json([
            'status'          => 'success',
            'item_name'       => $itemName,
            'balance'         => $this->service->getLatestBalance($itemName),
            'initial_balance' => $initialBalance,
            'summaries'       => $this->service->getSummaryBalances(),
        ]);
    }

    /**
     * Manual mutation (e.g. usage OUT or stock opname adjustment).
     */
    public function manualMutation(Request $request)
    {
        $request->validate([
            'item_name'        => 'required|string',
            'category'         => 'nullable|string',
            'unit'             => 'required|string',
            'type'             => 'required|in:IN,OUT',
            'qty'              => 'required|numeric|min:0.01',
            'reference_no'     => 'required|string|max:100',
            'notes'            => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        try {
            $this->service->recordStockMutation(
                $request->item_name,
                $request->category ?: 'General',
                $request->unit,
                $request->type,
                (float) $request->qty,
                $request->reference_no,
                $request->notes,
                Auth::user()->username ?? 'admin',
                null,
                null,
                null,
                $request->transaction_date
            );

            return back()->with('success', 'Mutasi stok manual berhasil dicatat.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal mencatat mutasi: ' . $e->getMessage());
        }
    }
}
