<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class ProcurementController extends Controller
{
    public function index()
    {
        return view('humanresources.procurement.index');
    }

    public function indexXml()
    {
	
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


        $LSP = Auth::user()->Location;
        $DOP = Auth::user()->DOP;
        $PRApproval = Auth::user()->PR_Approval;
        $Saya = Auth::user()->username;
        echo "<rows>";


        $kueri = DB::select(DB::raw("SELECT * FROM purchase_request WHERE Location = '$LSP' ORDER BY ID ASC"));
        $nomor = 1;

            foreach($kueri as $Fetch){
                $warna = "";
                if ($Fetch->Status == 'APPROVED') {
                    $warna = "style='background-color: #45bc45;'";
                }
                    echo "<row $warna id=\"$Fetch->ID\">";
                            echo "<cell>$nomor</cell>";
                            echo "<cell>".htmlspecialchars(($Fetch->PR_No))."</cell>";
                            echo "<cell>".htmlspecialchars(($Fetch->PR_Date))."</cell>";
                            echo "<cell>".htmlspecialchars(($Fetch->Category))."</cell>";
                            echo "<cell>".htmlspecialchars(($Fetch->Status))."</cell>";
                            echo "<cell>".htmlspecialchars(($Fetch->PO_No))."</cell>";
                            echo "<cell>".htmlspecialchars(($Fetch->Remark))."</cell>";
                            echo "<cell>".htmlspecialchars(($Fetch->Created_Date))."</cell>";
                            echo "<cell>".htmlspecialchars(($Fetch->Created_By))."</cell>";
                        echo "</row>";	

                    $nomor++;
            }

        echo "</rows>";
    }

    public function new()
    {
        $LSP = Auth::user()->Location;
        $Saya =  Auth::user()->username;
        $NIK =  Auth::user()->Emp_ID;

        $dpt =  Auth::user()->Department;
        $date = date('Y-m');
        $query = DB::table('purchase_request')->where("Location", $LSP)->where("PR_Date", "LIKE", "$date%")->count();
        $NoR = $query + 1;
        if ($NoR < 10) $NoR = "0$NoR";
        $dateR = date('Ym');
        $PR_No = strtoupper("PR/" . $LSP . "/$dateR-$NoR-" . substr(round(1) . md5(date('c')), -5));
        $Query = DB::table("master_department");
        if ($dpt != 'ICT' && $dpt != 'BOD' && $dpt != 'HCGA') {
            $Query->where("Initial", $dpt);
        }

        $company = DB::table('supplier')->where("Status", 'NORMAL')->get();
        
        $Department = $Query->get();
        return view('humanresources.procurement.create', compact("PR_No", 'NIK', 'Saya', 'dpt', 'Department', 'company'));
    }

    public function newSubmit(Request $reuqest)
    {
        $ars = "";
        $ax = "";
        $isi = "";
        $arr = [];
        foreach ($_POST as $key => $value) {

            if ($key != 'DelPRDetail' && $key != 'SubmitPRDetail' && $key != 'AddPRDetail') {
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

        $linkkey = Auth::user()->TokenAuth;
        $getlevelsx = DB::table("organization_structure")->where("Department", $dept)->first();
        if ($dept != 'HCGA') {
            $status = "APPROVAL_UPPER";
        }else {
            $status = "APPROVAL_GA";
        }
        $us = DB::table('users')->where("Emp_ID", $getlevelsx->NIK)->select("id")->first();
        $link = 'approvalpr';


        // $position = DB::table('master_position')->where("");
        $arr['Status'] = $status;
        $arr['Created_By'] = $Saya;
        $arr['Location'] = $LSP;
        $arr['Approval_Date'] = now();
        $arr['Creator_Level'] = 1;
        $arr['Note'] = '';
        $arr['Approval_Department'] = $dept;
        $pr = DB::table('purchase_request')->insertGetId($arr);
        try {
            $arrLog = $arr;
            $arrLog['Action_By'] = $Saya;
            $arrLog['Action_Date'] = now();
            $arrLog['Action_Status'] = "CREATED PR :: WAITING APPROVAL UPPER";
            $arrLog['Last_ID'] = $pr;
    
            DB::table('purchase_request_log')->insert($arrLog);
        
            DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$_POST[PR_No]', '$_POST[Category]', '', '', 'PURCHASE-REQUEST', 'status', '$_POST[Remark]', NOW(), '$Saya')"));

            echo "success";
        } catch (\Throwable $th) {
            Log::error($th);
        }
        
        // DB::insert(DB::raw("INSERT INTO purchase_request($ax,Status,Created_By,Location,Approval_Date,Creator_Level,Note,Approval_Department) VALUES($isi,'$status','$Saya','$LSP',NOW(),1,'','$dept')"));

        (new NotificationService)->makeNotification('PR APPROVAL', "PR {$_POST['PR_No']} Need to be Approved", 'approval', Auth::user()->id ?? null, $us->id ?? null);

        $subjek = "PURCHASE REQUEST APPROVAL REQUEST PR NO $_POST[PR_No] , Request By : $Name";
        $badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>".($getupper->Name ?? '') ." </b><Br><br>
        
                    Mohon dilakukan approval untuk Purchase Request tersebut. Berikut Detail PR yang telah dibuat<br>
                    
                    No PR : $_POST[PR_No]<br><br>
                    Reference : $_POST[Reference]<br><br>
                    Jenis Request : $_POST[Category]<br><br>
                    Note : $_POST[Remark]<br>
                    
                    
                    Mohon dilakukan approval untuk Permintaan Purchase Request.
                    
                    <br><br>
                    Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://dev.hexamatika.com/geu/hcga/public/$link?ID=$_POST[PR_No]&linkkey=$linkkey\' >KLIK DISINI</a><br><br>
                    
                    
                    <br><br>
                    
                    Terima kasih
                    GREEN ENERGI UTAMA
                    
        </div>";
        $email = $getupper->Email ?? '';
        DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$email','$subjek','$badan','UNSENT',NOW(),NOW())"));
    }

    public function newUpdate(Request $request)
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
        $PR_No = $request->PR_No;

        for ($i=0; $i < sizeof($checkids); $i++) {
            $action = "";
            $rowId = $checkids[$i]; //id or row which was updated 
            $newId = $rowId; //will be used for insert operation	
            $mode = $param[$rowId."_!nativeeditor_status"]; 
            switch($mode){
                case "inserted":
                    $action = $this->insertDetail($rowId,$param, $PR_No);
                    break;
            }
            echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
        }
        echo "</data>";
    }

    function insertDetail(string $rowId, array $param, string $PR_No) {
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
        $j = $param[$rowId."_c10"];
        $prices = $param[$rowId."_c10"]+0;

        $prd = DB::table('purchase_request_dtl')->insertGetId([
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
            'Default_Price' => $prices
            ]);
        
        try {
            DB::table('purchase_request_dtl_log')->insert([
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
                'Action_By' => $Saya,
                'Action_Date' => now(),
                'Action_Status' => "INSERT_ROW_DTL",
                'Last_ID' => $prd
                ]);
            
            return "insert";
        } catch (\Throwable $th) {
            Log::error($th);
        }

    }

    function updateDetail(string $rowId, array $param)
    {
        $LSP = Auth::user()->Location;
        $Saya = Auth::user()->username;

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
        $prices = $param[$rowId."_c10"]+0;
        $prd = DB::table('purchase_request_dtl')->where("ID", $rowId)->first();
        DB::table('purchase_request_dtl')->where("ID", $rowId)->update([
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
            'Default_Price' => $prices
        ]);
        try {
            DB::table('purchase_request_dtl_log')->insert([
                'PR_No' => $prd->Pr_No,
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
                'Action_By' => $Saya,
                'Action_Date' => now(),
                'Action_Status' => "INSERT_ROW_DTL",
                'Last_ID' => $prd
                ]);
        } catch (\Throwable $th) {
            Log::error($th);
        }
        
        return "update";
    }

    public function product(Request $request)
    {
        return view('humanresources.procurement.product');
    }

    public function productXml(Request $request)
    {
        error_reporting(0);
        ini_set('memory_limit', '-1');
            
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");



        $LSP = Auth::user()->Location;
        $DOP = Auth::user()->DOP;
        $priv = Auth::user()->Priv_ID;
        $Saya = Auth::user()->username;
        $nomor = 1;

        // $Fetchx = DB::select(DB::raw("SELECT * FROM product_code where Main_Category='$_GET[Category]' AND  Category='$_GET[Sub_Category]' AND Supplies_Type='$_GET[Purchase_Type]' order by Product_Code"));

        $Fetchx = DB::table('product_code')->orderBy("Product_Code", 'ASC');
        if ($_GET['Category'] == 'ASSET') {
            $Fetchx->where("Main_Category", $_GET['Category']);
        }else {
            $Fetchx->where("Main_Category",'!=', "ASSET");
        }

        $Fetchx = $Fetchx->get();

        
        echo "<rows>";

        foreach($Fetchx as $dapat){
            $cekstok = DB::select(DB::raw("SELECT SUM(Qty) as jums FROM supplies where Product_Code='$dapat->Product_Code' and Location='$LSP'"));
            $cekstok = DB::table('supplies')->where("Product_Code", $dapat->Product_Code)->where("Location", $LSP)->sum('Qty');
            echo "<row id=\"$dapat->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars(($dapat->Product_Code))."</cell>";
                echo "<cell>".htmlspecialchars(($dapat->Description))."</cell>";
                echo "<cell>".htmlspecialchars(($dapat->Specification))."</cell>";
                echo "<cell>".$cekstok."</cell>";
                echo "<cell>".htmlspecialchars(($dapat->Unit_Name))."</cell>";
                echo "<cell>".htmlspecialchars(($dapat->Foto))."</cell>";
                echo "<cell>".htmlspecialchars(($dapat->Dimension))."</cell>";
                echo "<cell>".htmlspecialchars(($dapat->Price))."</cell>";
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
        return view('humanresources.procurement.detail', compact('pr','Department'));
    }

    public function detailXml(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


        $LSP = Auth::user()->Location;
        $DOP = Auth::user()->DOP;
        $PRApproval = Auth::user()->PR_Approval;
        $Saya = Auth::user()->username;
        echo "<rows>";

        $dtls = DB::table('purchase_request_dtl')->where("PR_No", $request->PR_No)->get();
        $nomor = 1;

            foreach($dtls as $Fetch){
                echo "<row id=\"$Fetch->ID\">";
                    echo "<cell>$nomor</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Product_Code))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Product_Desc))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Qty))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Specification))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Unit))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Remark))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Category))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Purchase_Category))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Prices == 0 ? $Fetch->Default_Price : $Fetch->Prices))."</cell>";
                    echo "<cell>".htmlspecialchars(($Fetch->Prices == 0 ? $Fetch->Default_Price : $Fetch->Prices) * $Fetch->Qty)."</cell>";
                echo "</row>";	

                $nomor++;
            }

        echo "</rows>";
    }

    public function detailSubmit(Request $request)
    {
        $ars = "";
        $ax = "";
        $isi = "";
        $arr = [];
        $pr = DB::table('purchase_request')->where("PR_No", $request->PR_No)->first();
        $pr = json_decode(json_encode($pr), true);
        $prOld = $prNew = $pr;
        foreach ($_POST as $key => $value) {

            if ($key != 'DelPRDetail' && $key != 'SubmitPRDetail' && $key != 'AddPRDetail') {
                $ars .= "$key = '$value',";
                $ax .= "$key,";
                $isi .= "'$value',";
                $arr[$key] = $value;
                $prNew[$key] = $value;
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

        $linkkey = Auth::user()->TokenAuth;


        // $position = DB::table('master_position')->where("");


        // DB::insert(DB::raw("INSERT INTO purchase_request($ax) VALUES($isi)"));

        DB::table('purchase_request')->where("PR_No", $request->PR_No)->update($arr);
        try {
            $prOld['Action_By'] = $Saya;
            $prOld['Action_Date'] = now();
            $prOld["Action_Status"] = "PR_UPDATE_OLD";
            $prOld["Last_ID"] = $prOld['ID'];
            unset($prOld['ID']);
            DB::table('purchase_request_log')->insert($prOld);

            $prNew['Action_By'] = $Saya;
            $prNew['Action_Date'] = now();
            $prNew["Action_Status"] = "PR_UPDATE_OLD";
            $prNew["Last_ID"] = $prNew['ID'];
            unset($prNew['ID']);
            DB::table('purchase_request_log')->insert($prNew);
        } catch (\Throwable $th) {
            Log::error($th);
        }
        
        DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$request->PR_No', '$request->Category', '', '', 'PURCHASE-REQUEST-DETAIL-UPDATE', 'status', '$request->Remark', NOW(), '$Saya')"));

        echo "success";
    }

    public function detailUpdate(Request $request)
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
        $PR_No = $request->PR_No;

        for ($i=0; $i < sizeof($checkids); $i++) {
            $action = "";
            $rowId = $checkids[$i]; //id or row which was updated 
            $newId = $rowId; //will be used for insert operation	
            $mode = $param[$rowId."_!nativeeditor_status"]; 
            switch($mode){
                case "inserted":
                    $action = $this->insertDetail($rowId,$param, $PR_No);
                    break;
                case "updated":
                    $action = $this->updateDetail($rowId,$param, $PR_No);
                    break;
                case "deleted":
                    $action = $this->deleteDetail($rowId,$param, $PR_No);
                    break;
            }
            echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
        }
        echo "</data>";
    }

    public function log(Request $request)
    {
        return view('humanresources.procurement.log');
    }

    public function logData(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $LSP  = Auth::user()->Location;
        $Saya = Auth::user()->username;
        $nomor = 1;

        echo "<rows>";

        // ID bisa berupa PR_No atau ID purchase_request — sesuaikan query
        $ID = $request->ID ?? '';

        // Kolom milestone yang diambil dari purchase_request_log
        // Urutan mengikuti grid log.blade.php
        $Query = DB::table("purchase_request_log")
            ->where("PR_No", $ID)
            ->orderBy("ID", "DESC")
            ->get();

        foreach ($Query as $Fetch) {
            echo "<row id=\"{$Fetch->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Action_Date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Action_By) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Action_Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->PR_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Category) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Sub_Category) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Supplies_Type) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Note) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Reference) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }
}
