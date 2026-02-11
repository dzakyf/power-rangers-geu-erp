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

$Fetch2 = DB::table("proforma")->where("Proforma_No",$_GET['Task_ID'])->first();

	
	$Query2 = DB::table("proforma_detail")->where("Proforma_No",$Fetch2->Proforma_No)->orderBy("ID","DESC")->get();

	foreach($Query2 as $Fetch){			
			echo "<row id=\"$Fetch->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars($Fetch->PO_No)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Description)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->PO_Date)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Contract_No)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Total)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->Account_No)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->Account_Name)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->PIC)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->End_Date)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Project_Type)."</cell>";
			echo "</row>";

		$nomor++;
	}


echo "</rows>";
?>
