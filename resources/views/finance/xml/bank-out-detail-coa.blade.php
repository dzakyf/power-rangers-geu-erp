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


$nomor = 1;
$Query = DB::table("branch_coa")->where("Grouping_ID", "!=", "")->where("Listed_OnSearch", 1)->orderBy("Acc_No", 'asc')->get();
echo "<rows>";

foreach ($Query as $Fetch) {
	$keterangan = DB::table("branch_coa")->where("Acc_No", "=", $Fetch->Grouping_ID)->first();
	echo "<row id=\"$Fetch->Acc_No\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Acc_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Description) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Grouping_ID) . "-" . htmlspecialchars($keterangan->Description) . "</cell>";
	echo "</row>";

	$nomor++;
}




echo "</rows>";
