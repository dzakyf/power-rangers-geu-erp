<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\PackingList;
use App\ShipmentInstruction;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\WeightBridgeTicket;
use App\WeightBridgeTicketDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeightBridgeTicketController extends Controller
{
    public function indexWeightBridgeTicket()
    {
        return view('cns.export.wbt');
    }

    public function indexWeightBridgeTicketXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $nomor = 1;
        $wbt = WeightBridgeTicket::whereBetween('Departure_Date', [$request->tgl1, $request->tgl2])->get();
        foreach ($wbt as $key => $value) {
            $vsd = VesselScheduleDtl::where("SI_No", $value->SI_No)->get();
            foreach ($vsd as $k => $v) {
                $value->Container .= $v->Container . " (" . $v->Seal . "),";
            }
            if (isset($value->Container) && $value->Container != '') {
                $value->Container = substr($value->Container, 0, -1);
            }
            echo "<row id=\"$value->WBT_No\">";
                echo "<cell>".htmlspecialchars($nomor)."</cell>";
                echo "<cell>".htmlspecialchars($value->Packing_List_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Final_SI_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Contract_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Voyage)."</cell>";
                echo "<cell>".htmlspecialchars(date("d F Y", strtotime($value->Departure_Date)))."</cell>";
                echo "<cell>".htmlspecialchars($value->BL_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Container)."</cell>";
                echo "<cell>".htmlspecialchars($value->Created_By)."</cell>";
                echo "<cell>".htmlspecialchars($value->Created_Date)."</cell>";
            echo "</row>";
            $nomor++;
        }

        echo "</rows>";
    }

    public function printWeightBridgeTicket(Request $request)
    {
        $wbt = WeightBridgeTicket::where("WBT_No", $request->Task)->first();
        if (!$wbt) {
            return redirect()->back()->with('error', 'Data not found');
        }
        if ($wbt->Shipper == null) {
            $wbt->Shipper = 'SUP10001';
        }
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $key => $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $wbt->Details = WeightBridgeTicketDtl::where("WBT_No", $wbt->WBT_No)->join('vessel_schedule_dtls', 'weight_bridge_ticket_dtl.VSD_ID', '=', 'vessel_schedule_dtls.id')->select('weight_bridge_ticket_dtl.*', 'vessel_schedule_dtls.Container as Container', 'vessel_schedule_dtls.Seal as Seal')->get();
        return view('cns.export.print.wbt', compact('wbt', 'companies'));
    }

    public function newWeightBridgeTicket()
    {
        $pls = PackingList::where('Status', "CREATED")->get();
        return view('cns.export.wbt-new', compact('pls'));
    }

    public function getSIDetail(Request $request)
    {
        $pl = PackingList::where("Packing_List_No", $request->input("Packing_List_No"))->first();
        if ($pl) {
            $si = VesselScheduleDtl::where('SI_No', $pl->SI_No)->get();
            $pl->orderDetails = $si;
            return response()->json($pl);
        } else {
            return response()->json([]);
        }
    }

    function getRomawi($bln)
    {
        switch ($bln) {
            case 1:
                return "I";
                break;
            case 2:
                return "II";
                break;
            case 3:
                return "III";
                break;
            case 4:
                return "IV";
                break;
            case 5:
                return "V";
                break;
            case 6:
                return "VI";
                break;
            case 7:
                return "VII";
                break;
            case 8:
                return "VIII";
                break;
            case 9:
                return "IX";
                break;
            case 10:
                return "X";
                break;
            case 11:
                return "XI";
                break;
            case 12:
                return "XII";
                break;
        }
    }

    public function newWeightBridgeTicketSubmit(Request $request)
    {
        $pl = PackingList::where("Packing_List_No", $request->Packing_List_No)->first();
        if (!$pl) {
            echo "error";
            exit;
        }

        $getContainId = [];

        $r = $request->all();
        foreach ($r as $key => $value) {
            $exp = explode('Container', $key);
            if (count($exp) > 1) {
                $getContainId[] = $exp[1];
            }
        }

        $counter = WeightBridgeTicket::where("Created_Date", "LIKE", date("Y-m") . "%")->count();
        $wbtno = "WBT-GEU/" . sprintf("%03d", ($counter + 1)) . "/" . $this->getRomawi(date('n')) . "/" . date("Y");

        $wbt = WeightBridgeTicket::create([
            'WBT_No' => $wbtno,
            'Packing_List_No' => $pl->Packing_List_No,
            'Final_SI_No' => $pl->Final_SI_No,
            'SI_No' => $pl->SI_No,
            'Contract_No' => $pl->Contract_No,
            'Voyage' => $request->Voyage,
            'Departure_Date' => $request->Departure_Date,
            'BL_No' => $request->BL_No ?? '',
            'Created_By' => auth()->user()->username,
            'Created_Date' => now(),
        ]);

        foreach ($getContainId as $key => $value) {
            $vsd = VesselScheduleDtl::where("id", $value)->first();
            $name = "";
            $ext = "";
            if ($r['Container' . $value] != 'undefined') {
                $file = $r['Container' . $value];
                $filename = "Container_" . strtoupper($vsd->Container) . "_" . time() . "." . $file->getClientOriginalExtension();
                $file->move(public_path('filesupplier'), $filename);
                $name = "filesupplier/" . $filename;
                $ext = $file->getClientOriginalExtension();
            }
            if ($name != '') {
                $wbtDetail = WeightBridgeTicketDtl::create([
                    'WBT_No' => $wbtno,
                    'VSD_ID' => $value,
                    'Path_Loc' => $name,
                    'Path_Type' => $ext,
                    'Created_By' => auth()->user()->username,
                    'Created_Date' => now(),
                ]);
            }
        }

        echo "success";
    }

    public function detailWeightBridgeTicket(Request $request)
    {
        $wbt = WeightBridgeTicket::where("WBT_No", $request->Task)->first();
        if (!$wbt) {
            return redirect()->back()->with('error', 'Data not found');
        }
        $pls = PackingList::where('Status', "CREATED")->get();
        $wbt->Details = WeightBridgeTicketDtl::where("WBT_No", $wbt->WBT_No)->join('vessel_schedule_dtls', 'weight_bridge_ticket_dtl.VSD_ID', '=', 'vessel_schedule_dtls.id')->select('weight_bridge_ticket_dtl.*', 'vessel_schedule_dtls.Container as Container', 'vessel_schedule_dtls.Seal as Seal')->get();
        return view('cns.export.wbt-detail', compact('wbt', 'pls'));
    }

    public function detailWeightBridgeTicketSubmit(Request $request)
    {
        $wbt = WeightBridgeTicket::where("WBT_No", $request->WBT_No)->first();
        if (!$wbt) {
            echo "error";
            exit;
        }

        $pl = PackingList::where("Packing_List_No", $request->Packing_List_No)->first();
        if (!$pl) {
            echo "error";
            exit;
        }

        $getContainId = [];

        $r = $request->all();
        foreach ($r as $key => $value) {
            $exp = explode('Container', $key);
            if (count($exp) > 1) {
                $getContainId[] = $exp[1];
            }
        }

        $wbt->Packing_List_No = $request->Packing_List_No;
        $wbt->Final_SI_No = $request->Final_SI_No;
        $wbt->Contract_No = $request->Contract_No;
        $wbt->Voyage = $request->Voyage;
        $wbt->Departure_Date = $request->Departure_Date;
        $wbt->BL_No = $request->BL_No ?? '';
        $wbt->save();

        foreach ($getContainId as $key => $value) {
            $vsd = VesselScheduleDtl::where("id", $value)->first();
            $name = "";
            $ext = "";
            if ($r['Container' . $value] != 'undefined') {
                $file = $r['Container' . $value];
                $filename = "Container_" . strtoupper($vsd->Container) . "_" . time() . "." . $file->getClientOriginalExtension();
                $file->move(public_path('filesupplier'), $filename);
                $name = "filesupplier/" . $filename;
                $ext = $file->getClientOriginalExtension();
            }
            if ($name != '') {
                $wbtDetail = WeightBridgeTicketDtl::where('WBT_No', $request->WBT_No)->where('VSD_ID', $value)->first();
                if (!$wbtDetail) {
                    $wbtDetail = WeightBridgeTicketDtl::create([
                        'WBT_No' => $request->WBT_No,
                        'VSD_ID' => $value,
                        'Path_Loc' => $name,
                        'Path_Type' => $ext,
                        'Created_By' => auth()->user()->username,
                        'Created_Date' => now(),
                    ]);
                } else {
                    $wbtDetail->Path_Loc = $name;
                    $wbtDetail->Path_Type = $ext;
                    $wbtDetail->save();
                }
            }
        }

        echo "success";
    }
}
