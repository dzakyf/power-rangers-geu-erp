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

	$a = $param[$rowId . "_c1"]; // origin
	$b = $param[$rowId . "_c2"]; // destination
	$c = $param[$rowId . "_c3"]; // destination name
	$d = $param[$rowId . "_c4"]; // transport
	

	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
    if($d == 'UNSENT') {
        $sendDate = ",Send_Date=NOW()";
    } else {
        $sendDate = '';
    }
 
	DB::update(DB::raw("UPDATE mail_logs set Email_To='$a', Subject='$b', Status='$d' $sendDate where ID=$rowId"));

	return "update";
}
function deletekan($rowId, $param)
{
	$a = $param[$rowId . "_c1"]; // origin

	DB::delete(DB::raw("DELETE FROM mail_logs where ID=$rowId"));
	return "delete";
}
function insertkan($rowId, $param)
{
	

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
