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
$id2 = DB::table("cash")->where("ID", "=", $_GET['ID'])->first();
$Query = DB::table("cash_dtl")->where("Cash_No", $_GET['ID'])->orWhere("Cash_No", $id2->Cash_No)->get();
echo "<rows>";

foreach ($Query as $Fetch) {

	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Acc_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Date_Cash) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Reference) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Remark) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Cash_Val) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Bal_Acc) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
	echo "</row>";

	$nomor++;
}


echo "</rows>";
