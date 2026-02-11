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
$q = DB::table("project_reimbursement")->orderBy("ID","Desc")->get();
$nomor = 1;
echo "<rows>";

foreach($q as $Data){
	if ($Data->Status !='CLOSED' && $Data->Status != 'CANCELED'){
		$dx = (strtotime(date("c")) - strtotime($Data->Created_Date))/86400;
		$hari = round($dx,0);
	}else{
		$hari = 0;
	}
	echo "<row id=\"$Data->ID\" $warna>";
		echo "<cell $warna>$nomor</cell>";
		echo "<cell  $warna>".Bersih($Data->Task_ID)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Reimbursement_Type)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Description)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Created_By)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Total)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Paid)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Remain)."</cell>";
		echo "<cell  $warna>".Bersih($hari)."</cell>";
		echo "<cell  $warna>".Bersih(date("Y-m-d",strtotime($Data->Created_Date)))."</cell>";
		echo "<cell  $warna>".Bersih($Data->Payment_Type)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Status)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Created_By)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Department)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Reference)."</cell>";
	
	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
