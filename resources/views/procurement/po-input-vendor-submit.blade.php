	<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$ars = "";
$ax = "";
$isi = "";
foreach($_POST as $key=>$value)
{
		
		if ($key !='DelPRNewDetail' && $key !='SubmitPRNewDetail' && $key !='AddPRNewDetail'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);

$date = date('Y-m-d');


$pr = DB::table("purchase_request")->where("ID",$_POST['ID'])->first();

$ps = DB::select(DB::raw("SELECT COUNT(ID) as jumlah FROM purchase_summary WHERE Location = '$LSP' and Purchase_Date LIKE '$date%'"));
$V5 = $_POST['Vendor5'];
$V4 = $_POST['Vendor4'];
$V3 = $_POST['Vendor3'];
$V2 = $_POST['Vendor2'];
$V1 = $_POST['Vendor1'];
	
$NoR = $ps->jumlah + 1;
if ( $NoR < 10 ) $NoR = "0$NoR";
$dateR = date('YmdHis');
$PO_No = "PS/".$LSP."/$dateR-$NoR"; 
		
DB::insert(DB::raw("INSERT INTO purchase_summary(Location,PR_No,Task_ID,Purchase_Category,Purchase_Sub_Category,Purchase_Type,
		Purchase_Date,V1_ID,V2_ID,V3_ID,V4_ID,V5_ID,Created_By,Note,Priority)
		VALUES ('$LSP','$pr->PR_No','$PO_No','$_POST[Category]','$_POST[Sub_Category]','$_POST[Supplies_Type]',NOW(),'$V1','$V2','$V3','$V4','$V5',
		'$Saya','$notes','$pr->Priority_Status')"));

	DB::insert(DB::raw("INSERT INTO purchase_summary_detail 
			(PR_Related,Task_ID,Purchase_Category,Purchase_Sub_Category,Purchase_Type,
			Product_Code,Qty,Specification,Recommended_Spec,Created_By,Location,Proposed,Unit_Name,Default_Price)
			SELECT PR_No,'$PO_No',Purchase_Category,Purchase_Type,Category,Product_Code,Qty,Product_Desc,Specification,
			'$Saya','$LSP',Qty,Unit,Default_Price FROM purchase_request_dtl where PR_No='$pr->PR_No'"));

	
	DB::select(DB::raw("UPDATE purchase_request_dtl set PO_Task_No='$PO_No',PO_Task_Date=NOW() WHERE PR_No='$pr->PR_No'"));
	DB::select(DB::raw("UPDATE purchase_request set PO_Task_No='$PO_No',Processing_Status='CREATED TO PO',Processing_Date=NOW() WHERE ID=$_POST[ID]"));

DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$_POST[PR_No]', '$_POST[Category]', '$_POST[Sub_Category]', '$_POST[Supplies_Type]', 'PURCHASE-REQUEST-TO-PO', 'CREATE TO PO ', '$_POST[Note]', NOW(), '$Saya')"));

DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$PO_No', '$_POST[Category]', '$_POST[Sub_Category]', '$_POST[Supplies_Type]', 'PURCHASE-ORDER', 'NEW PO PROCESS', '$_POST[Note]', NOW(), '$Saya')"));

echo "success";

?>