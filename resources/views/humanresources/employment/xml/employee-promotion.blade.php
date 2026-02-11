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



$Query1 = DB::table("employment_akademic")->get();

foreach($Query1 as $Fetch1){
	$akademis[$Fetch1->Task_ID] = $Fetch1->Education_Major;
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("employment")->where("Location",$LSP)->where("Status","=","ACTIVE")->where("Category","INTERNAL")->orderBy("Name","ASC")->get();

foreach($Query as $Fetch){
	
	
		$birtdate = $Fetch->Birth_Date;
	if ($birtdate != '0000-00-00' && $birtdate != ''){
		$stro = floor((time() - strtotime($birtdate))/31556926);
		
	}
	$jurusan = $akademis[$Fetch->Task_ID];
	
	echo "<row id=\"$Fetch->NIK\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Gender)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Marital_Status)."</cell>";
		echo "<cell>".htmlspecialchars($stro)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Location)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Subsection)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Position)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Directorat)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Employment_Class)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Last_Education)."</cell>";
		echo "<cell>".htmlspecialchars($jurusan)."</cell>";
	echo "</row>";
$nomor++;
}

echo "</rows>";
?>
