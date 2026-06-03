<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShipmentInstruction;
use App\VesselScheduleDtl;
use App\CommercialInvoice;
use App\VesselSchedule;
use App\VesselScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommercialInvoiceController extends Controller
{
    public function indexCommercialInvoice()
    {
        return view('cns.export.ci');
    }

    public function indexCommercialInvoiceXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $key => $value) {
            $companies[$value->Supplier_ID] = $value->Company_Name;
        }

        $nomor = 1;
        $cis = CommercialInvoice::whereBetween('Inv_Date', [$request->tgl1, $request->tgl2])->get();
        foreach ($cis as $key => $value) {
            echo "<row id=\"$value->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($value->SI_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Inv_No)."</cell>";
                echo "<cell>".htmlspecialchars(date("d F Y", strtotime($value->Inv_Date)))."</cell>";
                echo "<cell>".htmlspecialchars($companies[$value->Consignee])."</cell>";
                echo "<cell>".htmlspecialchars($value->Description)."</cell>";
                echo "<cell>".htmlspecialchars($value->Qty)."</cell>";
                echo "<cell>".htmlspecialchars($value->Amount / $value->Qty)."</cell>";
                echo "<cell>".htmlspecialchars($value->Amount)."</cell>";
                echo "<cell>".htmlspecialchars($value->Amount)."</cell>";
                echo "<cell>".htmlspecialchars($value->Advance_Payment)."</cell>";
                echo "<cell>".htmlspecialchars($value->Other)."</cell>";
                echo "<cell>".htmlspecialchars($value->Total)."</cell>";
                echo "<cell>".htmlspecialchars($value->Note)."</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function printCommercialInvoice(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $key => $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $invoice = CommercialInvoice::where('Inv_No', $request->Task)->first();
        $buyer = $request->Buyer;
        return view('cns.export.print.ci', compact('invoice', 'companies','buyer'));
    }

    public function newCommercialInvoice()
    {
        $sis = ShipmentInstruction::where("Status", "FINAL")->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.ci-new', compact('sis','companies'));
    }

    public function getSIDetail(Request $request)
    {
        $si = ShipmentInstruction::where('Final_SI_No', $request->input('Final_SI_No'))->first();
        if ($si) {
            $vsd = VesselScheduleDtl::where("SI_No", $si->SI_No)->sum('Netto_MT');
            $si->Netto_MT = round($vsd, 2);
        }
        return response()->json($si);
    }

    public function newCommercialInvoiceSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);
        $r['Created_By'] = auth()->user()->username;
        $r['Created_Date'] = now();
        $r['SI_No'] = $r['Final_SI_No'];
        unset($r['Final_SI_No']);
        $r['Status'] = "CREATED";

        $getSI = ShipmentInstruction::where("Final_SI_No", $request->Final_SI_No)->first();
        if ($getSI) {
            $getVS = VesselSchedule::where("Order_No", $getSI->Order_No)->first();
            if ($getVS) {
                $getVS = json_decode(json_encode($getVS), true);
                $getVS['Last_ID'] = $getVS['id'];
                unset($getVS['id']);
    
                $getVS["Action_By"] = auth()->user()->username;
                $getVS['Action_Date'] = now();
                $getVS['Action_Status'] = "CREATE Commercial Invoice";
    
                VesselScheduleLog::create($getVS);
            }

        }

        CommercialInvoice::create($r);

        echo "success";
    }

    public function detailCommercialInvoice(Request $request)
    {
        $sis = ShipmentInstruction::where("Status", "FINAL")->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $ci = CommercialInvoice::where("Inv_No", $request->Task)->first();
        $ro = $ci->Status == 'CREATED' ? ['input' => '', 'select' => ''] : ['input' => 'readonly', 'select' => 'disabled'];
        return view('cns.export.ci-detail', compact('sis','companies', 'ci', 'ro'));
    }

    public function detailCommercialInvoiceSubmit(Request $request)
    {
        $r = $request->all();
        $id = $r['id'];
        unset($r['_token']);
        unset($r['id']);
        $cekCI = CommercialInvoice::where('ID', $id)->first();
        if (!$cekCI) {
            echo "error";
            return;
        }
        $notes = "";
        $status = "";
        if ($r['save'] == 1) {
            $notes = "SAVE AS DRAFT COMMERCIAL INVOICE";
            $status = "CREATED";
        }elseif($r['save'] == 2){
            $notes = "SUBMIT TO FINAL COMMERCIAL INVOICE";
            $status = "FINAL";
        }
        unset($r['save']);
        $r['Created_By'] = auth()->user()->username;
        $r['Created_Date'] = now();
        $r['SI_No'] = $r['Final_SI_No'];
        $r['status'] = $status;
        unset($r['Final_SI_No']);

        $getSI = ShipmentInstruction::where("Final_SI_No", $request->Final_SI_No)->first();
        if ($getSI) {
            $getVS = VesselSchedule::where("Order_No", $getSI->Order_No)->first();
            if ($getVS) {
                $getVS = json_decode(json_encode($getVS), true);
                $getVS['Last_ID'] = $getVS['id'];
                unset($getVS['id']);
    
                $getVS["Action_By"] = auth()->user()->username;
                $getVS['Action_Date'] = now();
                $getVS['Action_Status'] = $notes;
    
                VesselScheduleLog::create($getVS);
            }

        }

        CommercialInvoice::where('ID',$id)->update($r);
        echo "success";
    }
}
