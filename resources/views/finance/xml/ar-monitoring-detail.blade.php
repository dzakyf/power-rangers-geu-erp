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


// $Query = DB::table("account_receive")->where("Cust_ID", $_GET['CID'])->where("Location", $LSP)->where("Delivered", "=", 1)->where("Status", "!=", "CLOSED")->orderBy("ID", "DESC")->get();

$Query = DB::table("account_receive")->where("Cust_ID", $_GET['CID'])->where("Location", $LSP)->where("Delivered", "=", 1)->whereNotIn('Status', ['CLOSED', 'CANCELED', 'RETURN'])->orderBy("ID", "DESC")->get();

foreach ($Query as $Fetch) {
	$inv = DB::table("invoice")->where("Inv_No", "=", $Fetch->Inv_No)->first();
	$customer = DB::table("customerz")->where("Customer_No", $Fetch->Cust_ID)->first();
	$invd = strtotime($Fetch->Date_Receive);
	$hariini = strtotime(date('y-m-d'));
	$tempo = $hariini - $invd;
	$sisahari = $tempo / 86400;
	if ($inv->Invoice_Category == 'MANAGEMENT_FEE') {
		$proforma = DB::table("proforma")->where("Management_Fee", $inv->Inv_No)->first();
	} else {
		$proforma = DB::table("proforma")->where("Invoice_No", $inv->Inv_No)->first();
	}
	$jenis = $proforma->Proforma_Type;
	$acc = "112.1." . $customer->Acc_No_MF;
	$accname = "Piutang Usaha IDR " . $getcust->Company_Name;



	$customer = DB::table("customerz")->where("Customer_No", $Fetch->Cust_ID)->first();
	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_No_KSPS) . "(" . htmlspecialchars($inv->Invoice_Type) . ")</cell>";
	echo "<cell>$sisahari</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->AR_Val) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->AR_Remain) . "</cell>";
	echo "<cell>$acc</cell>";
	echo "<cell>$accname</cell>";
	echo "</row>";
	$nomor++;
}

echo "</rows>";
