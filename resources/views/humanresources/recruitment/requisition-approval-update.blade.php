<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function updatekan(string $rowId,array $param = [], $master, $dir = 0) {
	$ax = $param[$rowId."_c0"];
	$notes = htmlspecialchars($param["customPayload_notes"]); // additional

	$Saya = Auth::user()->username;
	if ($ax == 1 || $ax == 'On'){
		$get = DB::table('requisition')->where("ID", $rowId)->first();
		$approval = DB::table('requisition_approval')->where("Task_ID", $get->Task_ID)->where("Approved_By", $Saya)->first();
		if ($approval){
			DB::table('requisition_approval')
				->where('ID', $approval->ID)
				->update([
					"Approved_Status" => 1,
					"Approved_Date" => date("Y-m-d H:i:s"),
					"Approved_Note" => $notes
				]);
		}
		
		$approval = DB::table('requisition_approval')->where("Task_ID", $get->Task_ID)->where("Level", 4)->first();

		if ($dir) {
			$master = DB::table('master_hr_approval')
				->whereIn("Level", [4])
				->where("Location", $LSP)
				->first();

			if ($master) {
				DB::table('requisition_approval')->insert([
					"Task_ID" => $get->Task_ID,
					"Approved_By" => $master->username,
					"Approved_Status" => 0,
					"Approved_Date" => null,
					"Approved_Note" => null
				]);
			}
		}
		
		$get = json_decode(json_encode($get), true);
		$get['Last_ID'] = $get['ID'];
		$get['ID'] = null;
		$get['Action_By'] = $Saya;
		$get['Action_Date'] = date("Y-m-d H:i:s");
		$get['Action_Status'] = "APPROVED by " . $Saya;

		DB::table('requisition_log')->insert($get);
		
		$cek = DB::table('requisition_approval')
			->where("Task_ID", $get['Task_ID'])
			->where('Approved_Status', 0)
			->count();
		
		if ($cek == 0) {
			DB::table('requisition')->where("ID", $rowId)->update([
				"Status" => "APPROVED"
			]);
		}
	}
	return "update";
}
function deletekan(string $rowId,array $param = [], $master){
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

$masterApproval = DB::table('master_hr_approval')->where('username', Auth::user()->username)->first();

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
				$action = updatekan($rowId,$param, $masterApproval,0);
				break;
			case "updated-dir":
				$action = updatekan($rowId,$param, $masterApproval,1);
				break;
			case "deleted":
				$action = deletekan($rowId,$param, $masterApproval);
				break;
			default:
				$action = "error";
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";

?>