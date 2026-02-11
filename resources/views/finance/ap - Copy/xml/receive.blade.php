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

$Query = DB::table("account_payable")->where("Location",$LSP)->where("Status","=","PROPOSED")->orderBy("Due_Date","ASC")->get();

foreach($Query as $Fetch){
	$customer = DB::table("supplier")->where("Supplier_ID",$Fetch->Vendor_ID)->first();

			echo "<row id=\"$Fetch->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Inv_Vendor)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Inv_Vendor_Date)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Reference)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Total)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Due_Date)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Subject_Payment)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Receive)."</cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($customer->Company_Name)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Date_Received)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
			echo "</row>";

		$nomor++;
	
}

echo "</rows>";
?>
