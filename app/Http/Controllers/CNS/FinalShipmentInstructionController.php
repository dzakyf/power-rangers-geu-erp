<?php

namespace App\Http\Controllers\CNS;

use App\CommercialInvoice;
use App\Http\Controllers\Controller;
use App\PackingList;
use App\ShipmentInstruction;
use App\Services\CNS\ShippingInstructionPdfService;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\VesselScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalShipmentInstructionController extends Controller
{
    protected $pdfService;

    public function __construct(ShippingInstructionPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function indexFinalShipmentInstruction()
    {
        return view('cns.export.fsi');
    }

    public function indexFinalShipmentInstructionXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $vsdData = [];
        $vsds = VesselScheduleDtl::active()->whereNotNull('SI_No')->get();
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

        $shipments = ShipmentInstruction::whereBetween("Final_SI_Date", [$request->tgl1, $request->tgl2])->where('Status', "FINAL")->get();
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
        $shipment = ShipmentInstruction::where('Final_SI_No', $request->Task)->first();

        abort_if(!$shipment, 404);

        $pdf = $this->pdfService->render($shipment, 'FSI');

        return response($pdf['content'], 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdf['filename'] . '"',
        ]);
    }

    public function newFinalShipmentInstruction()
    {
        $vss = VesselSchedule::get();
        $sis = ShipmentInstruction::where('Status', "CREATED")->get();
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.fsi-new', compact('vss', 'sis', 'companies', 'masProducts'));
    }
    
    
    public function newFinalShipmentInstructionSubmit(Request $request)
    {
        $counter = DB::table("shipment_instructions")->where('SI_Date', "LIKE", date("Y-m"). "%")->where('Status', "FINAL")->count();

        $SINo = "FINALSI/EXP/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");
        ShipmentInstruction::where("SI_No", $request->SI_No)->update([
            'Final_SI_No' => $SINo,
            'Final_SI_Date' => $request->SI_Date,
            'Status' => "FINAL",
            'Updated_Date' => now(),
            'Updated_By' => auth()->user()->username
        ]);

        // return response()->json(['success' => true, 'message' => 'Shipping Instruction finalized successfully']);
        echo "<script>alert('Done Generate to Final SI')</script>";
    }

    public function getSI(Request $request)
    {
        $SINo = $request->input('SI_No');
        $shipment = ShipmentInstruction::where('SI_No', $SINo)->first();
        if ($shipment) {
            $vsd = VesselScheduleDtl::active()->where("SI_No", $SINo)->get();
            $shipment->orderDetails = $vsd;
            unset($shipment->id);
            return response()->json($shipment);
        } else {
            return response()->json(['error' => 'Shipping Instruction not found'], 404);
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

    public function detailFinalShipmentInstruction()
    {
        $vss = VesselSchedule::get();
        $sis = ShipmentInstruction::where('Status', "CREATED")->get();
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $shipment = ShipmentInstruction::where('ID', request()->query('Task'))->first();
        if ($shipment) {
            $vsd = VesselScheduleDtl::active()->where("SI_No", $shipment->SI_No)->get();
            $shipment->orderDetails = $vsd;
        }
        return view('cns.export.fsi-detail', compact('vss', 'sis', 'companies', 'masProducts', 'shipment'));
    }

    public function detailFinalShipmentInstructionSubmit(Request $request)
    {
        $id = $request->input('id');
        $si = null;
        if ($id) {
            $si = ShipmentInstruction::where('id', $id)->first();
        }
        if (!$si && $request->filled('SI_No')) {
            $si = ShipmentInstruction::where('SI_No', $request->input('SI_No'))->first();
        }

        if (!$si) {
            return response()->json(['error' => 'Shipment Instruction not found'], 404);
        }

        $updateData = [
            'Updated_Date' => now(),
            'Updated_By' => auth()->user()->username,
        ];

        if ($request->filled('Shipper')) {
            $updateData['Shipper'] = $request->Shipper;
        }
        if ($request->has('Shipper_Address')) {
            $updateData['Shipper_Address'] = $request->Shipper_Address;
        }
        if ($request->filled('Consignee')) {
            $updateData['Consignee'] = $request->Consignee;
        }
        if ($request->has('Consignee_Address')) {
            $updateData['Consignee_Address'] = $request->Consignee_Address;
        }
        if ($request->filled('Notify')) {
            $updateData['Notify'] = $request->Notify;
        }
        if ($request->has('Notify_Address')) {
            $updateData['Notify_Address'] = $request->Notify_Address;
        }
        if ($request->has('PEB_No')) {
            $updateData['PEB_No'] = $request->PEB_No;
        }
        if ($request->has('PEB_Date')) {
            $updateData['PEB_Date'] = $request->PEB_Date;
        }
        if ($request->has('BL_No')) {
            $updateData['BL_No'] = $request->BL_No;
        }
        if ($request->has('POL')) {
            $updateData['POL'] = $request->POL;
        }
        if ($request->has('POD')) {
            $updateData['POD'] = $request->POD;
        }

        $si->update($updateData);

        return response("success:Done Update Final SI");
    }

    public function generatePackingList(Request $request)
    {
        $shipment = ShipmentInstruction::where('Final_SI_No', $request->Task)->first();
        if (!$shipment) {
            // return response()->json(['error' => 'Shipping Instruction not found'], 404);
            echo "<script>alert('Shipping Instruction not found');</script>";
            return;
        }

        $ci = PackingList::where('Final_SI_No', $request->Task)->first();
        if ($ci) {
            // return response()->json(['error' => 'Commercial Invoice already generated for this Shipping Instruction'], 400);
            echo "<script>alert('Commercial Invoice already generated for this Shipping Instruction');</script>";
            return;
        }

        // $shipment->Status = "PACKING_LIST";
        // $shipment->save();

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();
        $vsd = VesselScheduleDtl::active()->where('Task_ID', $vs->Task_ID)
            ->get()
            ->map(function ($item) {
                return $item->Container . ' (' . $item->Seal . ')';
            })
            ->implode(', ');

        $vs = $vs->toArray();
        $vs['Last_ID'] = $vs['id'];
        $vs['Action_By'] = auth()->user()->username;
        $vs['Action_Date'] = now();
        $vs['Action_Status'] = "CREATE PL";
        $vs['Container'] = $vsd;
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
            // return response()->json(['error' => 'Shipping Instruction not found'], 404);
            echo "<script>alert('Shipping Instruction not found');</script>";
            return;
        }

        $ci = CommercialInvoice::where('SI_No', $request->Task)->first();
        if ($ci) {
            // return response()->json(['error' => 'Commercial Invoice already generated for this Shipping Instruction'], 400);
            echo "<script>alert('Commercial Invoice already generated for this Shipping Instruction');</script>";
            return;
        }

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();
        $vsd = VesselScheduleDtl::active()->where('Task_ID', $vs->Task_ID)
            ->get()
            ->map(function ($item) {
                return $item->Container . ' (' . $item->Seal . ')';
            })
            ->implode(', ');

        $vs = $vs->toArray();
        $vs['Last_ID'] = $vs['id'];
        $vs['Action_By'] = auth()->user()->username;
        $vs['Action_Date'] = now();
        $vs['Action_Status'] = "CREATE CI";
        $vs['Container'] = $vsd;
        unset($vs['id']);
        VesselScheduleLog::create($vs);

        $getVSD = VesselScheduleDtl::active()->where("Order_No", $vs['Order_No'])->where("SI_No", $shipment->SI_No)->sum('Netto_MT');
    
        $counter = CommercialInvoice::where('Inv_Date', "LIKE", date("Y-m") . "%")->count();
        
        // $ci = json_decode(json_encode($shipment), true);
        $ci = [];
        $ci['SI_No'] = $shipment->Final_SI_No;
        $ci["Inv_No"] = sprintf('%03d',$counter + 1) . "/INV-GEU/" . $this->getRomawi(date("n")) . "/" . date("Y");
        $ci['Inv_Date'] = date("Y-m-d");
        $ci['Shipper'] = $shipment->Shipper ?? '';
        $ci['Consignee'] = $shipment->Consignee ?? '';
        $ci['Description'] = "";
        $ci['Qty'] = $getVSD;
        $ci['Amount'] = 0;
        $ci['Advance_Payment'] = 0;
        $ci['Other'] = 0;
        $ci['Total'] = 0;
        $ci["Status"] = "CREATED";
        $ci['Created_By'] = auth()->user()->username;
        $ci['Created_Date'] = now();
        CommercialInvoice::insert($ci);

        echo "<script>alert('Commercial Invoice generated successfully');</script>";

        // return response()->json(['message' => 'Commercial Invoice generated successfully']);
    }
}
