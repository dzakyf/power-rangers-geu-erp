<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShipmentInstruction;
use Illuminate\Http\Request;

class WeightBridgeTicketController extends Controller
{
    public function indexWeightBridgeTicket()
    {
        return view('cns.export.wbt');
    }

    public function indexWeightBridgeTicketXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        // --- IGNORE ---

        echo "</rows>";
    }

    public function newWeightBridgeTicket()
    {
        $sis = ShipmentInstruction::where('Status', "FINAL")->get();
        return view('cns.export.wbt-new', compact('sis'));
    }

    public function getSIDetail(Request $request)
    {
        $si = ShipmentInstruction::where('SI_No', $request->input('FSI_No'))->first();
        return response()->json($si);
    }
}
