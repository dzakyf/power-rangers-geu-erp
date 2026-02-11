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
$q = DB::table("project_mgt")->where("Created_By",$saya)->where("Status",'CREATED')->orderBy("ID","Desc")->get();

$nomor = 1;
echo "<rows>";

foreach($q as $Data){
	// if ($Data->Source_Reference!=''){
		
	// 	$Dx = DB::table("opportunity_detail")->where("Task_ID",$Data->Source_Reference)->first();
	// 	$subjek = $Dx->Subject;
	// }else{
	// 	$Dx = DB::table("opportunity")->where("Task_ID",$Data->Source_Reference)->first();
	// 	$subjek = $Dx->Oppor_Subject;
	// }
	echo "<row id=\"$Data->ID\" $warna>";
		echo "<cell $warna>$nomor</cell>";
		echo "<cell  $warna>".Bersih($Data->Task_ID)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Project_Name)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Project_Date)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Description)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Source_Reference)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Reference)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Customer_Name)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Vendor_Name)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Created_Date)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Estimated_Revenue)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Estimated_Cost)."</cell>";
		echo "<cell  $warna>".Bersih($Data->Running_Cost)."</cell>";
	
	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
