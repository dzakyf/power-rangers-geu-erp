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


$KURS = DB::table("currency")->get();
foreach($KURS as $curs){
	$kursval[$curs->Currency_Symbol] = $curs->Currency_Value;
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$Saya = Auth::user()->username;
$nomor = 1;
$xd = DB::table("recurring_master")->where("ID",$_GET['ID'])->first();
$cekx = DB::table("recurring_detail")->where("Journal_No",$_GET['ID'])->orderBy("ID","DESC")->get();

echo "<rows>";

foreach($cekx as $Fetch){

	echo "<row id=\"$Fetch->ID\">";

		echo "<cell >$nomor</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Account_No)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Posting_Date)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Document_No)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Remark)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Debet)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Credit)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Department)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
