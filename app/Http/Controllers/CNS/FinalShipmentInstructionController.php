<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShipmentInstruction;
use App\VesselSchedule;
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

        $shipments = ShipmentInstruction::where('Status', "FINAL")->get();
        foreach ($shipments as $key => $shipment) {
            echo "<row id=\"$shipment->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Qty) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Vessel) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->POL) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->POD) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Stuffing) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Checking_CY) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->ETD) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->ATD) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->ETA) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->ATA) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Note) . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    public function newFinalShipmentInstruction()
    {
        $vss = VesselSchedule::get();
        $sis = ShipmentInstruction::where('Status', "CREATED")->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.si-new', compact('vss', 'sis', 'companies'));
    }

    public function getSI(Request $request)
    {
        $SINo = $request->input('SI_No');
        $shipment = ShipmentInstruction::where('SI_No', $SINo)->first();
        if ($shipment) {
            return response()->json([
                'Vessel' => $shipment->Vessel,
                'Qty1' => $shipment->Qty1,
                'Qty2' => $shipment->Qty2,
            ]);
        } else {
            return response()->json(['error' => 'Shipment Instruction not found'], 404);
        }
    }
}
