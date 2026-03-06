<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use App\WarehosuePartner;
use App\WarehosuePartnerSupplies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function indexWarehouseList()
    {
        return view('partner.warehouse.list');
    }

    public function indexWarehouseListXml()
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

        $get = WarehosuePartner::get();
        
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

    public function nonActiveWarehouseList(Request $request)
    {
        $cek = WarehosuePartner::where("id", $request->Task)->first();
        $status = 'ACTIVE';
        if ($cek->warehouse_status == 'ACTIVE') {
            $status = "INACTIVE";
        }

        WarehosuePartner::where("id", $request->Task)->update([
            'warehouse_status' => $status, 
        ]);

        echo "<script>alert('berhasil mengubah status');refreshdata();$('#divlistOpportunity').html();</script>";
    }

    public function newWarehouseList()
    {
        return view('partner.warehouse.list-new');
    }

    public function newWarehouseListSubmit(Request $request)
    {
        $getCount = WarehosuePartner::count();
        $tricode = DB::table('master_kodepos')->where("City", $request->Warehouse_Location)->first();

        $Task_ID = "GEU/".strtoupper($tricode->tricode)."/".sprintf('%04d', $getCount + 1);

        WarehosuePartner::create([
            'warehouse_task_id' => $Task_ID,
            'partner_task_id' => $request->Partner_Name,
            'warehouse_location' => $request->Warehouse_Location,
            'warehouse_status' => 'ACTIVE',
            'Location' => auth()->user()->Location,
            'Created_By' => auth()->user()->username,
            'Created_Date' => now(),
        ]);

        echo "success";
    }

    public function detailWarehouseList(Request $request)
    {
        $partner = DB::table('approval_request')->where('Status', 'APPROVED')->get()->pluck('Requester_Id');
        $partner = DB::table('opportunity_partner')->whereIn('id', $partner)->get();

        $kotakab = DB::table('master_kodepos')->select('City')->groupBy("City")->get();
        $wh = WarehosuePartner::where("id", $request->Task)->first();
        return view('partner.warehouse.list-detail', compact('wh', 'partner', 'kotakab'));
    }

    public function detailWarehouseListUpdate(Request $request)
    {
        $tricode = DB::table('master_kodepos')->where("City", $request->Warehouse_Location)->first();
        $Task_ID = explode('/', $request->warehouse_task_id);
        $Task_ID = $Task_ID[0] . '/' . $tricode->tricode . "/" . $Task_ID[2];

        WarehosuePartner::where('id', $request->id)->update([
            'warehouse_task_id' => $Task_ID,
            'partner_task_id' => $request->Partner_Name,
            'warehouse_location' => $request->Warehouse_Location,
            'Location' => auth()->user()->Location,
        ]);

        echo "success";
    }

    public function detaiLWarehouseListXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $wh = WarehosuePartner::where("id", $request->Task)->first();
        $partnerSupplies = WarehosuePartnerSupplies::where("warehouse_task_id", $wh->warehouse_task_id)->get();
        
        $nomor = 1;

        foreach ($partnerSupplies as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
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
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailWarehouseListNew(Request $request)
    {
        $wh = WarehosuePartner::where("id", $request->Task)->first();
        $kelurahan = DB::table('master_kodepos')->where('City', $wh->warehouse_location)->select('District')->groupBy("District")->get();
        return view('partner.warehouse.list-detail-new', compact('wh', 'kelurahan'));
    }

    public function detailWarehouseListNewSubmit(Request $request)
    {
        // WarehosuePartner::where();
        $r = $request->all();

        unset($r['_token']);

        $r['created_by'] = auth()->user()->username;
        $r['created_date'] = now();

        WarehosuePartnerSupplies::create($r);

        echo 'success';
    }

    public function detailWarehouseListDetail(Request $request)
    {
        $wh = WarehosuePartner::where("id", $request->Task)->first();
        $kelurahan = DB::table('master_kodepos')->where('City', $wh->warehouse_location)->select('District')->groupBy("District")->get();
        $wh = WarehosuePartnerSupplies::where("id", $request->Task)->first();
        return view('partner.warehouse.list-detail-detail', compact('wh', 'kelurahan'));
    }

    public function detailWarehouseListDetailSubmit(Request $request)
    {
        // WarehosuePartner::where();
        $r = $request->all();

        unset($r['id']);
        unset($r['_token']);

        WarehosuePartnerSupplies::where('id', $request->id)->update($r);

        echo 'success';
    }

    public function indexOpenWarehouse()
    {
        return view('partner.warehouse.open');
    }

    public function indexOpenWarehouseXml(Request $requst)
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

        // $getPartnerID = DB::table('opportunity_partner')->where("lead_id", auth()->user()->Emp_ID)->first();
        
        $get = WarehosuePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->ID\">";
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

    public function detailOpenWarehouse(Request $request)
    {
        $partner = DB::table('approval_request')->where('Status', 'APPROVED')->get()->pluck('Requester_Id');
        $partner = DB::table('opportunity_partner')->whereIn('id', $partner)->get();

        $kotakab = DB::table('master_kodepos')->select('City')->groupBy("City")->get();
        $wh = WarehosuePartner::where("id", $request->Task)->first();

        return view('partner.warehouse.open', compact('kotakab', 'wh'));
    }

    public function detailOpenWarehouseXml(Request $request)
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

        // $getPartnerID = DB::table('opportunity_partner')->where("lead_id", auth()->user()->Emp_ID)->first();
        
        $get = WarehosuePartnerSupplies::where('warehouse_task_id', $request->task)->get();
        
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
}
