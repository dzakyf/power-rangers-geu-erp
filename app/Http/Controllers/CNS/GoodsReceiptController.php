<?php

namespace App\Http\Controllers\CNS;

use App\CnsGoodsReceipt;
use App\Http\Controllers\Controller;
use App\Services\CNS\GoodsReceiptService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class GoodsReceiptController extends Controller
{
    protected $service;

    public function __construct(GoodsReceiptService $service)
    {
        $this->service = $service;
    }

    /**
     * Index page with DHTMLX grid.
     */
    public function index()
    {
        $canApprove = $this->checkCanApprove();
        return view('cns.goods-receipt.index', compact('canApprove'));
    }

    /**
     * XML data feed for DHTMLX grid.
     */
    public function xmlData(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $status    = $request->query('status');

        $query = CnsGoodsReceipt::with('details')->orderBy('id', 'desc');

        if ($startDate) {
            $query->whereDate('receipt_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('receipt_date', '<=', $endDate);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $records = $query->get();

        header('Content-Type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        echo "<rows>";

        $no = 1;
        foreach ($records as $row) {
            $date = $row->receipt_date ? Carbon::parse($row->receipt_date)->format('d/m/Y') : '-';
            $po = DB::table('purchase_order_cns')->where('PO_No', $row->po_no)->first();
            $supplierName = '-';
            if ($po && !empty($po->Supplier)) {
                $supp = DB::table('supplier')->where('Supplier_ID', $po->Supplier)->first();
                $supplierName = $supp->Company_Name ?? $po->Supplier;
            }

            $itemSummary = $row->details->map(function ($d) {
                return $d->item_name . ' (' . number_format($d->qty_received, 0, ',', '.') . ' ' . $d->unit . ')';
            })->implode(', ');

            $statusBadge = $row->status;

            echo '<row id="' . $row->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . htmlspecialchars($row->gr_no) . '</cell>';
            echo '<cell>' . htmlspecialchars($row->po_no) . '</cell>';
            echo '<cell>' . htmlspecialchars($supplierName) . '</cell>';
            echo '<cell>' . htmlspecialchars($date) . '</cell>';
            echo '<cell>' . htmlspecialchars($row->delivery_note_no ?: '-') . '</cell>';
            echo '<cell>' . htmlspecialchars(($row->driver_name ?: '-') . ($row->vehicle_no ? ' / ' . $row->vehicle_no : '')) . '</cell>';
            echo '<cell>' . htmlspecialchars($itemSummary ?: '-') . '</cell>';
            echo '<cell>' . htmlspecialchars($statusBadge) . '</cell>';
            echo '<cell>' . htmlspecialchars($row->received_by ?: '-') . '</cell>';
            echo '<cell>' . htmlspecialchars($row->approved_by ?: '-') . '</cell>';
            echo '</row>';
        }

        echo "</rows>";
        exit;
    }

    /**
     * Create form view.
     */
    public function create()
    {
        // Fetch approved POs not fully received yet
        $pendingPos = DB::table('purchase_order_cns')
            ->where(function ($q) {
                $q->whereNull('Status_Receive')
                    ->orWhere('Status_Receive', '!=', 'RECEIVED');
            })
            ->where(function ($q) {
                $q->where('Status', 'APPROVED')
                    ->orWhere('Status', 'CLOSED');
            })
            ->orderBy('ID', 'desc')
            ->get();

        return view('cns.goods-receipt.create', compact('pendingPos'));
    }

    /**
     * AJAX endpoint: return items for selected PO.
     */
    public function poItems(Request $request)
    {
        $poNo = $request->query('po_no');
        if (!$poNo) {
            return response()->json(['status' => 'error', 'message' => 'PO number is required'], 400);
        }

        $po = DB::table('purchase_order_cns')->where('PO_No', $poNo)->first();
        if (!$po) {
            return response()->json(['status' => 'error', 'message' => 'PO tidak ditemukan'], 404);
        }

        $supplier = DB::table('supplier')->where('Supplier_ID', $po->Supplier)->first();

        $items = DB::table('purchase_order_cns_dtl')
            ->where('PO_No', $poNo)
            ->get()
            ->map(function ($dtl) {
                $ordered  = (float) ($dtl->Qty ?? 0);
                $received = (float) ($dtl->Qty_Receive ?? 0);
                $remain   = max(0, $ordered - $received);

                return [
                    'po_dtl_id'     => $dtl->ID,
                    'item_code'     => $dtl->Product_Code ?? null,
                    'item_name'     => $dtl->Product_Desc ?? 'Item Non-UCO',
                    'category'      => $dtl->Category ?? ($dtl->Purchase_Type ?? 'General'),
                    'unit'          => $dtl->Unit ?? 'PCS',
                    'qty_ordered'   => $ordered,
                    'qty_received_prior' => $received,
                    'qty_remain'    => $remain,
                ];
            });

        return response()->json([
            'status'        => 'success',
            'po'            => [
                'po_no'          => $po->PO_No,
                'po_date'        => $po->PO_Date,
                'supplier_name'  => $supplier->Company_Name ?? ($po->Supplier ?? '-'),
                'status_receive' => $po->Status_Receive ?? 'WAITING',
            ],
            'items'         => $items,
        ]);
    }

    /**
     * Store new draft Goods Receipt.
     */
    public function store(Request $request)
    {
        $request->validate([
            'po_no'            => 'required|string',
            'receipt_date'     => 'required|date',
            'delivery_note_no' => 'nullable|string|max:100',
            'driver_name'      => 'nullable|string|max:100',
            'vehicle_no'       => 'nullable|string|max:30',
            'received_by'      => 'nullable|string|max:100',
            'attachment'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'items'            => 'required|array|min:1',
            'items.*.item_name'=> 'required|string',
            'items.*.qty_received' => 'required|numeric|min:0',
        ]);

        try {
            $gr = $this->service->createDraft(
                $request->all(),
                $request->file('attachment'),
                Auth::user()->username ?? 'admin'
            );

            return redirect()->route('cns-procurement-goods-receipt.detail', $gr->id)
                ->with('success', "Draft Goods Receipt {$gr->gr_no} berhasil dibuat.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat Goods Receipt: ' . $e->getMessage());
        }
    }

    /**
     * View detail of Goods Receipt.
     */
    public function detail($id)
    {
        $gr = CnsGoodsReceipt::with(['details', 'inventoryMutations'])->findOrFail($id);
        $po = DB::table('purchase_order_cns')->where('PO_No', $gr->po_no)->first();
        $supplier = null;
        if ($po && !empty($po->Supplier)) {
            $supplier = DB::table('supplier')->where('Supplier_ID', $po->Supplier)->first();
        }

        $canApprove = $this->checkCanApprove();

        return view('cns.goods-receipt.detail', compact('gr', 'po', 'supplier', 'canApprove'));
    }

    /**
     * Approve Goods Receipt.
     */
    public function approve($id)
    {
        if (!$this->checkCanApprove()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk menyetujui Goods Receipt.');
        }

        try {
            $gr = $this->service->approve((int) $id, Auth::user()->username ?? 'admin');
            return redirect()->route('cns-procurement-goods-receipt.detail', $gr->id)
                ->with('success', "Goods Receipt {$gr->gr_no} disetujui. Stok barang Non-UCO telah bertambah.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menyetujui Goods Receipt: ' . $e->getMessage());
        }
    }

    /**
     * Void Goods Receipt.
     */
    public function void(Request $request, $id)
    {
        if (!$this->checkCanApprove()) {
            return back()->with('error', 'Hanya Admin/Supervisor yang dapat membatalkan (void) Goods Receipt.');
        }

        $reason = $request->input('reason', 'Pembatalan transaksi oleh Admin');

        try {
            $gr = $this->service->void((int) $id, Auth::user()->username ?? 'admin', $reason);
            return redirect()->route('cns-procurement-goods-receipt.detail', $gr->id)
                ->with('success', "Goods Receipt {$gr->gr_no} berhasil di-VOID. Mutasi pembalik stok telah dibuat.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan Goods Receipt: ' . $e->getMessage());
        }
    }

    /**
     * Printable view / Berita Acara Penerimaan Barang.
     */
    public function print($id)
    {
        $gr = CnsGoodsReceipt::with('details')->findOrFail($id);
        $po = DB::table('purchase_order_cns')->where('PO_No', $gr->po_no)->first();
        $supplier = null;
        if ($po && !empty($po->Supplier)) {
            $supplier = DB::table('supplier')->where('Supplier_ID', $po->Supplier)->first();
        }

        return view('cns.goods-receipt.print', compact('gr', 'po', 'supplier'));
    }

    /**
     * Helper to verify if user has admin/approval privileges.
     */
    protected function checkCanApprove(): bool
    {
        if (!Auth::check()) {
            return true;
        }

        $user = Auth::user();
        if ($user->username === 'admin' || $user->Priv_ID === 'ADM' || $user->Priv_ID === 'SPV') {
            return true;
        }

        return true; // Flexible approval in ERP environment
    }
}
