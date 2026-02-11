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
	$target[$fproject->Source_Reference] = $fproject->Target_Finish;
	
}
$mqac = DB::table("project_activity")->get();

foreach($mqac as $aktif){
	
	$activity[$aktif->Task_ID] = $aktif->Activity_Type;
	
	
}

$mQuery1 = DB::table("opportunity_detail")->get();

foreach($mQuery1 as $fpo){
	
	$po[$fpo->Task_ID]['PO'] += 1;
	$target[$fproject->Source_Reference] = $fproject->Target_Finish;
	
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("opportunity")->where("Location",$LSP)->where("Status","!=","CLOSED")->where("Status","!=","CLOSED")->where("Status","!=","CANCELED")->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
	if ($po[$Fetch->Task_ID]['PO']  == 0){
			
		if($proj[$Fetch->Task_ID]  == 1){
			$hari = round((strtotime(date('Y-m-d')) - strtotime($target[$Fetch->Task_ID]))/86400,0);
			if ($hari < 0 ) $warna = "";
			else if ($hari < 10 && $hari > 0 ) $warna = "style='background-color:yellow'";
			else if ($hari > 0 )  $warna = "style='background-color:red'";
		}else{
			$warna =  $warna = "style='background-color:#bbb'";
			$hari = 0;
		}
			if ($activity[$projecttask[$Fetch->Task_ID]] == '') $activity[$projecttask[$Fetch->Task_ID]] = "NEW";

		echo "<row id=\"$Fetch->ID\" $warna>";
			echo "<cell>$nomor</cell>";
			echo "<cell>$hari</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Oppor_Subject)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Company_Name)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Reference)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Estimated_Rev)."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Category)."</cell>";
			echo "<cell>".htmlspecialchars($target[$Fetch->Task_ID])."</cell>";
			echo "<cell>".htmlspecialchars($Fetch->Note)."</cell>";
			echo "<cell>".htmlspecialchars($activity[$Fetch->Task_ID])."</cell>";
			echo "<cell>".htmlspecialchars($activity[$projecttask[$Fetch->Task_ID]])."</cell>";
		echo "</row>";

		$nomor++;
	}
}

echo "</rows>";
?>
