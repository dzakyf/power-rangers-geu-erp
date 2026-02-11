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

	DB::update(DB::raw("UPDATE invoice_sub_data SET Task_ID='$_GET[Task_ID]',Deskripsi='$a',Price='$b',Qty='$c',Subtotal='$d',Price_Fee='$e',Subtotal_Fee='$f' WHERE ID=$rowId"));




	return "update";
}
function deletekan($rowId, $param)
{
	DB::delete(DB::raw("DELETE FROM invoice_sub_data where ID=$rowId"));
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

	$Task_ID = $_GET['ID'];

	DB::insert(DB::raw("INSERT INTO invoice_sub_data(Task_ID,Deskripsi,Price,Qty,Subtotal,Price_Fee,Subtotal_Fee) VALUES('$_GET[Task_ID]','$a','$b','$c','$d','$e','$f')"));




	return "insert";
}



echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&", $request_body);
foreach ($ids as $datax) {

	$idenya = explode("=", $datax);
	if (preg_match('/(gamp;)/', $idenya[1])) {
		$replace = str_replace('gamp;', '&', $idenya[1]);
		$param[$idenya[0]] = $replace;
	} else {
		$param[$idenya[0]] = $idenya[1];
	}
}
$checkids = explode(",", $param["ids"]);
$Task_ID = $_GET['Task_ID'];

for ($i = 0; $i < sizeof($checkids); $i++) {
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId . "_!nativeeditor_status"];
	$d = $param[$rowId . "_c4"]; // transport
	$a = $param[$rowId . "_c1"]; // origin
	$total +=  $param[$rowId . "_c5"] *  $param[$rowId . "_c6"];
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
echo "</data>";
// DB::update(DB::raw("UPDATE bank_advance set Val_Used=$total,Val_Remain=Bank_Val-$total WHERE Task_ID='$_GET[ID]'"));
