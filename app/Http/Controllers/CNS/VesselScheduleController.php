<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShipmentInstruction;
use App\VesselSchedule;
use App\PackingList;
use App\VesselScheduleLog;
use Illuminate\Http\Request;

class VesselScheduleController extends Controller
{
    public function indexVesselSchedule()
    {
        return view('cns.vs.vessel-schedule');
    }

    public function indexVesselScheduleXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        // Sample data, replace with actual database query
        $vessels = VesselSchedule::whereBetween('Stuffing', [$request->tgl1, $request->tgl2])->get();
        foreach ($vessels as $key => $vessel) {
            echo "<row id=\"$vessel->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Qty) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Vessel) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->POL) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->POD) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Stuffing) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Checking_BC) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Closing_CY) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ETD) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ATD) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ETA) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ATA) . "</cell>";
            if ($vessel->ETD != "0000-00-00" && $vessel->ETA != "0000-00-00") {
                $etd = new \DateTime($vessel->ETD);
                $eta = new \DateTime($vessel->ETA);
                $interval = $etd->diff($eta);
                echo "<cell>" . htmlspecialchars($interval->format('%a days')) . "</cell>";
            } else {
                echo "<cell></cell>";
            }
            if (($vessel->ATD != "0000-00-00" && $vessel->ATA != "0000-00-00") && ($vessel->ATD != null && $vessel->ATA != null)) {
                $atd = new \DateTime($vessel->ATD);
                $ata = new \DateTime($vessel->ATA);
                $interval = $atd->diff($ata);
                echo "<cell>" . htmlspecialchars($interval->format('%a days')) . "</cell>";
            } else {
                echo "<cell></cell>";
            }
            echo "<cell>" . htmlspecialchars($vessel->Note) . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    public function newVesselSchedule()
    {
        return view('cns.vs.vessel-schedule-new');
    }

    public function newVesselScheduleSubmit(Request $request)
    {
        $cek = VesselSchedule::where('Order_No', $request->input('Order_No'))->first();
        if ($cek) {
            echo "duplicate";
            return;
        }
        $vs = new VesselSchedule();
        $vs->Task_ID = "VS/GEU/" . substr(md5(auth()->user()->username), 0 , 5) . "/" . date('YmdHis');
        $vs->Order_No = $request->input('Order_No');
        $vs->Qty = $request->input('Qty1') . " x " . $request->input('Qty2');
        $vs->Qty1 = $request->input('Qty1');
        $vs->Qty2 = $request->input('Qty2');
        $vs->Vessel = $request->input('Vessel');
        $vs->POL = $request->input('POL');
        $vs->POD = $request->input('POD');
        $vs->Stuffing = $request->input('Stuffing');
        $vs->Checking_BC = $request->input('Checking_BC');
        $vs->Closing_CY = $request->input('Closing_CY');
        $vs->ETD = $request->input('ETD');
        $vs->ETA = $request->input('ETA');
        $vs->Note = $request->input('Note');
        $vs->Created_By = auth()->user()->username;
        $vs->save();

        $vsl = new VesselScheduleLog();
        $vsl->Task_ID = "VS/GEU/" . substr(md5(auth()->user()->username), 0 , 5) . "/" . date('YmdHis');
        $vsl->Order_No = $request->input('Order_No');
        $vsl->Qty = $request->input('Qty1') . " x " . $request->input('Qty2');
        $vsl->Qty1 = $request->input('Qty1');
        $vsl->Qty2 = $request->input('Qty2');
        $vsl->Vessel = $request->input('Vessel');
        $vsl->POL = $request->input('POL');
        $vsl->POD = $request->input('POD');
        $vsl->Stuffing = $request->input('Stuffing');
        $vsl->Checking_BC = $request->input('Checking_BC');
        $vsl->Closing_CY = $request->input('Closing_CY');
        $vsl->ETD = $request->input('ETD');
        $vsl->ETA = $request->input('ETA');
        $vsl->Note = $request->input('Note');
        $vsl->Created_By = auth()->user()->username;
        $vsl->Created_Date = now();
        $vsl->Action_By = auth()->user()->username;
        $vsl->Action_Date = now();
        $vsl->Action_Status = "CREATED";
        $vsl->Last_ID = $vs->id;

        $vsl->save();

        echo "success";
    }

    public function editVesselSchedule()
    {
        //
    }

    public function editVesselScheduleSubmit()
    {
        //
    }

    public function detailVesselSchedule(Request $request)
    {
        $vs = VesselSchedule::where('id', $request->task)->first();
        return view('cns.vs.vessel-schedule-detail', compact('vs'));
    }

    public function detailVesselScheduleSubmit(Request $request)
    {
        $vs = VesselSchedule::where('id', $request->input('id'))->first();

        $cekSI = ShipmentInstruction::where('Order_No', $vs->Order_No)->first();

        if ($cekSI) {
            $cekSI->Order_No = $request->input('Order_No');
            $cekSI->save();
        }

        $cekPackingList = PackingList::where('Order_No', $vs->Order_No)->first();
        
        if ($cekPackingList) {
            $cekPackingList->Order_No = $request->input('Order_No');
            $cekPackingList->save();
        }

        $vs->Order_No = $request->input('Order_No');
        $vs->Qty = $request->input('Qty1') . " x " . $request->input('Qty2');
        $vs->Qty1 = $request->input('Qty1');
        $vs->Qty2 = $request->input('Qty2');
        $vs->Vessel = $request->input('Vessel');
        $vs->POL = $request->input('POL');
        $vs->POD = $request->input('POD');
        $vs->Stuffing = $request->input('Stuffing');
        $vs->Checking_BC = $request->input('Checking_BC');
        $vs->Closing_CY = $request->input('Closing_CY');
        $vs->ETD = $request->input('ETD');
        $vs->ATD = $request->input('ATD');
        $vs->ETA = $request->input('ETA');
        $vs->ATA = $request->input('ATA');
        $vs->Note = $request->input('Note');
        $vs->Created_By = auth()->user()->username;
        $vs->save();

        $vs = VesselSchedule::where('id', $request->input('id'))->first();
        $vs = $vs->toArray();
        $vs['Last_ID'] = $vs['id'];
        unset($vs['id']);
        $vs['Action_By'] = auth()->user()->username;
        $vs['Action_Date'] = now();
        $vs['Action_Status'] = "UPDATE DATA";
        $vsl = VesselSchedule::create($vs);

        echo "success";
    }
}
