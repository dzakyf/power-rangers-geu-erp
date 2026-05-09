<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    public function indexPurchaseRequest()
    {
        return view('cns.procurement.purchase-request');
    }

    public function indexPurchaseRequestXml(Request $request)
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


        $kueri = DB::table("purchase_request_cns")->whereBetween('PR_Date', [$request->tgl1, $request->tgl2])->orderBy("ID","DESC")->get();

        echo "<rows>";
        if ($lepel == 3){
            if ($dept == 'FIN'){
                $kueri = DB::table("purchase_request_cns")->where("Status",'APPROVAL_FINANCE')->orderBy("ID","DESC")->get();
            }else{
                $kueri = DB::table("purchase_request_cns")->where("Approval_Department",$dept)->where("Status","APPROVAL_UPPER")->orderBy("ID","DESC")->get();

            }
        }elseif($lepel == 2){
            $kueri = DB::table("purchase_request_cns")->where("Status",'APPROVAL_VP')->orderBy("ID","DESC")->get();

        }elseif($lepel == 4){
            if ($dept == 'GEA'){
                $kueri = DB::table("purchase_request_cns")->where("Status",'APPROVAL_GA')->orderBy("ID","DESC")->get();

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

    public function newsPurchaseRequest()
    {
        $LSP = Auth::user()->Location;
        $Saya =  Auth::user()->username;
        $NIK =  Auth::user()->Emp_ID;

        $dpt =  Auth::user()->Department;
        $date = date('Y');
        $count = DB::table('purchase_request_cns')->where("PR_Date", "LIKE", $date . "%")->where("Location", $LSP)->count() + 1;
        $PR_No = strtoupper("PR-EXP/" . sprintf("%03d", $count) . '/' .  $date);

        return view('cns.procurement.purchase-request-news', compact('PR_No', 'NIK', 'Saya'));
    }

    public function newPurchaseRequest()
    {
        return view('cns.procurement.purchase-request-new');
    }

    public function newPurchaseRequestSubmit(Request $request)
    {
        $ars = "";
        $ax = "";
        $isi = "";
        $arr = [];
        foreach ($_POST as $key => $value) {

            if ($key != 'DelPRNewDetail' && $key != 'SubmitPRNewDetail' && $key != 'AddPRNewDetail' && $key != 'Supplier_Name') {
                $ars .= "$key = '$value',";
                $ax .= "$key,";
                $isi .= "'$value',";
                $arr[$key] = $value;
            }
        }
        $ars = substr($ars, 0, -1);
        $ax = substr($ax, 0, -1);
        $isi = substr($isi, 0, -1);

        $Saya = Auth::User()->username;
        $Name = Auth::User()->Name;
        $LSP = Auth::user()->Location;
        $getnik = Auth::user()->Emp_ID;
        $dept = Auth::user()->Department;
        // $getlevelsx = DB::table("organization_structure")->where("NIK", $getnik)->first();
        // if ($getlevelsx->Level1 == 1) $lepel = 1;
        // else if ($getlevelsx->Level2 == 1) $lepel = 2;
        // else if ($getlevelsx->Level3 == 1) $lepel = 3;
        // else if ($getlevelsx->Level4 == 1) $lepel = 4;
        // else if ($getlevelsx->Level5 == 1) $lepel = 5;
        // else if ($getlevelsx->Level6 == 1) $lepel = 6;
        // else if ($getlevelsx->Level7 == 1) $lepel = 7;
        // else if ($getlevelsx->Level8 == 1) $lepel = 8;
        // $upper = $lepel - 1;
        // if ($upper == 3) {
        //     $getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", $_POST['Department'])->where("Alerted", 1)->first();
        //     $status = "APPROVAL_UPPER";
        //     $link = 'approvalpr';
        // } else {
        //     $getupper = DB::table("organization_structure")->where("Level2", 1)->where("Department", $getlevelsx->divisi)->where("Alerted", 1)->first();
        //     $status = "APPROVAL_UPPER";
        //     $link = 'approvalpr';
        // }

        $arr['Status'] = 'APPROVAL_UPPER';
        $arr['Created_By'] = $Saya;
        $arr['Location'] = $LSP;
        $arr['Approval_Date'] = now();
        $arr['Creator_Level'] = 1;

        DB::table('purchase_request_cns')->insert($arr);
        DB::table('purchase_log')->insert([
            'Location' => $LSP,
            'Task_ID' => $request->PR_No,
            'Category' => '',
            'Sub_Category' => '',
            'Purchase_Type' => $request->Supplies_Type,
            'Transaction' => 'PURCHASE-REQUEST',
            'Status' => 'APPROVAL_UPPER',
            'Remark' => $request->Note,
            'Date_Trx' => now(),
            'User_Trx' => $Saya,
        ]);


        // DB::insert(DB::raw("INSERT INTO purchase_request($ax,Status,Created_By,Location,Approval_Date,Creator_Level) VALUES($isi,'APPROVAL_UPPER','$Saya','$LSP',NOW(),1)"));
        // DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$_POST[PR_No]', '$_POST[Category]', '$_POST[Sub_Category]', '$_POST[Supplies_Type]', 'PURCHASE-REQUEST', 'status', '$_POST[Note]', NOW(), '$Saya')"));

        echo "success";
    }

    public function newPurchaseRequestUpdate(Request $request)
    {
        header("Content-type: text/xml");
        echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 

        error_reporting(E_ALL ^ E_NOTICE);

        echo "<data>";

        $request_body = urldecode(file_get_contents('php://input'));

        $ids = explode("&",$request_body);
        $param = [];
        foreach($ids as $datax){
            
            $idenya = explode("=",$datax);
            $param[$idenya[0]] = $idenya[1];
            
        }
        $checkids = explode(",",$param["ids"]);

        for ($i=0; $i < sizeof($checkids); $i++) { 
            $action = "";
            $rowId = $checkids[$i]; //id or row which was updated 
            $newId = $rowId; //will be used for insert operation	
            $mode = $param[$rowId."_!nativeeditor_status"]; 
                switch($mode){
                    case "inserted":
                        $action = $this->insertNewPurchaseRequestUpdate($rowId,$param, $request->PR_No);
                        break;
                }
                    
            
                
            echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
        }
        echo "</data>";
    }

    public function insertNewPurchaseRequestUpdate(string $rowId, array $param, string $PR_No)
    {
        $LSP = Auth::user()->Location;
        $Saya =  Auth::user()->username;
        $NIK =  Auth::user()->Emp_ID;

        $a = $param[$rowId."_c1"];
        $b = $param[$rowId."_c2"];
        $c = $param[$rowId."_c3"];
        $d = $param[$rowId."_c4"];
        $e = $param[$rowId."_c5"];
        $f = $param[$rowId."_c6"];
        $g = $param[$rowId."_c7"];
        $h = $param[$rowId."_c8"];
        $ix = $param[$rowId."_c9"];
        $prices = $param[$rowId."_c10"]+0;
        DB::table('purchase_request_cns_dtl')->insert([
            'PR_No' => $PR_No,
            'Category' => $h,
            'Product_Code' => $b,
            'Product_Desc' => $c,
            'Qty' => $d,
            'Specification' => $e,
            'Unit' => $f,
            'Remark' => $g,
            'Location' => $LSP,
            'Purchase_Type' => $a,
            'Purchase_Category' => $ix,
            'Default_Price' => $prices,
        ]);
        // DB::insert(DB::raw("insert into purchase_request_dtl (PR_No,Category,Product_Code,Product_Desc,Qty,Specification,Unit,Remark,Location,Purchase_Type,Purchase_Category,Default_Price) values('$PR_No','$h','$b','$c','$d','$e','$f','$g','$LSP','$a','$ix','$prices')"));        
                
        return "insert";
    }

    public function detailPurchaseRequest(Request $request)
    {
        return view('cns.procurement.purchase-request-detail');
    }

    public function detailPurchaseRequestXml(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


        $LSP = Auth::user()->Location;
        $PRApproval = Auth::user()->PR_Approval;
        $Saya = Auth::user()->username;

        echo "<rows>";
        $kueri = DB::select(DB::raw("SELECT * FROM purchase_request_cns_dtl WHERE PR_No='$_GET[ID]' ORDER BY ID ASC"));
        $nomor = 1;
        $warnanya = "";

        foreach($kueri as $Fetch){

                echo "<row id=\"$Fetch->ID\">";
                        echo "<cell $warnanya>$nomor</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Product_Code))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Product_Desc))."</cell>"; 
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Qty))."</cell>"; 
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Specification))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Unit))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Remark))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Category))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Purchase_Category))."</cell>";
                        echo "<cell $warnanya>".htmlspecialchars(($Fetch->Default_Price))."</cell>";
                    echo "</row>";	

                $nomor++;
        }

        echo "</rows>";
    }

    public function detailPurchaseRequestApprove(Request $request)
    {
        $cek = DB::table('purchase_request_cns')->where('PR_No', $request->PR_No)->first();
        if (!$cek) {
            echo "error";
            return;
        }

        DB::table('purchase_request_cns')->where('PR_No', $request->PR_No)->update([
            'Status' => 'APPROVED',
            'Approval_Date' => now(),
            'Approval_User' => auth()->user()->username,
        ]);

        DB::table('purchase_log')->insert([
            'Location' => Auth::user()->Location,
            'Task_ID' => $request->PR_No,
            'Category' => '',
            'Sub_Category' => '',
            'Purchase_Type' => $request->Supplies_Type,
            'Transaction' => 'PURCHASE-REQUEST',
            'Status' => 'APPROVED',
            'Remark' => $request->Note,
            'Date_Trx' => now(),
            'User_Trx' => Auth::user()->username,
        ]);

        echo "success";
    }

    public function detailPurchaseRequestReject(Request $request)
    {
        $cek = DB::table('purchase_request_cns')->where('PR_No', $request->PR_No)->first();
        if (!$cek) {
            echo "error";
            return;
        }

        DB::table('purchase_request_cns')->where('PR_No', $request->PR_No)->update([
            'Status' => 'REJECTED',
            'Approval_Date' => now(),
            'Approval_User' => auth()->user()->username,
        ]);

        DB::table('purchase_log')->insert([
            'Location' => Auth::user()->Location,
            'Task_ID' => $request->PR_No,
            'Category' => '',
            'Sub_Category' => '',
            'Purchase_Type' => $request->Supplies_Type,
            'Transaction' => 'PURCHASE-REQUEST',
            'Status' => 'REJECTED',
            'Remark' => $request->Note,
            'Date_Trx' => now(),
            'User_Trx' => Auth::user()->username,
        ]);
        
        echo "success";
    }

    public function outstandingPurchaseRequest()
    {
        return view('cns.procurement.purchase-request-outstanding');
    }

    public function outstandingPurchaseRequestXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


        $LSP = Auth::user()->Location;
        $PRApproval = Auth::user()->PR_Approval;
        $Saya = Auth::user()->username;

        echo "<rows>";
        $kueri = DB::select(DB::raw("SELECT * FROM purchase_request_cns WHERE PO_Status IN ('NORMAL','PARTIAL') AND Status = 'APPROVED' ORDER BY ID ASC"));
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

    Public function outstandingPurchaseRequestDetail(Request $request)
    {
        return view('cns.procurement.purchase-request-outstanding-detail');
    }

    public function outstandingPurchaseRequestDetailXml(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


        $LSP = Auth::user()->Location;
        $PRApproval = Auth::user()->PR_Approval;
        $Saya = Auth::user()->username;
        
        $po = DB::table('purchase_order_cns')->where("PR_No", $_GET['ID'])->get();
        foreach ($po as $key => $value) {
            $getPOCounter = DB::table('purchase_order_cns_dtl')->where("PO_No", $value->PO_No)->get();
            foreach ($getPOCounter as $kunci => $item) {
                $qtyPO[$value->Product_Desc] = ($qtyPO[$value->Product_Desc] ?? 0) + $item->Qty;
            }
        }

        echo "<rows>";
        $kueri = DB::select(DB::raw("SELECT * FROM purchase_request_cns_dtl WHERE PR_No='$_GET[ID]' ORDER BY ID ASC"));

        $nomor = 1;
        $warnanya = "";

        foreach($kueri as $Fetch){

            echo "<row id=\"$Fetch->ID\">";
                    echo "<cell $warnanya>$nomor</cell>";
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Product_Desc))."</cell>";
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Product_Desc))."</cell>"; 
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Qty))."</cell>"; 
                    echo "<cell $warnanya>".htmlspecialchars((0))."</cell>"; 
                    echo "<cell $warnanya>".htmlspecialchars(($qtyPO[$Fetch->Product_Desc] ?? 0))."</cell>"; 
                    echo "<cell $warnanya>".htmlspecialchars($Fetch->Qty - ($qtyPO[$Fetch->Product_Desc] ?? 0))."</cell>"; 
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Specification))."</cell>";
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Unit))."</cell>";
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Remark))."</cell>";
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Category))."</cell>";
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Purchase_Category))."</cell>";
                    echo "<cell $warnanya>".htmlspecialchars(($Fetch->Default_Price))."</cell>";
                    echo "<cell $warnanya>".htmlspecialchars((0))."</cell>";
                echo "</row>";	

                $nomor++;
        }

        echo "</rows>";
    }

    public function outstandingPurchaseRequestDetailSubmit()
    {
        //
    }

    public function outstandingPurchaseRequestDetailUpdate()
    {
        //
    }
}
