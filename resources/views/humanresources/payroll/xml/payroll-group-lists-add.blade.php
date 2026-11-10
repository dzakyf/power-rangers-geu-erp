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
// Ambil komponen yang SUDAH ADA di payrol_main_group_lists untuk Main_ID ini agar tidak muncul lagi (mencegah duplikasi)
$existingComponents = DB::table("payrol_main_group_lists")
    ->where("Main_ID", $_GET['ID'])
    ->pluck("Payroll_ID")
    ->toArray();

$ada = array_flip($existingComponents);

$Query = DB::table("payrol_master_lists")->orderBy("Payroll_ID","asc")->get();

foreach($Query as $Fetch){
	if (!isset($ada[$Fetch->Payroll_ID])){
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
