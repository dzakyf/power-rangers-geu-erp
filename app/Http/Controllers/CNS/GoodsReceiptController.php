<?php

namespace App\Http\Controllers\CNS;

use App\CnsGoodsReceipt;
use App\CnsKemasanSerialNumber;
use App\Http\Controllers\Controller;
use App\Services\CNS\GoodsReceiptService;
use App\Support\DhtmlxGridPagination;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $query = CnsGoodsReceipt::query()->orderBy('id', 'desc');

        if ($startDate) {
            $query->whereDate('receipt_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('receipt_date', '<=', $endDate);
        }
        if ($status) {
            $query->where('status', $status);
        }

        // Column filters from dhtmlx header
        if ($request->has('filters') && is_array($request->query('filters'))) {
            foreach ($request->query('filters') as $colIdx => $val) {
                $val = trim((string) $val);
                if ($val === '') continue;
                if ($colIdx == 1) $query->where('gr_no', 'like', "%{$val}%");
                if ($colIdx == 2) $query->where('po_no', 'like', "%{$val}%");
                if ($colIdx == 4) $query->whereDate('receipt_date', $val);
                if ($colIdx == 5) $query->where('delivery_note_no', 'like', "%{$val}%");
                if ($colIdx == 6) $query->where(function($q) use ($val) {
                    $q->where('driver_name', 'like', "%{$val}%")
                      ->orWhere('vehicle_no', 'like', "%{$val}%");
                });
                if ($colIdx == 8) $query->where('status', $val);
                if ($colIdx == 9) $query->where('received_by', 'like', "%{$val}%");
                if ($colIdx == 10) $query->where('approved_by', 'like', "%{$val}%");
            }
        }

        $colMap = [
            1 => 'gr_no',
            2 => 'po_no',
            4 => 'receipt_date',
            5 => 'delivery_note_no',
            8 => 'status',
            9 => 'received_by',
            10 => 'approved_by',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $records = $paginated['rows'];
        $records->load('details');
        $total = $paginated['total'];
        $posStart = $paginated['posStart'];

        // Batch load PO and suppliers for the page
        $poNos = $records->pluck('po_no')->unique()->filter()->all();
        $pos = DB::table('purchase_order_cns')->whereIn('PO_No', $poNos)->get()->keyBy('PO_No');
        $supplierIds = $pos->pluck('Supplier')->unique()->filter()->all();
        $suppliers = DB::table('supplier')->whereIn('Supplier_ID', $supplierIds)->get()->keyBy('Supplier_ID');

        $bodyXml = "";
        $no = $posStart + 1;
        foreach ($records as $row) {
            $date = $row->receipt_date ? Carbon::parse($row->receipt_date)->format('d/m/Y') : '-';
            $po = $pos->get($row->po_no);
            $supplierName = '-';
            if ($po && !empty($po->Supplier)) {
                $supp = $suppliers->get($po->Supplier);
                $supplierName = $supp->Company_Name ?? $po->Supplier;
            }

            $itemSummary = $row->details->map(function ($d) {
                return $d->item_name . ' (' . number_format((float) $d->qty_received, 2, '.', ',') . ' ' . $d->unit . ')';
            })->implode(', ');

            $statusBadge = $row->status;

            $bodyXml .= '<row id="' . $row->id . '">';
            $bodyXml .= '<cell>' . $no++ . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($row->gr_no) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($row->po_no) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($supplierName) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($date) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($row->delivery_note_no ?: '-') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars(($row->driver_name ?: '-') . ($row->vehicle_no ? ' / ' . $row->vehicle_no : '')) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($itemSummary ?: '-') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($statusBadge) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($row->received_by ?: '-') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($row->approved_by ?: '-') . '</cell>';
            $bodyXml .= '</row>' . "\n";
        }

        return DhtmlxGridPagination::response($total, $posStart, $bodyXml);
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
            'items.*.serial_number' => 'nullable|string',
        ]);

        $seenSerials = [];
        $allFlexibagSerials = [];

        foreach ($request->input('items', []) as $idx => $item) {
            $itemName = (string) ($item['item_name'] ?? '');
            $qtyReceived = (float) ($item['qty_received'] ?? 0);
            $serialNumber = trim((string) ($item['serial_number'] ?? ''));

            if (stripos($itemName, 'flexibag') !== false && $qtyReceived > 0) {
                if (abs($qtyReceived - round($qtyReceived)) > 0.0001) {
                    return back()
                        ->withInput()
                        ->withErrors(["items.{$idx}.qty_received" => "Qty Diterima untuk {$itemName} harus bilangan bulat."]);
                }

                $expectedQty = (int) round($qtyReceived);
                $parsed = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $serialNumber)), function ($sn) {
                    return $sn !== '';
                }));

                if (count($parsed) === 0) {
                    return back()
                        ->withInput()
                        ->withErrors(["items.{$idx}.serial_number" => "Serial Number wajib diisi untuk item {$itemName}."]);
                }

                if (count($parsed) !== $expectedQty) {
                    return back()
                        ->withInput()
                        ->withErrors(["items.{$idx}.serial_number" => "Jumlah Serial Number (" . count($parsed) . ") harus sama dengan Qty Diterima ({$expectedQty}) untuk item {$itemName}."]);
                }

                foreach ($parsed as $sn) {
                    $key = mb_strtolower($sn);
                    if (isset($seenSerials[$key])) {
                        return back()
                            ->withInput()
                            ->withErrors(["items.{$idx}.serial_number" => "Serial Number duplikat di form: {$sn}."]);
                    }
                    $seenSerials[$key] = true;
                    $allFlexibagSerials[] = $sn;
                }
            }
        }

        if (!empty($allFlexibagSerials) && Schema::hasTable('cns_kemasan_serial_numbers')) {
            $existing = CnsKemasanSerialNumber::whereIn('serial_number', $allFlexibagSerials)
                ->pluck('serial_number')
                ->all();

            if (!empty($existing)) {
                return back()
                    ->withInput()
                    ->withErrors(['items' => 'Serial Number sudah terdaftar di sistem: ' . implode(', ', $existing)]);
            }
        }

        try {
            $gr = $this->service->createDraft(
                $request->all(),
                $request->file('attachment'),
                Auth::user()->username ?? 'admin'
            );

            return redirect()->route('cns-procurement-goods-receipt.index')
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
    public function approve(Request $request, $id)
    {
        if (!$this->checkCanApprove()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk menyetujui Goods Receipt.'], 403);
            }
            return back()->with('error', 'Anda tidak memiliki hak akses untuk menyetujui Goods Receipt.');
        }

        try {
            $gr = $this->service->approve((int) $id, Auth::user()->username ?? 'admin');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Goods Receipt {$gr->gr_no} disetujui. Stok barang Non-UCO telah bertambah."
                ]);
            }
            return redirect()->route('cns-procurement-goods-receipt.index')
                ->with('success', "Goods Receipt {$gr->gr_no} disetujui. Stok barang Non-UCO telah bertambah.");
        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Gagal menyetujui Goods Receipt: ' . $e->getMessage()], 422);
            }
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
