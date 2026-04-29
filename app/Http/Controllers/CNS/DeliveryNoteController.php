<?php

namespace App\Http\Controllers\CNS;

use App\DeliveryNote;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryNoteController extends Controller
{
    public function indexDeliveryNote()
    {
        return view('cns.export.dn');
    }

    public function indexDeliveryNoteXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value->Company_Name;
        }

        $nomor = 1;
        $dns = DeliveryNote::get();
        foreach ($dns as $value) {
            $consigneeName = $companies[$value->Consignee] ?? $value->Consignee;
            echo "<row id=\"$value->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($value->DN_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Delivery_Date)."</cell>";
                echo "<cell>".htmlspecialchars($consigneeName)."</cell>";
                echo "<cell>".htmlspecialchars($value->Contract_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Product)."</cell>";
                echo "<cell>".htmlspecialchars($value->Total_Tonnage)."</cell>";
                echo "<cell>".htmlspecialchars($value->Country_Of_Origin)."</cell>";
                echo "<cell>".htmlspecialchars($value->Status)."</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function printDeliveryNote(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $dn = DeliveryNote::where('DN_No', $request->Task)->first();
        return view('cns.export.print.dn', compact('dn', 'companies'));
    }

    public function newDeliveryNote()
    {
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();

        $counter = DeliveryNote::whereYear('Delivery_Date', date('Y'))
            ->whereMonth('Delivery_Date', date('m'))
            ->count();
        $months = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $romawi = $months[date('n') - 1];
        $DnNo = sprintf('%03d', $counter + 1) . "/DN-GEU/$romawi/" . date('Y');

        return view('cns.export.dn-new', compact('companies', 'DnNo'));
    }

    public function newDeliveryNoteSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);
        $r['Created_By'] = auth()->user()->username;
        $r['Created_Date'] = now();
        $r['Status'] = 'CREATED';

        DeliveryNote::create($r);

        echo "success";
    }

    public function detailDeliveryNote(Request $request)
    {
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $dn = DeliveryNote::where('DN_No', $request->Task)->first();
        return view('cns.export.dn-detail', compact('companies', 'dn'));
    }

    public function detailDeliveryNoteSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);

        DeliveryNote::where('DN_No', $r['DN_No'])->update($r);

        echo "success";
    }
}
