<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$Saya = Auth::user()->username;
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");



$nomor = 1;
	
$Query = DB::table("employment")->get();


foreach($Query as $Fetch2){
	$karyawan[$Fetch2->NIK]['Salary_Group'] = $Fetch2->Salary_Group;
	$karyawan[$Fetch2->NIK]['Name'] = $Fetch2->Name;
	$karyawan[$Fetch2->NIK]['Gender'] = $Fetch2->Gender;
	$karyawan[$Fetch2->NIK]['Position'] = $Fetch2->Position;
}
echo "<rows>";

$Query = DB::table("salary")->where("Task_ID",$_GET['Task_ID'])->get();


foreach($Query as $Fetch){
	$kary = $karyawan[$Fetch->Group_ID];
	echo "<row id=\"$Fetch->NIK\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($kary['Name'])."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Group_ID)."</cell>";
		echo "<cell>".htmlspecialchars($kary['Gender'])."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Group_ID)."</cell>";
		echo "<cell>".htmlspecialchars($kary['Salary_Group'])."</cell>";
		echo "<cell>".htmlspecialchars($kary['Position'])."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Pokok)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Tunjangan)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Overtime)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->KJK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Tunjangan_PPH)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Deduction)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PPH)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->THP)."</cell>";

	echo "</row>";
$nomor++;
}

echo "</rows>";
?>
