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
	DB::delete(DB::raw("UPDATE bank_withdrawal SET Status='CANCELED' where ID=$rowId")); 
DB::insert(DB::raw("INSERT INTO bank_log(Location,Bank_No,Bank_Acc,Transaction,Description,Reference,Status,Bank_Value,Created_Date,Created_By)
		VALUES('$LSP','$Bank_No','$d','CANCEL TASKLOG','CANCEL BANK WITHDRAWAL','','CANCELED',0,NOW(),'$Saya')"));
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