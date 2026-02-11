<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo ('<?xml version="1.0" encoding="iso-8859-1"?>');


function updatekan($rowId, $param)
{

	$a = $param[$rowId . "_c1"]; // origin
	$b = $param[$rowId . "_c2"]; // destination
	$c = $param[$rowId . "_c3"]; // destination name
	$d = $param[$rowId . "_c4"]; // transport
	$e = $param[$rowId . "_c5"]; // service
	$f = $param[$rowId . "_c6"]; // treshold
	$g = $param[$rowId . "_c7"]; // unit price
	$h = $param[$rowId . "_c8"]; // additional
	$ix = $param[$rowId . "_c9"]; // lead time
	$j = $param[$rowId . "_c10"]; // lead time
	$k = $param[$rowId . "_c11"]; // lead time
	$l = $param[$rowId . "_c12"]; // lead time
	$m = $param[$rowId . "_c13"]; // lead time
	$n = $param[$rowId . "_c14"]; // lead time
	$o = $param[$rowId . "_c15"]; // lead time
	$p = $param[$rowId . "_c16"]; // lead time

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;

	$adv = DB::table('bank_advance')->where('Task_ID', $rowId)->first();
	$cashval = $adv->Cash_Val;
	if ($adv->Posting == 9) {
		$nomorposting = 0;
		$pos1 = "Debet,Credit";
		$pos2 = "Credit,Debet";
		$deskripsi = $adv->Description;
		$sisa = $adv->Cash_Val;
		$total = 0;
		$jno = 'CA-' . date('ymdhis') . substr($_POST['Task_ID'], -4);
		$from = DB::table('cash_handler')->where('NIK_Handler', $adv->Created_By)->first();


		DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						$pos1,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
						Task_ID,Location,NOW(),Task_ID,Reference,User_Responsibility_ID,User_Responsibility_Name, '$jno',Acc_No,'', User_Reference,'','$deskripsi','$sisa',0,Created_By,'$Saya',NOW(),Task_ID,Department FROM bank_advance WHERE Task_ID='$rowId'"));

		DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
							$pos2,User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
							Task_ID,Location,NOW(),Task_ID,Reference,'$from->NIK_Handler','$from->Name_Handler', '$jno','$from->Account_No','', User_Reference,'','$deskripsi','$sisa',0,Created_By,'$Saya',NOW(),Task_ID,Department FROM bank_advance WHERE Task_ID='$rowId'"));
		DB::update(DB::raw("UPDATE bank_advance SET Posting=0,Posting_By='$Saya',Posting_Date=NOW() WHERE Task_ID='$rowId'"));
	}
	return "update";
}
function deletekan($rowId, $param)
{
	DB::update(DB::raw("UPDATE bank_advance SET  Posting=3, Status ='CREATED' WHERE Task_ID='$rowId'"));

	return "delete";
}



echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&", $request_body);
foreach ($ids as $datax) {

	$idenya = explode("=", $datax);
	$param[$idenya[0]] = $idenya[1];
}
$checkids = explode(",", $param["ids"]);

for ($i = 0; $i < sizeof($checkids); $i++) {
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId . "_!nativeeditor_status"];
	$d = $param[$rowId . "_c4"]; // transport
	$a = $param[$rowId . "_c1"]; // origin
	$nomorx = $param[$rowId . "_c0"];

	switch ($mode) {
		case "updated":
			$action = updatekan($rowId, $param);
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
