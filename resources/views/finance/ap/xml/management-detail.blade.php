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


// $Query = DB::table("account_payable")->where("Vendor_ID",$_GET['CID'])->where("Location",$LSP)->where("Status","=","RECEIVED")->orWhere("Status","=","PARTIAL")->orderBy("ID","DESC")->get();

$Query = DB::table("account_payable")->where("Vendor_ID", $_GET['CID'])->where("Location", $LSP)->whereIN("Status", ["RECEIVED", "PARTIAL"])->orderBy("ID", "DESC")->get();

foreach ($Query as $Fetch) {
	$customer = DB::table("supplier")->where("Supplier_ID", $Fetch->Vendor_ID)->first();
	$invd = strtotime($Fetch->Due_Date);
	$hariini = strtotime(date('y-m-d'));
	$tempo = $hariini - $invd;
	$sisahari = $tempo / 86400;

	$jenis = $proforma->Proforma_Type;
	$acc = "211.1";
	$accname = "Hutang Usaha IDR ";



	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_Vendor) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_Vendor_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Reference) . "</cell>";
	echo "<cell>$sisahari</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Remain_Val) . "</cell>";
	echo "<cell>$acc</cell>";
	echo "<cell>$accname</cell>";
	echo "<cell>$Fetch->Subject_Payment</cell>";
	echo "</row>";
	$nomor++;
}

echo "</rows>";
