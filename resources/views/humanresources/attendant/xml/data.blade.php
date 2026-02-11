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



$NIK = Auth::user()->Emp_ID;
$LSP = Auth::user()->Location;
$name = Auth::user()->Name;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";
$Query = DB::table("attendant_log_main")->where("NIK",$NIK)->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Periode)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
