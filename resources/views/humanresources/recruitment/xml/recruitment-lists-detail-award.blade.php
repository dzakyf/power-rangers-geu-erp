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



$Query1 = DB::table("recruitment_akademic")->get();

foreach($Query1 as $Fetch1){
	$akademis[$Fetch1->Task_ID] = $Fetch1->Education_Major;
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("recruitment_awards")->where("Task_ID",$_GET['ID'])->get();

foreach($Query as $Fetch){
	
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->MonthYear)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Place)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Detail)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Title)."</cell>";
	echo "</row>";
$nomor++;
}

echo "</rows>";
?>
