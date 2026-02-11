<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo ('<?xml version="1.0" encoding="iso-8859-1"?>');


function updatekan($rowId, $param, $jno, $totalval)
{

	$a = $param[$rowId . "_c1"]; // origin
	$b = $param[$rowId . "_c2"]; // destination
	$c = $param[$rowId . "_c3"]; // destination name
	$d = $param[$rowId . "_c4"]; // transport
	$e = $param[$rowId . "_c5"] + 0; // service
	$f = $param[$rowId . "_c6"]; // treshold
	$g = $param[$rowId . "_c7"]; // unit price
	$h = $param[$rowId . "_c8"]; // additional
	$ix = $param[$rowId . "_c9"]; // lead time
	$j = $param[$rowId . "_c10"] + 0; // lead time
	$k = $param[$rowId . "_c11"]; // lead time
	$l = $param[$rowId . "_c12"]; // lead time
	$m = $param[$rowId . "_c13"]; // lead time
	$n = $param[$rowId . "_c14"]; // lead time
	$o = $param[$rowId . "_c15"]; // lead time
	$p = $param[$rowId . "_c16"]; // lead time

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;

	DB::update(DB::raw("UPDATE bank_advance_dtl SET Posting=1,Posting_Date=NOW(),Posting_By='$Saya' WHERE ID=$rowId"));
	$getAp = DB::table('account_payable')->where('Task_ID', $c)->first();
	if($getAp) {
		$sisa = $getAp->Remain_Val - $e;
		if($sisa > 0) {
			$xstatus = "PARTIAL";
		} else {
			$xstatus = "CLOSED";
		}

		DB::update(DB::raw("UPDATE account_payable SET Payment_Type = 'BANK ADVANCED',Payment_Note = CONCAT(Payment_Note,'--# ','$d'),Payment_Reference=$e,Paid=Paid + $e,Remain_Val=Remain_Val-$e,Status='$xstatus' WHERE ID = $getAp->ID"));
	}



	if ($e == 0) {

		DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
				Credit,Debet,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
				Task_ID,Location,'$b',Reference,'$c',User_Responsibility_ID,User_Responsibility_Name, '$jno','$h','', '$ix',User_Reference,Description,'$j',0,Created_By,'$Saya',NOW(),Task_ID,'$g' FROM bank_advance WHERE Task_ID='$_GET[ID]'"));
	} else {
		if (empty($a)) {
			DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
				Credit,Debet,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
				Task_ID,Location,'$b',Reference,'$c',User_Responsibility_ID,User_Responsibility_Name, '$jno','$h','', '$ix',User_Reference,Description,0,'$e',Created_By,'$Saya',NOW(),Task_ID,'$g' FROM bank_advance WHERE Task_ID='$_GET[ID]'"));
		} else {
			DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
				Credit,Debet,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
				Task_ID,Location,'$b',Reference,'$c',User_Responsibility_ID,User_Responsibility_Name, '$jno','$a','', '$ix',User_Reference,Description,0,'$e',Created_By,'$Saya',NOW(),Task_ID,'$g' FROM bank_advance WHERE Task_ID='$_GET[ID]'"));
		}


		if ($j > 0) {
			DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
					Credit,Debet,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
					Task_ID,Location,'$b',Reference,'$c',User_Responsibility_ID,User_Responsibility_Name, '$jno','$h','', '$ix',User_Reference,Description,'$j',0,Created_By,'$Saya',NOW(),Task_ID,'$g' FROM bank_advance WHERE Task_ID='$_GET[ID]'"));
		}
	}



	return "update";
}
function deletekan($rowId, $param)
{
	DB::delete(DB::raw("DELETE FROM bank_advance_dtl where ID=$rowId"));
}




echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$jno = 'JBA-' . date('ymdhis') . substr($_POST['Task_ID'], -4);

$ids = explode("&", $request_body);
foreach ($ids as $datax) {

	$idenya = explode("=", $datax);
	$param[$idenya[0]] = $idenya[1];
}
$checkids = explode(",", $param["ids"]);
$total = 0;
$adv = DB::table('cash_advance')->where('Task_ID', $_GET['ID'])->first();
$cashval = $adv->Cash_Val;

for ($i = 0; $i < sizeof($checkids); $i++) {
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId . "_!nativeeditor_status"];
	$d = $param[$rowId . "_c4"]; // transport
	$a = $param[$rowId . "_c1"]; // origin
	$b = $param[$rowId . "_c2"]; // destination
	$c = $param[$rowId . "_c3"]; // destination name
	$d = $param[$rowId . "_c4"]; // transport
	$e = $param[$rowId . "_c5"]; // service


	switch ($mode) {
		case "updated":
			$action = updatekan($rowId, $param, $jno, $cashval);
			$total +=  $param[$rowId . "_c5"];
			$lastakun = $a;

			break;
		case "deleted":
			$action = deletekan($rowId, $param);
			break;
		case "inserted":
			break;
	}
	echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
}
echo "</data>";

$nomorposting = "";
if ($adv->Posting == 0) {

	$from = DB::table('cash_handler')->where('NIK_Handler', $adv->Created_By)->first();

	$sisa = $cashval - $total;
	if ($sisa  > 0) {


		$pos2 = "Debet,Credit";
		$pos1 = "Credit,Debet";


		$deskripsi = "BANK REIMBURSEMENT";

		$nomorposting = 1;


		DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						$pos1,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
						Task_ID,Location,NOW(),Task_ID,Reference,User_Responsibility_ID,User_Responsibility_Name, '$jno','$cashval->User_Responsibility','', User_Reference,'','$deskripsi','$sisa',0,Created_By,'$Saya',NOW(),Task_ID,Department FROM bank_advance WHERE Task_ID='$_GET[ID]'"));

		DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						$pos2,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
						Task_ID,Location,NOW(),Task_ID,Reference,'$from->NIK_Handler','$from->Name_Handler', '$jno','$from->Account_No','', User_Reference,'','$deskripsi','$sisa',0,Created_By,'$Saya',NOW(),Task_ID,Department FROM bank_advance WHERE Task_ID='$_GET[ID]'"));
	}

	DB::update(DB::raw("UPDATE bank_advance SET Posting=1,Posting_By='$Saya',Posting_Date=NOW() WHERE Task_ID='$_GET[ID]'"));
}
