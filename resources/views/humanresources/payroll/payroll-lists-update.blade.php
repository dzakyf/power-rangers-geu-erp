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
	$r = $param[$rowId."_c18"]; // lead time


$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
		
	DB::update(DB::raw("UPDATE payrol_master_lists set Urutan='$a',Name ='$b',Kena_Potongan='$c',Default_PPH='$d',Type_Payroll='$e',Calculation_Method='$f'
		,Calculation_Value='$g'
		,Calculation_Max_Value='$h'
		,Limit_Amount='$ix'
		,Formula_Desc = '$j'
		,WP = '$k',
		Modified_By = '$Saya',
		Modified_Date = NOW(),
		AP_Salary_Group = '$r'
	where ID=$rowId"));
			


	
	
	return "update";
}
function deletekan($rowId,$param){
	DB::delete(DB::raw("DELETE FROM payrol_master_lists where ID=$rowId")); 	
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
	$r = $param[$rowId."_c18"]; // lead time

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	$getLast = DB::table("payrol_master_lists")->orderBy('ID', 'DESC')->first(); 
	$PIDx = str_replace("PY","",$getLast->Payroll_ID);
	$PID = "PY".sprintf("%'.06d", (int)$PIDx + 1);
	DB::insert(DB::raw("INSERT INTO payrol_master_lists(Payroll_ID,Urutan,Name,Kena_Potongan,Default_PPH,Type_Payroll,Calculation_Method,Calculation_Value,Calculation_Max_Value,Limit_Amount,Formula_Desc,WP,Created_By,Created_Date,AP_Salary_Group) VALUES('$PID','$a','$b','$c','$d','$e','$f','$g','$h','$ix','$j','$k','$Saya',NOW(),'$r')"));


	
	
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