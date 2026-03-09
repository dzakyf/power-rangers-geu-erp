<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterStockPrice;
use App\MasterWarehouseAccount;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerJournal;
use App\WarehousePartnerSupplies;
use App\WarehouseStockRequest;
use Illuminate\Http\Request;

class ProfitSharingController extends Controller
{
    public function indexProfitSharing()
    {
        return view('partner.profit-sharing');
    }

    public function indexProfitSharingXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;

        if ($priv == 'PARTNER') {
            $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        }else {
            $get = WarehousePartner::get();
        }
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->partner_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function lkMonthlyProfitSharing(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;
        $calculate = [];

        //Hitung Pendaptan
        $calculate[401][40001] = 0;
        $calculate[401][40002] = MasterStockPrice::where('date_for', 'LIKE', "$request->date%")->avg('price');
        $calculate[501][50001] = WarehouseStockRequest::where('request_date', 'LIKE', $request->date.'%')->sum('request_qty');
        $calculate[501][50002] = WarehousePartnerSupplies::where('received_date', 'LIKE', $request->date.'%')->avg('price');

        $whs = WarehousePartnerSupplies::where("warehouse_task_id", $request->Task)->where('received_date', 'LIKE', $request->date.'%')->get();
        $supplies = [];
        foreach ($whs as $key => $value) {
            $month = date('Y-m', strtotime($value->received_date));
            if (!isset($supplies['qty'])) {
                $supplies['qty'] = 0;
            }
            if (!isset($supplies['avg'])) {
                $supplies['avg'] = $value->price;
            }
            $supplies['avg'] = (($supplies['avg'] * $supplies['qty']) + $value->total) / ($supplies['qty'] + $value->received_qty);
        }
        $calculate[501][50002] = $supplies['avg'];

        $calculate[401][40001] = $calculate[401][40002] * $calculate[501][50001];
        $calculate[501][50000] = $calculate[501][50001] * $calculate[501][50002];

        // Hitung Opex
        $getAccount = MasterWarehouseAccount::get();
        foreach ($getAccount as $key => $value) {
            if (!isset($calculate[$value->group_account])) {
                $calculate[$value->group_account] = [];
            }
            if (!isset($calculate[$value->group_account][$value->account_no])) {
                $calculate[$value->group_account][$value->account_no] = 0;
            }

            $getOpex = WarehousePartnerJournal::where('warehouse_task_id', $request->Task)
                ->where('posting_date', 'LIKE', $request->date.'%')
                ->where('account_no', $value->account_no)
                ->where('group_account', $value->group_account)
                ->sum('nominal');

            $calculate[$value->group_account][$value->account_no] += $getOpex;
        }
        
        foreach ($calculate as $key => $Fetch) {
            echo "<row id=\"$key\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch[400001]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function lkMonthlyProfitSharingXml()
    {
        return view('partner.profit-sharing-lk-monthly');
    }

    public function lkAnnuallyProfitSharing()
    {
        return view('partner.profit-sharing-lk-annually');
    }
}
