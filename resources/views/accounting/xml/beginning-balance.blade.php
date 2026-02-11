<?php
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
	header("Content-type: application/xhtml+xml");
} else {
	header("Content-type: text/xml");
}
echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");



$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

$Kota = $_GET["City"];
$Tgl = $_GET["Tgl1"] - 1;
$Acc = $_GET['Acc'];

if ($Acc) {
	$wacc = "AND Acc_No='$Acc'";
} else {
	$wacc = "";
}

echo "<rows>";

$namadatabase = "ho";
$namadatabase_sayear = $namadatabase . "_sa_year";

$QAgent = DB::select(DB::raw("SELECT * FROM $namadatabase_sayear WHERE Journal_Year= '$Tgl' $wacc ORDER BY Acc_No"));
foreach ($QAgent as $FGL) {

	$row = DB::table("branch_coa")->where("Acc_No",$FGL->Acc_No)->first();

	if ($row->Acc_No != '') {
		if ($row->Grouping_ID == '') {
			$warna = "bgColor='yellow'";
			$GLInfo = "HEADER/GROUP";
		} else {
			$warna = "";
			$GLInfo = "NORMAL";
		}
		
	} else {
		$warna = "bgColor='red'";
		$GLInfo = "INVALID";
	}
	echo "<row id=\"$FGL->ID\" $warna>";
	echo "<cell $warna>$nomor</cell>"; #a
	echo "<cell $warna>" . htmlspecialchars($FGL->Acc_No) . "</cell>"; #b
	echo "<cell $warna>" . htmlspecialchars($FGL->Description) . "</cell>"; #c
	echo "<cell $warna>" . htmlspecialchars($FGL->Location) . "</cell>"; #d
	echo "<cell $warna>" . htmlspecialchars($FGL->Group_Bal) . "</cell>"; #d
	echo "<cell $warna>" . htmlspecialchars($FGL->Group_Report) . "</cell>"; #g
	echo "<cell $warna>" . htmlspecialchars($FGL->Journal_Value + 0) . "</cell>"; #h
	echo "<cell $warna>" . htmlspecialchars($FGL->Journal_Year) . "</cell>"; #g
	echo "<cell $warna>" . htmlspecialchars($FGL->Remark) . "</cell>"; #g
	echo "<cell $warna>" . htmlspecialchars($FGL->Created_Date) . "</cell>"; #g
	echo "<cell $warna>" . htmlspecialchars($FGL->Created_By) . "</cell>"; #g
	echo "</row>";
	$nomor++;
}




echo "</rows>";
