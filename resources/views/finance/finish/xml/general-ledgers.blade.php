<?php
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


//error_reporting(E_ALL ^ E_NOTICE);

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
	header("Content-type: application/xhtml+xml");
} else {
	header("Content-type: text/xml");
}
echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");
function Bersih($Data)
{
	$X2 = trim($Data);

	$X2 = preg_replace('/[^(\x20-\x7F)]*/', '', $X2);
	$X2 = htmlspecialchars($X2);
	return $X2;
}
function Filter($C)
{
	$Datahtmlspecialchars = preg_replace('/[^(\x20-\x7F)]*/', '', $C);
	return Bersih($Datahtmlspecialchars);
}

$Kota = $_GET["City"];
$Tgl1 = $_GET["Tgl1"];
$Tgl2 = $_GET["Tgl2"];
$Acc = '111.2';
if ($Acc) {
	$wacc = "AND Account_No LIKE '111.2%' OR Account_No LIKE  '111.3%'";
} else {
	$wacc = "";
}
$LSP = $Kota;

$nomor = 1;
echo "<rows>";
$namadatabase = "ho";
$namadatabase_sayear = $namadatabase . "_sa_year";

$QAgent = DB::select(DB::raw("SELECT * FROM $namadatabase WHERE Posting_Date BETWEEN '$Tgl1' AND '$Tgl2' $wacc ORDER BY Posting_Date ASC,ID ASC,Journal_No"));

foreach ($QAgent as $FGL) {


	$row = DB::table("branch_coa")->where("Acc_No", "$FGL->Account_No")->first();


	if ($row->Acc_No == '' || $row->Acc_No == null) {
		$warna = "bgColor='red'";
		$GLInfo = "INVALID";
	} else {
		if ($row->Listed_OnSearch == 0) {
			$warna = "bgColor='yellow'";
			$GLInfo = "HEADER/GROUP";
		} else {
			$warna = "";
			$GLInfo = "NORMAL";
		}
	}

	echo "<row id=\"$FGL->ID\" $warna>";
	echo "<cell $warna>$nomor</cell>"; #a
	echo "<cell $warna>" . Filter($FGL->Posting_Date) . "</cell>"; #b
	echo "<cell $warna>" . Filter($FGL->Document_No) . "</cell>"; #c
	echo "<cell $warna>" . Filter($FGL->External_Doc) . "</cell>"; #d
	echo "<cell $warna>" . Filter($FGL->Journal_No) . "</cell>"; #d
	echo "<cell $warna>$FGL->Account_No</cell>"; #f
	echo "<cell $warna>" . Filter($row->Description) . "</cell>"; #g
	echo "<cell $warna>" . Filter($FGL->Remark) . "</cell>"; #h
	echo "<cell $warna>" . Filter($FGL->Debet + 0) . "</cell>"; #i
	echo "<cell $warna>" . Filter($FGL->Credit + 0) . "</cell>"; #j
	echo "<cell $warna>$FGL->Reference_No</cell>";
	echo "<cell $warna>" . Filter($FGL->Reference_Desc) . "</cell>";
	echo "<cell $warna>$FGL->User_Resp</cell>";
	echo "<cell $warna>$FGL->Department</cell>";
	echo "<cell $warna>" . Filter($FGL->Partner_Name) . "</cell>";
	echo "<cell $warna>" . Filter($FGL->Partner_Code) . "</cell>";
	echo "<cell $warna>$GLInfo</cell>";
	echo "</row>";

	$nomor++;
}




echo "</rows>";
