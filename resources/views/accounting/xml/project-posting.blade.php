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
	
	$projecttask[$fproject->Source_Reference] = $fproject->Task_ID;
	$posting[$fproject->Source_Reference] = $fproject->Posting;
	$proj[$fproject->Source_Reference] = 1;
	$target[$fproject->Source_Reference] = $fproject->Target_Finish;
	
}
$mqac = DB::table("project_activity")->get();

foreach($mqac as $aktif){
	
	$activity[$aktif->Task_ID] = $aktif->Activity_Type;
	
	
}

$mQuery1 = DB::table("opportunity_detail")->get();

foreach($mQuery1 as $fpo){
	
	$po[$fpo->Task_ID]['Subject'] = $fpo->Subject;
	$po[$fpo->Task_ID]['No'] = $fpo->PO_No;
	$po[$fpo->Task_ID]['PO'] += 1;
	$po[$fpo->Task_ID]['PO_Date'] = $fpo->PO_Date;
	$po[$fpo->Task_ID]['End'] = $fpo->End_Date;
	$target[$fproject->Source_Reference] = $fproject->Target_Finish;
	
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("opportunity")->where("Location",$LSP)->where("Status","!=","CLOSED")->where("Status","!=","CANCELED")->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
		
	if($posting[$Fetch->Task_ID] == 0 ){
		if ($po[$Fetch->Task_ID]['PO']  > 0 && $Fetch->Posting == 0){
				
			if($proj[$Fetch->Task_ID]  == 1){
				$hari = round((strtotime(date('Y-m-d'))-strtotime($po[$Fetch->Task_ID]['End']))/86400,0);
				if ($hari < 0 ) $warna = "";
				else if ($hari < 10 && $hari > 0 )  $warna = "style='background-color:yellow'";
				else if ($hari > 0 ) $warna = "style='background-color:red'";
				
				if ($activity[$projecttask[$Fetch->Task_ID]] == '') $activity[$projecttask[$Fetch->Task_ID]] = "NEW";
					echo "<row id=\"$Fetch->ID\" $warna>";
						echo "<cell $warna>0</cell>";
						echo "<cell $warna>".$Fetch->Task_ID."</cell>";
						echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['PO_Date'])."</cell>";
						echo "<cell $warna>".date("M Y",strtotime($po[$Fetch->Task_ID]['PO_Date']))."</cell>";
						echo "<cell $warna>".htmlspecialchars($Fetch->Company_Name)."</cell>";
						echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['No'])."</cell>";
						echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['Subject'])."</cell>";
						echo "<cell $warna>".htmlspecialchars($Fetch->Created_Date)."</cell>";
						echo "<cell $warna>".htmlspecialchars($Fetch->Estimated_Rev)."</cell>";
						echo "<cell $warna>".htmlspecialchars($Fetch->Category)."</cell>";
						echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['End'])."</cell>";
						echo "<cell $warna>".htmlspecialchars($Fetch->Note)."</cell>";
						echo "<cell>".htmlspecialchars($activity[$projecttask[$Fetch->Task_ID]])."</cell>";
					echo "</row>";

					$nomor++;
				}
			}
	}
}

echo "</rows>";
?>
