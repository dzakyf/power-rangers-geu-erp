<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	/*
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");
*/


$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("recruitment")->where("Location",$LSP)->where("Status","!=","CANCELED")->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
	$rekusisi = "";
	$pono = "";
		$pecahin = explode(",",$Fetch->Requisition_ID);
		
		foreach($pecahin as $pecahreq){
		$Requisition = DB::table("requisition")->where("Task_ID",$pecahreq)->first();
		
	$warna1 = "";
		$warna2 = "";
		$warna3 = "";
		$warna4 = "";
		$warna5 = "";
		$warna6 = "";
		$warna7 = "";
		$warna8 = "";
		$warna9 = "";
		$status1 = 0;
		$status2 = 0;
		$status3 = 0;
		$status4 = 0;
		$status5 = 0;
		$status6 = 0;
		$status7 = 0;
		$status8 = 0;
		$status9 = 0;
		if ($Fetch->Status=='CREATED'){ 
			$status1 = 1;
			$warna2= "style='backgorund-color:red'";
		}elseif ($Fetch->Status=='UPLOADED'){
			$status2 = 1;
			$warna2 = "style='backgorund-color:red'";
		}elseif ($Fetch->Status=='PENDING'){
			$statusPending = 1;
			$warnapending = "style='backgorund-color:red'";
		}elseif ($Fetch->Status=='INTERVIEWED'){
			$status3 = 1;
			$warna3 = "style='backgorund-color:red'";
		}elseif ($Fetch->Status=='BUSINESS_REVIEW'){
			$status4 = 1;
			$warna4 = "style='backgorund-color:red'";
		}elseif ($Fetch->Status=='INTERVIEWED'){
			$status5 = 1;
			$warna5 = "style='backgorund-color:red'";
		}elseif ($Fetch->Status=='CLOSED'){
			$status6 = 1;
			$warna6 = "style='backgorund-color:red'";
		}elseif ($Fetch->Status=='REJECT'){
			$status7 = 1;
			$warna5 = "style='backgorund-color:red'";
			$warna7 = "style='backgorund-color:red'";
		
		}elseif ($Fetch->Status=='OFFER DECLINED'){
			$status8 = 1;
			$warna8 = "style='backgorund-color:red'";
	
		}elseif ($Fetch->Status=='INVITATION DECLINED'){
			$status9 = 1;
				$warna9 = "style='backgorund-color:red'";
		}
		echo "<row id=\"$Fetch->ID|$nomor\">";
			echo "<cell>$nomor</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Recruitment_Type)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Gender)."</cell>";
			echo "<cell>".htmlspecialchars($Requisition->PO_No)."</cell>";
			echo "<cell>".htmlspecialchars($Requisition->Job_Position)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Last_Education)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Education_Major)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Interview_By)."</cell>";
			// echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
			echo "<cell $warna1>".htmlspecialchars($status1)."</cell>";
			echo "<cell $warna2>".htmlspecialchars($status2)."</cell>";
			echo "<cell $warna9>".htmlspecialchars($status9)."</cell>";
			echo "<cell $warna7>".htmlspecialchars($status7)."</cell>";
			echo "<cell $warna3>".htmlspecialchars($status3)."</cell>";
			echo "<cell $warnapending>".htmlspecialchars($statusPending)."</cell>";
			echo "<cell $warna4>".htmlspecialchars($status4)."</cell>";
			echo "<cell $warna5>".htmlspecialchars($status5)."</cell>";
			echo "<cell $warna8>".htmlspecialchars($status8)."</cell>";
			echo "<cell $warna6>".htmlspecialchars($status6)."</cell>";
			echo "</row>";
			$nomor++;

		}
}

echo "</rows>";
?>
