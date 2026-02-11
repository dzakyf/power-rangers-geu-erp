<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");



$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";


$Query = DB::table("account_receive_log")->where("Cust_ID",$_GET['CID'])->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
	

	$customer = DB::table("customerz")->where("Customer_No",$_GET['CID'])->first();
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($_GET['CID'])."</cell>";
		echo "<cell>".htmlspecialchars($customer->Company_Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Processed_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PTP)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Action)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Action_Result)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Result_Output)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PIC)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PIC_No)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->AR_Val_Before)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Payment_Value)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Note)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Invoice_No)."</cell>";
	echo "</row>";
$nomor++;
}

echo "</rows>";
?>
