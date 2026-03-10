<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterStockPrice;
use App\User;
use App\WarehousePartner;
use App\WarehouseStockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseStockRequestController extends Controller
{

    public function indexWarehouseStockRequest()
    {
        return view('partner.warehouse.stock-request');
    }

    public function indexWarehouseStockRequestXml()
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
            $usname[$value->Emp_ID] = $value->Name;
        }
        $getPartner = DB::table('opportunity_partner')->get();
        foreach ($getPartner as $key => $value) {
            $usname[$value->lead_id] = $value->full_name;
        }
        $nomor = 1;

        if ($priv == 'PARTNER') {
            $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        }else {
            $get = WarehousePartner::get();
        }
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->partner_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailWarehouseStockRequest(Request $request)
    {
        return view('partner.warehouse.stock-request-detail');
    }

    public function detailWarehouseStockRequestXml(Request $request)
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

        $get = WarehouseStockRequest::where('warehouse_task_id', $request->Task)->get();
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->request_date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->request_qty) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->price) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->total) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->reference) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->remark) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->created_by]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailWarehouseStockRequestNew(Request $request)
    {
        $task = $request->Task;
        $masterPrice = MasterStockPrice::orderBy("date_for", 'desc')->orderBy("created_date", 'desc')->first();

        return view('partner.warehouse.stock-request-detail-new', compact('masterPrice', 'task'));
    }

    public function detailWarehouseStockRequestNewSubmit(Request $request)
    {
        $r = $request->all();

        unset($r['_token']);

        $r['created_by'] = auth()->user()->username;
        $r['created_date'] = now();
        $r['request_qty'] = $r['proposed_qty'];

        if ($r['reference'] == null) {
            $r['reference'] = '';
        }
        if ($r['remark'] == null) {
            $r['remark'] = '';
        }

        WarehouseStockRequest::create($r);

        echo "success";
    }
}
