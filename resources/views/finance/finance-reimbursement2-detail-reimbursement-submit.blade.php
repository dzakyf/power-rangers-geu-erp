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


$getdata = DB::table("reimbursement")->where("ID", $_POST['ID'])->first();
$total = $getdata->Total;
$paid = $getdata->Paid;


$_POST['Payment_Value'] = str_replace(".", "", $_POST['Payment_Value']);
$_POST['Payment_Value'] = str_replace(",", "", $_POST['Payment_Value']);
$Payment_Value = $_POST['Payment_Value'];
$Payment_Date = $_POST['Payment_Date'];
$Payment_Note = $_POST['Payment_Note'];
$paymentmode = explode("|", $_POST['Payment_Mode']);
$sisa = $total - $paid - $Payment_Value;
$accno = $paymentmode[1];
if ($paymentmode[0] == 'BANK') {
	$tipe = "BANK";
	$getbank = DB::table("bank_acc")->where("Acc_No", $paymentmode[1])->first();
	$Bank_No = 'BOR/' . $LSP . '/' . date('ymd') . '-' . date('His');

	DB::insert(DB::raw("INSERT INTO bank (Location,Bank_No,Bank_Type,Bank_Date,Bank_Acc,Status,Description,Currency,Created_Date,Created_By,Total,Department,Reference)
		VALUES('$LSP','$Bank_No','BANK_OUT_REIMBURSEMENT','$Payment_Date','$paymentmode[1]','CLOSED','BANK OUT REIMBURSEMENT INDIVIDUAL :: $Payment_Note','Rp',NOW(),'$Saya',$Payment_Value,'$getdata->Department','$getdata->Reimbursement_No')"));

	DB::insert(DB::raw("INSERT INTO bank_log(Location,Bank_No,Bank_Acc,Transaction,Description,Reference,Status,Bank_Value,Created_Date,Created_By)
		VALUES('$LSP','$Bank_No','$paymentmode[1]','CREATE TASKLOG AND CLOSE','BANK OUT REIMBURSEMENT INDIVIDUAL :: $Payment_Note','','CLOSED','$Payment_Value',NOW(),'$Saya')"));
	DB::insert(DB::raw("INSERT INTO bank_dtl(Bank_No,Acc_No,Date_Bank,Reference,Remark,Bank_Val,Bal_Acc,Created_By,Last_Update,Department) SELECT '$Bank_No',Acc_No,Reimbursement_Date,Reimbursement_No,Remark,Amount,1,'$Saya',NOW(),Department from reimbursement_dtl WHERE Reimbursement_No='$getdata->Reimbursement_No'"));

	$paymentacc = $getbank->Bank_Acc;
	$DID = $Bank_No;
} else {
	$tipe = "CASH";
	$Cash_No = 'COR/' . $LSP . '/' . date('ymd') . '-' . date('His');
	$DID = $Cash_No;
	DB::insert(DB::raw("INSERT INTO cash (Location,Cash_No,Cash_Type,Cash_Date,Description,Status,Total,Voucher_No,Department,Created_Date,Created_By,Currency,Reference)
		VALUES('$LSP','$Cash_No','CASH_OUT_REIMBURSEMENT','$Payment_Date','$paymentmode[1]','CLOSED',$Payment_Value,'$getdata->Task_ID','$getdata->Department',NOW(),'$Saya','Rp','$getdata->Reimbursement_No')"));

	DB::insert(DB::raw("INSERT INTO cash_dtl(Cash_No,Acc_No,Date_Cash,Reference,Remark,Cash_Val,Bal_Acc,Created_By,Last_Update,Department) SELECT '$Cash_No',Acc_No,Reimbursement_Date,Reimbursement_No,Remark,Amount,1,'$Saya',NOW(),Department from reimbursement_dtl WHERE Reimbursement_No='$getdata->Reimbursement_No'"));


	DB::insert(DB::raw("INSERT INTO cash_log(Location,Cash_No,Transaction,Description,Reference,Status,Created_Date,Created_By)
		VALUES('$LSP','$Cash_No','CREATE TASKLOG','$Payment_Note','CASH_OUT_REIMBURSEMENT_INDIVIDUAL','CREATED',NOW(),'$Saya')"));
	$paymentacc = $paymentmode[1];
}

DB::update(DB::raw("UPDATE reimbursement set Status='CLOSED',Paid=$Payment_Value,Remain=$sisa,Payment_Type='$paymentmode[0]',Payment_Reference_No='$paymentmode[1]',Payment_Acc_No='$paymentacc',Payment_Date=NOW(),Payment_By='$Saya' WHERE ID=$getdata->ID"));



echo "success";
