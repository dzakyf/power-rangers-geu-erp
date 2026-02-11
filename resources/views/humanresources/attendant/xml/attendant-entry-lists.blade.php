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



$hrilibur = DB::select(DB::raw("SELECT * FROM libur order by libur_tgl"));
foreach($hrilibur as  $xdx){
	$libur[$xdx->libur_tgl] = 1;
	$namalibur[$xdx->libur_tgl] = $xdx->libur_keterangan;
}



$NIK = Auth::user()->Emp_ID;
$LSP = Auth::user()->Location;
$name = Auth::user()->Name;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";
$mains = DB::table("attendant_log_main")->where("Task_ID",$_GET['ID'])->first();
$Query = DB::table("attendant_log")->where("Task_ID",$_GET['ID'])->orderBy("Scan_Date","ASC")->get();
foreach($Query as $Fetch){
	$dx[$Fetch->Scan_Date]['ID'] = $Fetch->ID;
	$dx[$Fetch->Scan_Date]['Start_Time'] = $Fetch->Start_Time;
	$dx[$Fetch->Scan_Date]['End_Time'] = $Fetch->End_Time;
	$dx[$Fetch->Scan_Date]['Created_By'] = $Fetch->Created_By;
	$dx[$Fetch->Scan_Date]['Created_Date'] = $Fetch->Created_Date;
	$dx[$Fetch->Scan_Date]['Shift_1'] = $Fetch->Shift_1;
	$dx[$Fetch->Scan_Date]['Shift_2'] = $Fetch->Shift_2;
	$dx[$Fetch->Scan_Date]['Shift_3'] = $Fetch->Shift_3;
	
}

	// Start date
	$date = substr($mains->Periode,0,4)."-".substr($mains->Periode,-2)."-01";
	// End date
	$end_date = date("Y-m-t",strtotime($date));

	while (strtotime($date) <= strtotime($end_date)) {
		$ada = $dx[$date];
		$dayname = date("D",strtotime($date));
		$daynum = date("N", strtotime($date));
		$adalibur = $libur[$date];
		if ($adalibur == 1){
			$kategori = "LIBUR - ". $namalibur[$date];
		}else{
				
			if ($daynum == 7 || $daynum == 6){
				$kategori  = "LIBUR WEEKEND";
			}else{
				$kategori = "";
			}
		}
		if ($ada){
			$jammulai = $ada['Start_Time'];
			$jamend = $ada['End_Time'];
			$dibuatoleh = $ada['Created_By'];
			$dibuattgl = $ada['Created_Date'];
			$shift1 = $ada['Shift_1']+0;
			$shift2 = $ada['Shift_2']+0;
			$shift3 = $ada['Shift_3']+0;
			$idnya = $ada['ID'];
			$eksis = 1;
		}else{
			$jammulai = "08:00";
			$jamend = "17:00";
			$dibuatoleh = $saya;
			$dibuattgl = date("c");
			$shift1 = 1;
			$shift2 = 0;
			$shift3 = 0;
			$idnya = "non".$nomor;
			$eksis  = 0;
		}
	
        echo "<row id=\"$idnya\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($date)."</cell>";
		echo "<cell>".htmlspecialchars($dayname)."</cell>";
		echo "<cell>".htmlspecialchars($kategori)."</cell>";
		echo "<cell>".htmlspecialchars($jammulai)."</cell>";
		echo "<cell>".htmlspecialchars($jamend)."</cell>";
		echo "<cell>".htmlspecialchars($shift1)."</cell>";
		echo "<cell>".htmlspecialchars($shift2)."</cell>";
		echo "<cell>".htmlspecialchars($shift3)."</cell>";
		echo "<cell>$eksis</cell>";

		echo "</row>";

		$nomor++;
        $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));

	}


echo "</rows>";
?>
