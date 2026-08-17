<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerSupplies;
use App\WarehouseStockRequest;
use App\WarehouseStockOpname;
use SimpleXMLElement;
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
            $usname[$value->Emp_ID] = $value->Name;
        }
        $getPartner = DB::table('opportunity_partner')->get();
        $usaddress = [];
        foreach ($getPartner as $key => $value) {
            $usname[$value->lead_id] = $value->full_name;
            $usaddress[$value->lead_id] = $value->address;
        }
        $nomor = 1;

        if ($priv == 'MITRA') {
            $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        } else {
            $get = WarehousePartner::get();
        }

        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usaddress[$Fetch->partner_task_id] ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->capacity) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function nonActiveWarehouseList(Request $request)
    {
        $cek = WarehousePartner::where("id", $request->Task)->first();
        $status = 'ACTIVE';
        if ($cek->warehouse_status == 'ACTIVE') {
            $status = "INACTIVE";
        }

        WarehousePartner::where("id", $request->Task)->update([
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
        $getCount = WarehousePartner::count();
        $tricode = DB::table('master_kodepos')->where("City", $request->Warehouse_Location)->first();

        $Task_ID = "GEU-EXP/" . strtoupper($tricode->tricode) . "/" . sprintf('%04d', $getCount + 1);

        WarehousePartner::create([
            'warehouse_task_id' => $Task_ID,
            'partner_task_id' => $request->Partner_Name,
            'warehouse_name' => $request->warehouse_name,
            'warehouse_type' => $request->Warehouse_Type,
            'warehouse_location' => $request->Warehouse_Location,
            'warehouse_status' => 'ACTIVE',
            'capacity' => $request->Capacity,
            'Location' => auth()->user()->Location,
            'lat' => $request->lat ?? null,
            'lng' => $request->lng ?? null,
            'Created_By' => auth()->user()->username,
            'Created_Date' => now(),
        ]);

        echo "success";
    }

    public function detailWarehouseList(Request $request)
    {
        $wh = WarehousePartner::where("id", $request->Task)->first();
        $partner = DB::table('approval_request')->where('Status', 'APPROVED')->where('Request_Type', 'ONBOARDING')->get()->pluck('Requester_Id');
        $partner = DB::table('users')->whereIn("id", $partner)->get()->pluck('Emp_ID');
        $partner = DB::table('opportunity_partner')->where(function ($q) use ($partner, $wh) {
            $q->whereIn('id', $partner)->orWhere('lead_id', $wh->partner_task_id);
        })->where('status', 'ACTIVE')->get();

        $kotakab = DB::table('master_kodepos')->select('City')->groupBy("City")->get();
        return view('partner.warehouse.list-detail', compact('wh', 'partner', 'kotakab'));
    }

    public function detailWarehouseListUpdate(Request $request)
    {
        $tricode = DB::table('master_kodepos')->where("City", $request->Warehouse_Location)->first();
        $Task_ID = explode('/', $request->warehouse_task_id);
        $Task_ID = $Task_ID[0] . '/' . $tricode->tricode . "/" . $Task_ID[2];

        WarehousePartner::where('id', $request->id)->update([
            'warehouse_task_id' => $Task_ID,
            'partner_task_id' => $request->Partner_Name,
            'warehouse_name' => $request->warehouse_name,
            'warehouse_type' => $request->Warehouse_Type,
            'capacity' => $request->Capacity,
            'warehouse_location' => $request->Warehouse_Location,
            'Location' => auth()->user()->Location,
            'lat' => $request->lat ?? null,
            'lng' => $request->lng ?? null,
        ]);

        echo "success";
    }

    public function detaiLWarehouseListXml(Request $request)
    {
        $wh = WarehousePartner::where("id", $request->Task)->first();
        $whTaskId = $wh->warehouse_task_id ?? '';
        $whId = $wh->id ?? 0;

        $rows = [];

        // 1. Instock
        foreach (WarehousePartnerSupplies::where("warehouse_task_id", $whTaskId)->get() as $s) {
            $rows[] = ['date' => $s->received_date, 'type' => 'INSTOCK', 'qty_in' => $s->received_qty, 'qty_out' => 0, 'reference' => (string) $s->id, 'details' => $s->source . ' - ' . ($s->name_poo ?? '')];
        }

        // 2. Stock Request (old outstock)
        foreach (WarehouseStockRequest::where("warehouse_task_id", $whTaskId)->get() as $r) {
            $rows[] = ['date' => $r->request_date, 'type' => 'Outstock', 'qty_in' => 0, 'qty_out' => $r->request_qty, 'reference' => $r->reference, 'details' => $r->remark ?? ''];
        }

        // 3. Outstock Loading (new outstock)
        $outstockData = DB::table('outstock_loading')
            ->join('outstock', 'outstock_loading.Outstock_ID', '=', 'outstock.ID')
            ->join('outstock_warehouses', 'outstock.ID', '=', 'outstock_warehouses.Outstock_ID')
            ->where('outstock_warehouses.Warehouse_ID', $whId)
            ->select('outstock_loading.Date', 'outstock.Code', 'outstock_loading.Actual_Qty', 'outstock_loading.Notes')
            ->get();

        foreach ($outstockData as $o) {
            $rows[] = ['date' => $o->Date, 'type' => 'OUTSTOCK', 'qty_in' => 0, 'qty_out' => $o->Actual_Qty, 'reference' => $o->Code, 'details' => $o->Notes ?? ''];
        }

        // 4. Stock Opname
        foreach (WarehouseStockOpname::where('Warehouse_ID', $whId)->get() as $op) {
            $rows[] = ['date' => $op->Opname_Date, 'type' => 'OPNAME', 'qty_in' => $op->Physical_Qty, 'qty_out' => 0, 'reference' => $op->Opname_Number, 'details' => 'System: ' . $op->System_Qty . ' | Diff: ' . $op->Difference];
        }

        // Sort ascending for balance calculation
        usort($rows, function($a, $b) { return strcmp($a['date'], $b['date']); });

        // Calculate running balance, reset on OPNAME
        $balance = 0;
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]['type'] === 'OPNAME') {
                $balance = $rows[$i]['qty_in'];          // Reset to Physical_Qty
                $rows[$i]['qty_in'] = 0;                  // Don't add to In total
            } elseif ($rows[$i]['type'] === 'INSTOCK') {
                $balance += $rows[$i]['qty_in'];
            } else {
                $balance -= $rows[$i]['qty_out'];
            }
            $rows[$i]['balance'] = $balance;
        }

        // Sort descending for display
        usort($rows, function($a, $b) { return strcmp($b['date'], $a['date']); });

        $xml = new SimpleXMLElement('<rows/>');
        $no = 1;
        foreach ($rows as $r) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $no);
            $row->addChild('cell', $no);
            $row->addChild('cell', htmlspecialchars((string) $r['date']));
            $row->addChild('cell', htmlspecialchars((string) $r['type']));
            $row->addChild('cell', (string) $r['qty_in']);
            $row->addChild('cell', (string) $r['qty_out']);
            $row->addChild('cell', (string) $r['balance']);
            $no++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function detailWarehouseListNew(Request $request)
    {
        $wh = WarehousePartner::where("id", $request->Task)->first();
        $kelurahan = DB::table('master_kodepos')->where('City', $wh->warehouse_location)->select('District')->groupBy("District")->get();
        return view('partner.warehouse.list-detail-new', compact('wh', 'kelurahan'));
    }

    public function detailWarehouseListNewSubmit(Request $request)
    {
        // WarehousePartner::where();
        $r = $request->all();

        unset($r['_token']);

        $r['created_by'] = auth()->user()->username;
        $r['created_date'] = now();

        WarehousePartnerSupplies::create($r);

        echo 'success';
    }

    public function detailWarehouseListDetail(Request $request)
    {
        $wh = WarehousePartner::where("id", $request->Task)->first();
        $kelurahan = DB::table('master_kodepos')->where('City', $wh->warehouse_location)->select('District')->groupBy("District")->get();
        $wh = WarehousePartnerSupplies::where("id", $request->Task)->first();
        return view('partner.warehouse.list-detail-detail', compact('wh', 'kelurahan'));
    }

    public function detailWarehouseListDetailSubmit(Request $request)
    {
        // WarehousePartner::where();
        $r = $request->all();

        unset($r['id']);
        unset($r['_token']);

        WarehousePartnerSupplies::where('id', $request->id)->update($r);

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

        $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();

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
        $wh = WarehousePartner::where("id", $request->Task)->first();

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

        $get = WarehousePartnerSupplies::where('warehouse_task_id', $request->task)->get();

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


    public function getWarehouses(Request $r)
    {
        $data = DB::table('warehouse_partner as wp')
            ->leftJoin('opportunity_partner as op', 'wp.partner_task_id', '=', 'op.lead_id')
            ->leftJoin('users as u', 'u.id', '=', 'op.user_row_id')
            ->select('wp.id',  'wp.warehouse_task_id', 'wp.warehouse_location', 'wp.warehouse_status', 'wp.Created_Date', 'wp.lat', 'wp.lng')
            ->when($r->partner_id,        fn($q) => $q->where('op.lead_id',            $r->partner_id))
            ->when($r->wh_loc,            fn($q) => $q->where('wp.warehouse_location', $r->wh_loc))
            ->when($r->wh_type,           fn($q) => $q->where('wp.warehouse_type',     $r->wh_type))
            ->when($r->wh_id, fn($q) => $q->where('wp.warehouse_task_id',  $r->wh_id))
            ->orderBy('wp.id', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
