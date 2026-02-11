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

$Fetchx = DB::select(DB::raw("SELECT * FROM contract ORDER BY ID DESC"));

echo "<rows>";

foreach($Fetchx as $dapat){

		echo "<row id=\"$dapat->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Contract_Type))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Contract_No))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Reference_No))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Start_Date))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->End_Date))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Supplier_Name))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Note))."</cell>";
			echo "</row>";	

$nomor++;
}


echo "</rows>";
?>
