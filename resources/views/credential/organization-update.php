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


	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;

	DB::update(DB::raw("UPDATE organization_structure set `NIK`='$a',`Name`='$b',`Level1`='$c',`Level2`='$d',`Level3`='$e',`Level4`='$f',`Level5`='$g',`Level6`='$h',`Level7`='$ix',`Department`='$j',`Dept_Code`='$k',`Email`='$l',`Alerted`='$m',`divisi`='$n'  where ID=$rowId"));

	return "update";
}
function deletekan($rowId, $param)
{
	$a = $param[$rowId . "_c1"]; // origin

	DB::delete(DB::raw("DELETE FROM organization_structure where ID=$rowId"));
	return "delete";
}
function insertkan($rowId, $param)
{
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
	DB::insert(DB::raw("INSERT INTO `organization_structure`(`NIK`, `Name`, `Level1`, `Level2`, `Level3`, `Level4`, `Level5`, `Level6`, `Level7`, `Department`, `Dept_Code`, `Email`, `Alerted`, `divisi`) VALUES ('$a','$b','$c','$d','$e','$f','$g','$h','$ix','$j','$k','$l','$m','$n')"));

	return "insert";
}



echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

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
	$nomorx = $param[$rowId . "_c0"];

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

	echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
}
echo "</data>";
