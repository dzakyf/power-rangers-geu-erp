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


echo "<rows>";

$Query = DB::table("employment")->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>0</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Gender)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Last_Education)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Position)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Work_Area)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Employment_Status)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Phone)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
