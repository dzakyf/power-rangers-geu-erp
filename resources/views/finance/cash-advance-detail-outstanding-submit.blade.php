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
foreach ($_POST as $key => $value) {

	if ($key != 'ID' && $key != 'SubmitTransfer') {
		if ($key == 'Advance_Val') $value = str_replace(",", "", $value);
		$ars .= "$key = '$value',";
		$ax .= "\"$key\",";
		$isi .= "'$value',";
	}
}
$_POST['Advance_Val'] = str_replace(",", "", $_POST['Advance_Val']);
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars, 0, -1);
$ax = substr($ax, 0, -1);
$isi = substr($isi, 0, -1);
$cekakun = DB::table('cash_handler')->where('NIK_Handler', $Saya)->first();
$Acc = $cekakun->Account_No;

$TaskID = 'CA/' . $LSP . '/' . date('ymd') . '-' . date('His');

//$pecah = explode("|", $_POST['Bank_Account']);
$skrg = date("Y-m-d H:i:s");
DB::insert(DB::raw("UPDATE cash_advance set Status='CREATED',Date_Payment='$_POST[Date_Payment]',Description='$_POST[Description]', cash_handler='$_POST[Cash_Account]'  WHERE ID=$_POST[ID]"));


$jno = 'CA-' . date('ymdhis') . substr($_POST['Task_ID'], -4);

DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Credit,Debet,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
						Task_ID,Location,'$_POST[Date_Payment]',Task_ID,Reference,User_Responsibility_ID,User_Responsibility_Name, '$jno','111.1','Petty Cash', '$_POST[Cash_Account]','','$_POST[Description]',Cash_Val,0,User_Reference,'$Saya',NOW(),Task_ID,Department FROM cash_advance WHERE ID=$_POST[ID]"));


DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Debet,Credit,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
						Task_ID,Location,'$_POST[Date_Payment]',Task_ID,Reference,User_Responsibility_ID,User_Responsibility_Name, '$jno',Acc_No,User_Responsibility_Name, '','','$_POST[Description]',Cash_Val,0,User_Reference,'$Saya',NOW(),Task_ID,Department FROM cash_advance WHERE ID=$_POST[ID]"));


echo "success";
