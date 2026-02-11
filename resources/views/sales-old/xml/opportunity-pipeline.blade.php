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
	
	$proj[$fproject->Source_Reference] = 1;
	
}
$mqac = DB::table("project_master_activity")->orderBy("Activity_Grade","ASC")->get();

foreach($mqac as $aktif){
	
	$jenis[$aktif->Activity_Type] = $aktif->Activity_Group;
	
	
}
	
	

$mQuery1 = DB::table("project_activity")->where("Activity_Date","LIKE","$_GET[Tgl1]%")->get();

foreach($mQuery1 as $fpo){
	$bulan = date("m",strtotime($fpo->Activity_Date));
	$target[$bulan][$jenis[$fpo->Activity_Type]] += $fpo->Estimated_Rev;
	$totalx[$jenis[$fpo->Activity_Type]] += $fpo->Estimated_Rev;
	
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";
$mqac2 = DB::table("project_master_activity")->select("Activity_Group")->groupBy("Activity_Group")->orderBy("Activity_Grade")->get();
foreach($mqac2 as $aktif2){
	$jns = $aktif2->Activity_Group;
	$total = $totalx[$jns];
		echo "<row id=\"$nomor\" $warna>";
			echo "<cell>$nomor</cell>";
			echo "<cell>$aktif2->Activity_Group</cell>";
			echo "<cell>".htmlspecialchars($target['01'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['02'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['03'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['04'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['05'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['06'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['07'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['08'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['09'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['10'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['11'][$jns]+0)."</cell>";
			echo "<cell>".htmlspecialchars($target['12'][$jns]+0)."</cell>";
			echo "<cell>$total</cell>";
			
		echo "</row>";

		$nomor++;
	
}

echo "</rows>";
?>
