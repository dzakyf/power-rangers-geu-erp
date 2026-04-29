<?php

namespace App\Http\Controllers\CNS;

use App\SustainabilityDeclaration;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SustainabilityDeclarationController extends Controller
{
    public function indexSustainabilityDeclaration()
    {
        return view('cns.export.sd');
    }

    public function indexSustainabilityDeclarationXml()
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
        $sds = SustainabilityDeclaration::get();
        foreach ($sds as $value) {
            $consigneeName = $companies[$value->Consignee] ?? $value->Consignee;
            echo "<row id=\"$value->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($value->SD_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Issue_Date)."</cell>";
                echo "<cell>".htmlspecialchars($consigneeName)."</cell>";
                echo "<cell>".htmlspecialchars($value->Contract_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Dispatch_Date)."</cell>";
                echo "<cell>".htmlspecialchars($value->Quantity)."</cell>";
                echo "<cell>".htmlspecialchars($value->Country_Of_Origin)."</cell>";
                echo "<cell>".htmlspecialchars($value->Status)."</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function printSustainabilityDeclaration(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $sd = SustainabilityDeclaration::where('SD_No', $request->Task)->first();
        return view('cns.export.print.sd', compact('sd', 'companies'));
    }

    public function newSustainabilityDeclaration()
    {
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();

        $counter   = SustainabilityDeclaration::whereYear('Issue_Date', date('Y'))->count();
        $SdNo      = date('Y') . sprintf('%04d', $counter + 1) . date('Ymd');

        return view('cns.export.sd-new', compact('companies', 'SdNo'));
    }

    public function newSustainabilityDeclarationSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);

        $r['ISCC_Compliant']         = isset($r['ISCC_Compliant']) ? 1 : 0;
        $r['EU_RED_Compliant']       = isset($r['EU_RED_Compliant']) ? 1 : 0;
        $r['Dispatch_Same_As_Supplier']   = isset($r['Dispatch_Same_As_Supplier']) ? 1 : 0;
        $r['Receipt_Same_As_Recipient']   = isset($r['Receipt_Same_As_Recipient']) ? 1 : 0;
        $r['GHG_DDV_Oil_Extraction'] = isset($r['GHG_DDV_Oil_Extraction']) ? 1 : 0;
        $r['GHG_DDV_Soil_N2O']       = isset($r['GHG_DDV_Soil_N2O']) ? 1 : 0;
        $r['Addon_FSS']              = isset($r['Addon_FSS']) ? 1 : 0;
        $r['Addon_EUDR']             = isset($r['Addon_EUDR']) ? 1 : 0;
        $r['Addon_NonGMO_Feed']      = isset($r['Addon_NonGMO_Feed']) ? 1 : 0;
        $r['Addon_NonGMO_Technical'] = isset($r['Addon_NonGMO_Technical']) ? 1 : 0;

        $r['Created_By']   = auth()->user()->username;
        $r['Created_Date'] = now();
        $r['Status']       = 'CREATED';

        SustainabilityDeclaration::create($r);

        echo "success";
    }

    public function detailSustainabilityDeclaration(Request $request)
    {
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $sd = SustainabilityDeclaration::where('SD_No', $request->Task)->first();
        return view('cns.export.sd-detail', compact('companies', 'sd'));
    }

    public function detailSustainabilityDeclarationSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);

        $r['ISCC_Compliant']              = isset($r['ISCC_Compliant']) ? 1 : 0;
        $r['EU_RED_Compliant']            = isset($r['EU_RED_Compliant']) ? 1 : 0;
        $r['Dispatch_Same_As_Supplier']   = isset($r['Dispatch_Same_As_Supplier']) ? 1 : 0;
        $r['Receipt_Same_As_Recipient']   = isset($r['Receipt_Same_As_Recipient']) ? 1 : 0;
        $r['GHG_DDV_Oil_Extraction']      = isset($r['GHG_DDV_Oil_Extraction']) ? 1 : 0;
        $r['GHG_DDV_Soil_N2O']            = isset($r['GHG_DDV_Soil_N2O']) ? 1 : 0;
        $r['Addon_FSS']                   = isset($r['Addon_FSS']) ? 1 : 0;
        $r['Addon_EUDR']                  = isset($r['Addon_EUDR']) ? 1 : 0;
        $r['Addon_NonGMO_Feed']           = isset($r['Addon_NonGMO_Feed']) ? 1 : 0;
        $r['Addon_NonGMO_Technical']      = isset($r['Addon_NonGMO_Technical']) ? 1 : 0;

        SustainabilityDeclaration::where('SD_No', $r['SD_No'])->update($r);

        echo "success";
    }
}
