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

// $Query = DB::table("account_payable")->where("Location",$LSP)->where("Status","=","RECEIVED")->orWhere("Status","=","PARTIAL")->orderBy("ID","DESC")->get();
$Query = DB::select(DB::raw("SELECT Vendor_ID,COUNT(ID) as OPENINV,SUM(Total) as total,SUM(Paid) as bayar,SUM(Remain_Val) as sisa FROM account_payable WHERE Location='$LSP' AND Status IN('RECEIVED','PARTIAL') 
AND Remain_Val != '0' 
AND Remain_Val != '28' 
AND Remain_Val != '9' 
AND Inv_Vendor != '2494/KOPLA/INV/IX/2021'
AND Inv_Vendor != '2471/KOPLA/INV/IX/2021'
AND Inv_Vendor != '2233/KOPLA/INV/VIII/2021'
AND Inv_Vendor != '2232/KOPLA/INV/VIII/2021'
AND Inv_Vendor != '2237/KOPLA/INV/VIII/2021'
AND Inv_Vendor != '1824/KOPLA/INV/VII/2021'
AND Inv_Vendor != '1825/KOPLA/INV/VII/2021'
AND Inv_Vendor != '1992/KOPLA/INV/VIII/2021'
AND Inv_Vendor != '1770/KOPLA/INV/VII/2021'
AND Inv_Vendor != '1617/KOPLA/INV/VI/2021'
AND Inv_Vendor != '1484/KOPLA/INV/VI/2021'
AND Inv_Vendor != '1485/KOPLA/INV/VI/2021'
AND Inv_Vendor != '1250/KOPLA/INV/V/2021'
AND Inv_Vendor != '0449/KOPLA/INV/III/2021'
AND Inv_Vendor != '0448/KOPLA/INV/III/2021'
AND Inv_Vendor != '0712/KOPLA/INV/IV/2021'
GROUP BY Vendor_ID"));


foreach ($Query as $Fetch) {
	$customer = DB::table("supplier")->where("Supplier_ID", $Fetch->Vendor_ID)->first();

	echo "<row id=\"$nomor\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Vendor_ID) . "</cell>";
	echo "<cell>" . htmlspecialchars($customer->Company_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->OPENINV) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->bayar) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->sisa) . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
