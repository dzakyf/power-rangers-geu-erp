<?php
header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 

error_reporting(E_ALL ^ E_NOTICE);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
function deletekan($rowId,$param){

	DB::delete(DB::raw("DELETE FROM purchase_request_dtl where ID=$rowId")); 	
	return "delete";
}
	
function inserted($rowId,$param) {
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
	$prices = $param[$rowId."_c11"]+0;
	$PR_No = $_GET['PR_No'];
		DB::insert(DB::raw("insert into purchase_request_dtl (PR_No,Category,Product_Code,Product_Desc,Qty,Specification,Unit,Remark,Location,Purchase_Type,Purchase_Category,Default_Price) values('$PR_No','$h','$b','$c','$d','$e','$f','$g','$LSP','$a','$ix','$prices')"));
	
	
			
		return "insert";
}	
function updatekan($rowId,$param) {

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
	DB::update(DB::raw("UPDATE purchase_request_dtl  set Qty='$d',Remark='$g' where ID=$rowId"));
	
	
			
		return "update";
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
		switch($mode){
			case "updated":
				$action = updatekan($rowId,$param);
				break;
			case "deleted":
				$action = deletekan($rowId,$param);
				break;
			case "inserted":
				$action = inserted($rowId,$param);
				break;
		}
			
	
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";

?>