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
	$q = $param[$rowId . "_c17"];
	$r = $param[$rowId . "_c18"];
	$s = $param[$rowId . "_c19"];
	$t = $param[$rowId . "_c20"];
	$u = $param[$rowId . "_c21"];
	$v = $param[$rowId . "_c22"];
	$w = $param[$rowId . "_c23"];
	$x = $param[$rowId . "_c24"];
	$y = $param[$rowId . "_c25"];
	$z = $param[$rowId . "_c26"];
	$aa = $param[$rowId . "_c27"];
	$ab = $param[$rowId . "_c28"];
	$ac = $param[$rowId . "_c29"];
	$ad = $param[$rowId . "_c30"];
	$ae = $param[$rowId . "_c31"];
	$af = $param[$rowId . "_c32"];
	$ag = $param[$rowId . "_c33"];
	$ah = $param[$rowId . "_c34"];

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	$cekcoa = DB::table("branch_coa")->where("Acc_No", $a)->first();

	DB::update(DB::raw("UPDATE proyeksi_cashflow_detail set  COA='$a',Description='$cekcoa->Description',  `Day_1`='$d', `Day_2`='$e', `Day_3`='$f', `Day_4`='$g', `Day_5`='$h', `Day_6`='$ix', `Day_7`='$j', `Day_8`='$k', `Day_9`='$l', `Day_10`='$m', `Day_11`='$n', `Day_12`='$o', `Day_13`='$p', `Day_14`='$q', `Day_15`='$r', `Day_16`='$s', `Day_17`='$t', `Day_18`='$u', `Day_19`='$v', `Day_20`='$w', `Day_21`='$x', `Day_22`='$y', `Day_23`='$z', `Day_24`='$aa', `Day_25`='$ab', `Day_26`='$ac', `Day_27`='$ad', `Day_28`='$ae', `Day_29`='$af', `Day_30`='$ag', `Day_31`='$ah', `YM` = '$c' where ID=$rowId"));





	return "update";
}
function deletekan($rowId, $param)
{
	DB::delete(DB::raw("DELETE FROM proyeksi_cashflow_detail where ID=$rowId"));
}
function insertkan($rowId, $param)
{
	$a = $param[$rowId . "_c1"]; // origin
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
	$q = $param[$rowId . "_c17"];
	$r = $param[$rowId . "_c18"];
	$s = $param[$rowId . "_c19"];
	$t = $param[$rowId . "_c20"];
	$u = $param[$rowId . "_c21"];
	$v = $param[$rowId . "_c22"];
	$w = $param[$rowId . "_c23"];
	$x = $param[$rowId . "_c24"];
	$y = $param[$rowId . "_c25"];
	$z = $param[$rowId . "_c26"];
	$aa = $param[$rowId . "_c27"];
	$ab = $param[$rowId . "_c28"];
	$ac = $param[$rowId . "_c29"];
	$ad = $param[$rowId . "_c30"];
	$ae = $param[$rowId . "_c31"];
	$af = $param[$rowId . "_c32"];
	$ag = $param[$rowId . "_c33"];
	$ah = $param[$rowId . "_c34"];

	$Saya = Auth::user()->username;
	$Task_ID = $_GET['ID'];
	$cekcoa = DB::table("branch_coa")->where("Acc_No", $a)->first();

	DB::insert(DB::raw("INSERT INTO `proyeksi_cashflow_detail`(`Task_ID`, `COA`, `Description`, `YM`, `Day_1`, `Day_2`, `Day_3`, `Day_4`, `Day_5`, `Day_6`, `Day_7`, `Day_8`, `Day_9`, `Day_10`, `Day_11`, `Day_12`, `Day_13`, `Day_14`, `Day_15`, `Day_16`, `Day_17`, `Day_18`, `Day_19`, `Day_20`, `Day_21`, `Day_22`, `Day_23`, `Day_24`, `Day_25`, `Day_26`, `Day_27`, `Day_28`, `Day_29`, `Day_30`, `Day_31`, `Created_Date`, `Created_By`) VALUES ('$Task_ID', '$a','$cekcoa->Description','$c',$d,$e,$f,$g,$h,$ix, $j, $k, $l, $m, $n, $o, $p, $q, $r, $s,$t,$u,$v,$w,$x,$y,$z,$aa,$ab,$ac,$ad,$ae,$af,$ag,$ah,NOW(),'$Saya' )"));




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
$total = 0;
$arr = [];
for ($i = 0; $i < sizeof($checkids); $i++) {
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId . "_!nativeeditor_status"];
	$total = $param[$rowId . "_c35"]; // lead time
	$bulan = $param[$rowId . "_c3"]; // destination name
	$kunci = date("F", strtotime($bulan)) . "_Value";
	if (isset($arr[$kunci])) {
		$arr[$kunci] = $arr[$kunci] + $total;
	} else {
		$arr[$kunci] = $total;
	}
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

	echo $bulan . " = " . $arr[$kunci] . "<br>";
}
echo "</data>";
$data = "";
foreach ($arr as $key => $value) {
	$data .= $key . "=" . $value . ",";
}
echo "<br>" . $data;
DB::update(DB::raw("UPDATE proyeksi_cashflow  set " . $data . "Created_Date=NOW() where ID=$_GET[ID]"));
