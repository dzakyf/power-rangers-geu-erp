<?php
ini_set("max_execution_time",6000);
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
	
$axx = DB::table("payrol_main_group")->where("Main_ID",$_GET['Main_ID'])->get();

foreach($axx as $mainx){
		
	$dx = "";
	$qx = DB::table("payrol_master_group")->where("Main_ID",$mainx->Main_ID)->get();
	
	foreach($qx as $dx){
		$notepajak = "";
		$grup[$dx->Main_ID] = $dx->Use_For;
		$statusgrup[$dx->Main_ID][$dx->Group_ID] = $dx->Status_Salary;
		$qxx = DB::table("payrol_group_lists")->where("Main_ID",$dx->Main_ID)->where("Group_ID",$dx->Group_ID)->get();
		foreach($qxx as $mx){
			if ($mx->Payroll_ID == 'PY000000'){
				$whtx[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
			}elseif ($mx->Payroll_ID == 'PYH00001'){
				$tunjanganwhtx[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
			}elseif ($mx->Type_Payroll == 'ALLOWANCE'){
				if ($mx->Prorate > 0 ) $mx->Amounts = $mx->Prorate;
				$tunjanganx[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
			}elseif($mx->Type_Payroll == 'BASIC'){
				if ($mx->Prorate > 0 ) $mx->Amounts = $mx->Prorate;
				$basik[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
			}else{
				$costs[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
			}
		}
	}
}

echo "<rows>";
$Query = DB::select(DB::raw("SELECT * FROM employment where Location='$LSP' AND Salary_Group='$_GET[Main_ID]' and ((Status='ACTIVE' AND  Employment_Status!='RESIGNED') OR (Employment_Status='RESIGNED' AND Prorate_Resigned=0)) order by Name"));

foreach($Query as $Fetch){
	if ($Fetch->Employment_Status =='RESIGNED' && $Fetch->Prorate_Resigned == 0){
					$WARNA = " style='background-color:yellow;'";

	}else{
			
		$cekstatus = $statusgrup[$Fetch->Salary_Group][$Fetch->NIK];
		
		$WARNA = "";
		if ($cekstatus == 'INACTIVE'){
			$WARNA = " style='background-color:red;color:white'";

		}else{
			
			$days = 0;
			if ($Fetch->Start_Date != '0000-00-00' && $Fetch->Start_Date!=NULL && $Fetch->Start_Date!=null){
				
				$tglmasuk = new DateTime(date("Y-m-d",strtotime($Fetch->Start_Date)));
				
				$ends = new DateTime(date("Y-m-t"));
				$ends->modify('+1 day');
				$interval = $ends->diff($tglmasuk);

				$days = $interval->days;
				$period = new DatePeriod($tglmasuk, new DateInterval('P1D'), $ends);
				//$holidays = array('2012-09-07');

				foreach($period as $dt) {
					$curr = $dt->format('D');

					if ($curr == 'Sat' || $curr == 'Sun') {
						$days--;
					}
				}
				
				if ($days < 21){
					
					$WARNA = " style='background-color:green;color:white'";
					
				}else{
					
					if ($Fetch->Prorate_Salary == 1){
						
						$days = 0;
						$WARNA = "";
					}else{
						$WARNA = " style='background-color:green;color:white'";

					}
				}
			
			}else{
				$WARNA = "";
			}
				
		}
	}
	$kos = $costs[$Fetch->Salary_Group][$Fetch->NIK]+0;
	$tunjangan = $tunjanganx[$Fetch->Salary_Group][$Fetch->NIK]+0;
	$basic = $basik[$Fetch->Salary_Group][$Fetch->NIK]+0;
	$pajak = $whtx[$Fetch->Salary_Group][$Fetch->NIK]+0;
	$tunjanganpjk = $tunjanganwhtx[$Fetch->Salary_Group][$Fetch->NIK]+0;
	
	$thp = (($basic + $tunjangan )  + $kos) - ($pajak + $tunjanganpjk);
	
	echo "<row id=\"$Fetch->NIK\" $WARNA>";
		echo "<cell $WARNA>$nomor</cell>";
		echo "<cell $WARNA>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell $WARNA>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell $WARNA>".htmlspecialchars($Fetch->Gender)."</cell>";
		echo "<cell $WARNA>".htmlspecialchars($grup[$Fetch->Salary_Group])."</cell>";
		echo "<cell $WARNA>".htmlspecialchars($Fetch->Salary_Group)."</cell>";
		echo "<cell $WARNA>".htmlspecialchars($Fetch->Position)."</cell>";
		echo "<cell $WARNA>$basic</cell>";
		echo "<cell $WARNA>$tunjangan</cell>";
		echo "<cell $WARNA>$tunjanganpjk</cell>";
		echo "<cell $WARNA>$kos</cell>";
		echo "<cell $WARNA>$pajak</cell>";
		echo "<cell $WARNA>$thp</cell>";
	echo "</row>";
$nomor++;
}

echo "</rows>";
?>
