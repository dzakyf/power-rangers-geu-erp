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
	$collector = $param[$rowId."_c9"];
	$dreceive = $param[$rowId."_c10"];
	$picrec = $param[$rowId."_c11"];
	$note = $param[$rowId."_c12"];
	
	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	if ($h=='on' || $h == 1){
	
	
	$customer = DB::table("customerz")->where("Customer_No",$m)->first();
	$invd = strtotime($b);
	$hari = $customer->Due_Days*86400;
	$tempo = date("Y-m-d",$invd+$hari);


		DB::update(DB::raw("UPDATE account_receive SET TOP='$customer->Due_Days',Due_Date='$f',Delivered=1,Debt_Collector='$collector',Delivery_Date='$g',Date_Receive='$dreceive',PIC_Receive='$picrec',Delivery_Note='$note',update_by='$Saya' WHERE ID=$rowId"));
	}else{
		DB::update(DB::raw("UPDATE account_receive SET Delivery_Date='$g' WHERE ID=$rowId"));
		
	}
			
		
	
	return "update";
}
function deletekan($rowId,$param){
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

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;

		$Task_ID = $_GET['ID'];

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
DB::update(DB::raw("UPDATE bank_advance set Val_Used=$total,Val_Remain=Bank_Val-$total WHERE Task_ID='$_GET[ID]'"));
?>