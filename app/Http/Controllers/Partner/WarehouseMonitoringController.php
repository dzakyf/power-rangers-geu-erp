<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerSupplies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseMonitoringController extends Controller
{
    public function indexMonitoringWarehouse()
    {
        return view('partner.warehouse.monitoring');
    }

    public function indexMonitoringWarehouseXml(Request $request)
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

        $get = WarehousePartner::get();
        
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

    public function detailMonitoringWarehouse(Request $request)
    {
        $partner = DB::table('approval_request')->where('Status', 'APPROVED')->get()->pluck('Requester_Id');
        $partner = DB::table('opportunity_partner')->whereIn('id', $partner)->get();

        $kotakab = DB::table('master_kodepos')->select('City')->groupBy("City")->get();
        $wh = WarehousePartner::where("id", $request->Task)->first();
        $whs = WarehousePartnerSupplies::where("warehouse_task_id", $wh->warehouse_task_id)->get();
        $supplies = [];
        foreach ($whs as $key => $value) {
            $month = date('Y-m', $value->received_date);
            if(!isset($supplies[$month])) {
                $supplies[$month] = [];
            }
            if (!isset($supplies[$month]['qty'])) {
                $supplies[$month]['qty'] = 0;
            }
            if (!isset($supplies[$month]['avg'])) {
                $supplies[$month]['avg'] = $value->price;
            }

            $supplies[$month]['qty'] += $value->received_qty;
            $supplies[$month]['avg'] += (($supplies[$month]['avg'] * $supplies[$month]['qty']) + $value->total) / ($supplies[$month]['qty'] + $value->qty);
        }

        return view('partner.warehouse.monitoring-detail', compact('wh', 'whs', 'partner', 'kotakab'));
    }

    public function detailMonitoringWarehouseXml(Request $request)
    {
        //
    }

    public function listMonitoringWarehouse(Request $request)
    {
        $partner = DB::table('approval_request')->where('Status', 'APPROVED')->get()->pluck('Requester_Id');
        $partner = DB::table('opportunity_partner')->whereIn('id', $partner)->get();

        $kotakab = DB::table('master_kodepos')->select('City')->groupBy("City")->get();
        $wh = WarehousePartner::where("id", $request->Task)->first();
        $whs = WarehousePartnerSupplies::where("warehouse_task_id", $wh->warehouse_task_id)->where('received_date', 'like', $request->month.'%')->get();
        return view('partner.warehouse.monitoring-detail-list', compact('wh', 'whs' ,'partner', 'kotakab'));
    }
}
