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
function Filter($C) {
	$DataFilter = preg_replace('/[^(\x20-\x7F)]*/','', $C);
	return htmlspecialchars($DataFilter);
}

function Bersih($Data){
	$X = trim($Data);
	$X2 = Filter($X);
	$X2 = preg_replace('/[^(\x20-\x7F)]*/','', $X2);
	return $X2;
}

$saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$qa = DB::table("account_payable")->where("ID",$_GET['ID'])->first();
$nomor = 1;
echo "<rows>";
$qt = DB::select(DB::raw("SELECT Journal_No FROM ho where Task_ID='$qa->Task_ID' OR Reference_No ='$qa->Inv_Vendor' GROUP BY Journal_No"));
foreach($qt as $Datas){
	$dm = DB::table("ho")->where("Journal_No",$Datas->Journal_No)->get();

	foreach($dm as $Data){
		echo "<row id=\"$Data->ID\" $warna>";
			echo "<cell $warna>$nomor</cell>";
			echo "<cell  $warna>".Bersih($Data->Document_No)."</cell>";
			echo "<cell  $warna>".Bersih($Data->Posting_Date)."</cell>";
			echo "<cell  $warna>".Bersih($Data->Account_No)."</cell>";
			echo "<cell  $warna>".Bersih($Data->Remark)."</cell>";
			echo "<cell  $warna>".Bersih($Data->Debet)."</cell>";
			echo "<cell  $warna>".Bersih($Data->Credit)."</cell>";
			echo "<cell  $warna>".Bersih($Data->External_Doc)."</cell>";	
			echo "<cell  $warna>".Bersih($Data->Reference_No)."</cell>";	
			echo "<cell  $warna>".Bersih($Data->Created_By)."</cell>";
			echo "<cell  $warna>".Bersih($Data->Created_Date)."</cell>";
		echo "</row>";

		$nomor++;
	}
}


echo "</rows>";
