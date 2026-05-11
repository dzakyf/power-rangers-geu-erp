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

        $kueri = DB::table('purchase_order_cns')->where("Status", "CLOSED")->get();
        $nomor = 1;

            foreach($kueri as $Fetch){
                $Row_Usr = DB::table("users")->where("username",$Fetch->User_Trx)->first();
                $namasup = DB::table("supplier")->where("Supplier_ID",$Fetch->Supplier)->first();

            
                $datadesc = "";
                $sql_detail = DB::table("purchase_order_cns_dtl")->where("PO_No",$Fetch->PO_No)->get();
                foreach($sql_detail as $row_detail){
                    $datadesc .= $row_detail->Product_Desc . " ($row_detail->Qty $row_detail->Unit),";
                }
                
                $datadesc = rtrim($datadesc,",");
            
                echo "<row id=\"$Fetch->PO_No\">";
                    echo "<cell>$nomor</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->PO_No))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->PR_No))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->PO_No_KSPS))."</cell>";
                    echo "<cell>".htmlspecialchars(($namasup->Company_Name ?? ''))."</cell>";
                    echo "<cell>".htmlspecialchars(((date("d-m-Y H:i", strtotime($Fetch->Date_Trx)))))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Purchase_Category))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Purchase_Sub_Category))."</cell>"; 
                    echo "<cell>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
                    echo "<cell>".htmlspecialchars(($Row_Usr->Name))."</cell>";
                    echo "<cell>".htmlspecialchars(($Row_Usr->Department))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Status))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->User_Trx))."</cell>";
                    echo "<cell>".htmlspecialchars(($datadesc))."</cell>";
                echo "</row>";
                $nomor++;
            }

        echo "</rows>";
    }

    public function detailPurchaseOrder()
    {
        return view('cns.procurement.purchase-order-detail');
    }
    
    function Products(string $PID)
    {
        if ($PID == '') {
            $fx = DB::table("product_code")->where("Product_Code", $PID)->first();
            return $fx;
        }
        return json_decode(json_encode([]));
    }

    public function detailPurchaseOrderXml(Request $request)
    {

        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $nomor = 1;
        //if(isset($_GET->src)) $src = $_GET->src; else $src = 1;

        $Task_ID = $_GET['ID'];
        $kueri = DB::select(DB::raw("SELECT * FROM purchase_summary_cns_detail WHERE Task_ID='$Task_ID'"));
        echo "<rows>";
        foreach ($kueri as $Fetch) {

            $NamaProduk = $Fetch->Product_Desc;
            $Products = Products($Fetch->Product_Code);
            if (!empty($Products)) {
                if (trim($Products->Description) == trim($Fetch->Specification)) {
                    $NamaProduk = trim($Products->Description) . "#" . trim($Fetch->Recommended_Spec);
                } else {
                    $NamaProduk = $Fetch->Specification. "#" .$Fetch->Recommended_Spec;
                }
            }
            


            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Product_Code)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($NamaProduk . " [" . $Fetch->Unit_Name . "]")) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Qty)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->Proposed)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Products->UnitPrice)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V1_Propose)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V1_TOP)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V1_Win)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V2_Propose)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V2_TOP)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V2_Win)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V3_Propose)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V3_TOP)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V3_Win)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V4_Propose)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V4_TOP)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V4_Win)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V5_Propose)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V5_TOP)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->V6_Win)) . "</cell>";
            echo "<cell>" . htmlspecialchars(($Fetch->PR_Related)) . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";

    }

    public function printPurchaseOrder()
    {
        return view('cns.procurement.print.purchase-order');
    }
}
