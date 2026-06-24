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
$Query = DB::table("overtime_lists")->where("Submit_Approval",1)->where("Created_By",$Saya)->orderBy("NIK","ASC")->orderBy("Start_Date","ASC")->limit(100)->get();

foreach($Query as $Fetch){
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Start_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->End_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Early_IN)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Early_OUT)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->After_IN)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->After_OUT)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Holiday)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Total_OT)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Calculated_OT)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->IS_Outdated)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Salary_Period)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Amounts)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reference)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Note)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Salary_Group)."</cell>";
		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
