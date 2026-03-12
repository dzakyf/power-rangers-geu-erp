<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerSupplies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseOpenController extends Controller
{
    

    public function indexWarehouseOpen()
    {
        return view('partner.warehouse.open');
    }

    public function indexWarehouseOpenXml()
    {

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;

        // $getPartnerID = DB::table('opportunity_partner')->where("lead_id", auth()->user()->Emp_ID)->first();
        $wh = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->first();
        
        $get = WarehousePartnerSupplies::where('warehouse_task_id', $wh->warehouse_task_id)->get();
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->received_date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->pickup_type) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->name_poo) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->source) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->coverage) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->received_qty) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->price) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->total) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->rejected_qty) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->attachment) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }
    

    public function newWarehouseOpen(Request $request)
    {
        $wh = WarehousePartner::where("partner_task_id", auth()->user()->Emp_ID)->first();
        $kelurahan = DB::table('master_kodepos')->where('City', $wh->warehouse_location)->select('District')->groupBy("District")->get();

        return view('partner.warehouse.open-new', compact('wh', 'kelurahan'));
    }

    public function newWarehouseOpenSubmit(Request $request)
    {
        // WarehousePartner::where();
        $r = $request->all();

        unset($r['_token']);

        $r['created_by'] = auth()->user()->username;
        $r['created_date'] = now();

        WarehousePartnerSupplies::create($r);

        echo 'success';
    }

    public function detailWarehouseOpen(Request $request)
    {
        $wh = WarehousePartner::where("id", $request->Task)->first();
        $kelurahan = DB::table('master_kodepos')->where('City', $wh->warehouse_location)->select('District')->groupBy("District")->get();
        $wh = WarehousePartnerSupplies::where("id", $request->Task)->first();
        return view('partner.warehouse.open-detail', compact('wh', 'kelurahan'));
    }

    public function detailWarehouseOpenSubmit(Request $request)
    {
        // WarehousePartner::where();
        $r = $request->all();

        unset($r['id']);
        unset($r['_token']);

        WarehousePartnerSupplies::where('id', $request->id)->update($r);

        echo 'success';
    }
}
