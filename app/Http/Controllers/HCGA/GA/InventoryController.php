<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use App\Services\GA\GaInventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    use GaXml;

    protected $inventory;

    public function __construct(GaInventoryService $inventory)
    {
        $this->inventory = $inventory;
    }

    public function lowStockIndex()
    {
        abort_unless(can_menu('SI01014', 'view'), 403);

        return view('humanresources.ga.inventory.low-stock', [
            'locations' => $this->locationOptions(),
            'warehouses' => $this->warehouseOptions(),
            'defaultLocation' => (string) Auth::user()->Location,
        ]);
    }

    public function lowStockData(Request $request)
    {
        abort_unless(can_menu('SI01014', 'view'), 403);

        $rows = $this->inventory->lowStockRows($this->filters($request));

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            $onHand = (float) $row->On_Hand;
            $minQty = (float) $row->Min_Qty;
            $status = $onHand <= 0 ? 'HABIS' : 'DI BAWAH MINIMUM';

            echo '<row id="' . $this->xml($row->Product_Code) . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Product_Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Description) . '</cell>';
            echo '<cell>' . $this->xml($row->Unit_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Location ?: '-') . '</cell>';
            echo '<cell>' . $this->number($onHand, 2) . '</cell>';
            echo '<cell>' . $this->number($minQty, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Shortage, 2) . '</cell>';
            echo '<cell>' . $this->xml($status) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function minimumStockIndex(Request $request)
    {
        abort_unless(can_menu('SI01016', 'view'), 403);

        return view('humanresources.ga.inventory.minimum-stock', [
            'locations' => $this->locationOptions(),
            'warehouses' => $this->warehouseOptions(),
            'products' => $this->productOptions(),
            'defaultLocation' => (string) $request->query('Location', Auth::user()->Location),
            'defaultWhId' => (string) $request->query('WH_ID', ''),
            'defaultProductCode' => (string) $request->query('Product_Code', ''),
        ]);
    }

    public function minimumStockData(Request $request)
    {
        abort_unless(can_menu('SI01016', 'view'), 403);

        $rows = $this->inventory->minimumStockRows($this->filters($request));

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->id . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Product_Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Description) . '</cell>';
            echo '<cell>' . $this->xml($row->Location ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->WH_ID ?: '-') . '</cell>';
            echo '<cell>' . $this->number((float) $row->Min_Qty, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Target_Qty, 2) . '</cell>';
            echo '<cell>' . ((int) $row->Is_Active === 1 ? 'Ya' : 'Tidak') . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function saveMinimumStock(Request $request)
    {
        abort_unless(can_menu('SI01016', 'edit'), 403);

        $data = $request->validate([
            'Product_Code' => 'required|string|max:60',
            'Location' => 'nullable|string|max:50',
            'WH_ID' => 'nullable|string|max:50',
            'Min_Qty' => 'nullable|numeric|min:0',
            'Target_Qty' => 'nullable|numeric|min:0',
            'Is_Active' => 'nullable',
        ]);

        try {
            $row = $this->inventory->saveMinimumStock($data, Auth::user());

            return response()->json([
                'status' => 'success',
                'message' => 'Minimum stock berhasil disimpan.',
                'row' => $row,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('GA inventory minimum save failed', ['message' => $exception->getMessage()]);

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function ledgerIndex()
    {
        abort_unless(can_menu('SI01015', 'view'), 403);

        return view('humanresources.ga.inventory.ledger', [
            'locations' => $this->locationOptions(),
            'warehouses' => $this->warehouseOptions(),
            'defaultLocation' => (string) Auth::user()->Location,
            'defaultDateFrom' => Carbon::now()->startOfMonth()->toDateString(),
            'defaultDateTo' => Carbon::now()->toDateString(),
        ]);
    }

    public function ledgerData(Request $request)
    {
        abort_unless(can_menu('SI01015', 'view'), 403);

        $filters = $this->filters($request);
        $query = DB::table('ga_stock_ledger as l')
            ->leftJoin('product_code as pc', 'pc.Product_Code', '=', 'l.Product_Code')
            ->select('l.*', 'pc.Description', 'pc.Unit_Name')
            ->when($filters['product_code'] !== '', function ($q) use ($filters) {
                $q->where('l.Product_Code', $filters['product_code']);
            })
            ->when($filters['location'] !== '', function ($q) use ($filters) {
                $q->where('l.Location', $filters['location']);
            })
            ->when($filters['wh_id'] !== '', function ($q) use ($filters) {
                $q->where('l.WH_ID', $filters['wh_id']);
            })
            ->when($filters['date_from'] !== '', function ($q) use ($filters) {
                $q->where('l.Movement_Date', '>=', $filters['date_from']);
            })
            ->when($filters['date_to'] !== '', function ($q) use ($filters) {
                $q->where('l.Movement_Date', '<=', $filters['date_to']);
            })
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $q->where(function ($sub) use ($filters) {
                    $sub->where('l.Product_Code', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('pc.Description', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->orderByDesc('l.Movement_Date')
            ->orderByDesc('l.id')
            ->limit(2000);

        $rows = $query->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            $source = trim((string) $row->Source_Type);
            if ($source !== '' && trim((string) $row->Source_ID) !== '') {
                $source .= ' / ' . $row->Source_ID;
            }
            if ($source === '') {
                $source = '-';
            }

            echo '<row id="' . (int) $row->id . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Movement_Date) . '</cell>';
            echo '<cell>' . $this->xml($row->Movement_Type) . '</cell>';
            echo '<cell>' . $this->xml($row->Product_Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Description) . '</cell>';
            echo '<cell>' . $this->xml($row->Location ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->WH_ID ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Bin ?: '-') . '</cell>';
            echo '<cell>' . $this->number((float) $row->Qty_In, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Qty_Out, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Unit_Cost, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Running_Qty, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Running_Value, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Avg_Cost, 4) . '</cell>';
            echo '<cell>' . $this->xml($source) . '</cell>';
            echo '<cell>' . $this->xml($row->Created_By ?: '-') . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function backfillOpening(Request $request)
    {
        abort_unless(can_menu('SI01015', 'edit'), 403);

        $data = $request->validate([
            'Movement_Date' => 'nullable|date',
            'Location' => 'nullable|string|max:50',
            'WH_ID' => 'nullable|string|max:50',
        ]);

        try {
            $result = $this->inventory->backfillOpeningBalance(
                $data['Movement_Date'] ?? null,
                $data['Location'] ?? null,
                $data['WH_ID'] ?? null,
                Auth::user()
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Backfill opening selesai: ' . $result['created'] . ' dibuat, ' . $result['skipped'] . ' dilewati.',
                'result' => $result,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('GA inventory opening backfill failed', ['message' => $exception->getMessage()]);

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function cutoffIndex()
    {
        abort_unless(can_menu('SI01017', 'view'), 403);

        return view('humanresources.ga.inventory.cutoff', [
            'locations' => $this->locationOptions(),
            'warehouses' => $this->warehouseOptions(),
            'defaultLocation' => (string) Auth::user()->Location,
            'defaultAsOf' => Carbon::now()->toDateString(),
        ]);
    }

    public function cutoffData(Request $request)
    {
        abort_unless(can_menu('SI01017', 'view'), 403);

        $rows = $this->inventory->cutoffRows($this->filters($request));

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->id . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Product_Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Description) . '</cell>';
            echo '<cell>' . $this->xml($row->Unit_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Location ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->WH_ID ?: '-') . '</cell>';
            echo '<cell>' . $this->number((float) $row->Running_Qty, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Running_Value, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Avg_Cost, 4) . '</cell>';
            echo '<cell>' . $this->xml($row->Movement_Date) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function ytdIndex()
    {
        abort_unless(can_menu('SI01025', 'view'), 403);

        return view('humanresources.ga.inventory.ytd', [
            'locations' => $this->locationOptions(),
            'warehouses' => $this->warehouseOptions(),
            'defaultLocation' => (string) Auth::user()->Location,
            'defaultYear' => date('Y'),
            'defaultAsOf' => Carbon::now()->toDateString(),
        ]);
    }

    public function ytdData(Request $request)
    {
        abort_unless(can_menu('SI01025', 'view'), 403);

        $filters = $this->filters($request);
        $filters['year'] = $request->query('year', date('Y'));
        $rows = $this->inventory->ytdStockRows($filters);

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $idx => $row) {
            echo '<row id="' . ($idx + 1) . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Product_Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Description) . '</cell>';
            echo '<cell>' . $this->xml($row->Unit_Name) . '</cell>';
            echo '<cell>' . $this->xml($row->Location) . '</cell>';
            echo '<cell>' . $this->xml($row->WH_ID) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Beginning_Qty, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Beginning_Value, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->YTD_In, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->YTD_Out, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Ending_Qty, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Ending_Value, 2) . '</cell>';
            echo '<cell>' . $this->number((float) $row->Avg_Cost, 4) . '</cell>';
            echo '<cell>' . $this->xml($row->As_Of) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    protected function filters(Request $request)
    {
        return [
            'location' => trim((string) $request->query('location', '')),
            'wh_id' => trim((string) $request->query('wh_id', '')),
            'search' => trim((string) $request->query('search', '')),
            'product_code' => trim((string) $request->query('product_code', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
            'as_of' => trim((string) $request->query('as_of', '')),
        ];
    }

    protected function locationOptions()
    {
        return DB::table('product_code')
            ->select('Location')
            ->whereNotNull('Location')
            ->where('Location', '<>', '')
            ->distinct()
            ->orderBy('Location')
            ->pluck('Location');
    }

    protected function warehouseOptions()
    {
        return DB::table('supplies')
            ->select('WH_ID')
            ->whereNotNull('WH_ID')
            ->where('WH_ID', '<>', '')
            ->distinct()
            ->orderBy('WH_ID')
            ->pluck('WH_ID');
    }

    protected function productOptions()
    {
        return DB::table('product_code')
            ->select('Product_Code', 'Description')
            ->whereNotNull('Product_Code')
            ->where('Product_Code', '<>', '')
            ->orderBy('Product_Code')
            ->limit(1000)
            ->get();
    }

    protected function number($value, $decimals = 2)
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return number_format((float) $value, $decimals, '.', '');
    }

    public function reverseStockRequest(Request $request)
    {
        $srNo = $request->input('SR_No');
        $reason = $request->input('Reason', '');

        try {
            $this->inventory->reverseStockRequest($srNo, $reason, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Stock Request ' . $srNo . ' berhasil di-reversal.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function confirmHandover(Request $request)
    {
        $srNo = $request->input('SR_No');

        try {
            $updated = $this->inventory->confirmHandover($srNo, $request->all(), Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Handover Stock Request ' . $srNo . ' berhasil dikonfirmasi.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function transferStockIndex()
    {
        $locations = $this->locationOptions();
        $warehouses = $this->warehouseOptions();
        $products = $this->productOptions();

        return view('humanresources.ga.inventory.transfer-stock', compact('locations', 'warehouses', 'products'));
    }

    public function transferStockSave(Request $request)
    {
        try {
            $result = $this->inventory->transferStock($request->all(), Auth::user());
            return response()->json([
                'status' => 'success',
                'message' => 'Transfer stok berhasil dicatat dengan No: ' . $result['transfer_no'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function reusableIndex()
    {
        $locations = $this->locationOptions();
        $warehouses = $this->warehouseOptions();
        $products = $this->productOptions();

        return view('humanresources.ga.inventory.reusable-index', compact('locations', 'warehouses', 'products'));
    }

    public function reusableUnitData(Request $request)
    {
        $units = DB::table('ga_reusable_unit as u')
            ->leftJoin('product_code as p', 'p.Product_Code', '=', 'u.Product_Code')
            ->select('u.*', 'p.Description as Product_Description')
            ->orderBy('u.id', 'DESC')
            ->get();

        header('Content-Type: text/xml');
        echo '<rows>';
        $no = 1;
        foreach ($units as $u) {
            echo '<row id="' . (int) $u->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($u->Unit_Code) . '</cell>';
            echo '<cell>' . $this->xml($u->Product_Code) . '</cell>';
            echo '<cell>' . $this->xml($u->Product_Description ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($u->Serial_Number ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($u->Asset_No ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($u->Condition_Status) . '</cell>';
            echo '<cell>' . $this->xml($u->Loan_Status) . '</cell>';
            echo '<cell>' . $this->xml($u->Current_Location ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($u->Current_Holder_Name ?: '-') . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function reusableLoanData(Request $request)
    {
        $loans = DB::table('ga_reusable_loan as l')
            ->join('ga_reusable_unit as u', 'u.id', '=', 'l.Unit_ID')
            ->leftJoin('product_code as p', 'p.Product_Code', '=', 'u.Product_Code')
            ->select('l.*', 'u.Unit_Code', 'u.Product_Code', 'p.Description as Product_Description')
            ->orderBy('l.id', 'DESC')
            ->get();

        header('Content-Type: text/xml');
        echo '<rows>';
        $no = 1;
        foreach ($loans as $l) {
            echo '<row id="' . (int) $l->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($l->Loan_No) . '</cell>';
            echo '<cell>' . $this->xml($l->Unit_Code) . '</cell>';
            echo '<cell>' . $this->xml($l->Product_Description ?: $l->Product_Code) . '</cell>';
            echo '<cell>' . $this->xml($l->Borrower_Name) . '</cell>';
            echo '<cell>' . $this->xml($l->Borrower_Department ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($l->Date_Out) . '</cell>';
            echo '<cell>' . $this->xml($l->Due_Date) . '</cell>';
            echo '<cell>' . $this->xml($l->Status) . '</cell>';
            echo '<cell>' . $this->xml($l->Returned_At ?: '-') . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function reusableSaveUnit(Request $request)
    {
        try {
            $unit = $this->inventory->registerReusableUnit($request->all(), Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Unit reusable berhasil didaftarkan.', 'data' => $unit]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function reusableSaveLoan(Request $request)
    {
        try {
            $loan = $this->inventory->loanReusableUnit($request->all(), Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Peminjaman unit berhasil dicatat dengan No: ' . $loan->Loan_No, 'data' => $loan]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function reusableSaveReturn(Request $request)
    {
        try {
            $return = $this->inventory->returnReusableUnit($request->all(), Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Pengembalian unit berhasil dicatat.', 'data' => $return]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function adjustmentIndex()
    {
        $locations = $this->locationOptions();
        $warehouses = $this->warehouseOptions();
        $products = $this->productOptions();

        return view('humanresources.ga.inventory.adjustment', compact('locations', 'warehouses', 'products'));
    }

    public function adjustmentData(Request $request)
    {
        $adjustments = DB::table('ga_stock_adjustment as a')
            ->leftJoin('product_code as p', 'p.Product_Code', '=', 'a.Product_Code')
            ->select('a.*', 'p.Description as Product_Description')
            ->orderBy('a.id', 'DESC')
            ->limit(500)
            ->get();

        header('Content-Type: text/xml');
        echo '<rows>';
        $no = 1;
        foreach ($adjustments as $a) {
            echo '<row id="' . (int) $a->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($a->Adjustment_No) . '</cell>';
            echo '<cell>' . $this->xml($a->Adjustment_Date) . '</cell>';
            echo '<cell>' . $this->xml($a->Product_Code) . '</cell>';
            echo '<cell>' . $this->xml($a->Product_Description ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($a->Type) . '</cell>';
            echo '<cell>' . $this->number($a->Qty, 2) . '</cell>';
            echo '<cell>' . $this->number($a->Unit_Cost, 2) . '</cell>';
            echo '<cell>' . $this->number($a->Total_Value, 2) . '</cell>';
            echo '<cell>' . $this->xml($a->Location) . '</cell>';
            echo '<cell>' . $this->xml($a->Reason ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($a->Created_By) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function saveAdjustment(Request $request)
    {
        try {
            $adj = $this->inventory->recordStockAdjustment($request->all(), Auth::user());
            return response()->json([
                'status' => 'success',
                'message' => 'Penyesuaian stok berhasil dicatat dengan No: ' . $adj->Adjustment_No . ' (Nilai: Rp ' . number_format($adj->Total_Value, 2) . ')',
                'data' => $adj
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function generatePurchaseRequestFromLowStock(Request $request)
    {
        $codes = (array) $request->input('product_codes', []);
        try {
            $pr = $this->inventory->generateDraftPurchaseRequest($codes, $request->all(), Auth::user());
            return response()->json([
                'status' => 'success',
                'message' => 'Draft Purchase Request berhasil dibuat dengan No: ' . $pr->PR_No . ' (Estimasi: Rp ' . number_format($pr->Estimated_Budget, 2) . ')',
                'pr_no' => $pr->PR_No,
                'id' => $pr->ID
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
