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

    public function printPurchaseRequest()
    {
        return view('cns.procurement.print.purchase-request');
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
                if (in_array($key, ['Subtotal', 'Tax', 'Shipping', 'Other', 'Total'], true)) {
                    $value = str_replace(',', '', (string) $value);
                }
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
                        echo "<cell $warnanya>".htmlspecialchars(number_format((float) $Fetch->Qty * (float) $Fetch->Default_Price, 2, '.', ''))."</cell>";
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

        $getPOCounter = DB::table('purchase_order_cns_dtl')->where("PR_No", $request->ID)->get();
        foreach ($getPOCounter as $kunci => $item) {
            $qtyPO[$item->Product_Desc] = ($qtyPO[$item->Product_Desc] ?? 0) + $item->Qty;
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

    public function outstandingPurchaseRequestDetailSubmit(Request $request)
    {
        $cek = DB::table('purchase_request_cns')->where('PR_No', $request->PR_No)->first();
        if (!$cek) {
            echo "error";
            return;
        }
        
        $subtotal = (float) str_replace(',', '', (string) $request->Subtotal);
        $tax = (float) str_replace(',', '', (string) $request->Tax);
        $shipping = (float) str_replace(',', '', (string) $request->Shipping);
        $other = (float) str_replace(',', '', (string) $request->Other);
        $total = (float) str_replace(',', '', (string) $request->Total);

        $psid = DB::table('purchase_summary_cns')->insertGetId([
            'Task_ID' => $request->PS_No,
            'PR_No' => $request->PR_No,
            'Purchase_Category' => $cek->Category,
            'Purchase_Sub_Category' => $cek->Sub_Category,
            'Purchase_Type' => $cek->Supplies_Type,
            'Purchase_Date' => now(),
            'Status' => "CLOSED",
            'V1_ID' => $request->Supplier,
            'V1_Cur' => "IDR",
            'V1_Fee_Mgt' => $other,
            'V1_VAT' => $tax,
            'V1_Fee_VAT' => 0,
            'V1_PPH' => 0,
            'V1_DC' => $shipping,
            'V1_Discount' => 0,
            'V1_Note' => $request->Note,
            'V1_Total' => $total,
            'Created_Date' => now(),
            'Created_By' => auth()->user()->username,
            'Location' => auth()->user()->Location,
            'Note' => date('Y-m-d H:i:s') . ' : User ' . auth()->user()->username . ' => ' . $request->Note,
            'Priority' => $cek->Priority_Status,
        ]);

        $countPO = DB::table('purchase_order_cns')->where("PO_Date", 'LIKE', date('Y',strtotime($request->PO_Date)). '%')->count() + 1;
        $pono = "PO-EXP/" . sprintf("%03d", $countPO) . '/' . date('Y');

        DB::table('purchase_order_cns')->insertGetId([
            'Task_ID' => $request->PS_No,
            'Term_Payment' => $request->Payment_Term,
            'Pay_Term' => $request->Payment_Term,
            'Location' => auth()->user()->Location,
            'Process_By' => auth()->user()->Emp_ID,
            'Purchase_Type' => $cek->Supplies_Type,
            'Purchase_Category' => $cek->Category,
            'Purchase_Sub_Category' => $cek->Sub_Category,
            'PR_No' => $cek->PR_No,
            'PO_No' => $pono,
            'Subject_Payment' => "",
            'PO_No_KSPS' => $pono,
            'PO_Date' => $request->PO_Date,
            'PO_Method' => 'POSTPAID',
            'PO_Dest' => NULL,
            'Department' => '',
            'Supplier' => $cek->Supplier,
            'Subtotal' => $subtotal,
            'Discount' => 0,
            'PPN' => $tax,
            'PPH' => 0,
            'Delivery_Fee' => $shipping,
            'Management_Fee' => $other,
            'Management_Fee_Exclude_PPN' => 0,
            'Total' => $total,
            'Total_Paid' => 0,
            'Status' => 'CLOSED',
            'Remark' => $request->Note,
            'Date_Trx' => now(),
            'User_Trx' => auth()->user()->username,
            'Status_Receive' => 'WAITING',
        ]);

        $getAllSummary = DB::table('purchase_summary_cns_detail')->where('Task_ID', $request->PS_No)->get();
        foreach ($getAllSummary as $key => $value) {
            DB::table('purchase_order_cns_dtl')->insertGetId([
                'Location' => auth()->user()->Location,
                'PR_No' => $cek->PR_No,
                'PO_No' => $pono,
                'Category' => $value->Purchase_Category,
                'Sub_Category' => $value->Purchase_Sub_Category,
                'Purchase_Type' => $value->Purchase_Type,
                'Product_Code' => $value->Product_Code,
                'Barcode' => $value->Product_Code,
                'Product_Desc' => $value->Product_Desc,
                'Specification' => $value->Specification,
                'Brand' => null,
                'Product_Type' => $value->Purchase_Type,
                'Qty' => $value->Proposed,
                'Unit' => $value->Unit_Name,
                'Price' => $value->V1_Propose,
                'Discount' => 0,
                'Subtotal' => $value->V1_TOP,
                'PPN' => 0,
                'PPN_pct' => 0,
                'Total' => $value->V1_TOP,
                'Remark' => '',
            ]);
        }


        DB::table('purchase_log')->insert([
            'Location' => Auth::user()->Location,
            'Task_ID' => $request->PR_No,
            'Category' => '',
            'Sub_Category' => '',
            'Purchase_Type' => $request->Supplies_Type,
            'Transaction' => 'PURCHASE-REQUEST',
            'Status' => 'CREATE-PO',
            'Remark' => $request->Note,
            'Date_Trx' => now(),
            'User_Trx' => Auth::user()->username,
        ]);

        echo 'success';
    }

    public function outstandingPurchaseRequestDetailUpdate(Request $request)
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
                    case "updated":
                        $action = $this->outstandingPurchaseRequestDetailUpdateUpdatekan($rowId,$param, $request->PS_No);
                        break;
                }
                    
            
                
            echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
        }
        echo "</data>";

        $done = 1;
        $prd = DB::table('purchase_request_cns_dtl')->where("PR_No", $request->PR_No)->get();
        foreach ($prd as $key => $value) {
            $remain = $value->Qty;
            $pod = DB::table('purchase_summary_cns_detail')->where("PR_Related", $request->PR_No)->where('Product_Desc', $value->Product_Desc)->get();
            if (count($pod) > 0) {
                foreach ($pod as $kunci => $item) {
                    $remain -= $item->Qty;
                }
                if ($remain > 0) {
                    $done = 0;
                }
            }else {
                $done = 0;
            }
        }

        if ($done) {
            DB::table('purchase_request_cns')->where("PR_No", $request->PR_No)->update([
                'PO_Status' => 'CLOSED',
            ]);


            DB::table('purchase_log')->insert([
                'Location' => Auth::user()->Location,
                'Task_ID' => $request->PR_No,
                'Category' => '',
                'Sub_Category' => '',
                'Purchase_Type' => "",
                'Transaction' => 'PURCHASE-REQUEST',
                'Status' => 'DONE PR TO PO',
                'Remark' => "",
                'Date_Trx' => now(),
                'User_Trx' => Auth::user()->username,
            ]);
        }else {
            DB::table('purchase_request_cns')->where("PR_No", $request->PR_No)->update([
                'PO_Status' => 'PARTIAL',
            ]);


            DB::table('purchase_log')->insert([
                'Location' => Auth::user()->Location,
                'Task_ID' => $request->PR_No,
                'Category' => '',
                'Sub_Category' => '',
                'Purchase_Type' => "",
                'Transaction' => 'PURCHASE-REQUEST',
                'Status' => 'PARTIALLY CREATED PO',
                'Remark' => "",
                'Date_Trx' => now(),
                'User_Trx' => Auth::user()->username,
            ]);
        }

        
    }

    public function outstandingPurchaseRequestDetailUpdateUpdatekan(string $rowId, array $param, string $PS_No)
    {
        $a = $param[$rowId."_c1"];
        $b = $param[$rowId."_c2"];
        $c = $param[$rowId."_c3"];
        $d = $param[$rowId."_c4"];
        $e = $param[$rowId."_c5"];
        $f = $param[$rowId."_c6"];
        $g = $param[$rowId."_c7"];
        $h = $param[$rowId."_c8"];
        $ix = $param[$rowId."_c9"];
        $j = $param[$rowId."_c10"];
        $k = $param[$rowId."_c11"];
        $l = $param[$rowId."_c12"];
        $m = $param[$rowId."_c13"];
        $n = $param[$rowId."_c14"];

        if($e <= 0) {
            return 'zeroqty';
        }

        $cek = DB::table('purchase_request_cns_dtl')->where("ID", $rowId)->first();
        if (!$cek) {
            return 'error';
        }
        
        $pscdid = DB::table('purchase_summary_cns_detail')->insertGetId([
            'Task_ID' => $PS_No,
            'Purchase_Category' => $cek->Category,
            'Purchase_Sub_Category' => $cek->Purchase_Category,
            'Purchase_Type' => $cek->Purchase_Type,
            'Product_Code' => $cek->Product_Code,
            'Product_Desc' => $cek->Product_Desc,
            'Default_Price' => $cek->Default_Price,
            'Qty' => $cek->Qty,
            'Proposed' => $e,
            'Specification' => $cek->Specification,
            'Unit_Name' => $cek->Unit,
            'Recommended_Spec' => $cek->Specification,
            'V1_Propose' => $cek->Default_Price,
            'V1_TOP' => $n,
            'V1_Win' => 1,
            'Note' => $j,
            'Created_Date' => now(),
            'Created_By' => auth()->user()->username,
            'Location' => auth()->user()->Location,
            'PR_Related' => $cek->PR_No,
            'Status_Request' => "NORMAL",
        ]);

        DB::table('purchase_request_cns_dtl')->where("ID", $rowId)->update([
            'PSD_ID' => $pscdid,
        ]);

        return 'update';
    }
}
