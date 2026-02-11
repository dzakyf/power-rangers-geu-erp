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
	$Query = DB::table("proforma")->where("Location", $LSP)->where("Status", "=", "CREATED")->orderBy("ID", "DESC")->get();
} else {
	$Query = DB::table("proforma")->where("Location", $LSP)->where("Status", "=", "CREATED")->where("Created_NIK", "=", $saya)->orderBy("ID", "DESC")->get();
}




foreach ($Query as $Fetch) {
	if ($BA_Date != '') {
		$hari = strtotime($skrg) - strtotime($Fetch->BA_Date);
		$hari = round($hari / 86400, 2);
	} else {
		$hari = 0;
	}

	$pox = DB::table("proforma_detail")->where("Proforma_No", $Fetch->Proforma_No)->first();
	$Dx = DB::table("opportunity_detail")->where("Task_ID", $pox->Task_ID)->first();
	$subjek = $Dx->Subject;
	$Query2 = DB::table("proforma_detail")->where("Proforma_No", $Fetch->Proforma_No)->first();


	echo "<row id=\"$Fetch->Proforma_No\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Proforma_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Dateinsert) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->BA_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->BA_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($subjek) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Subject) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Periode) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Subtotal_PO) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Man_Fee) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Proforma_Type) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Signed_By) . "</cell>";
	echo "<cell>" . htmlspecialchars($Query2->PO_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Account_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Dateinsert) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Created_NIK) . "</cell>";



	echo "</row>";

	$nomor++;
}

echo "</rows>";
