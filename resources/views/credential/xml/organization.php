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
$nomor =1;
echo "<rows>";

$Query = DB::table("organization_structure")->orderBy("Name","ASC")->get();

foreach($Query as $Fetch){
	echo "<row id=\"$Fetch->ID\" >";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Level1)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Level2)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Level3)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Level4)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Level5)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Level6)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Level7)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
        echo "<cell>".htmlspecialchars($Fetch->Dept_Code)."</cell>";
        echo "<cell>".htmlspecialchars($Fetch->Email)."</cell>";
        echo "<cell>".htmlspecialchars($Fetch->Alerted)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->divisi)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
