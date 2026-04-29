<?php

namespace App\Http\Controllers\CNS;

use App\CertificateOfOrigin;
use App\CertificateOfOriginDtl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificateOfOriginController extends Controller
{
    public function indexCertificateOfOrigin()
    {
        return view('cns.export.coo');
    }

    public function indexCertificateOfOriginXml()
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
        $coos = CertificateOfOrigin::get();
        foreach ($coos as $value) {
            $consigneeName = $companies[$value->Consignee] ?? $value->Consignee;
            echo "<row id=\"$value->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($value->COO_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Fin_Inv_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Fin_Inv_Date)."</cell>";
                echo "<cell>".htmlspecialchars($value->Contract_No)."</cell>";
                echo "<cell>".htmlspecialchars($consigneeName)."</cell>";
                echo "<cell>".htmlspecialchars($value->Final_Destination)."</cell>";
                echo "<cell>".htmlspecialchars($value->Dispatch_Method ?? $value->Method_Of_Dispatch)."</cell>";
                echo "<cell>".htmlspecialchars($value->Shipment_Type)."</cell>";
                echo "<cell>".htmlspecialchars($value->HS_Code)."</cell>";
                echo "<cell>".htmlspecialchars($value->Good_Description)."</cell>";
                echo "<cell>".htmlspecialchars($value->Net_Weight)."</cell>";
                echo "<cell>".htmlspecialchars($value->Note)."</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function printCertificateOfOrigin(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $coo = CertificateOfOrigin::where('COO_No', $request->Task)->first();
        $coo->Details = CertificateOfOriginDtl::where('COO_No', $coo->COO_No)->get();
        return view('cns.export.print.coo', compact('coo', 'companies'));
    }

    public function newCertificateOfOrigin()
    {
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();

        $counter = CertificateOfOrigin::whereYear('Created_Date', date('Y'))
            ->whereMonth('Created_Date', date('m'))
            ->count();
        $months = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $romawi = $months[date('n') - 1];
        $CooNo = "COO-GEU/" . sprintf('%03d', $counter + 1) . "/$romawi/" . date('Y');

        return view('cns.export.coo-new', compact('companies', 'CooNo'));
    }

    public function newCertificateOfOriginSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);
        unset($r['containers']);
        unset($r['seals']);

        $r['Created_By']   = auth()->user()->username;
        $r['Created_Date'] = now();
        $r['Status']       = 'CREATED';

        CertificateOfOrigin::create($r);

        $containers = $request->containers ?? [];
        $seals      = $request->seals ?? [];
        foreach ($containers as $i => $container) {
            if (trim($container) === '') continue;
            CertificateOfOriginDtl::create([
                'COO_No'       => $r['COO_No'],
                'Container_No' => $container,
                'Seal_No'      => $seals[$i] ?? '',
            ]);
        }

        echo "success";
    }

    public function detailCertificateOfOrigin(Request $request)
    {
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $coo = CertificateOfOrigin::where('COO_No', $request->Task)->first();
        $coo->Details = CertificateOfOriginDtl::where('COO_No', $coo->COO_No)->get();
        return view('cns.export.coo-detail', compact('companies', 'coo'));
    }

    public function detailCertificateOfOriginSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);
        unset($r['containers']);
        unset($r['seals']);

        CertificateOfOrigin::where('COO_No', $r['COO_No'])->update($r);

        CertificateOfOriginDtl::where('COO_No', $r['COO_No'])->delete();

        $containers = $request->containers ?? [];
        $seals      = $request->seals ?? [];
        foreach ($containers as $i => $container) {
            if (trim($container) === '') continue;
            CertificateOfOriginDtl::create([
                'COO_No'       => $r['COO_No'],
                'Container_No' => $container,
                'Seal_No'      => $seals[$i] ?? '',
            ]);
        }

        echo "success";
    }
}
