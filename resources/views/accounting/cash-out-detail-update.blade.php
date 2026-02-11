<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 

function updatekan($rowId,$param,$jno){
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // destination
	$c = $param[$rowId."_c3"]; // destination name
	$d = $param[$rowId."_c4"]; // transport
	$f = $param[$rowId."_c6"]; // treshold
	$e = $param[$rowId."_c5"]*$f; // service
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
		if ($g == '') $gx = "Department"; else $gx="'$g'";
		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
		Cash_No, Location, Cash_Date, Cash_No, '$c', '','', '$jno', '$a','', '','','$d','$e',0,Created_By,'$Saya',NOW(), $gx FROM cash where ID=$Cash_No"));	
	
	
	return "update";
	
}



echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$Cash_No = $_GET['ID'];
$jno='JCO-'.date('ymdhis').substr($_GET['ID'],0,-4);
$fa = DB::select(DB::raw("SELECT * FROM cash WHERE ID=$Cash_No"));
$ids = explode("&",$request_body);
foreach($ids as $datax){
	
	$idenya = explode("=",$datax);
	$param[$idenya[0]] = $idenya[1];
	
}
$checkids = explode(",",$param["ids"]);
$ref = 'CASH OUT';
for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$a = $param[$rowId."_c1"]; // origin
		$nomorx = $param[$rowId."_c0"];
		$ref .="::".$d;
		$total += $param[$rowId."_c5"]*$param[$rowId."_c6"];
		$action = updatekan($rowId,$param,$jno);
				
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
$words = preg_split('/(\s+)/u', trim($ref), -1, PREG_SPLIT_DELIM_CAPTURE);
        if (count($words) / 2 > 700) {
           $truncateWord = implode('', array_slice($words, 0, (700 * 2) - 1)) . "...";
        } else {
			$truncateWord = $ref;
		}
DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
		Cash_No, Location, Cash_Date, Cash_No, Voucher_No, '','', '$jno', Description,'', '','','$truncateWord',0,$total,Created_By,'$Saya',NOW(),Department FROM cash where ID=$Cash_No"));	
		
	DB::update(DB::raw("UPDATE cash SET Total='$total',Status='CLOSED',Posting_By='$Saya',Posting=1,Posting_Date=NOW() WHERE ID=$Cash_No"));
	
	
echo "</data>";

?>