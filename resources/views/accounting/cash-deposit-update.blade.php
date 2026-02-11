<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 



function deletekan($rowId,$param){
		$a = $param[$rowId."_c1"]; // origin
		$b = $param[$rowId."_c2"]; // destination
		$c = $param[$rowId."_c3"]; // destination name
		$d = $param[$rowId."_c4"]; // transport
		$e = $param[$rowId."_c5"]; // service
		$f = $param[$rowId."_c6"]; // treshold
		$g = $param[$rowId."_c7"]; // unit price
		$Saya = Auth::user()->username;
		$LSP = Auth::user()->Location;

		DB::update(DB::raw("UPDATE cash_deposit SET Status='CANCELED' WHERE ID=$rowId"));
		DB::insert(DB::raw("INSERT INTO cash_log(Location,Cash_No,Transaction,Description,Reference,Status,Created_Date,Created_By)
		VALUES('$LSP','$rowId','CANCEL TASKLOG AND IGNORE','$c','CASH_DEPOSIT','$f',NOW(),'$Saya')"));
		
		return "delete";
}


echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&",$request_body);
foreach($ids as $datax){
	
	$idenya = explode("=",$datax);
	$param[$idenya[0]] = $idenya[1];
	
}
$checkids = explode(",",$param["ids"]);
$Task_ID = $_GET['Task_ID'];

for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$a = $param[$rowId."_c1"]; // origin
		$nomorx = $param[$rowId."_c0"];

		switch($mode){
			case "updated":
				break;
		case "deleted":
				$action = deletekan($rowId,$param);
				break;
		case "inserted":
				break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";

?>