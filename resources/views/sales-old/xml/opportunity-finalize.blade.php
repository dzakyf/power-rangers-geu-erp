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



$mQuery1 = DB::table("opportunity_detail")->get();

foreach($mQuery1 as $fpo){
	
	$po[$fpo->Task_ID]['No'] = $fpo->PO_No;
	$po[$fpo->Task_ID]['PO'] += 1;
	$po[$fpo->Task_ID]['End'] = $fpo->End_Date;
	$target[$fproject->Source_Reference] = $fproject->Target_Finish;
	
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";


$mQuery = DB::table("project_mgt")->where("Status","=","FINISH")->get();

foreach($mQuery as $fproject){
	
	$proj[$fproject->Source_Reference] = 1;
	

	$Query = DB::table("opportunity")->where("Location",$LSP)->where("Task_ID",$fproject->Source_Reference)->orderBy("ID","DESC")->get();

	foreach($Query as $Fetch){
		
		if ($po[$Fetch->Task_ID]['PO']  > 0){
				
			if($proj[$Fetch->Task_ID]  == 1){
				$hari = round((strtotime(date('Y-m-d'))-strtotime($po[$Fetch->Task_ID]['End']))/86400,0);
				if ($hari < 0 ) $warna = "";
				else if ($hari < 10 && $hari > 0 )  $warna = "style='background-color:yellow'";
				else if ($hari > 0 ) $warna = "style='background-color:red'";
			}else{
				$warna =  $warna = "style='background-color:#bbb'";
				$hari = 0;
			}
			echo "<row id=\"$Fetch->ID\" $warna>";
				echo "<cell $warna>$nomor</cell>";
				echo "<cell $warna>$fproject->Task_ID</cell>";
				echo "<cell $warna>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell $warna>".htmlspecialchars($Fetch->Oppor_Subject)."</cell>";
				echo "<cell $warna>".htmlspecialchars($Fetch->Company_Name)."</cell>";
				echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['No'])."</cell>";
				echo "<cell $warna>".htmlspecialchars($Fetch->Created_Date)."</cell>";
				echo "<cell $warna>".htmlspecialchars($Fetch->Estimated_Rev)."</cell>";
				echo "<cell $warna>".htmlspecialchars($Fetch->Category)."</cell>";
				echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['End'])."</cell>";
				echo "<cell $warna>".htmlspecialchars($Fetch->Note)."</cell>";
			echo "</row>";

			$nomor++;
		}
	}
}
echo "</rows>";
	
?>
