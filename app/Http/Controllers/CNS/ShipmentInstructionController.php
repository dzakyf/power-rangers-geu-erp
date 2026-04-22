<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShipmentInstruction;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentInstructionController extends Controller
{
    public function indexShipmentInstruction()
    {
        return view('cns.export.si');
    }

    public function indexShipmentInstructionXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $shipments = ShipmentInstruction::where('Status', "CREATED")->get();
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

    public function newShipmentInstruction()
    {
        $vss = VesselSchedule::get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.si-new', compact('vss', 'companies'));
    }

    public function getOrderDetail(Request $request)
    {
        $orderNo = $request->input('Order_No');
        $orderDetail = VesselSchedule::where('Order_No', $orderNo)->first();
        $orderDetails = VesselScheduleDtl::where('Order_No', $orderNo)->get();
        if ($orderDetail) {
            return response()->json([
                'Qty' => $orderDetail->Qty,
                'Qty1' => $orderDetail->Qty1,
                'Qty2' => $orderDetail->Qty2,
                'Vessel' => $orderDetail->Vessel,
                'Stuffing' => $orderDetail->Stuffing,
                'ETD' => $orderDetail->ETD,
                'ETA' => $orderDetail->ETA,
                'POL' => $orderDetail->POL,
                'POD' => $orderDetail->POD,
                'orderDetails' => $orderDetails
            ]);
        } else {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }
    
    public function newShipmentInstructionSubmit(Request $request)
    {
        // Validate the request data
        $request->validate([
            'Order_No' => 'required|exists:vessel_schedule,Order_No',
            'Container1' => 'required',
            'Container2' => 'nullable',
            'Container3' => 'nullable',
            'Container4' => 'nullable',
            'Container5' => 'nullable',
        ]);

        // Process the form submission
        $orderNo = $request->input('Order_No');
        $containers = [];
        for ($i = 1; $i <= 5; $i++) {
            $container = $request->input("Container$i");
            if ($container) {
                $containers[] = $container;
            }
        }

        ShipmentInstruction::create([
            'SI_No' => $request->input('SI_No'),
            'Order_No' => $orderNo,
            'Qty' => $request->input('Qty1') . " x " . $request->input('Qty2'),
            'Qty1' => $request->input('Qty1'),
            'Qty2' => $request->input('Qty2'),
            'SI_Date' => $request->input('SI_Date'),
            'Shipper' => $request->input('Shipper'),
            'Consignee' => $request->input('Consignee'),
            'Notify' => $request->input('Notify'),
            'Destination_Letter' => $request->input('Destination_Letter'),
            'Delivery_Terms' => $request->input('Delivery_Terms'),
            'Contract_No' => $request->input('Contract_No'),
            'Voyage' => $request->input('Voyage'),
            'Delivery_Terms' => $request->input('Delivery_Terms'),
            'ETD' => $request->input('ETD'),
            'ETA' => $request->input('ETA'),
            'Stuffing' => $request->input('Stuffing'),
            'POL' => $request->input('POL'),
            'POD' => $request->input('POD'),
            'PEB_Date' => $request->input('PEB_Date'),
            'PEB_No' => $request->input('PEB_No'),
            'BL_No' => $request->input('BL_No'),
            'HS_Code' => $request->input('HS_Code'),
            'Status' => "CREATED",
            'Created_Date' => now(),
            'Created_By' => auth()->user()->username
        ]);

        foreach ($containers as $key => $value) {
            VesselScheduleDtl::where("ID", $value)->update([
                'SI_No' => $request->SI_No
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Shipment Instruction submitted successfully']);
    }
    
    public function generateFinalShipmentInstruction(Request $request)
    {
        $counter = DB::table("shipment_instructions")->where('SI_Date', "LIKE", date("Y-m"). "%")->where('Status', "FINAL")->count();

        $SINo = "FINALSI/EXP/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");
        ShipmentInstruction::where("ID", $request->task)->update([
            'Final_SI_No' => $SINo,
            'Status' => "FINAL",
            'Updated_Date' => now(),
            'Updated_By' => auth()->user()->username
        ]);

        // return response()->json(['success' => true, 'message' => 'Shipment Instruction finalized successfully']);
        echo "<script>alert('Done Generate to Final SI')</script>";
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
}
