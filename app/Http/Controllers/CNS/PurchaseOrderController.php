<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function indexPurchaseRequest()
    {
        return view('cns.procurement.purchase-request');
    }

    public function indexPurchaseRequestXml()
    {
	
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


        $Saya = Auth::User()->username;
        $Name = Auth::User()->Name;
        $LSP = Auth::user()->Location;
        $getnik = Auth::user()->Emp_ID;
        $dept = Auth::user()->Department;

        $getlevelsx = DB::table("organization_structure")->where("NIK",$getnik)->first();
        $lepel = 1;

        if ($getlevelsx) {
            if ($getlevelsx->Level1 == 1) $lepel = 1;
            else if ($getlevelsx->Level2 == 1) $lepel = 2;
            else if ($getlevelsx->Level3 == 1) $lepel = 3;
            else if ($getlevelsx->Level4 == 1) $lepel = 4;
            else if ($getlevelsx->Level5 == 1) $lepel = 5;
            else if ($getlevelsx->Level6 == 1) $lepel = 6;
            else if ($getlevelsx->Level7 == 1) $lepel = 7;
            else if ($getlevelsx->Level8 == 1) $lepel = 8;
        }


        $kueri = DB::table("purchase_order_cns")->orderBy("ID","DESC")->get();

        echo "<rows>";
        if ($lepel == 3){
            if ($dept == 'FIN'){
                $kueri = DB::table("purchase_order_cns")->where("Status",'APPROVAL_FINANCE')->orderBy("ID","DESC")->get();
            }else{
                $kueri = DB::table("purchase_order_cns")->where("Approval_Department",$dept)->where("Status","APPROVAL_UPPER")->orderBy("ID","DESC")->get();

            }
        }elseif($lepel == 2){
            $kueri = DB::table("purchase_order_cns")->where("Status",'APPROVAL_VP')->orderBy("ID","DESC")->get();

        }elseif($lepel == 4){
            if ($dept == 'GEA'){
                $kueri = DB::table("purchase_order_cns")->where("Status",'APPROVAL_GA')->orderBy("ID","DESC")->get();

            }
        }

        $nomor = 1;
        $warnanya = "";
        foreach($kueri as $Fetch){

                echo "<row id=\"$Fetch->ID\">";
                        echo "<cell $warnanya>$nomor</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->PR_No))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->PR_Date))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Supplier))."</cell>"; 
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Payment_Term))."</cell>"; 
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Supplies_Type))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Status))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Note))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Created_Date))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Created_By))."</cell>";
                    echo "</row>";	

                $nomor++;
        }

        echo "</rows>";
    }
}
