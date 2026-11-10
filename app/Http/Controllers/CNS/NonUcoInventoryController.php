<?php

namespace App\Http\Controllers\CNS;

use App\CnsInventoryNonUco;
use App\Http\Controllers\Controller;
use App\Services\CNS\NonUcoInventoryService;
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

        $records = $query->get();

        header('Content-Type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        echo "<rows>";

        $no = 1;

        if ($startDate && $itemName) {
            $priorRecord = CnsInventoryNonUco::where('item_name', $itemName)
                ->whereDate('transaction_date', '<', $startDate)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $initialBalance = $priorRecord ? (float) $priorRecord->current_balance : 0.0;
            $virtualDate = Carbon::parse($startDate)->format('d/m/Y');
            $formattedInitial = number_format($initialBalance, 0, ',', '.');

            echo '<row id="virtual_saldo_awal" style="font-weight:bold; background-color:#e8f4fd;">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . htmlspecialchars($virtualDate) . '</cell>';
            echo '<cell>' . htmlspecialchars($priorRecord->category ?? '-') . '</cell>';
            echo '<cell>' . htmlspecialchars($itemName) . '</cell>';
            echo '<cell>SALDO AWAL PERIODE</cell>';
            echo '<cell>SALDO AWAL</cell>';
            echo '<cell>0</cell>';
            echo '<cell>0</cell>';
            echo '<cell>' . htmlspecialchars($formattedInitial) . '</cell>';
            echo '<cell>' . htmlspecialchars($priorRecord->unit ?? 'PCS') . '</cell>';
            echo '<cell>Saldo awal sebelum periode filter</cell>';
            echo '<cell>SYSTEM</cell>';
            echo '</row>';
        }

        foreach ($records as $row) {
            $date = $row->transaction_date ? Carbon::parse($row->transaction_date)->format('d/m/Y') : '-';
            $ref = $row->reference_no ?: '-';
            $type = $row->type;
            $qtyIn = (float) $row->qty_in > 0 ? number_format((float) $row->qty_in, 0, ',', '.') : '0';
            $qtyOut = (float) $row->qty_out > 0 ? number_format((float) $row->qty_out, 0, ',', '.') : '0';
            $balance = number_format((float) $row->current_balance, 0, ',', '.');
            $notes = $row->notes ?: '-';
            $createdBy = $row->created_by ?: '-';

            echo '<row id="' . $row->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . htmlspecialchars($date) . '</cell>';
            echo '<cell>' . htmlspecialchars($row->category ?: 'General') . '</cell>';
            echo '<cell>' . htmlspecialchars($row->item_name) . '</cell>';
            echo '<cell>' . htmlspecialchars($ref) . '</cell>';
            echo '<cell>' . htmlspecialchars($type) . '</cell>';
            echo '<cell>' . htmlspecialchars($qtyIn) . '</cell>';
            echo '<cell>' . htmlspecialchars($qtyOut) . '</cell>';
            echo '<cell>' . htmlspecialchars($balance) . '</cell>';
            echo '<cell>' . htmlspecialchars($row->unit ?: 'PCS') . '</cell>';
            echo '<cell>' . htmlspecialchars($notes) . '</cell>';
            echo '<cell>' . htmlspecialchars($createdBy) . '</cell>';
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
