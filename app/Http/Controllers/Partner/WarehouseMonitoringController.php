<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerSupplies;
use App\WarehouseStockRequest;
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
        $wh = WarehousePartner::where("warehouse_task_id", $request->Task)->first();
        $whs = WarehousePartnerSupplies::where("warehouse_task_id", $wh->warehouse_task_id)->get();

        return view('partner.warehouse.monitoring-detail', compact('wh', 'whs', 'partner', 'kotakab'));
    }

    public function detailMonitoringWarehouseXml(Request $request)
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

        $whs = WarehousePartnerSupplies::where("warehouse_task_id", $request->Task)->get();
        $supplies = [];
        foreach ($whs as $key => $value) {
            $month = date('Y-m', strtotime($value->received_date));
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
            $supplies[$month]['avg'] = (($supplies[$month]['avg'] * $supplies[$month]['qty']) + $value->total) / ($supplies[$month]['qty'] + $value->received_qty);
        }

        $whsr = WarehouseStockRequest::where("warehouse_task_id", $request->Task)->get();
        foreach ($whsr as $key => $value) {
            $month = date('Y-m', strtotime($value->request_date));
            if(!isset($supplies[$month])) {
                $supplies[$month] = [];
            }
            if (!isset($supplies[$month]['qty_out'])) {
                $supplies[$month]['qty_out'] = 0;
            }
            if (!isset($supplies[$month]['avg_out'])) {
                $supplies[$month]['avg_out'] = $value->price;
            }

            $supplies[$month]['qty_out'] += $value->request_qty;
            $supplies[$month]['avg_out'] += (($supplies[$month]['avg_out'] * $supplies[$month]['qty_out']) + $value->total) / ($supplies[$month]['qty_out'] + $value->request_qty);
        }

        $lastStock = 0;
        
        foreach ($supplies as $key => $Fetch) {
            $qty = $Fetch['qty'] ?? 0;
            $avg = $Fetch['avg'] ?? 0;
            $qty_out = $Fetch['qty_out'] ?? 0;
            $avg_out = $Fetch['avg_out'] ?? 0;
            echo "<row id=\"$key\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars(date('Y-m-t', strtotime($key . '-01'))) . "</cell>";
            echo "<cell>" . htmlspecialchars($lastStock + $qty ?? 0) . "</cell>";
            echo "<cell>" . htmlspecialchars($avg ?? 0) . "</cell>";
            echo "<cell>" . htmlspecialchars($qty * $avg) . "</cell>";
            echo "<cell>" . htmlspecialchars('') . "</cell>";
            echo "<cell>" . htmlspecialchars('') . "</cell>";
            echo "<cell>" . htmlspecialchars($qty_out) . "</cell>";
            $lastStock += $qty - $qty_out;
            echo "<cell>" . htmlspecialchars($lastStock) . "</cell>";
            echo "<cell>" . htmlspecialchars($avg_out) . "</cell>";
            echo "<cell>" . htmlspecialchars($qty_out * $avg_out) . "</cell>";
            echo "<cell>" . htmlspecialchars('') . "</cell>";
            echo "<cell>" . htmlspecialchars('') . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailMonitoringWarehouseList(Request $request)
    {
        $partner = DB::table('approval_request')->where('Status', 'APPROVED')->get()->pluck('Requester_Id');
        $partner = DB::table('opportunity_partner')->whereIn('id', $partner)->get();

        $kotakab = DB::table('master_kodepos')->select('City')->groupBy("City")->get();
        $wh = WarehousePartner::where("warehouse_task_id", $request->Task)->first();
        $whs = WarehousePartnerSupplies::where("warehouse_task_id", $wh->warehouse_task_id)->where('received_date', 'like', $request->month.'%')->get();
        return view('partner.warehouse.monitoring-detail-list', compact('wh', 'whs' ,'partner', 'kotakab'));
    }

    public function detailMonitoringWarehouseSuppliesXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $partnerSupplies = WarehousePartnerSupplies::where("warehouse_task_id", $request->Task)->where('received_date', 'like', $request->date . '%')->get();
        
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

    public function detailMonitoringWarehouseRequestXml(Request $request)
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

        $get = WarehouseStockRequest::where('warehouse_task_id', $request->Task)->where('request_date', 'like', $request->date . '%')->get();
        
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
}
