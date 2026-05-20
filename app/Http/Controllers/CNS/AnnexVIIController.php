<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ShipmentInstruction;

class AnnexVIIController extends Controller
{
    public function __construct()
    {
        // throw new \Exception('Not implemented');
    }

    public function indexAnnex()
    {
        return view('cns.export.annex-index');
    }

    public function annexNew(Request $request)
    {
        $shipment = null;
        // accept SI_No or id parameter to lookup shipment
        if ($request->has('SI_No')) {
            $shipment = ShipmentInstruction::where('SI_No', $request->SI_No)->first();
        } elseif ($request->has('id')) {
            $shipment = ShipmentInstruction::where('id', $request->id)->first();
        }

        return view('cns.export.annex-new', compact('shipment'));
    }

    public function getAnnexData(Request $r)
    {
        return;
    }

    public function submitAnnexData() {}

    public function detailAnnexData() {}

    public function deleteAnnexDatra() {}

    public function printAnnex()
    {
        return view('cns.export.print.annex-print');
    }

    public function printAnnexSample()
    {
        return view('cns.export.print.annex-print-sample');
    }
}
