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

$Query = DB::table("account_receive")->where("Location", $LSP)->where("Delivered", "=", NULL)->where("Status", "=", "RECEIVED")->orderBy("ID", "DESC")->get();

foreach ($Query as $Fetch) {
	$customer = DB::table("customerz")->where("Customer_No", $Fetch->Cust_ID)->first();
	$inv = DB::table("invoice")->where("Inv_No", "=", $Fetch->Inv_No)->first();

	$invd = strtotime($Fetch->Inv_Date);
	$hari = $customer->Due_Days * 86400;
	$tempo = date("d M Y", $invd + $hari);
	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_No_KSPS) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Ref_Doc) . "</cell>";
	echo "<cell>" . htmlspecialchars($inv->Invoice_Type) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->AR_Val) . "</cell>";
	echo "<cell>" . htmlspecialchars($tempo) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Delivery_Date) . "</cell>";
	echo "<cell>0</cell>";
	echo "<cell></cell>";
	echo "<cell></cell>";
	echo "<cell></cell>";
	echo "<cell></cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Cust_ID) . "</cell>";
	echo "<cell>" . htmlspecialchars($customer->Company_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->User_Trx) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Date_Trx) . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
