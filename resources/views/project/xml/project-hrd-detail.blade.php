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

$Query = DB::table("project_detail_requisition")->where("Task_ID",$_GET['Task_ID'])->get();

foreach($Query as $Fetch){
		
	echo "<row id=\"$Fetch->ID\" >";
		echo "<cell>".substr($Fetch->Task_ID,-7)."-$Fetch->ID</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Position)."</cell>";
		echo "<cell type='ro'>".htmlspecialchars($Fetch->Qty)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Requirement)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Detail_Position)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Salaries)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Site)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Salary)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Benefit)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->User_Interview)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Email_User_Interview)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Phone_User_Interview)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
