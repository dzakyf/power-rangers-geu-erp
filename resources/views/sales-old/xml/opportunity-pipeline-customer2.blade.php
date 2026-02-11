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


$mQuery = DB::table("project_mgt")->where("Source_Reference","!=","")->get();

foreach($mQuery as $fproject){
	
	$xx = DB::table("opportunity")->where("Task_ID",$fproject->Source_Reference)->first();

	$proj[$fproject->Task_ID] = $xx->Account_No;
	
}
$mqac = DB::table("project_master_activity")->orderBy("Activity_Grade","ASC")->get();

foreach($mqac as $aktif){
	
	$jenis[$aktif->Activity_Type] = $aktif->Activity_Group;
	
	
}
	
$mser = DB::table("customerz")->get();

foreach($mser as $users){
	
	$user[$users->Customer_No] = $users->Company_Name;
	
	
}
		

$mQuery1 = DB::table("project_activity")->where("Activity_Date","LIKE","$_GET[Tgl1]%")->get();
foreach($mQuery1 as $fpo){
	$nama = $proj[$fpo->Task_ID];
	$target[$nama][$jenis[$fpo->Activity_Type]] += $fpo->Estimated_Rev;
	$target2[$nama][$jenis[$fpo->Activity_Type]] += 1;
}

$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;
echo "<rows>";
$mqac2 = DB::table("customerz")->orderBy("Company_Name")->get();
foreach($mqac2 as $aktif2){

	$jns = $aktif2->Customer_No;
		echo "<row id=\"$nomor\" $warna>";
			echo "<cell>$nomor</cell>";
			echo "<cell>$aktif2->Company_Name</cell>";
			echo "<cell>".htmlspecialchars($target2[$jns]['PROBING']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target[$jns]['PROBING']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target2[$jns]['NEW']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target[$jns]['NEW']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target2[$jns]['SPH']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target[$jns]['SPH']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target2[$jns]['WON']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target[$jns]['WON']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target2[$jns]['ADDENDUM']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target[$jns]['ADDENDUM']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target2[$jns]['CLOSING']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target[$jns]['CLOSING']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target2[$jns]['FAILED']+0)."</cell>";
			echo "<cell>".htmlspecialchars($target[$jns]['FAILED']+0)."</cell>";
		echo "</row>";

		$nomor++;
	
}

echo "</rows>";
?>
