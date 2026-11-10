<?php

namespace App\Http\Controllers\CNS;

use App\CnsFlexibagInventory;
use App\CnsInventoryNonUco;
use App\Http\Controllers\Controller;
use App\Services\CNS\FlexibagInventoryService;
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

        $records = $query->get();

        header('Content-Type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        echo "<rows>";

        $no = 1;

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
            $virtualDate = Carbon::parse($startDate)->format('d/m/Y');
            $formattedInitial = number_format($initialBalance, 0, ',', '.');

            echo '<row id="virtual_saldo_awal" style="font-weight:bold; background-color:#e8f4fd;">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . htmlspecialchars($virtualDate) . '</cell>';
            echo '<cell>SALDO AWAL PERIODE</cell>';
            echo '<cell>SALDO AWAL</cell>';
            echo '<cell>0</cell>';
            echo '<cell>0</cell>';
            echo '<cell>' . htmlspecialchars($formattedInitial) . '</cell>';
            echo '<cell>Saldo akhir bulan sebelumnya</cell>';
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
            echo '<cell>' . htmlspecialchars($ref) . '</cell>';
            echo '<cell>' . htmlspecialchars($type) . '</cell>';
            echo '<cell>' . htmlspecialchars($qtyIn) . '</cell>';
            echo '<cell>' . htmlspecialchars($qtyOut) . '</cell>';
            echo '<cell>' . htmlspecialchars($balance) . '</cell>';
            echo '<cell>' . htmlspecialchars($notes) . '</cell>';
            echo '<cell>' . htmlspecialchars($createdBy) . '</cell>';
            echo '</row>';
        }

        echo "</rows>";
        exit;
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
