<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$src = $_POST['data'];
$LSP = Auth::user()->Location;
$saya =  Auth::user()->username;
$Nama =  Auth::user()->Name;

function CekVendorWin($ven, $taskid)
{
	$vendor = "V" . $ven . "_Win";
	$TaskID = $_POST['TaskID'];
	$cekV  = DB::table('purchase_summary_detail')->selectRaw("count(ID) as jumlah")
		->where('PR_Related', '=', "$taskid")
		->where('Task_ID', '=', "$TaskID")
		->where("$vendor", 1)->get();
	foreach ($cekV as $key) {
		$jum = $key->jumlah;
	}
	$rowV = $jum;
	if ($rowV >= 1) {
		$jumlah = 1;
	} else {
		$jumlah = 0;
	}
	return $jumlah;
}


$Note = $_POST['Note'];
$PO_No = $_POST['PO_No'];
$TaskID = $_POST['TaskID'];

$taskid = $_POST['ID'];
if ($_POST['kurs1'] <> '')	$kurs1 = $_POST['kurs1'];
else $kurs1 = "Rp";
if ($_POST['kurs2'] <> '')	$kurs2 = $_POST['kurs2'];
else $kurs2 = "Rp";
if ($_POST['kurs3'] <> '')	$kurs3 = $_POST['kurs3'];
else $kurs3 = "Rp";
if ($_POST['kurs4'] <> '')	$kurs4 = $_POST['kurs4'];
else $kurs4 = "Rp";
if ($_POST['kurs5'] <> '')	$kurs5 = $_POST['kurs5'];
else $kurs5 = "Rp";
$ars = "";
$ax = "";
$isi = "";
foreach ($_POST as $key => $value) {

	if ($key != 'TaskID' && $key != 'Note' && $key != 'undefined') {
		if ($key == 'kurs1') $key = "V1_Cur";
		if ($key == 'kurs2') $key = "V2_Cur";
		if ($key == 'kurs3') $key = "V3_Cur";
		if ($key == 'kurs4') $key = "V4_Cur";
		if ($key == 'kurs5') $key = "V5_Cur";
		if ($value == 'on') $value = '1';
		if ($value == 'off') $value = '0';
		$ars .= "$key = '$value',";
		$ax .= "$key,";
		$isi .= "'$value',";
	}
}

$notes = date("Y-m-d H:i:s") . " : User  $saya => Input Price Vendor # $Note";

DB::update(DB::raw(str_replace("NaN", "0", "UPDATE purchase_summary SET $ars  Status='PROPOSED',Note = CONCAT('$notes <br>',Note) WHERE Task_ID='$TaskID'")));
echo "success";
