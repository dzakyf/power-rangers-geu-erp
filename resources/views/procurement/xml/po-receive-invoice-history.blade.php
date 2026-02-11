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
$supp = DB::table("supplier")->get();
foreach ($supp as $spp) {

	$getSupplier[$spp->Supplier_ID]['name'] = $spp->Company_Name;
}

echo "<rows>";


$textqry = "SELECT * FROM account_payable WHERE Location='$LSP' order by ID DESC";
$qry = DB::select(DB::raw($textqry));
foreach ($qry as $getData) {
	$now = strtotime(date('c'));
	$ags = strtotime($getData->Dateinsert);
	$rentang = $now - $ags;
	$aging = round($rentang / 86400);
	$exe = DB::table("purchase_order")->where("PO_No", $getData->Reference)->first();
	$DataSup = $getSupplier[$getData->Vendor_ID]['name'];

	if ($getData->Status == 'PURCH_APP') {
		$warnanya = " bgColor='#CC0000' style='font-style:italic;color:#ffffff;font-weight:bold;'";
		$stats = "NEEDS APPROVAL";
	} else {
		$warnanya = "";
		$stats = "CREATED";
	}

	echo "<row id=\"" . $getData->ID . "\">";
	echo "<cell $warnanya>$nomor</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($getData->Reference) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($DataSup) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($getData->Department) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($getData->Purchase_Category) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($getData->Total) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($aging) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars(date("d/m/Y H:i", strtotime($getData->Dateinsert))) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars(date("d/m/Y", strtotime($getData->Due_Date))) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($getData->Status) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($appr) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($getData->Date_Received) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($getData->Inv_Vendor) . "</cell>";
	echo "<cell $warnanya>" . htmlspecialchars($getData->Inv_Vendor_Date) . "</cell>";
	echo "</row>";
	$nomor++;
}

echo "</rows>";
