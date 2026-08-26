<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcurementApprovalController extends Controller
{
    public function index()
    {
        return view('humanresources.procurement.approval');
    }

    public function data()
    {
        $Saya = Auth::User()->username;
        $Name = Auth::User()->Name;
        $LSP = Auth::user()->Location;
        $getnik = Auth::user()->Emp_ID;
        $dept = Auth::user()->Department;
        $pos = Auth::user()->Position;

        // $pos = DB::table('position')->where("Initial", $pos)->first();

        $getlevelsx = DB::table("organization_structure")->where("NIK",$getnik)->first();
        $lepel = 0;

        if ($getlevelsx->Level1 == 1) $lepel = 1;
        else if ($getlevelsx->Level2 == 1) $lepel = 2;
        else if ($getlevelsx->Level3 == 1) $lepel = 3;
        else if ($getlevelsx->Level4 == 1) $lepel = 4;
        else if ($getlevelsx->Level5 == 1) $lepel = 5;
        else if ($getlevelsx->Level6 == 1) $lepel = 6;
        else if ($getlevelsx->Level7 == 1) $lepel = 7;
        else if ($getlevelsx->Level8 == 1) $lepel = 8;
        if ($lepel > 5) {
            return;
        }


        echo "<rows>";

        $kueri = DB::table('purchase_request')->where("Location", $LSP)->orderBy('ID', "DESC");

        if ($dept != 'HCGA') {
            $kueri->where("Approval_Department",$dept);
            if ($lepel == 1) {
                $kueri->where("Status", "APPROVAL_DEPT");
            }else if($lepel == 3) {
                $kueri->where("Status","APPROVAL_UPPER");
            }
        }else {
            $kueri->where("Status", "APPROVAL_GA");
        }
        
        $kueri = $kueri->get();


        $nomor = 1;
        foreach($kueri as $Fetch){

                echo "<row id=\"$Fetch->ID\">";
                        echo "<cell>$nomor</cell>";
                        echo "<cell>".htmlspecialchars(($Fetch->PR_No))."</cell>";
                        echo "<cell>".htmlspecialchars(($Fetch->PR_Date))."</cell>";
                        echo "<cell>".htmlspecialchars(($Fetch->Category))."</cell>"; 
                        echo "<cell>".htmlspecialchars(($Fetch->Sub_Category))."</cell>"; 
                        echo "<cell>".htmlspecialchars(($Fetch->Supplies_Type))."</cell>";
                        echo "<cell>".htmlspecialchars(($Fetch->Status))."</cell>";
                        echo "<cell>".htmlspecialchars(($Fetch->Note))."</cell>";
                        echo "<cell>".htmlspecialchars(($Fetch->Created_Date))."</cell>";
                        echo "<cell>".htmlspecialchars(($Fetch->Created_By))."</cell>";
                    echo "</row>";	

                $nomor++;
        }

        echo "</rows>";
    }

    public function detail(Request $request)
    {
        $dpt = Auth::user()->Department;
        $pr = DB::table('purchase_request')->where("PR_No", $request->PR_No)->first();
        $Query = DB::table("master_department");
        if ($dpt != 'ICT' && $dpt != 'BOD' && $dpt != 'HCGA') {
            $Query->where("Initial", $dpt);
        }
        
        $Department = $Query->get();
        $company = DB::table('supplier')->where("Status", 'NORMAL')->get();

        $pr->details = DB::table('purchase_request_dtl')->where("PR_No", $request->PR_No)->get();
        return view('humanresources.procurement.approval-detail', compact('pr','Department','company'));
    }
}
