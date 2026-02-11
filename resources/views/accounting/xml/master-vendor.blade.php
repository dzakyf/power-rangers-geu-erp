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

$Fetchx = DB::select(DB::raw("SELECT * FROM supplier ORDER BY Company_Name"));

echo "<rows>";

foreach($Fetchx as $dapat){

		echo "<row id=\"$dapat->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars(($dapat->AP_Salary_Group))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Company_Name))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Contact_Person))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->City))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Phone))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Fax))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Email))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Sales_Name))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Sales_No))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Payment))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Supplier_Category))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Supplier_Sub_Category))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Supplier_Group_Category))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->NPWP))."</cell>";
			echo "</row>";	

$nomor++;
}


echo "</rows>";
?>
