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
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;


	$getdata = DB::table("account_payable")->where("ID",$_POST['ID'])->first();
	$total = $getdata->Total;
	$paid = $getdata->Paid;
	
	
$_POST['Payment_Value'] = str_replace(".","",$_POST['Payment_Value']);
$_POST['Payment_Value'] = str_replace(",","",$_POST['Payment_Value']);
$Payment_Value = $_POST['Payment_Value'];
$Payment_Date = $_POST['Payment_Date'];
$Payment_Note = $_POST['Payment_Note'];
$paymentmode =explode("|",$_POST['Payment_Mode']);
$sisa = $total - $paid - $Payment_Value;
$getbank = DB::table("bank_acc")->where("Acc_No",$paymentmode[1])->first();
$paymentacc = $getbank->Bank_Acc;	
$tipe ="AP";
$ID = 
$jno = "JAP-PY-".date("ymdhis");

DB::insert(DB::raw("INSERT INTO account_payable_payment_log (Task_ID, Payment_Type, Payment_Task_ID, Payment_Reference, Payment_Acc_No, Payment_Value, Note, Reference, Created_By, Created_Date)
	values 
	('$_POST[ID]', '$tipe', '$_POST[AP_No]', '$paymentmode[1]', '$paymentacc', '$Payment_Value', '$Payment_Note', '$getdata->Project_ID', '$Saya', NOW())
	"));
if ($sisa > 0) {
	
	DB::update(DB::raw("UPDATE account_payable set Status='PARTIAL',Paid=Paid+$Payment_Value,Remain_Val=$sisa,Payment_Type='$paymentmode[0]',Payment_Reference='$paymentmode[1]',Payment_Acc_No='$paymentacc' WHERE ID=$getdata->ID"));
}else{
	DB::update(DB::raw("UPDATE account_payable set Status='CLOSED',Paid=Total,Remain_Val=0,Payment_Type='$paymentmode[0]',Payment_Reference='$paymentmode[1]',Payment_Acc_No='$paymentacc' WHERE ID=$getdata->ID"));
}
DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
Task_ID, Location, '$Payment_Date',  Inv_Vendor,Reference, Vendor_ID,Vendor_Name, '$jno', Payment_Acc_No,'Hutang Usaha IDR', '$getbank->Acc_No','$getbank->Bank_Name',CONCAT('$Payment_Note ::', Subject_Payment),'$Payment_Value',0,'$Saya','$Saya',NOW(),Department FROM account_payable WHERE ID=$getdata->ID"));
		
DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
Credit,Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
Task_ID, Location, '$Payment_Date',  Inv_Vendor,Reference, Vendor_ID,Vendor_Name, '$jno', Payment_Acc_No,'Hutang Usaha IDR', '$getbank->Acc_No','$getbank->Bank_Name',CONCAT('$Payment_Note ::', Subject_Payment),'$Payment_Value',0,'$Saya','$Saya',NOW(),Department FROM account_payable WHERE ID=$getdata->ID"));

echo "success";

?>