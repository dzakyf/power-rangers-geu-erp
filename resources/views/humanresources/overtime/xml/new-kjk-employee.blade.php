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

$qxx = DB::table("payrol_group_lists")->where("Kena_Potongan",1)->get();
	foreach($qxx as $mx){
		$jumlahuang[$mx->Main_ID][$mx->Group_ID] += $mx->Amounts;
	}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$Saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";
$Query = DB::table("employment")->where("Status",'ACTIVE')->orderBy("Name","ASC")->get();

foreach($Query as $Fetch){
	$uangs = $jumlahuang[$Fetch->Salary_Group][$Fetch->NIK] + 0;

	$rate = ceil($uangs / 173) + 0;
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Gender)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Position)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Work_Area)."</cell>";
		echo "<cell>$uangs</cell>";
		echo "<cell>$rate</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Salary_Group)."</cell>";

		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
