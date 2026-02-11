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

$Query = DB::select(DB::raw("SELECT Cust_ID,COUNT(ID) as OPENINV,SUM(AR_Val) as total,SUM(AR_Paid) as bayar,SUM(AR_Remain) as sisa FROM account_receive WHERE Location='$LSP' AND Delivered=1  AND Status <> 'CANCELED' AND Inv_Date Between '$_GET[Tgl1]' AND '$_GET[Tgl2]' GROUP BY Cust_ID"));


foreach ($Query as $Fetch) {

	$customer = DB::table("customerz")->where("Customer_No", $Fetch->Cust_ID)->first();
	echo "<row id=\"$Fetch->Cust_ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Cust_ID) . "</cell>";
	echo "<cell>" . htmlspecialchars($customer->Company_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->OPENINV) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->bayar) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->sisa) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Received_By) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->User_Trx) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Location) . "</cell>";
	echo "</row>";
	$nomor++;
}

echo "</rows>";
