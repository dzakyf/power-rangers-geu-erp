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
$tanggal1 = $_GET['Tgl1'];
$tanggal2 = $_GET['Tgl2'];



// $Fetchx = DB::table("invoice")->where("Status1","'APPROVED'")->whereBetween("Inv_Date", ["'$tanggal1'","'$tanggal2'"])->orderBy("ID", "DESC")->get();

$Fetchx = DB::select(DB::raw("select * from `account_receive` where Location='$LSP' AND Delivered=1 AND Status IN('RECEIVED','PARTIAL') order by `ID` desc"));

echo "<rows>";

foreach ($Fetchx as $Fetch) {

	
	
	echo "<row id=\"$Fetch->Inv_No_KSPS\">";
	echo "<cell>" . htmlspecialchars($nomor) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Delivery_Note) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_No_KSPS) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Ref_Doc) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->AR_Val) . "</cell>";
	echo "</row>";
	$nomor++;
}


echo "</rows>";
