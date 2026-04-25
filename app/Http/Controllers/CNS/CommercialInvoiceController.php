<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShipmentInstruction;
use App\VesselScheduleDtl;
use App\CommercialInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommercialInvoiceController extends Controller
{
    public function indexCommercialInvoice()
    {
        return view('cns.export.ci');
    }

    public function indexCommercialInvoiceXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        echo "</rows>";
    }

    public function printCommercialInvoice()
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->first();
        foreach ($company as $key => $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $invoice = CommercialInvoice::where('Status', "CREATED")->first();
        return view('cns.export.print.ci', compact('invoice', 'companies'));
    }

    public function newCommercialInvoice()
    {
        $sis = ShipmentInstruction::where("Status", "FINAL")->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.ci-new', compact('sis','companies'));
    }

    public function getSIDetail(Request $request)
    {
        $si = ShipmentInstruction::where('Final_SI_No', $request->input('SI_No'))->first();
        if ($si) {
            $vsd = VesselScheduleDtl::where("SI_No", $si->SI_No)->sum('Netto_MT');
            $si->Netto_MT = round($vsd, 2);
        }
        return response()->json($si);
    }
}
