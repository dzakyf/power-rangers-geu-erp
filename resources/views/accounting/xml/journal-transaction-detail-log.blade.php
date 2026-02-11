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


echo "<rows>";
$LSP = Auth::user()->Location;
$saya = Auth::user()->username;
$nomor = 1;
$Query = DB::table("branch_journal_task_history")->where("Task_ID",$_GET["Task_ID"])->orderBy("ID","DESC")->get();	

foreach ($Query as $Fetch){

	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Responsible)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Description)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Actions)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
	echo "</row>";

$nomor++;
}




echo "</rows>";
?>
