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

		DB::update(DB::raw("UPDATE bank SET Bank_Acc='$d',Department='$c',Description='BANK IN $cek->Bank_Name $cek->Bank_Branch $d',Total='$f',Status='$j',Reference='$k' WHERE ID=$rowId"));
			DB::insert(DB::raw("INSERT INTO bank_log(Location,Bank_No,Bank_Acc,Transaction,Description,Reference,Status,Bank_Value,Created_Date,Created_By)
		VALUES('$LSP','$Bank_No','$d','CREATE TASKLOG','CREATE BANK IN','','CREATED',0,NOW(),'$Saya')"));
			
		if ($j == 'CLOSED'){
			$jno='JBI-'.date('ymdhis').substr($_GET['ID'],0,-4);

			$bankw = DB::table("bank")->where("ID",$Bank_No)->first();

			$BANKS = DB::table("bank_acc")->where("Acc_No",$bankw->Bank_Acc)->first();

			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
					Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
					Bank_No, Location, Bank_Date, Bank_No, Reference, '','', '$jno', '$BANKS->Bank_Acc','$BANKS->Bank_Name', Bank_Acc,'$BANKS->Bank_Name','$ref',$total,0,Created_By,'$Saya',NOW(),Department FROM bank where ID = $Bank_No"));	
					
			DB::update(DB::raw("UPDATE bank SET Posting_By='$Saya',Posting=1,Posting_Date=NOW() WHERE ID=$Bank_No"));
				
			
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
			Bank_No, Location, Bank_Date, Bank_No, '$c', '','', '$jno', '$a','', '','','$d',0,'$e',Created_By,'$Saya',NOW(),'$g' FROM bank_dtl where ID=$Bank_No"));
	

				
		}			
		
	
	return "update";
}
function deletekan($rowId,$param){
	DB::delete(DB::raw("UPDATE bank SET Status='CANCELED' where ID=$rowId")); 
DB::insert(DB::raw("INSERT INTO bank_log(Location,Bank_No,Bank_Acc,Transaction,Description,Reference,Status,Bank_Value,Created_Date,Created_By)
		VALUES('$LSP','$Bank_No','$d','CANCEL TASKLOG','CREATE BANK IN','','CANCELED',0,NOW(),'$Saya')"));
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
		
		
	if ($c == '' || $d == '' || $e == ''){
		return "error";
		exit;
	}		
	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;

	$cek = DB::table("bank_acc")->where("Acc_No",$d)->first();
		$Bank_No = 'BI/'.$LSP.'/'.date('ymd').'-'.date('His');
	
		DB::insert(DB::raw("INSERT INTO bank (Location,Bank_No,Bank_Type,Bank_Date,Bank_Acc,Status,Description,Currency,Created_Date,Created_By,Total,Department,Reference)
		VALUES('$LSP','$Bank_No','BANK_IN','$b','$d','CREATED','BANK IN $cek->Bank_Name $cek->Bank_Branch $d','$e',NOW(),'$Saya',0,'$c','$k')"));
		
		DB::insert(DB::raw("INSERT INTO bank_log(Location,Bank_No,Bank_Acc,Transaction,Description,Reference,Status,Bank_Value,Created_Date,Created_By)
		VALUES('$LSP','$Bank_No','$d','CREATE TASKLOG','CREATE BANK IN','','CREATED',0,NOW(),'$Saya')"));
			
	
	
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
		$nomorx = $param[$rowId."_c0"];

		switch($mode){
			case "updated":
				$action = updatekan($rowId,$param);
				break;
		case "deleted":
				$action = deletekan($rowId,$param);
				break;
		case "inserted":
				$action = insertkan($rowId,$param);
				break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";

?>