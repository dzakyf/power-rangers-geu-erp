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
if ($Saya == 'admin'){
$Fetchx = DB::select(DB::raw("SELECT * FROM reimbursement ORDER BY ID DESC"));
}else{
$Fetchx = DB::select(DB::raw("SELECT * FROM reimbursement WHERE Created_By='$Saya' ORDER BY ID DESC"));
}

echo "<rows>";

foreach($Fetchx as $Fetch){
$str = strtotime($Fetch->Reimbursement_Date);
$nw = strtotime(date('c'));
$sisa = $nw - $str;
$days = round($sisa/86400);

	echo "<row id=\"$Fetch->Reimbursement_No\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reimbursement_No)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reimbursement_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->User_Reference)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reference_No)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Total)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->User_Responsibility_Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Currency)."</cell>";

	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
