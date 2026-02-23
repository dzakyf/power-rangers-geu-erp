<?php
error_reporting(0);

use App\Opportunity;
use App\OpportunityLog;
use App\OpportunitySales;
use App\OpportunitySalesLog;
use App\OpportunitySalesDtl;
use App\OpportunitySalesDtlLog;
use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function updatekan($rowId,$param, $salesID) {
	
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

    $Saya = auth()->user()->username;
	$cek = OpportunitySales::where("ID", $rowId)->first();
    if ($cek && $b == "CLOSED") {
		$old = json_decode(json_encode($cek), true);
		$old['Action_By'] = $Saya;
		$old['Action_Status'] = "CLOSED_ASSIGNMENT";
		$old['Action_Date'] = now();
		$old['Last_ID'] = $rowId;
		unset($old['ID']);

		OpportunitySalesLog::create($old);

		OpportunitySales::where('ID',$rowId)->update([
			'Status' => $b,
		]);
    }
		
	
	return "update";
}
function deletekan($rowId,$param,$salesID){
	// DB::delete(DB::raw("UPDATE customerz SET Status='CANCELED_FU' where ID=$rowId")); 
    // DB::table('opportunity')->where("ID", $rowId)->update([
    //     'Status' => 'CANCELED_QUALIFIED',
    //     'Updated_By' => auth()->user()->username,
    //     'Updated_Date' => now()
    // ]);
	// 	return "delete";

}
function insertkan($rowId,$param,$salesID){
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

$salesID = "SAL/".$LSP."/".date('Ymdhis')."/".substr(md5($Saya),0,3);

for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	if ($rowId != 'customPayload') {
		$newId = $rowId; //will be used for insert operation	
		$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$a = $param[$rowId."_c1"]; // origin
		$nomorx = $param[$rowId."_c0"];

		switch($mode){
			case "updated":
				$action = updatekan($rowId,$param, $salesID);
				break;
		case "deleted":
				$action = deletekan($rowId,$param, $salesID);
				break;
		case "inserted":
				$action = insertkan($rowId,$param, $salesID);
				break;
		}
			
		echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
	}
}
echo "</data>";

?>