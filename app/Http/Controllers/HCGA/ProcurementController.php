<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\Services\StructureService;
use Mpdf\Mpdf;

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

    public function newSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $ars = "";
            $ax = "";
            $isi = "";
            $arr = [];
            foreach ($_POST as $key => $value) {
    
                if ($key != 'DelPRDetail' && $key != 'SubmitPRDetail' && $key != 'AddPRDetail' && $key != 'Reference' && $key != 'Product_Desc' && $key != 'Specification' && $key != 'Qty' && $key != 'Unit' && $key != 'Description' && substr($key,0,2) != 't_' && $key != 'undefined' && $key != 'GridData' && $key != 'RowCount' && $key != 'Image' && $key != 'file_0' && $key != 'Price' && $key != 'Total' && $key != '_token') {
                    $value = trim($value);
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
    
            $sup = DB::table('supplier')->where("Supplier_ID", $arr['Supplier_ID'])->first();
    
            $linkkey = Auth::user()->TokenAuth;
            $getUpper = (new StructureService)->getDirectUpper($getnik);
            
            if ($dept != 'HCGA') {
                $status = "APPROVAL_UPPER";
            }else {
                $status = "APPROVAL_GA";
            }
            $us = DB::table('users')->where("Emp_ID", $getUpper->upper_nik ?? '')->select("id")->first();
            $link = 'approvalpr';
    
    
            // $position = DB::table('master_position')->where("");
            $arr['Status'] = $status;
            $arr['Created_By'] = $Saya;
            $arr['Location'] = $LSP;
            $arr['Supplier_Name'] = $sup->Company_Name;
            $arr['Approval_Date'] = now();
            $arr['Creator_Level'] = 1;
            $arr['Reference'] = '';
            $arr['Note'] = '';
            $arr['Approval_Department'] = $dept;
            $pr = DB::table('purchase_request')->insertGetId($arr);

            // === MULTI-PRODUCT: Insert dari GridData (JSON) ===
            $gridDataJson = $request->input('GridData');
            if (!empty($gridDataJson)) {
                $gridData = json_decode($gridDataJson, true);
                if (is_array($gridData) && count($gridData) > 0) {
                    foreach ($gridData as $product) {
                        // Handle file upload
                        $attachmentPath = '';
                        $fileKey = $product['Image'] ?? '';
                        if (!empty($fileKey) && $request->hasFile($fileKey)) {
                            $file = $request->file($fileKey);
                            $filename = time() . '_' . $file->getClientOriginalName();
                            $file->move(public_path('uploads/procurement'), $filename);
                            $attachmentPath = 'uploads/procurement/' . $filename;
                        }
                        
                        DB::table('purchase_request_dtl')->insert([
                            'PR_No' => $arr['PR_No'],
                            'Product_Code' => $product['Product_Code'] ?? '',
                            'Product_Desc' => $product['Product_Desc'] ?? '',
                            'Qty' => $product['Qty'] ?? 0,
                            'Specification' => $product['Specification'] ?? '',
                            'Unit' => $product['Unit'] ?? 'PCS',
                            'Default_Price' => isset($product['Price']) ? (float)$product['Price'] : 0,
                            'Location' => $LSP,
                            'Purchase_Category' => $arr['Sub_Category'] ?? '',
                            'Purchase_Type' => $arr['Category'] ?? '',
                            'Category' => $product['Category'] ?? $arr['Category'] ?? '',
                            'Remark' => $product['Remark'] ?? '',
                            'Attachment' => $attachmentPath,
                        ]);
                    }
                }
            } else {
                // Fallback untuk single product (jika tidak ada GridData)
                DB::table('purchase_request_dtl')->insert([
                    'PR_No' => $arr['PR_No'],
                    'Product_Desc' => $request->Product_Desc ?? '',
                    'Specification' => $request->Specification ?? '',
                    'Qty' => $request->Qty ?? 0,
                    'Reference' => $request->Reference ?? '',
                    'Unit' => $request->Unit ?? 'PCS',
                    'Location' => $LSP,
                    'Purchase_Category' => $arr['Sub_Category'] ?? '',
                    'Purchase_Type' => $arr['Category'] ?? '',
                    'Category' => $arr['Category'] ?? '',
                    'Product_Code' => '',
                    'Attachment' => $arr['Attachment'] ?? '',
                ]);
            }
            try {
                $arrLog = $arr;
                $arrLog['Action_By'] = $Saya;
                $arrLog['Action_Date'] = now();
                $arrLog['Action_Status'] = "CREATED PR :: WAITING APPROVAL UPPER";
                $arrLog['Last_ID'] = $pr;
        
                DB::table('purchase_request_log')->insert($arrLog);
            
                DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$request->PR_No', '$request->Category', '', '', 'PURCHASE-REQUEST', 'status', '$request->Specification', NOW(), '$Saya')"));
    
                echo "success";
            } catch (\Throwable $th) {
                Log::error($th);
            }
            
            // DB::insert(DB::raw("INSERT INTO purchase_request($ax,Status,Created_By,Location,Approval_Date,Creator_Level,Note,Approval_Department) VALUES($isi,'$status','$Saya','$LSP',NOW(),1,'','$dept')"));
    
            (new NotificationService)->makeNotification('PR APPROVAL', "PR {$request->PR_No} Need to be Approved", 'approval', Auth::user()->id ?? null, $us->id ?? null);
    
            $subjek = "PURCHASE REQUEST APPROVAL REQUEST PR NO $request->PR_No , Request By : $Name";
            $badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>".($getupper->Name ?? '') ." </b><Br><br>
            
                        Mohon dilakukan approval untuk Purchase Request tersebut. Berikut Detail PR yang telah dibuat<br>
                        
                        No PR : $request->PR_No<br><br>
                        
                        
                        Mohon dilakukan approval untuk Permintaan Purchase Request.
                        
                        <br><br>
                        Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://dev.hexamatika.com/geu/hcga/public/$link?ID=$request->PR_No&linkkey=$linkkey\' >KLIK DISINI</a><br><br>
                        
                        
                        <br><br>
                        
                        Terima kasih
                        GREEN ENERGI UTAMA
                        
            </div>";
            $email = $getupper->Email ?? '';
            DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$email','$subjek','$badan','UNSENT',NOW(),NOW())"));

            DB::commit();
            echo 'success';
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
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
        $checkids = explode(",", $param["ids"]);
        $PR_No = $request->PR_No;

        // === PROTEKSI: Jangan izinkan perubahan grid setelah PR di-APPROVED ===
        $prStatus = DB::table('purchase_request')->where("PR_No", $PR_No)->value('Status');
        if ($prStatus == 'APPROVED') {
            echo "<data>";
            echo "<action type='error' sid='' tid=''/>";
            echo "</data>";
            return;
        }

        for ($i = 0; $i < sizeof($checkids); $i++) {
            $action = "";
            $rowId = $checkids[$i]; //id or row which was updated
            $newId = $rowId; //will be used for insert operation
            $mode = $param[$rowId . "_!nativeeditor_status"];
            switch ($mode) {
                case "inserted":
                    $action = $this->insertDetail($rowId, $param, $PR_No);
                    break;
            }
            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
        echo "</data>";
    }

    function insertDetail(string $rowId, array $param, string $PR_No) {
        $LSP = Auth::user()->Location;
        $Saya = Auth::user()->username;
        $NIK = Auth::user()->Emp_ID;

        // === PROTEKSI: Jangan izinkan insert detail setelah PR di-APPROVED ===
        $prStatus = DB::table('purchase_request')->where("PR_No", $PR_No)->value('Status');
        if ($prStatus == 'APPROVED') {
            return "error";
        }

        $a = $param[$rowId . "_c1"];
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

        // === PROTEKSI: Jangan izinkan update detail setelah PR di-APPROVED ===
        $prd = DB::table('purchase_request_dtl')->where("ID", $rowId)->first();
        $prStatus = DB::table('purchase_request')->where("PR_No", $prd->PR_No)->value('Status');
        if ($prStatus == 'APPROVED') {
            return "error";
        }

        $a = $param[$rowId . "_c1"];
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
        $company = DB::table('supplier')->where("Status", 'NORMAL')->get();

        $pr->details = DB::table('purchase_request_dtl')->where("PR_No", $request->PR_No)->get();
        return view('humanresources.procurement.detail', compact('pr','Department','company'));
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
        try {
            DB::beginTransaction();

            $ars = "";
            $ax = "";
            $isi = "";
            $arr = [];
            $pr = DB::table('purchase_request')->where("PR_No", $request->PR_No)->first();
            $pr = json_decode(json_encode($pr), true);
            $prOld = $prNew = $pr;
            foreach ($_POST as $key => $value) {
    
                if ($key != 'DelPRDetail' && $key != 'SubmitPRDetail' && $key != 'AddPRDetail' && $key != 'Reference' && $key != 'Product_Desc' && $key != 'Specification' && $key != 'Qty' && $key != 'Unit' && $key != 'Description' && substr($key,0,2) != 't_') {
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
            $LSP = Auth::user()->Location;

            // === PROTEKSI: Jangan izinkan perubahan setelah PR di-APPROVED ===
            $prStatus = DB::table('purchase_request')->where("PR_No", $request->PR_No)->value('Status');
            if ($prStatus == 'APPROVED') {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'PR sudah di-APPROVED. Tidak dapat melakukan perubahan data.'], 403);
            }

            DB::table('purchase_request')->where("PR_No", $request->PR_No)->update($arr);
            // Update hanya baris detail yang tersinkronisasi oleh DHTMLX grid (atau produk pertama jika form biasa)
            $firstDtl = DB::table('purchase_request_dtl')->where("PR_No", $request->PR_No)->first();
            if ($firstDtl) {
                DB::table('purchase_request_dtl')->where("ID", $firstDtl->ID)->update([
                    'Product_Desc' => $request->Product_Desc,
                    'Specification' => $request->Specification,
                    'Qty' => $request->Qty,
                    'Reference' => $request->Reference,
                    'Unit' => $request->Unit,
                    'Location' => $LSP,
                    'Purchase_Category' => $arr['Category'],
                    'Purchase_Type' => $arr['Category'],
                    'Category' => $arr['Category'],
                ]);
            }
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


            DB::commit();
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $th->getMessage()],500);
        }
    }

    public function detailUpdate(Request $request)
    {
        header("Content-type: text/xml");
        echo('<?xml version="1.0" encoding="iso-8859-1"?>');

        error_reporting(E_ALL ^ E_NOTICE);

        echo "<data>";

        $request_body = urldecode(file_get_contents('php://input'));

        $ids = explode("&", $request_body);
        $param = [];
        foreach ($ids as $datax) {

            $idenya = explode("=", $datax);
            $param[$idenya[0]] = $idenya[1];

        }
        $checkids = explode(",", $param["ids"]);
        $PR_No = $request->PR_No;

        // === PROTEKSI: Jangan izinkan perubahan grid setelah PR di-APPROVED ===
        $prStatus = DB::table('purchase_request')->where("PR_No", $PR_No)->value('Status');
        if ($prStatus == 'APPROVED') {
            echo "<data>";
            echo "<action type='error' sid='' tid=''/>";
            echo "</data>";
            return;
        }

        for ($i = 0; $i < sizeof($checkids); $i++) {
            $action = "";
            $rowId = $checkids[$i];
            $newId = $rowId;
            $mode = $param[$rowId . "_!nativeeditor_status"];
            switch ($mode) {
                case "inserted":
                    $action = $this->insertDetail($rowId, $param, $PR_No);
                    break;
                case "updated":
                    $action = $this->updateDetail($rowId, $param);
                    break;
                case "deleted":
                    $action = "delete";
                    break;
            }
            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
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

    /**
     * Teks master dokumen cetak. ponytail: pindah ke tabel config bila HCGA ubah Doc ID / ketentuan.
     */
    const PRINT_DOC = [
        'pr_title' => 'Purchase Request',
        'pr_owner' => 'HCGA',
        'pr_rev' => '#00',
        'pr_effective' => '8 Januari 2024',
        'pr_doc_id' => 'FR/HCGA/02-00',
        'po_title' => 'PURCHASE ORDER (PO)',
        'po_terms' => [
            'Barang yang dikirim harus sesuai dengan spesifikasi permintaan',
            'Pembeli berhak menolak dan mengembalikan barang apabila barang tidak sesuai dengan pesanan, dan biaya pengembalian ditanggung oleh pihak vendor',
            'Pengiriman dan jumlah barang dikirim sesuai dengan alamat yang tercantum dalam PO',
            'Pembayaran akan dilakukan sesuai dengan invoice',
            'Segala perselisihan yang timbul dari PO ini akan diselesaikan melalui musyawarah dan mufakat.',
        ],
    ];

    private function rp($n)
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }

    public function printPR(Request $request)
    {
        $LSP = Auth::user()->Location;
        $pr = DB::table('purchase_request')->where('PR_No', $request->PR_No)->first();
        if (!$pr) abort(404, 'PR tidak ditemukan');
        if ($pr->Location !== $LSP) abort(403, 'PR bukan lokasi Anda');

        $details = DB::table('purchase_request_dtl')->where('PR_No', $pr->PR_No)->orderBy('ID')->get();
        $creator = DB::table('users')->where('username', $pr->Created_By)->first();
        $appr = DB::table('purchase_request_log')->where('PR_No', $pr->PR_No)
            ->whereNotIn('Action_Status', ['CREATED PR :: WAITING APPROVAL UPPER'])
            ->orderBy('ID')->get();
        $doc = self::PRINT_DOC;

        $html = view('humanresources.procurement.prints.pr', [
            'pr' => $pr, 'details' => $details, 'creator' => $creator,
            'appr' => $appr, 'doc' => $doc,
        ])->render();

        // Collect attachments
        $attachments = [];
        foreach ($details as $d) {
            if (!empty($d->Attachment)) {
                $paths = is_string($d->Attachment) ? explode(',', $d->Attachment) : [$d->Attachment];
                foreach ($paths as $p) {
                    $p = trim($p);
                    if ($p && file_exists(public_path($p))) {
                        $attachments[] = public_path($p);
                    }
                }
            }
        }

        return $this->streamPdf($html, 'PR_' . preg_replace('/[^A-Za-z0-9\\-]/', '_', $pr->PR_No), $attachments);
    }

    public function printPO(Request $request)
    {
        $LSP = Auth::user()->Location;
        $poNo = $request->PO_No ?: DB::table('purchase_request')->where('PR_No', $request->PR_No)->value('PO_No');
        if (!$poNo) abort(404, 'PO belum diterbitkan untuk PR ini');
        $po = DB::table('purchase_order')->where('PO_No', $poNo)->first();
        if (!$po) abort(404, 'PO tidak ditemukan');
        if ($po->Location !== '' && $po->Location !== $LSP) abort(403, 'PO bukan lokasi Anda');

        $details = DB::table('purchase_order_dtl')->where('PO_No', $po->PO_No)->orderBy('ID')->get();
        $vendor = $po->Supplier ? DB::table('supplier')->where('Supplier_ID', $po->Supplier)->first() : null;
        $pr = $po->PR_No ? DB::table('purchase_request')->where('PR_No', $po->PR_No)->first() : null;
        $isRent = stripos(($po->Purchase_Type ?? '') . ' ' . ($po->Remark ?? ''), 'sewa') !== false;
        $doc = self::PRINT_DOC;

        $html = view('humanresources.procurement.prints.po', [
            'po' => $po, 'details' => $details, 'vendor' => $vendor,
            'pr' => $pr, 'isRent' => $isRent, 'doc' => $doc,
        ])->render();

        return $this->streamPdf($html, 'PO_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $po->PO_No));
    }

    private function streamPdf($html, $filename, $attachments = [])
    {
        if (!is_dir(storage_path('app/mpdf'))) mkdir(storage_path('app/mpdf'), 0755, true);

        // ── Header & Footer images (base64) ─────────────────────────────────
        $headerPath = public_path('assets/images/surat/header_geu.jpg');
        $footerPath = public_path('assets/images/surat/footer_geu.png');

        $headerB64 = base64_encode(file_get_contents($headerPath));
        $footerB64 = base64_encode(file_get_contents($footerPath));
        $headerExt = strtolower(pathinfo($headerPath, PATHINFO_EXTENSION));
        $footerExt = strtolower(pathinfo($footerPath, PATHINFO_EXTENSION));

        $headerHtml = '
            <div style="margin:0;padding:0;line-height:0;">
                <img src="data:image/' . $headerExt . ';base64,' . $headerB64 . '"
                     style="width:100%;display:block;">
            </div>';

        $footerHtml = '
            <div style="margin:0;padding:0;line-height:0;">
                <img src="data:image/' . $footerExt . ';base64,' . $footerB64 . '"
                     style="width:100%;display:block;">
            </div>';

        // ── Inisialisasi mPDF ───────────────────────────────────────────────
        $mpdf = new Mpdf([
            'format'        => 'A4',
            'tempDir'       => storage_path('app/mpdf'),
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'setAutoTopMargin'    => 'stretch',
            'setAutoBottomMargin' => 'stretch',
        ]);

        $mpdf->splitTableBorderWidth = 1;

        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);

        $mpdf->WriteHTML($html);

        // ── Merge attachments (image/PDF) ───────────────────────────────────
        if (!empty($attachments)) {
            foreach ($attachments as $path) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    // Image: add new page with image fit A4
                    $mpdf->AddPage();
                    $mpdf->Image($path, 0, 0, 210, 297, $ext, '', true, false);
                } elseif ($ext === 'pdf') {
                    // PDF: import pages
                    $pageCount = $mpdf->SetSourceFile($path);
                    for ($i = 1; $i <= $pageCount; $i++) {
                        $mpdf->AddPage();
                        $tplId = $mpdf->ImportPage($i);
                        $mpdf->UseTemplate($tplId);
                    }
                }
            }
        }

        $mpdf->SetTitle($filename);
        return response($mpdf->Output($filename . '.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '.pdf"',
        ]);
    }

    /**
     * Approve Changes - Creator menyetujui perubahan yang dilakukan approver
	 * Flow: Approver ubah data → PR ke CHANGES_REVIEWED_BY_CREATOR → Creator approve → lanjut ke tahap berikutnya
	 */
	public function approveChanges(Request $request)
	{
		try {
			DB::beginTransaction();

			$PR_No = $request->PR_No;
			$Saya = Auth::User()->username;
			$LSP = Auth::user()->Location;

			$pr = DB::table('purchase_request')->where('PR_No', $PR_No)->first();

			if (!$pr) {
				return response()->json(['status' => 'error', 'message' => 'PR tidak ditemukan'], 404);
			}

			// Verifikasi bahwa user adalah creator
			if ($pr->Created_By != $Saya) {
				return response()->json(['status' => 'error', 'message' => 'Hanya creator PR yang dapat menyetujui perubahan'], 403);
			}

			// Verifikasi status
			if ($pr->Status != 'CHANGES_REVIEWED_BY_CREATOR') {
				return response()->json(['status' => 'error', 'message' => 'PR tidak dalam status CHANGES_REVIEWED_BY_CREATOR'], 400);
			}

			// Update status: kembali ke tahap approval berikutnya
			// Jika PR berasal dari HCGA → APPROVAL_GA, jika bukan → APPROVAL_UPPER
			if ($pr->Approval_Department == 'HCGA') {
				$nstatus = 'APPROVAL_GA';
			} else {
				$nstatus = 'APPROVAL_UPPER';
			}

			$data = [
				'Status' => $nstatus,
				'Note' => $pr->Note . date("Y-m-d H:i:s") . " => Creator menyetujui perubahan. PR kembali ke tahap approval.\n",
			];

			DB::table('purchase_request')->where('ID', $pr->ID)->update($data);

			// Log perubahan
			DB::table('purchase_request_log')->insert([
				'PR_No' => $PR_No,
				'Action_By' => $Saya,
				'Action_Date' => now(),
				'Action_Status' => "CREATOR_APPROVED_CHANGES: PR dikembalikan ke tahap approval $nstatus",
				'Last_ID' => $pr->ID,
				'Category' => $pr->Category,
				'Sub_Category' => $pr->Sub_Category,
				'Supplies_Type' => $pr->Supplies_Type,
				'Note' => $pr->Note,
				'Reference' => $pr->Reference,
			]);

			// Notifikasi ke approver selanjutnya
			$getlevel = DB::table('level_general')->where("Status_Approval", "LIKE", "%$nstatus%")->first();
			$getupper = DB::table("organization_structure")->where("Level" . $getlevel->Level, 1)
				->where("Department", $pr->Approval_Department)
				->first();

			if ($getupper) {
				$us = DB::table('users')->where("Emp_ID", $getupper->NIK)->select("id")->first();
				(new NotificationService)->makeNotification(
					"PR {$PR_No} Kembali ke Approval",
					"PR {$PR_No} telah disetujui oleh creator. Silahkan lanjutkan approval.",
					'approval',
					Auth::user()->id,
					$us->id
				);
			}

			DB::commit();
			echo "success";
		} catch (\Throwable $th) {
			DB::rollBack();
			Log::error($th);
			return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
		}
	}

}
