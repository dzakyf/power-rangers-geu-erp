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

$Query = DB::table("activity")->where("Location",$LSP)->orderBy("ID","DESC")->get();


foreach($Query as $Fetch){
		$oppor = DB::table("opportunity")->where("Task_ID",$Fetch->Task_ID)->first();
		$oppordetail = DB::table("opportunity_detail")->where("Task_ID",$Fetch->Task_ID)->first();

	echo "<row id=\"$Fetch->ID\">";
			echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($oppordetail->PO_No)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Activity_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Activity_Type)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Priority)."</cell>";
		echo "<cell>".htmlspecialchars(($Fetch->Activity_Description))."</cell>";
		echo "<cell>".htmlspecialchars($oppor->Company_Name)."</cell>";
		echo "<cell>".htmlspecialchars($oppordetail->Subject)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Note)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
