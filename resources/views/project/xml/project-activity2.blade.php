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

$project = DB::table("project_mgt")->where("Task_ID",$_GET['Task_ID'])->first();
$Query = DB::table("project_activity")->where("Task_ID",$_GET['Task_ID'])->orderBy("ID","DESC")->get();


foreach($Query as $Fetch){
		
		echo "<row id=\"$Fetch->ID\">";
			echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reference)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Activity_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Activity_Type)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Priority)."</cell>";
		echo "<cell>".htmlspecialchars(($Fetch->Activity_Description))."</cell>";
		echo "<cell>".htmlspecialchars($project->Customer_Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Description)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Note)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
