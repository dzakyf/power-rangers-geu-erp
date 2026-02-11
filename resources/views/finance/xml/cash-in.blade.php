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


$KURS = DB::table("currency")->get();
foreach($KURS as $curs){
	$kursval[$curs->Currency_Symbol] = $curs->Currency_Value;
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$Saya = Auth::user()->username;
$nomor = 1;
if ($priv != 'ADM'){
	yanglama:
	$Fetchx = DB::select(DB::raw("SELECT * FROM cash where Status IN('CREATED') AND Cash_Type='CASH_IN' AND Created_By='$Saya' ORDER BY ID DESC"));
	
}else{
		$Fetchx = DB::select(DB::raw("SELECT * FROM cash where Status IN('CREATED','PROPOSED','APPROVAL_REQUEST') AND Cash_Type='CASH_IN' ORDER BY ID DESC"));
}
echo "<rows>";

foreach($Fetchx as $Fetch){

	$nilai = $kursval[$Fetch->Currency];
	if ($nilai == '') $nilai = 1;
	echo "<row id=\"$Fetch->ID\">";

	echo "<cell $bg_color>$nomor</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Cash_No)."</cell>";
		echo "<cell $bg_color type='ro'>".htmlspecialchars($Fetch->Cash_Date)."</cell>";
		echo "<cell $bg_color type='ro'>".htmlspecialchars($Fetch->Description)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Voucher_No)."</cell>";
		echo "<cell $bg_color type='ro'>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Total)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Total*$nilai)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell $bg_color>".htmlspecialchars($Fetch->Currency)."</cell>";
	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
