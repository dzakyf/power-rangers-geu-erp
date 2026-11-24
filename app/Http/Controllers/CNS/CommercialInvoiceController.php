<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShippingInstruction;
use App\VesselScheduleDtl;
use App\CommercialInvoice;
use App\Support\DhtmlxGridPagination;
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
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $companies = [];
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value->Company_Name;
        }

        $query = CommercialInvoice::whereBetween('Inv_Date', [$request->tgl1, $request->tgl2]);

        $colMap = [
            1 => 'SI_No',
            2 => 'Inv_No',
            3 => 'Inv_Date',
            6 => 'Description',
            7 => 'Qty',
            9 => 'Amount',
            10 => 'Amount',
            11 => 'Advance_Payment',
            12 => 'Other',
            13 => 'Total',
            14 => 'Note',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($paginated['rows'] as $value) {
            $unitPrice = (!empty($value->Qty) && (float)$value->Qty != 0) ? ($value->Amount / $value->Qty) : 0;
            $bodyXml .= "<row id=\"$value->ID\">";
            $bodyXml .= "<cell>$nomor</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->SI_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Inv_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars(date("d F Y", strtotime($value->Inv_Date)))."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($companies[$value->Shipper] ?? '')."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($companies[$value->Consignee] ?? '')."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Description)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Qty)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($unitPrice)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Amount)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Amount)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Advance_Payment)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Other)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Total)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Note)."</cell>";
            $bodyXml .= "</row>\n";

            $nomor++;
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
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
        $sis = ShippingInstruction::where("Status", "FINAL")->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.ci-new', compact('sis','companies'));
    }

    public function getSIDetail(Request $request)
    {
        $si = ShippingInstruction::where('Final_SI_No', $request->input('Final_SI_No'))->first();
        if ($si) {
            $vsd = VesselScheduleDtl::active()->where("SI_No", $si->SI_No)->sum('Netto_MT');
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

        $getSI = ShippingInstruction::where("Final_SI_No", $request->Final_SI_No)->first();
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
        $sis = ShippingInstruction::where("Status", "FINAL")->get();
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

        $getSI = ShippingInstruction::where("Final_SI_No", $request->Final_SI_No)->first();
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
