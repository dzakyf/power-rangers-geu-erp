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

	DB::update(DB::raw("UPDATE cash_advance_dtl SET Acc_No='$a',Date_Cash='$b',Reference='$c',Remark='$d',Cash_Val='$e',Bal_Acc='$f',Department='$g',COA_Lawan='$h',Reference_Lawan='$ix',Nilai='$j',Sisa='$k' WHERE ID=$rowId"));

			
		
	
	return "update";
}
function deletekan($rowId,$param){
	DB::delete(DB::raw("DELETE FROM cash_advance_dtl where ID=$rowId")); 	
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

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;

		$Cash_No = $_GET['ID'];

		DB::insert(DB::raw("INSERT INTO cash_advance_dtl(Cash_No,Acc_No,Date_Cash,Reference,Remark,Cash_Val,Bal_Acc,Created_By,Last_Update,Department,COA_Lawan,Reference_Lawan,Nilai,Sisa) VALUES('$Cash_No','$a','$b','$c','$d','$e','$f','$Saya',NOW(),'$g','$h','$ix','$j','$k')"));

		
	
	
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
$Task_ID = $_GET['Task_ID'];

for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$a = $param[$rowId."_c1"]; // origin
		$total +=  $param[$rowId."_c5"] *  $param[$rowId."_c6"];
		switch($mode){
			case "updated":
				$action = updatekan($rowId,$param);
				break;
		case "deleted":
				$action = updatekan($rowId,$param);
				break;
		case "inserted":
				$action = insertkan($rowId,$param);
				break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";
DB::update(DB::raw("UPDATE cash_advance set Val_Used=$total,Val_Remain=Cash_Val-$total WHERE Task_ID='$_GET[ID]'"));
?>