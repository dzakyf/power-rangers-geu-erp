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
if ($priv == "MARSHAL" || $priv == "ADM") {
	$Query = DB::table("proforma")->where("Location", $LSP)->where("Status", "=", "PROPOSED")->orderBy("Last_Update", "ASC")->get();
} else {
	$Query = DB::table("proforma")->where("Location", $LSP)->where("Status", "=", "PROPOSED")->where("Created_NIK", "=", $saya)->orderBy("Last_Update", "ASC")->get();
}


foreach ($Query as $Fetch) {
	$proforma_detail = DB::table("proforma_detail")->where("Proforma_No", $Fetch->Proforma_No)->first();

	if ($Fetch->Last_Update == null) {
		$date = $Fetch->Dateinsert;
	} else {
		$date = $Fetch->Last_Update;
	}

	echo "<row id=\"$Fetch->Proforma_No\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->BA_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->BA_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Subject) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Periode) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Signed_By) . "</cell>";
	echo "<cell>" . htmlspecialchars($proforma_detail->Department) . "</cell>";
	echo "<cell>" . htmlspecialchars($date) . "</cell>";


	echo "</row>";

	$nomor++;
}

echo "</rows>";
