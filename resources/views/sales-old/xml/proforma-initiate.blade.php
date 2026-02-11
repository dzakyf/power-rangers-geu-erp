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

// $Query = DB::table("opportunity")->where("Location",$LSP)->where("Status","!=","FINISH")->where("Status","!=","CANCELED")->orderBy("ID","DESC")->get();

$Query = DB::table("opportunity")->where("Location",$LSP)->whereNotIn('Status',['CLOSED', 'CANCELED'])->orderBy("ID","DESC")->get();

foreach($Query as $Fetch2){
	
	$Query2 = DB::table("opportunity_detail")->where("Task_ID",$Fetch2->Task_ID)->orderBy("ID","DESC")->get();

	foreach($Query2 as $Fetch){	
		$subjek = $Fetch->Subject;
		
			echo "<row id=\"$Fetch->ID\">";
				echo "<cell>0</cell>";
				echo "<cell>".htmlspecialchars($Fetch->PO_No)."</cell>";
				echo "<cell>".htmlspecialchars($subjek)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Start_Date)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->End_Date)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Contract_No)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Total)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->Account_No)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->Company_Name)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->PIC)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Project_Type)."</cell>";
				echo "<cell>0</cell>";
			echo "</row>";

		$nomor++;
	}
}

echo "</rows>";
?>
