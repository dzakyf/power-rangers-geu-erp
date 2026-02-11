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

echo "<rows>";

$Query = DB::table("opportunity")->where("Location", $LSP)->where("Status", 'QUALIFIED')->orderBy("ID", "DESC")->get();

foreach ($Query as $Fetch) {
    if ($Fetch->Capital_Estimation == 1) {
        $estimation = "<50 Million";
    } elseif ($Fetch->Capital_Estimation == 2) {
        $estimation = "100 Million - 200 Million";
    } elseif ($Fetch->Capital_Estimation == 3) {
        $estimation = "200 Million - 300 Million";
    } elseif ($Fetch->Capital_Estimation == 4) {
        $estimation = "300 Million";
    } else {
        $estimation = "50 Million - 100 Million";
    }

	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Task_ID) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Created_By) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Full_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Phone_Number) . "</cell>";
	echo "<cell>" . htmlspecialchars("Opsi " . $Fetch->Partnership_Interest) . "</cell>";
	echo "<cell>" . htmlspecialchars($estimation) . "</cell>";
	echo "<cell ". ($Fetch->FU_1 == 1 ? "disabled='true'" : "") .">" . htmlspecialchars($Fetch->FU_1) . "</cell>";
	echo "<cell ". ($Fetch->FU_2 == 1 ? "disabled='true'" : "") .">" . htmlspecialchars($Fetch->FU_2) . "</cell>";
	echo "<cell ". ($Fetch->FU_3 == 1 ? "disabled='true'" : "") .">" . htmlspecialchars($Fetch->FU_3) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Created_Date) . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
