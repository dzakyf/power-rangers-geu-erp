<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function insertkan($rowId,$param) {
	
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // destination
	$c = $param[$rowId."_c3"]; // destination name
	$d = $param[$rowId."_c4"]; // transport
	$e = $param[$rowId."_c5"]; // service
	$f = $param[$rowId."_c6"]; // treshold
	$g = $param[$rowId."_c7"]; // unit price
	$h = $param[$rowId."_c8"]; // additional
	$ix = $param[$rowId."_c9"]; // lead time
	$Task_ID = $param[$rowId."_c10"]; // lead time
	$k = $param[$rowId."_c11"]; // lead time
	$l = $param[$rowId."_c12"]; // lead time
	$m = $param[$rowId."_c13"]; // lead time
	$n = $param[$rowId."_c14"]; // lead time
	$o = $param[$rowId."_c15"]; // lead time
	$p = $param[$rowId."_c16"]; // lead time

	$acx = DB::table("supplier")->where("Supplier_ID",$k)->first();
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
		
	return "insert";
}
function deletekan($rowId,$param){

		return "delete";

}
function updatekan($rowId,$param){
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
	$Task_ID = $_GET['Task_ID'];

	
	$acx = DB::table("supplier")->where("Supplier_ID",$k)->first();
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$nama =  Auth::user()->Name;
	$PRD = DB::table("product_code")->where("Product_Code",$b)->first();

	$PDesc = $PRD->Description;
	$Spec = $PRD->Specification;
	$units = $PRD->Unit_Name;	
	$cekdtl = DB::table("purchase_order_dtl")->where("ID",$rowId)->first();
	$sisalama = $cekdtl->Qty_Remain - $g;
	if ($sisalama < 1){
		DB::update(DB::raw("UPDATE purchase_order set Status_Receive='CLOSED' where PO_No='$cekdtl->PO_No'"));
	}
	$pesanmama = date("Y-m-d H:i:s")." => $nama : $h";
			DB::update(DB::raw("UPDATE purchase_order_dtl set Barcode='$c',SN='$d',Qty_Receive=Qty_Receive+'$f',Qty_Remain=Qty_Remain-'$g',Remark=concat('$pesanmama \n',Remark) where ID=$rowId"));
	$valrec = $ix * $f;	
		$query =DB::table("supplies")->where("Product_Code",$b)->where("Barcode",$d)->get();
		$countf = count($query);
		
		
		DB::insert(DB::raw("INSERT INTO supplies(Supplies_Type,Location,Product_Code,Product_Description,Specification,
		Barcode,SN,Qty,Unit,UnitPrice,PO_No,PR_No,GR_No,Status,Supplier,Remark,User_Trx) SELECT Purchase_Category,'$LSP','$b','$PDesc','$Spec','$c','$d','$f','$units','$ix',PO_No,PR_No,'$Task_ID','GOOD','','$pesanmama','$Saya' FROM good_receive WHERE GR_No='$Task_ID'"));		
		
	return "update";
	
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