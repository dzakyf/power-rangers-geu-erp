<?php
header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 

error_reporting(E_ALL ^ E_NOTICE);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	
function updatekan($rowId,$param) {
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$NIK =  Auth::user()->Emp_ID;

	$a = $param[$rowId."_c1"];
	$b = $param[$rowId."_c2"];
	$c = $param[$rowId."_c3"];
	$d = $param[$rowId."_c4"];
	$e = $param[$rowId."_c5"];
	$f = $param[$rowId."_c6"];
	$g = $param[$rowId."_c7"];
	$h = $param[$rowId."_c8"];
	$ix = $param[$rowId."_c9"];
	$j = $param[$rowId."_c10"];

	if($f !=''){
		DB::insert(DB::raw("UPDATE purchase_request set Priority_Status='$f',Priority_Note='$g',Priority_By='$Saya'  WHERE ID=$rowId"));
		
		DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) SELECT '$LSP', PR_No, Category,Sub_Category,Supplies_Type, 'PURCHASE-REQUEST', 'CONFIRM PRIORITY', '$g', NOW(), '$Saya' FROM purchase_request where ID=$rowId"));	
	}
	
			
		return "insert";
}

echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));

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
	
	$action = updatekan($rowId,$param);
		
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";

?>