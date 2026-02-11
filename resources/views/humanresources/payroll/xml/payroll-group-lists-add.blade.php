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
$mx = DB::table("payrol_group_lists")->where("Group_ID",$_GET['ID'])->get();
if ($mx){
	foreach($mx as $px){
		$ada[$px->Payroll_ID] = 1;
	}
}
$Query = DB::table("payrol_master_lists")->orderBy("Payroll_ID","asc")->get();

foreach($Query as $Fetch){
	if ($ada[$Fetch->Payroll_ID] != 1){
		
		echo "<row id=\"$Fetch->ID\">";
			echo "<cell>0</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Payroll_ID)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Kena_Potongan)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Default_PPH)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Type_Payroll)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "</row>";

	$nomor++;
	}
}

echo "</rows>";
?>
