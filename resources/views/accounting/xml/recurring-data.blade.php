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

$cekx = DB::table("recurring_master")->where("Period_Times",">","Exec_Times")->where("Status","CREATED")->orderBy("ID","DESC")->get();

echo "<rows>";

foreach($cekx as $Fetch){
	if ($Fetch->Period_Times > $Fetch->Exec_Times){
		
	echo "<row id=\"$Fetch->ID\">";

		echo "<cell >$nomor</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Recurring_ID)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Recurring_Type)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Recurring_Description)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Start_Recurring_Date)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Period_Times)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Last_Recurring_Date)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Recurring_Date)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Periode)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Exec_Times)."</cell>";
		echo "<cell >".htmlspecialchars($Fetch->Last_Process)."</cell>";
	echo "</row>";

$nomor++;
	}
}

echo "</rows>";
?>
