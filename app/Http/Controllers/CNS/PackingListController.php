<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\PackingList;
use App\ShipmentInstruction;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\VesselScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackingListController extends Controller
{
    public function indexPackingList()
    {
        return view('cns.export.pl');
    }

    public function indexPackingListXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $vsds = VesselScheduleDtl::whereNotNull('SI_No')->get();
        foreach ($vsds as $key => $vsd) {
            if (!isset($vsdData[$vsd->SI_No])) {
                $vsdData[$vsd->SI_No] = [
                    'list' => [],
                    'tare' => 0,
                    'netto_kg' => 0,
                    'netto_mt' => 0,
                    'gross_kg' => 0,
                    'gross_mt' => 0
                ];
            }
            $vsdData[$vsd->SI_No]['list'][] = $vsd->Container . " (" . $vsd->Seal . ")";
            $vsdData[$vsd->SI_No]['tare'] += $vsd->Tare;
            $vsdData[$vsd->SI_No]['netto_kg'] += $vsd->Netto_KG;
            $vsdData[$vsd->SI_No]['netto_mt'] += $vsd->Netto_MT;
            $vsdData[$vsd->SI_No]['gross_kg'] += $vsd->Gross_KG;
            $vsdData[$vsd->SI_No]['gross_mt'] += $vsd->Gross_MT;
        }

        $shipments = PackingList::whereBetween('PL_Date',[$request->tgl1, $request->tgl2])->where('Status', "CREATED")->get();
        foreach ($shipments as $key => $shipment) {
            echo "<row id=\"$shipment->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Packing_List_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Final_SI_No) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("d F Y", strtotime($shipment->PL_Date))) . "</cell>";
            if (isset($vsdData[$shipment->SI_No])) {
                echo "<cell>" . htmlspecialchars(implode(', ', $vsdData[$shipment->SI_No]['list'])) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['tare']) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['netto_kg']) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['netto_mt']) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['gross_kg']) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['gross_mt']) . "</cell>";
            }else {
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function printPackingList(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $key => $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $shipment = PackingList::where('Packing_List_No', $request->Task)->first();
        $vsds = VesselScheduleDtl::where('SI_No', $shipment->SI_No)->get();
        return view('cns.export.print.pl', compact('shipment', 'vsds','companies'));
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

    public function newPackingList()
    {
        
        $counter = PackingList::where('PL_Date', "LIKE", date("Y-m") . "%")->count();

        $plno = "PL-GEU/EXP-BC/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");

        $fsis = ShipmentInstruction::where("Status", "FINAL")->where("Final_SI_No", '!=', null)->get();

        $companies = DB::table('supplier')->where("Status", "NORMAL")->where("Location", auth()->user()->Location)->get();

        $masProducts = DB::table('master_product_cns')->get();

        return view('cns.export.pl-new', compact('plno','fsis', 'companies', 'masProducts'));
    }

    public function newPackingListSubmit(Request $request)
    {
        $shipment = ShipmentInstruction::where("Final_SI_No", $request->Final_SI_No)->first();

        if (!$shipment) {
            echo "error";
            exit;
        }

        $pl = new PackingList();
        $pl->Packing_List_No = $request->input('Packing_List_No');
        $pl->Final_SI_No = $request->input('Final_SI_No');
        $pl->SI_No = $shipment->SI_No;
        $pl->Order_No = $shipment->Order_No;
        $pl->SI_Date = $shipment->SI_Date;
        $pl->Final_SI_Date = $shipment->Final_SI_Date;
        $pl->Qty = $request->input('Qty1') . " x " . $request->input('Qty2');
        $pl->Qty1 = $request->input('Qty1');
        $pl->Qty2 = $request->input('Qty2');
        $pl->PL_Date = $request->input('PL_Date');
        $pl->Shipper = $request->input('Shipper') ?? '';
        $pl->Consignee = $request->input('Consignee') ?? '';
        $pl->Notify = $request->input('Notify') ?? '';
        $pl->Destination_Letter = $request->input('Destination_Letter') ?? '';
        $pl->Delivery_Terms = $request->input('Delivery_Terms') ?? '';
        $pl->Contract_No = $request->input('Contract_No') ?? '';
        $pl->Voyage = $request->input('Voyage') ?? '';
        $pl->ETD = $request->input('ETD');
        $pl->ETA = $request->input('ETA');
        $pl->Stuffing = $request->input('Stuffing');
        $pl->POL = $request->input('POL') ?? "";
        $pl->POD = $request->input('POD') ?? "";
        $pl->PEB_Date = $request->input('PEB_Date');
        $pl->PEB_No = $request->input('PEB_No') ?? '';
        $pl->BL_No = $request->input('BL_No') ?? '';
        $pl->HS_Code = $request->input('HS_Code');
        $pl->Product_Name = $request->input('Product_Name');
        $pl->Status = "CREATED";
        $pl->Created_Date = now();
        $pl->Created_By = auth()->user()->username;
        $pl->save();

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();
        $vsd = VesselScheduleDtl::where('Task_ID', $vs->Task_ID)
            ->get()
            ->map(function ($item) {
                return $item->Container . ' (' . $item->Seal . ')';
            })
            ->implode(', ');
            
        $vs = json_decode(json_encode($vs), true);
        $vs['Last_ID'] = $vs['id'];
        unset($vs['id']);
        $vs['Container'] = $vsd;

        $vs['Action_By'] = auth()->user()->username;
        $vs['Action_Date'] = now();
        $vs['Action_Status'] = "CREATE PACKING LIST WITH NO: " . $request->input('Packing_List_No');

        VesselScheduleLog::create($vs);

        echo "success";
    }

    public function getSI(Request $request)
    {
        $SINo = $request->input('SI_No');
        $shipment = ShipmentInstruction::where('Final_SI_No', $SINo)->first();
        if ($shipment) {
            $shipment->orderDetails = VesselScheduleDtl::where("SI_No", $shipment->SI_No)->get();
            return response()->json($shipment);
        } else {
            return response()->json(['error' => 'Shipment Instruction not found'], 404);
        }
    }

    public function detailPackingList(Request $request)
    {
        $counter = PackingList::where('PL_Date', "LIKE", date("Y-m") . "%")->count();

        $plno = "PL-GEU/EXP-BC/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");

        $fsis = ShipmentInstruction::where("Status", "FINAL")->where("Final_SI_No", '!=', null)->get();

        $companies = DB::table('supplier')->where("Status", "NORMAL")->where("Location", auth()->user()->Location)->get();

        $masProducts = DB::table('master_product_cns')->get();

        $pl = PackingList::where("ID", $request->Task)->first();

        return view('cns.export.pl-detail', compact('plno','fsis', 'companies', 'masProducts', 'pl'));
    }
}
