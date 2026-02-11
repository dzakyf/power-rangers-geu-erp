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
$q = DB::table("project_documents")->where("Task_ID",$_GET['ID'])->orderBy("ID","Desc")->get();
$nomor = 1;
echo "<rows>";

foreach($q as $Data){
	
	echo "<row id=\"$Data->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell >".Bersih($Data->File_Category)."</cell>";
		echo "<cell >".Bersih($Data->Document_No)."</cell>";
		echo "<cell >".Bersih($Data->Reference_No)."</cell>";
		echo "<cell >".Bersih($Data->Document_Date)."</cell>";
		echo "<cell>download.png</cell>";
		echo "<cell >".Bersih($Data->Path_File)."</cell>";

	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
