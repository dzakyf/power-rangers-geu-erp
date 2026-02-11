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


$Query = DB::table("project_issues_log")->where("ID",$_GET['ID'])->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){

	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Action_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Action_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Action_Note)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Start_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->End_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Priority)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Issue_Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Category)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Description)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Risk_Information)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Loss_Revenue)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Solving_Method)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Assignment)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
