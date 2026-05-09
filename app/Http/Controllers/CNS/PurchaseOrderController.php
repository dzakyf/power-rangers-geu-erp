<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function indexPurchaseOrder()
    {
        return view('cns.procurement.purchase-order');
    }

    public function indexPurchaseOrderXml()
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
        echo "<rows>";

        $kueri = DB::table('purcahse_order_cns')->where("Status", "CLOSED")->get();
        $nomor = 1;

            foreach($kueri as $Fetch){
                $Row_Usr = DB::table("users")->where("username",$Fetch->User_Trx)->first();
                $namasup = DB::table("supplier")->where("Supplier_ID",$Fetch->Supplier)->first();

            
                $datadesc = "";
                $sql_detail = DB::table("purchase_order_dtl")->where("PR_No",$Fetch->PR_No)->get();
                foreach($sql_detail as $row_detail){
                    $datadesc .= $row_detail->Product_Desc . " ($row_detail->Qty $row_detail->Unit),";
                }
                
                $datadesc = rtrim($datadesc,",");
            
                echo "<row id=\"$Fetch->PO_No\">";
                    echo "<cell>$nomor</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->PO_No))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->PR_No))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->PO_No_KSPS))."</cell>";
                    echo "<cell>".htmlspecialchars(($namasup->Company_Name))."</cell>";
                    echo "<cell>".htmlspecialchars(((date("d-m-Y H:i", strtotime($Fetch->Date_Trx)))))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Purchase_Category))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Purchase_Sub_Category))."</cell>"; 
                    echo "<cell>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
                    echo "<cell>".htmlspecialchars(($Row_Usr->Name))."</cell>";
                    echo "<cell>".htmlspecialchars(($Row_Usr->Department))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Status))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Created_By))."</cell>";
                    echo "<cell>".htmlspecialchars(($datadesc))."</cell>";
                echo "</row>";
                $nomor++;
            }

        echo "</rows>";
    }
}
