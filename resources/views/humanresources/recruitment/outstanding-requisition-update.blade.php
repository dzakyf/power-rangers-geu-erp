<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo ('<?xml version="1.0" encoding="iso-8859-1"?>');


function updatekan($rowId, $param)
{
	$ax = $param[$rowId . "_c0"];
	$a = $param[$rowId . "_c2"]; // origin
	$b = $param[$rowId . "_c3"]; // destination
	$c = $param[$rowId . "_c4"]; // destination name
	$d = $param[$rowId . "_c5"]; // transport
	$e = $param[$rowId . "_c6"]; // service
	$f = $param[$rowId . "_c7"]; // treshold
	$g = $param[$rowId . "_c8"]; // unit price
	$h = htmlspecialchars($param[$rowId . "_c9"]); // additional
	$ix = $param[$rowId . "_c10"]; // lead time
	$j = $param[$rowId . "_c11"]; // lead time
	$k = $param[$rowId . "_c12"]; // lead time
	$l = $param[$rowId . "_c13"]; // lead time
	$m = $param[$rowId . "_c14"]; // lead time
	$n = $param[$rowId . "_c15"]; // lead time
	$o = $param[$rowId . "_c16"]; // lead time
	$p = $param[$rowId . "_c17"]; // lead time


	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	DB::insert(DB::raw("INSERT INTO  requisition  ( Location ,  Task_ID ,  Job_Position ,  Description ,  Requirement ,  Department ,  Priority ,  Qty ,  Start_Date ,  End_Date ,  Status ,  Created_Date ,  Created_By ,  Requisition_Type ,  PO_No ,  PO_Desc ,Subject)
		
		SELECT '$LSP' , Task_ID , Position , Detail_Position ,  Requirement, Department,  'NORMAL' ,  Qty ,  '$k' , '$l' ,  'CREATED' ,  NOW() ,  '$Saya' ,  'PO' ,  Task_ID,CONCAT(Position ,' :: ',Site),'$j'  FROM project_detail_requisition where ID=$rowId"));

	DB::update(DB::raw("UPDATE project_detail_requisition SET Approved=1,Approved_Date=NOW(),Approved_By='$Saya' where ID=$rowId"));

	$cek = DB::table("project_detail_requisition")->where("ID", $rowId)->first();
	$requisition = DB::table("requisition")->where("Task_ID", $cek->Task_ID)->first();
	if ($requisition->Requisition_Type == 'PO') {
		$pm = DB::table("project_mgt")->where("Task_ID", $cek->Task_ID)->first();
		$cust = DB::table("customerz")->where("Customer_No", $pm->Customer_No)->first();
		$pmd = DB::table("opportunity_detail")->where("Task_ID", $pm->Source_Reference)->first();
		if ($pmd->Project_Type == 'OUTSOURCING') {
			$COATIPE = "COA_OS_" . $cust->Alias_Customer;
			$tipe = 'OUTSOURCE';
		} elseif ($pmd->Project_Type == 'OUTTASK') {
			$COATIPE = "COA_OT_" . $cust->Alias_Customer;
			$tipe = 'OUTTASK';
		} else {
			$COATIPE = "COA_PROJECT_" . $cust->Alias_Customer;
			$tipe = 'PROJECT';
		}
		$cekGroup = DB::table("payrol_main_group")->where("Main_ID", $cek->Task_ID)->first();

		if (!$cekGroup) {
			DB::insert(DB::raw("INSERT INTO payrol_main_group (Main_ID, Group_Name, Reference, Category, Use_For, Department, COA_Type, Created_By, Created_Date) SELECT Task_ID, Project_Name, '$pmd->PO_No', 'SALARY', '$tipe', 'BUS', '$COATIPE', '$Saya', NOW() FROM project_mgt where Task_ID='$pm->Task_ID' "));
		}
	}
	return "update";
}
function deletekan($rowId, $param)
{
	$Saya = Auth::user()->username;

	DB::update(DB::raw("UPDATE project_detail_requisition SET Approved=3,Approved_Date=NOW(),Approved_By='$Saya' where ID=$rowId"));
	return "delete";
}
function insertkan($rowId, $param)
{
	$a = $param[$rowId . "_c1"]; // origin
	$b = $param[$rowId . "_c2"]; // destination
	$c = $param[$rowId . "_c3"]; // destination name
	$d = $param[$rowId . "_c4"]; // transport
	$e = $param[$rowId . "_c5"]; // service
	$f = $param[$rowId . "_c6"]; // treshold
	$g = $param[$rowId . "_c7"]; // unit price
	$h = $param[$rowId . "_c8"]; // additional
	$ix = $param[$rowId . "_c9"]; // lead time
	$j = $param[$rowId . "_c10"]; // lead time
	$k = $param[$rowId . "_c11"]; // lead time
	$l = $param[$rowId . "_c12"]; // lead time
	$m = $param[$rowId . "_c13"]; // lead time
	$n = $param[$rowId . "_c14"]; // lead time
	$o = $param[$rowId . "_c15"]; // lead time
	$p = $param[$rowId . "_c16"]; // lead time



	return "insert";
}



echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));

$ids = explode("&", $request_body);
foreach ($ids as $datax) {

	$idenya = explode("=", $datax);
	$param[$idenya[0]] = $idenya[1];
}
$checkids = explode(",", $param["ids"]);
$Task_ID = $_GET['Task_ID'];

for ($i = 0; $i < sizeof($checkids); $i++) {
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId . "_!nativeeditor_status"];
	$d = $param[$rowId . "_c4"]; // transport
	$a = $param[$rowId . "_c1"]; // origin
	$ax = $param[$rowId . "_c0"];
	if ($ax == 1 || $ax == 'On') {

		switch ($mode) {
			case "updated":
				$action = updatekan($rowId, $param);
				break;
			case "deleted":
				$action = deletekan($rowId, $param);
				break;
			case "inserted":
				$action = insertkan($rowId, $param);
				break;
		}
	}

	echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
}
echo "</data>";
