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
	$Fetchx = DB::select(DB::raw("SELECT * FROM opportunity_detail where PO_No!='' ORDER BY ID DESC limit 4000"));

echo "<rows>";

foreach ($Fetchx as $Fetch) {

	$nilai = $kursval[$Fetch->Currency];
	if ($nilai == '') $nilai = 1;
	echo "<row id=\"$Fetch->ID\">";

	echo "<cell $bg_color>$nomor</cell>";
	echo "<cell $bg_color>" . htmlspecialchars($Fetch->PO_Initial) . "</cell>";
	echo "<cell $bg_color>" . htmlspecialchars($Fetch->Task_ID) . "</cell>";
	echo "<cell $bg_color>" . htmlspecialchars($Fetch->PO_No) . "</cell>";
	echo "<cell $bg_color>" . htmlspecialchars($Fetch->Subject) . "</cell>";
	echo "<cell $bg_color>" . htmlspecialchars($Fetch->PO_Date) . "</cell>";
	echo "<cell $bg_color>" . htmlspecialchars($Fetch->Reference) . "</cell>";
	echo "<cell $bg_color>" . htmlspecialchars($Fetch->Description) . "</cell>";
	echo "<cell $bg_color>" . htmlspecialchars($Fetch->Total) . "</cell>";
	echo "</row>";

	$nomor++;
}


echo "</rows>";
