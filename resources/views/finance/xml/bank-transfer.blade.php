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
$Saya = Auth::user()->username;
$nomor = 1;

$Fetchx = DB::select(DB::raw("SELECT * FROM bank_transfer where Status = 'CREATED'"));

echo "<rows>";

foreach($Fetchx as $Fetch){

	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->From_Bank)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->To_Bank)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Transfer_Value)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reference)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
