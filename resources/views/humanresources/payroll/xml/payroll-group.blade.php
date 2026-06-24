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
$grup = [];
$qx = DB::table("employment")->get();
foreach($qx as $dx){
	if (!isset($grup[$dx->Salary_Group])) {
		$grup[$dx->Salary_Group] = 0;
	}
	$grup[$dx->Salary_Group]++;
}
$Query = DB::table("payrol_main_group")->orderBy("Main_ID","ASC")->get();

foreach($Query as $Fetch){
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Main_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Group_Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Category)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Use_For)."</cell>";
		echo "<cell>".htmlspecialchars($grup[$Fetch->Main_ID]+0)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
