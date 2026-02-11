<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function updatekan($rowId,$param) {
	
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // destination
	$c = $param[$rowId."_c3"]; // destination name
	$d = $param[$rowId."_c4"]; // transport
	$e = $param[$rowId."_c5"]; // service
	$f = $param[$rowId."_c6"]; // treshold
	$g = $param[$rowId."_c7"]; // unit price
	$h = $param[$rowId."_c8"]; // additional
	$ix = $param[$rowId."_c9"]; // lead time
	$j = $param[$rowId."_c10"]; // lead time
	$k = $param[$rowId."_c11"]; // lead time
	$l = $param[$rowId."_c12"]; // lead time
	$m = $param[$rowId."_c13"]; // lead time
	$n = $param[$rowId."_c14"]; // lead time
	$o = $param[$rowId."_c15"]; // lead time
	$p = $param[$rowId."_c16"]; // lead time

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
	$dept =DB::table("master_department")->where("Initial",$b)->first();

	DB::update(DB::raw("UPDATE proyeksi_cashflow set  Proyeksi_Year='$a', Department_Code='$b', Department='$dept->Department', Description='$c', January_Value='$d', February_Value='$e', March_Value='$f', April_Value='$g', May_Value='$h', June_Value='$ix', July_Value='$j', August_Value='$k', September_Value='$l', October_Value='$m', November_Value='$n', December_Value='$o', Total_Year='$p', Note='$q' where ID=$rowId"));
			


	
	
	return "update";
}
function deletekan($rowId,$param){
	DB::delete(DB::raw("DELETE FROM proyeksi_cashflow where ID=$rowId")); 	
	return "delete";
}
function insertkan($rowId,$param){
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // destination
	$c = $param[$rowId."_c3"]; // destination name
	$d = $param[$rowId."_c4"]; // transport
	$e = $param[$rowId."_c5"]; // service
	$f = $param[$rowId."_c6"]; // treshold
	$g = $param[$rowId."_c7"]; // unit price
	$h = $param[$rowId."_c8"]; // additional
	$ix = $param[$rowId."_c9"]; // lead time
	$j = $param[$rowId."_c10"]; // lead time
	$k = $param[$rowId."_c11"]; // lead time
	$l = $param[$rowId."_c12"]; // lead time
	$m = $param[$rowId."_c13"]; // lead time
	$n = $param[$rowId."_c14"]; // lead time
	$o = $param[$rowId."_c15"]; // lead time
	$p = $param[$rowId."_c16"]; // lead time
	$q = $param[$rowId."_c17"]; // lead time
	$r = $param[$rowId."_c18"]; // lead time

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	$Task_ID = "BM-".$LSP."-".date('ymdhis');
	$dept =DB::table("master_department")->where("Initial",$b)->first();
	DB::insert(DB::raw("INSERT INTO proyeksi_cashflow (Task_ID, Proyeksi_Year, Department_Code, Department, Description, January_Value, February_Value, March_Value, April_Value, May_Value, June_Value, July_Value, August_Value, September_Value, October_Value, November_Value, December_Value, Total_Year, Note, Created_By, Created_Date, Status) VALUES('$Task_ID','$a', '$b','$dept->Department', '$c', '$d', '$e', '$f', '$g', '$h', '$ix', '$j', '$k', '$l', '$m', '$n', '$o', '$p', '$q','$Saya',NOW(), 'CREATED')"));


	
	
	return "insert";
	
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
		switch($mode){
			case "updated":
				$action = updatekan($rowId,$param);break;
		case "deleted":
				$action = deletekan($rowId,$param);break;
		case "inserted":
				$action = insertkan($rowId,$param);break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";




?>