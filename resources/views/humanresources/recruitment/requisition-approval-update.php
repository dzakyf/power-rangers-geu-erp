<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function updatekan(string $rowId,array $param = []) {
	$ax = $param[$rowId."_c0"];
	$notes = htmlspecialchars($param["customPayload_notes"]); // additional

	$Saya = Auth::user()->username;
	if ($ax == 1 || $ax == 'On'){
		$get = DB::table('requisition')->where("ID", $rowId)->first();
		$approval = DB::table('requisition_approval')->where("Task_ID", $get->Task_ID)->where("Approved_By", $Saya)->first();
		if ($approval){
			DB::table('requisition_approval')
				->where("Task_ID", $get->Task_ID)
				->where("Approved_By", $Saya)->update([
					"Approved_Status" => 1,
					"Approved_Date" => date("Y-m-d H:i:s"),
					"Approved_Note" => $notes
				]);
		}
		$get = json_decode(json_encode($get), true);
		$get['Last_ID'] = $get['ID'];
		$get['ID'] = null;
		$get['Action_By'] = $Saya;
		$get['Action_Date'] = date("Y-m-d H:i:s");
		$get['Action_Status'] = "APPROVED";

		DB::table('requisition_log')->insert($get);
	}
	return "update";
}
function deletekan(string $rowId,array $param = []){
	$ax = $param[$rowId."_c0"];
	$notes = htmlspecialchars($param["customPayload_notes"]); // additional

	$Saya = Auth::user()->username;
	if ($ax == 1 || $ax == 'On'){
		$get = DB::table('requisition')->where("ID", $rowId)->first();
		$approval = DB::table('requisition_approval')
			->where("Task_ID", $get->Task_ID)
			->where("Approved_By", $Saya)
			->first();
		if ($approval){
			DB::table('requisition_approval')
				->where("Task_ID", $get->Task_ID)
				->where("Approved_By", $Saya)->update([
					"Approved_Status" => 2,
					"Approved_Date" => date("Y-m-d H:i:s"),
					"Approved_Note" => $notes
				]);
		}
		$get = json_decode(json_encode($get), true);
		$get['Last_ID'] = $get['ID'];
		$get['ID'] = null;
		$get['Action_By'] = $Saya;
		$get['Action_Date'] = date("Y-m-d H:i:s");
		$get['Action_Status'] = "REJECTED";

		DB::table('requisition_log')->insert($get);
	}
	return "delete";

}


echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));

$ids = explode("&",$request_body);
$param = [];
$action = '';
foreach($ids as $datax){
	
	$idenya = explode("=",$datax);
	$param[$idenya[0]] = $idenya[1];
	
}
$checkids = explode(",",$param["ids"]);


for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	if($rowId == "customPayload") continue;
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
			default:
				$action = "error";
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";

?>