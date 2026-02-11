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
$Saya = Auth::user()->username;
$nomor = 1;

//$Fetchx = DB::select(DB::raw("SELECT * FROM reimbursement_dtl WHERE Reimbursement_No  LIKE  '" . $_GET['ID'] . "' ORDER BY ID ASC"));

$Fetchx = DB::table("reimbursement_dtl")->where("Reimbursement_No", $_GET['ID'])->orderBy("ID", "ASC")->get();

echo "<rows>";

foreach ($Fetchx as $Fetch) {
	// $str = strtotime($Fetch->Reimbursement_Date);
	// $nw = strtotime(date('c'));
	// $sisa = $nw - $str;
	// $days = round($sisa / 86400);

	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Reimbursement_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Reference) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->External_Reference) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Remark) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Amount) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Currency_Val) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";

	echo "</row>";

	$nomor++;
}


echo "</rows>";
