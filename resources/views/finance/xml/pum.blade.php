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

$Query = DB::table("pum_karyawan")->orderBy("Name","ASC")->get();

foreach($Query as $Fetch){

	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Cust_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PIC)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Inv_No)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Location)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Inv_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Batasan)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
