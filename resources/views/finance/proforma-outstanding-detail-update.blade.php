<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo ('<?xml version="1.0" encoding="iso-8859-1"?>');


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

	/*DB::insert(DB::raw("INSERT INTO proforma_detail_file(Proforma_No,File_Category,Reference_No,Path_File) 
	VALUES ('$_GET[Task_ID]','$a','$b','$e')"));
		*/


	return "update";
}
function deletekan($rowId, $param)
{

	//DB::table("proforma_detail_file")->where("ID",$rowId)->delete();
	return "delete";
}

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

	//DB::update(DB::raw("UPDATE proforma_detail_file SET File_Category='$a',Reference_No='$b',Path_File='$e' where ID=$rowId"));



	return "insert";
}

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;


echo "<data>";
$Proforma_No = "PRF-$LSP-" . date('ym-dhis');
$request_body = urldecode(file_get_contents('php://input'));


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
			$action = updatekan($rowId, $param);
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

DB::update(DB::raw("UPDATE proforma set Status='RECEIVED' where Proforma_No='$Task_ID'"));

$proforma = DB::table("proforma")->where("Proforma_No", $Task_ID)->first();
$proformadata = DB::table("proforma_detail")->where("Proforma_No", $Task_ID)->first();
$oppor = DB::table("opportunity")->where("Task_ID", $proformadata->Task_ID)->first();

$jno = "JPM-BA-" . date("ymdhis");
$getcust = DB::table("customerz")->where("Customer_No", $oppor->Account_No)->first();

$coax  = "213.8";
$coaxname = "Hutang PO Penjualan";

DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
		'$proformadata->Task_ID', Location, NOW(),  BA_No,'$proformadata->PO_No', Account_No,Account_Name, '$jno', '$coax','$coaxname', '','',CONCAT(Subject,' ',Periode),Subtotal_PO,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));


$jenis = $proforma->Proforma_Type;

if ($jenis == 'OUTSOURCING') {
	$acc = "411.";
	$accname = "Pendapatan OS " . $getcust->Company_Name;
} elseif ($jenis == 'OUTTASK') {
	$acc = "412.";
	$accname = "Pendapatan OT " . $getcust->Company_Name;
} else {
	$acc = "413.";
	$accname = "Pendapatan Project " . $getcust->Company_Name;
}
$acc .= $getcust->Acc_No_Rev;

DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		 Credit, Debet, User_Resp, Created_By, Created_Date,Department) SELECT
		'$proformadata->Task_ID', Location, NOW(),  BA_No,'$proformadata->PO_No', Account_No,Account_Name, '$jno', '$acc','$accname', '','',CONCAT(Subject,' ',Periode),Subtotal_PO,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));


$kueri = "UPDATE proforma set Posting=1,Posting_By='$Saya',Posting_Date=NOW() WHERE ID=$proforma->ID";
DB::update(DB::raw($kueri));

echo "</data>";
