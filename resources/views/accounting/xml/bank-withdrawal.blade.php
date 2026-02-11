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

		$Fetchx = DB::select(DB::raw("SELECT * FROM bank_withdrawal where Status IN('CLOSED') AND Posting=0 ORDER BY ID DESC"));

echo "<rows>";

foreach($Fetchx as $Fetch){

	$nilai = $kursval[$Fetch->Currency];
	if ($nilai == '') $nilai = 1;
	echo "<row id=\"$Fetch->ID\">";

	echo "<cell $bg_color>$nomor</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->From_Bank)."</cell>";
		echo "<cell $bg_color type='ro'>".htmlspecialchars($Fetch->Withdrawal_To)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Withdrawal_Value)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Reference)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Withdrawal_Date)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Department)."</cell>";
	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
