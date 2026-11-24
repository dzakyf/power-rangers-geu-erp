<?php

namespace App\Http\Controllers\CNS;

use App\CnsFlexibagInventory;
use App\CnsInventoryNonUco;
use App\Http\Controllers\Controller;
use App\Services\CNS\FlexibagInventoryService;
use App\Support\DhtmlxGridPagination;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FlexibagInventoryController extends Controller
{
    protected $service;

    public function __construct(FlexibagInventoryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $currentBalance = $this->service->getLatestBalance();
        return view('cns.inventory-flexibag.index', compact('currentBalance'));
    }

    public function xmlData(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        if (Schema::hasTable('cns_inventory_non_uco')) {
            $query = CnsInventoryNonUco::with('vesselSchedule')
                ->where(function ($q) {
                    $q->where('category', 'Flexibag')
                      ->orWhere('item_name', 'LIKE', '%Flexibag%');
                })
                ->orderBy('transaction_date', 'asc')
                ->orderBy('id', 'asc');
        } else {
            $query = CnsFlexibagInventory::with('vesselSchedule')
                ->orderBy('transaction_date', 'asc')
                ->orderBy('id', 'asc');
        }

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        // Column filters from dhtmlx header
        if ($request->has('filters') && is_array($request->query('filters'))) {
            foreach ($request->query('filters') as $colIdx => $val) {
                $val = trim((string) $val);
                if ($val === '') continue;
                if ($colIdx == 1) $query->whereDate('transaction_date', $val);
                if ($colIdx == 2) $query->where('reference_no', 'like', "%{$val}%");
                if ($colIdx == 3) $query->where('type', $val);
                if ($colIdx == 7) $query->where('notes', 'like', "%{$val}%");
                if ($colIdx == 8) $query->where('created_by', 'like', "%{$val}%");
            }
        }

        $colMap = [
            1 => 'transaction_date',
            2 => 'reference_no',
            3 => 'type',
            4 => 'qty_in',
            5 => 'qty_out',
            6 => 'current_balance',
            7 => 'notes',
            8 => 'created_by',
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

        if ($startDate && $posStart === 0) {
            if (Schema::hasTable('cns_inventory_non_uco')) {
                $priorRecord = CnsInventoryNonUco::where(function ($q) {
                        $q->where('category', 'Flexibag')
                          ->orWhere('item_name', 'LIKE', '%Flexibag%');
                    })
                    ->whereDate('transaction_date', '<', $startDate)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $priorRecord = CnsFlexibagInventory::whereDate('transaction_date', '<', $startDate)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
            }

            $initialBalance = $priorRecord ? (float) $priorRecord->current_balance : 0.0;
            $virtualDate = Carbon::parse($startDate)->format('d/m/Y');
            $formattedInitial = number_format((float) $initialBalance, 2, '.', ',');

            $bodyXml .= '<row id="virtual_saldo_awal" style="font-weight:bold; background-color:#e8f4fd;">';
            $bodyXml .= '<cell>' . $no++ . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($virtualDate) . '</cell>';
            $bodyXml .= '<cell>SALDO AWAL PERIODE</cell>';
            $bodyXml .= '<cell>SALDO AWAL</cell>';
            $bodyXml .= '<cell>0.00</cell>';
            $bodyXml .= '<cell>0.00</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($formattedInitial) . '</cell>';
            $bodyXml .= '<cell>Saldo akhir bulan sebelumnya</cell>';
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
            $bodyXml .= '<cell>' . htmlspecialchars($ref) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($type) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($qtyIn) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($qtyOut) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($balance) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($notes) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($createdBy) . '</cell>';
            $bodyXml .= '</row>' . "\n";
        }

        return DhtmlxGridPagination::response($total, $posStart, $bodyXml);
    }

    public function balanceJson(Request $request)
    {
        $startDate = $request->query('start_date');
        $initialBalance = 0.0;

        if ($startDate) {
            if (Schema::hasTable('cns_inventory_non_uco')) {
                $priorRecord = CnsInventoryNonUco::where(function ($q) {
                        $q->where('category', 'Flexibag')
                          ->orWhere('item_name', 'LIKE', '%Flexibag%');
                    })
                    ->whereDate('transaction_date', '<', $startDate)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $priorRecord = CnsFlexibagInventory::whereDate('transaction_date', '<', $startDate)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
            }
            $initialBalance = $priorRecord ? (float) $priorRecord->current_balance : 0.0;
        }

        return response()->json([
            'status' => 'success',
            'balance' => $this->service->getLatestBalance(),
            'initial_balance' => $initialBalance,
        ]);
    }
}
