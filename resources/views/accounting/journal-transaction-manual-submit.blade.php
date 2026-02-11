<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function updatekan($rowId,$param,$journal) {
	
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

	
	return "update";
}
function deletekan($rowId,$param){
	DB::delete(DB::raw("DELETE FROM branch_journal_temp where ID=$rowId")); 	
}
function insertkan($rowId,$param,$journal){
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

	DB::insert(DB::raw("insert into branch_journal_temp(Task_ID,Location,Posting_Date,Document_No,External_Doc,Journal_No,Account_No,Account_Name,Reference_No,Remark,Debet,Credit,Partner_Name,Partner_Code,Created_By,Created_Date,User_Resp)VALUES('$journal','$city','$a','$b','$c','$journal','$e','$f','$g','$h','$ix','$j','$k','$l','$saya',NOW(),'$saya')"));

	
	
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
$kounting=1;
$ymd = date('ymdhi');

$Task_ID = $_GET['City']."-".$ymd.$kounting;

for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$a = $param[$rowId."_c1"]; // origin
		$nomorx = $param[$rowId."_c0"];

		switch($mode){
			case "updated":
				$action = updatekan($rowId,$param,$Task_ID);
				if ($diupdate == "") $diupdate = $nomorx ;else $diupdate.=",$nomorx";
				break;
		case "deleted":
				$action = deletekan($rowId,$param);
			if ($diinsert == "") $diinsert = $nomorx ;else $diinsert.=",$nomorx";
				break;
		case "inserted":
				$action = insertkan($rowId,$param,$Task_ID);
			if ($didelete == "") $didelete = $nomorx ;else $didelete.=",$nomorx";
				break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";
if ($diupdate != ""){
	$logs = "# update action at row no : $diupdate";
}
if ($diinsert != ""){
	$logs .= "# insert action at row no : $diinsert";
}
if ($didelete != ""){
	$logs .= "# delete action at row no : $didelete";
}
echo "</data>";
	DB::insert(DB::raw("INSERT INTO mam_akunting.branch_journal_task(Task_ID,Task_Date,Location,Status,Created_By)
	   VALUES ('$Task_ID',NOW(),'$_GET[City]','CREATED','$Saya')"));

	DB::insert(DB::raw("INSERT INTO mam_akunting.branch_journal_task_history(Task_ID,Location,Responsible,Description,Actions,Status,Created_By)
	   VALUES ('$Task_ID','$_GET[City]','$Saya','Create Journal Task $logs ','NEW_DATA','CREATED','$Saya')"));
   


?>