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

$Query = DB::table("recruitment_history")->where("NIK",$_GET['ID'])->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
	
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Date_Process)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Activity)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reference)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Notes)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Start_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->End_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Remark)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->User_Trx)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Date_Trx)."</cell>";
	echo "</row>";
$nomor++;
}

echo "</rows>";
?>
