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

class FinalShipmentInstructionController extends Controller
{
    public function indexFinalShipmentInstruction()
    {
        return view('cns.export.fsi');
    }

    public function indexFinalShipmentInstructionXml()
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

        $shipments = ShipmentInstruction::where('Status', "FINAL")->get();
        foreach ($shipments as $key => $shipment) {
            echo "<row id=\"$shipment->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Final_SI_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Final_SI_Date) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->SI_No) . "</cell>";
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

    public function printFinalShipmentInstruction(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $key => $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $shipment = ShipmentInstruction::where('Final_SI_No', $request->Task)->first();
        $vsds = VesselScheduleDtl::where('SI_No', $shipment->SI_No)->get();
        return view('cns.export.print.fsi', compact('shipment', 'vsds','companies'));
    }

    public function newFinalShipmentInstruction()
    {
        $vss = VesselSchedule::get();
        $sis = ShipmentInstruction::where('Status', "CREATED")->get();
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.fsi-new', compact('vss', 'sis', 'companies', 'masProducts'));
    }

    public function getSI(Request $request)
    {
        $SINo = $request->input('SI_No');
        $shipment = ShipmentInstruction::where('SI_No', $SINo)->first();
        if ($shipment) {
            $vsd = VesselScheduleDtl::where("SI_No", $SINo)->get();
            $shipment->orderDetails = $vsd;
            unset($shipment->id);
            return response()->json($shipment);
        } else {
            return response()->json(['error' => 'Shipment Instruction not found'], 404);
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

    public function generatePackingList(Request $request)
    {
        $shipment = ShipmentInstruction::where('Final_SI_No', $request->Task)->first();
        if (!$shipment) {
            return response()->json(['error' => 'Shipment Instruction not found'], 404);
        }

        // $shipment->Status = "PACKING_LIST";
        // $shipment->save();

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();

        $vs = $vs->toArray();
        $vs['Last_ID'] = $vs['id'];
        $vs['Action_By'] = auth()->user()->username;
        $vs['Action_Date'] = now();
        $vs['Action_Status'] = "CREATE PL";
        unset($vs['id']);
        VesselScheduleLog::create($vs);

        $counter = PackingList::where('PL_Date', "LIKE", date("Y-m") . "%")->count();
        
        $shipment = json_decode(json_encode($shipment), true);
        $shipment["Packing_List_No"] = "PL-GEU/EXP-BC/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");
        $shipment['PL_Date'] = $request->PL_Date;
        $shipment['Created_By'] = auth()->user()->username;
        $shipment['Created_Date'] = now();
        $shipment['Updated_By'] = null;
        $shipment['Updated_Date'] = null;
        $shipment['Status'] = "CREATED";
        PackingList::create($shipment);

        echo "<script>alert('Packing List generated successfully');</script>";

        // return response()->json(['message' => 'Packing List generated successfully']);
    }

    public function generateCommercialInvoice(Request $request)
    {
        $shipment = ShipmentInstruction::where('Final_SI_No', $request->Task)->first();
        if (!$shipment) {
            return response()->json(['error' => 'Shipment Instruction not found'], 404);
        }

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();

        $vs = $vs->toArray();
        $vs['Last_ID'] = $vs['id'];
        $vs['Action_By'] = auth()->user()->username;
        $vs['Action_Date'] = now();
        $vs['Action_Status'] = "CREATE CI";
        unset($vs['id']);
        VesselScheduleLog::create($vs);

        $getVSD = VesselScheduleDtl::where("Order_No", $vs->Order_No)->where("SI_No", $shipment->SI_No)->sum('Netto_MT');
    
        $counter = DB::table('commercial_invoice')->where('Inv_Date', "LIKE", date("Y-m") . "%")->count();
        
        $shipment = json_decode(json_encode($shipment), true);
        $shipment["Inv_No"] = sprintf('%03d',$counter + 1) . "/INV-GEU/" . $this->getRomawi(date("n")) . "/" . date("Y");
        $shipment['Inv_Date'] = date("Y-m-d");
        $shipment['Shipper'] = $shipment['Shipper'] ?? '';
        $shipment['Consignee'] = $shipment['Consignee'] ?? '';
        $shipment['Description'] = "";
        $shipment['Qty'] = $getVSD;
        $shipment['Amount'] = 0;
        $shipment['Advance_Payment'] = 0;
        $shipment['Other'] = 0;
        $shipment['Total'] = 0;
        $shipment["Status"] = "CREATED";
        $shipment['Created_By'] = auth()->user()->username;
        $shipment['Created_Date'] = now();
        DB::table('commercial_invoice')->insert($shipment);

        return response()->json(['message' => 'Commercial Invoice generated successfully']);
    }
}
