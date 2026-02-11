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
$Saya = Auth::user()->username;
$nomor = 1;

$Fetchx = DB::select(DB::raw("SELECT * FROM bank_advance where Status IN('CREATED','PROPOSED','APPROVAL_REQUEST') ORDER BY ID DESC"));

echo "<rows>";

foreach($Fetchx as $Fetch){
$str = strtotime($Fetch->Advance_Date);
$nw = strtotime(date('c'));
$sisa = $nw - $str;
$days = round($sisa/86400);

	echo "<row id=\"$Fetch[Task_ID]\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reference)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Description)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->User_Responsibility)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Bank_Val)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Val_Remain)."</cell>";
		echo "<cell>".htmlspecialchars($days)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Advance_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
