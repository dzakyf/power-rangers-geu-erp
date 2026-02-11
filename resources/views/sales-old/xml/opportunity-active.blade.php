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

	function diffDate($start, $end)
	{
		$diff = round(abs(strtotime($end) - strtotime($start)) / 86400 / 30);
	
		return  $diff;
	}
$mQuery = DB::table("project_mgt")->where("Source_Reference","!=","")->get();

foreach($mQuery as $fproject){
	
	$projecttask[$fproject->Source_Reference] = $fproject->Task_ID;
	$proj[$fproject->Source_Reference] = 1;
	$target[$fproject->Source_Reference] = $fproject->Target_Finish;
	
}
$mqac = DB::table("project_activity")->get();

foreach($mqac as $aktif){
	
	$activity[$aktif->Task_ID] = $aktif->Activity_Type;
	
	
}

$mQuery1 = DB::table("opportunity_detail")->get();

foreach($mQuery1 as $fpo){
	$data = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
	$month_total = 0;
	$po[$fpo->Task_ID]['No'] = $fpo->PO_No;
	$po[$fpo->Task_ID]['PO'] += 1;
	$po[$fpo->Task_ID]['Start'] = $fpo->Start_Date;
	$po[$fpo->Task_ID]['End'] = $fpo->End_Date;
	$po[$fpo->Task_ID]['Total'] = 0;
	$target[$fproject->Source_Reference] = $fproject->Target_Finish;
	$po[$fpo->Task_ID]['bulan']= $data;
	if(date("Y", strtotime($fpo->End_Date)) >= date("Y") ) {
		$po[$fpo->Task_ID]['Total'] = $fpo->Total;
		
		if($fpo->Pencatatan == "MONTHLY") {
			$bagi = diffDate($fpo->Start_Date, $fpo->End_Date);
			$totaltagihan = round($fpo->Total / $bagi, 0);

			$periode_start = date("Y",strtotime($fpo->Start_Date)) < date("Y") ? 1 : date("n", strtotime($fpo->Start_Date));
			$periode_end =  date("Y",strtotime($fpo->End_Date)) > date("Y") ? 12 : date("n", strtotime($fpo->End_Date));
			for ($i=$periode_start; $i < $periode_end+1; $i++) { 
				$data[$i] += $totaltagihan + 0;
			}
			$month_total = array_sum($data);
		} else {
			$periode_start = date("Y",strtotime($fpo->Start_Date)) < date("Y") ? 1 : date("n", strtotime($fpo->Start_Date));
	
			$data[$periode_start] += $fpo->Total + 0;	
			
			$month_total = array_sum($data);
		}
	} else {
		$data = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
	}
	$po[$fpo->Task_ID]['bulan']= $data;
	$po[$fpo->Task_ID]['bulan_total']= 	$month_total;
	
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("opportunity")->where("Location",$LSP)->where("Status","!=","CLOSED")->where("Status","!=","CANCELED")->orderBy("ID","DESC")->get();

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
			echo "<cell $warna>$hari</cell>"; 
			echo "<cell $warna>".htmlspecialchars($Fetch->Oppor_Subject)."</cell>";
			echo "<cell $warna>".htmlspecialchars($Fetch->Company_Name)."</cell>";
			echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['No'])."</cell>";
			echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['Start'])."</cell>";
			echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['End'])."</cell>";
			echo "<cell $warna>".htmlspecialchars($po[$Fetch->Task_ID]['Total'])."</cell>";

			for ($i = 1; $i < 13; $i++) {

				echo "<cell>" . htmlspecialchars($po[$Fetch->Task_ID]['bulan'][$i]) . "</cell>";
			}
			echo "<cell>" . htmlspecialchars($po[$Fetch->Task_ID]['bulan_total']) . "</cell>";
		echo "</row>";

		$nomor++;
		
	}
}

echo "</rows>";
