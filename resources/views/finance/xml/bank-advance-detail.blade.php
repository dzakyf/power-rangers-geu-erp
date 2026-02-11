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


$nomor = 1;
$Query = DB::table("bank_advance_dtl")->where("Task_ID",$_GET['ID'])->get();
echo "<rows>";

foreach($Query as $Fetch){
	if (is_null($Fetch->Date_Cash)) $Fetch->Date_Cash = $Fetch->Date_Bank;
	if ($Fetch->Posting==1) {
		$warna = " type='ro' style='background-color:red;color:#fff'";
	}else{
		$warna = "";
	}
	echo "<row id=\"$Fetch->ID\" $warna>";
		echo "<cell $warna>$nomor</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Acc_No)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Date_Cash)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Reference)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Remark)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Bank_Val)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Bal_Acc)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->COA_Lawan)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Reference_Lawan)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Nilai)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Sisa)."</cell>";
		echo "<cell $warna>".htmlspecialchars($Fetch->Posting)."</cell>";
	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
