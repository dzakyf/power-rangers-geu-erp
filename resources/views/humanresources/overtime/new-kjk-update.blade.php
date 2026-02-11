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
	
	$ax = $param[$rowId."_c0"]; // origin
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
	
	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	
		if ($ax =='SB'){
			$SubmitApproval = 1;
		}else{
			$SubmitApproval = 0;
		}
	DB::update(DB::raw("UPDATE kjk_lists set Allowance='$d',Rate_KJK='$e',KJK_Value='$f'
		,Total='$g'
		,KJK_Period='$h'
		,Submit_Approval = $SubmitApproval
		where ID=$rowId"));
			


	
	return "update";
}
function deletekan($rowId,$param){
	DB::delete(DB::raw("DELETE FROM kjk_lists where ID=$rowId")); 	
}
function insertkan($rowId,$param){
	
	$ax = $param[$rowId."_c0"]; // origin
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // destination
	$c = $param[$rowId."_c3"]; // destination name
	$d = $param[$rowId."_c4"]; // transport
	$e = $param[$rowId."_c5"]; // service
	$f = $param[$rowId."_c6"]+0; // treshold
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

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	
	if ($ax =='SB'){
		$SubmitApproval = 1;
	}else{
		$SubmitApproval = 0;
	}
	

	DB::insert(DB::raw("INSERT INTO kjk_lists (Task_ID, NIK, Name,Allowance, Rate_KJK, KJK_Value, Total, KJK_Period,Note,Created_Date, Created_By,Submit_Approval,Salary_Group) VALUES('$a','$b','$c','$d','$e','$f','$g','$h','$ix',NOW(),'$Saya','$SubmitApproval','$j')"));

	
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
$sbmit = "";
for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$a = $param[$rowId."_c0"]; // origin
		
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