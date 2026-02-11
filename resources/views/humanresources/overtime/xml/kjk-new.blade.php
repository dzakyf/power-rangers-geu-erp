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
$Saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";
$Query = DB::table("kjk_lists")->where("Task_ID",$_GET['Task_ID'])->orWhere("Submit_Approval",0)->orderBy("NIK","ASC")->orderBy("Name","ASC")->get();

foreach($Query as $Fetch){
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Allowance)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Rate_KJK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->KJK_Value)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Total)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->KJK_Period)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Note)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Salary_Group)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
