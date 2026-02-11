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

$TaskID = 'BA/' . $LSP . '/' . date('ymd') . '-' . date('His');

$pecah = explode("|", $_POST['Bank_Account']);
$skrg = date("Y-m-d H:i:s");
DB::insert(DB::raw("UPDATE bank_advance set Bank_Account='$pecah[0]',Bank_Acc='$pecah[1]', Status='CREATED',Date_Payment='$_POST[Date_Payment]',Description=CONCAT(Description,'$_POST[Description]') WHERE ID=$_POST[ID]"));


$jno = 'BA-' . date('ymdhis') . substr($_POST['Task_ID'], -4);

if($_POST['adm_bank'] > 0) {

	DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Credit,Debet,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
						Task_ID,Location,'$_POST[Date_Payment]',Task_ID,Reference,User_Responsibility_ID,User_Responsibility_Name, '$jno','$pecah[1]','', '$pecah[0]','','$_POST[Description]',Bank_Val+$_POST[adm_bank],0,User_Reference,'$Saya',NOW(),Task_ID,Department FROM bank_advance WHERE ID=$_POST[ID]"));


	DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Debet,Credit,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
						Task_ID,Location,'$_POST[Date_Payment]',Task_ID,Reference,User_Responsibility_ID,User_Responsibility_Name, '$jno',Acc_No,User_Responsibility_Name, '','','$_POST[Description]',Bank_Val,0,User_Reference,'$Saya',NOW(),Task_ID,Department FROM bank_advance WHERE ID=$_POST[ID]"));
	DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
	Debet,Credit,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
	Task_ID,Location,'$_POST[Date_Payment]',Task_ID,Reference,User_Responsibility_ID,User_Responsibility_Name, '$jno','522.8.1',User_Responsibility_Name, '','','Beban Adm Bank#$_POST[Description]',$_POST[adm_bank],0,User_Reference,'$Saya',NOW(),Task_ID,Department FROM bank_advance WHERE ID=$_POST[ID]"));

} else {

	DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Credit,Debet,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
						Task_ID,Location,'$_POST[Date_Payment]',Task_ID,Reference,User_Responsibility_ID,User_Responsibility_Name, '$jno','$pecah[1]','', '$pecah[0]','','$_POST[Description]',Bank_Val,0,User_Reference,'$Saya',NOW(),Task_ID,Department FROM bank_advance WHERE ID=$_POST[ID]"));


DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Debet,Credit,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
						Task_ID,Location,'$_POST[Date_Payment]',Task_ID,Reference,User_Responsibility_ID,User_Responsibility_Name, '$jno',Acc_No,User_Responsibility_Name, '','','$_POST[Description]',Bank_Val,0,User_Reference,'$Saya',NOW(),Task_ID,Department FROM bank_advance WHERE ID=$_POST[ID]"));
}




echo "success";
