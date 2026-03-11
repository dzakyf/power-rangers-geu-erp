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



$LSP = Auth::user()->Location ?? 'GEU-HO';
$DOP = Auth::user()->DOP ?? '';
$priv = Auth::user()->Priv_ID ?? '';
$saya = Auth::user()->username ?? 'admin';
$nomor = 1;

echo "<rows>";

$Query = DB::table("opportunity_sales_dtl")->where('Sales_ID', $_GET['ID'])->orderBy("ID", "DESC")->get();

foreach ($Query as $Fetch) {
	$Fetch = DB::table("opportunity")->where("Task_ID", $Fetch->Task_ID)->first();
    if ($Fetch->Capital_Estimation == 1) {
        $estimation = "<50 Juta";
    } elseif ($Fetch->Capital_Estimation == 2) {
        $estimation = "100 Juta - 200 Juta";
    } elseif ($Fetch->Capital_Estimation == 3) {
        $estimation = "200 Juta - 300 Juta";
    } elseif ($Fetch->Capital_Estimation == 4) {
        $estimation = "300 Juta";
    } else {
        $estimation = "50 Juta - 100 Juta";
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
	echo "<cell>" . htmlspecialchars($Fetch->Created_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Source_Channel) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Response) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Qualification) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Classification) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Cancel_Reason) . "</cell>";
	if ($Fetch->Status == 'Deal') {
		echo "<cell>" . htmlspecialchars('Blast') . "</cell>";
	}else {
		echo "<cell>" . htmlspecialchars('') . "</cell>";
	}
	echo "</row>";

	$nomor++;
}

echo "</rows>";
