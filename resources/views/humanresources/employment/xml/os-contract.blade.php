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

$Query = DB::table("employment")->where("Location",$LSP)->where("Recruitment_Status","!=","")->where("Status","=","ACTIVE")->orderBy("End_Date","ASC")->get();

foreach($Query as $Fetch){
	$daysexp = 0;
		$birtdate = $Fetch->Birth_Date;
	if ($birtdate != '0000-00-00' && $birtdate != ''){
		$stro = floor((time() - strtotime($birtdate))/31556926);
		
	}
	if ($Fetch->End_Date != '0000-00-00' && $Fetch->End_Date != ''){
		$daysexp = floor((strtotime(date("Y-m-d")) - strtotime($Fetch->End_Date))/86400);
		
	}
	if ($Fetch->Recruitment_Status=='FRESH'){
	
		$warna = "style='background-color:green;color:white'";
	}else{
		if ($daysexp < 0 && $daysexp > -30) { 
			$warna = "style='background-color:yellow;color:white'";
		}elseif ($daysexp > 1) { 
			$warna = "style='background-color:red;color:white'";
		}else{
			$warna = "";
		}
	}
	echo "<row id=\"$Fetch->NIK\" $warna>";
		echo "<cell $warna>$nomor</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Gender)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Marital_Status)."</cell>";
		echo "<cell $warna>".htmlspecialchars($stro)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Position)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Contract_No)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Start_Date)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->End_Date)."</cell>";
		echo "<cell $warna>$daysexp</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Location)."</cell>";
	echo "</row>";
$nomor++;
}

$Query = DB::table("employment")->where("Location",$LSP)->where("Status","=","ACTIVE")->orderBy("End_Date","ASC")->get();

foreach($Query as $Fetch){
	$daysexp = 0;
		$birtdate = $Fetch->Birth_Date;
	if ($birtdate != '0000-00-00' && $birtdate != ''){
		$stro = floor((time() - strtotime($birtdate))/31556926);
		
	}
	if ($Fetch->End_Date != '0000-00-00' && $Fetch->End_Date != ''){
		$daysexp = floor((strtotime(date("Y-m-d")) - strtotime($Fetch->End_Date))/86400);
		
	}
	if ($Fetch->Recruitment_Status=='FRESH'){
	
		$warna = "style='background-color:green;color:white'";
	}else{
		if ($daysexp < 0 && $daysexp > -60) { 
			$warna = "style='background-color:yellow;color:#000'";
		}elseif ($daysexp > 1) { 
			$warna = "style='background-color:red;color:white'";
		}else{
			$warna = "";
		}
	}
	echo "<row id=\"$Fetch->NIK\" $warna>";
		echo "<cell $warna>$nomor</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Gender)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Marital_Status)."</cell>";
		echo "<cell $warna>".htmlspecialchars($stro)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Position)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Contract_No)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Start_Date)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->End_Date)."</cell>";
		echo "<cell $warna>$daysexp</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Location)."</cell>";
	echo "</row>";
$nomor++;
}

echo "</rows>";
?>
