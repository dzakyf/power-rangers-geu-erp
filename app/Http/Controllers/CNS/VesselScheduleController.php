<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\VesselSchedule;
use Illuminate\Http\Request;

class VesselScheduleController extends Controller
{
    public function indexVesselSchedule()
    {
        return view('cns.vs.vessel-schedule');
    }

    public function indexVesselScheduleXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        // Sample data, replace with actual database query
        $vessels = VesselSchedule::get();
        foreach ($vessels as $key => $vessel) {
            echo "<row id=\"$vessel->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Qty) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Vessel) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->POL) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->POD) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Stuffing) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Checking_CY) . "</cell>";
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
            if ($vessel->ATD != "0000-00-00" && $vessel->ATA != "0000-00-00") {
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
        $vs->Checking_CY = $request->input('Checking_CY');
        $vs->ETD = $request->input('ETD');
        $vs->ATD = $request->input('ATD');
        $vs->ETA = $request->input('ETA');
        $vs->ATA = $request->input('ATA');
        $vs->Note = $request->input('Note');
        $vs->save();
    }

    public function editVesselSchedule()
    {
        //
    }

    public function editVesselScheduleSubmit()
    {
        //
    }

    public function detailVesselSchedule()
    {
        return view('cns.vs.vessel-schedule-detail');
    }

    public function detailVesselScheduleSubmit(Request $request)
    {
        //
    }
}
