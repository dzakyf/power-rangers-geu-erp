<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo ('<?xml version="1.0" encoding="iso-8859-1"?>');


function updatekan($rowId, $proformano, $param)
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


	DB::update(DB::raw("UPDATE proforma_detail_file SET File_Category='$a',Reference_No='$b',Path_File='$d' where ID=$rowId"));


	return "update";
}
function deletekan($rowId, $param)
{

	DB::table("proforma_detail_file")->where("ID", $rowId)->delete();
	return "delete";
}

function insertkan($rowId, $param)
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


	DB::insert(DB::raw("INSERT INTO proforma_detail_file(Proforma_No,File_Category,Reference_No,Path_File,Created_Date) 
	VALUES ('$_GET[Task_ID]','$a','$b','$d',NOW())"));


	return "insert";
}

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;


echo "<data>";
$Proforma_No = "PRF-$LSP-" . date('ym-dhis');
$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&", $request_body);
foreach ($ids as $datax) {

	$idenya = explode("=", $datax);
	$param[$idenya[0]] = $idenya[1];
}
$checkids = explode(",", $param["ids"]);
$Task_ID = $_GET['Task_ID'];
$qty = 1;
for ($i = 0; $i < sizeof($checkids); $i++) {
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId . "_!nativeeditor_status"];
	$d = $param[$rowId . "_c4"]; // transport
	$a = $param[$rowId . "_c1"]; // origin
	switch ($mode) {
		case "updated":
			$action = updatekan($rowId, $Proforma_No, $param);
			break;
		case "deleted":
			$action = updatekan($rowId, $param);
			break;
		case "inserted":
			$action = insertkan($rowId, $param);
			break;
	}
	echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
}
// $salt = md5(date('c'));

// $getlevelsx = DB::table("organization_structure")->where("Department", "BUS")->where("Level3", "1")->where("Alerted", "1")->first();
// $mailto = $getlevelsx->Email;

DB::update(DB::raw("UPDATE proforma set Status='PROPOSED', Last_Update = NOW() where Proforma_No='$_GET[Task_ID]'"));
// $PROFORMA = DB::table("proforma")->where("Proforma_No",	$_GET['Task_ID'])->first();
// $subjek = "MOBILE APPROVAL BAST NO $PROFORMA->BA_No # DATE : $PROFORMA->BA_Date";
// $badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>" . $getlevelsx->Name . "</b><Br><br>

// 					" . $Saya . " telah mengajukan approval BAST. Mohon dilakukan approval untuk BAST tersebut. Persetujuan dapat dilakukan menggunakan link berikut ini :<br><br>

// 					<a href=\"https://erp.ksps.co.id/erpksps/public/approveproforma?ID=$_GET[Task_ID]&key=$salt\">Approval</a>


// 					<br><br>

// 					Terima kasih
// 					PT. GREEN ENERGY UTAMA

// 		</div>";
// DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$mailto','$subjek','$badan','UNSENT',NOW(),NOW())"));
echo "</data>";
