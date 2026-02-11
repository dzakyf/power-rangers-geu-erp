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

echo "<rows>";
$Query = DB::table("payroll_tax_reduction")->orderBy("Marital_ID","ASC")->get();

foreach($Query as $Fetch){
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Marital_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Description)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reduction_Amount)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Marital_Status)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Children_Count)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Modified_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Modified_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Last_Seen_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Last_Seen_Date)."</cell>";
		echo "</row>";

$nomor++;
}
DB::update(DB::raw("UPDATE payroll_tax_reduction set Last_Seen_By = '$Saya',Last_Seen_Date = NOW()"));

echo "</rows>";
?>
