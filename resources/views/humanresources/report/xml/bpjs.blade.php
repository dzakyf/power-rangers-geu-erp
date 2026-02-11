<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	

	//error_reporting(E_ALL ^ E_NOTICE);
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

$femp = DB::table("employment")->where("Salary_Group","!=",'')->get();

 foreach($femp as $employes){
	$pegawai[$employes->NIK]['Task_ID'] = $employes->Task_ID;
	$pegawai[$employes->NIK]['KTP'] = $employes->KTP;
	$pegawai[$employes->NIK]['Name'] = $employes->Name;
	$pegawai[$employes->NIK]['Location'] = ($employes->Work_Area == '')? $employes->Location : $employes->Work_Area;
	$pegawai[$employes->NIK]['Salary_Group'] = ($employes->Registration_Status_Note == '')? $employes->Salary_Group : $employes->Registration_Status_Note;
	$pegawai[$employes->NIK]['Birth_Date'] = date("d/m/Y",strtotime($employes->Birth_Date));

 }
 $fkjpb = DB::table("employment_insurances")->where("Salary_Component","!=",'')->get();

 foreach($fkjpb as $finsurance){
	$asuransi[$finsurance->Task_ID][$finsurance->Salary_Component]['Nomor_Kartu'] = $finsurance->Card_No;
	$asuransi[$finsurance->Task_ID][$finsurance->Salary_Component]['Nomor_Polis'] = $finsurance->Polis_No;

 }
   $salgrup = DB::table("payrol_main_group")->get();

 foreach($salgrup as $groupgaji){
	$gaji[$groupgaji->Main_ID]['Name'] = $groupgaji->Group_Name;

 }
$Kota = $_GET["City"];
$Tgl1 = $_GET["Tgl1"];
$Tgl2 = $_GET["Tgl2"];

$nomor = 1;
echo "<rows>";
$fa = DB::table("salary_main")->where("Salary_Period","=",$Tgl1)->where("Status","!=",'CANCELED')->first();

$fetch = DB::table("salary")->where("Task_ID","=",$fa->Task_ID)->get();
 foreach($fetch as $ambil){

		$fetchlist = DB::select(DB::raw("SELECT * FROM salary_lists WHERE Task_ID='$fa->Task_ID' AND Group_ID='$ambil->Group_ID'"));
		 foreach($fetchlist as $detail){
			 if ($detail->Kena_Potongan == 1) $nilaithp[$ambil->Group_ID] += $detail->Amounts;
			 if ($detail->Amounts < 0 ) $detail->Amounts *= -1;
			if ($detail->Payroll_ID == 'PY000039') $jkk[$ambil->Group_ID] += $detail->Amounts;
			else if ($detail->Payroll_ID == 'PY000041') $jkm[$ambil->Group_ID] += $detail->Amounts;
			else if ($detail->Payroll_ID == 'PY000033') $pjht[$ambil->Group_ID] += $detail->Amounts;
			else if ($detail->Payroll_ID == 'PY000037') $jht[$ambil->Group_ID] += $detail->Amounts;
			else if ($detail->Payroll_ID == 'PY000036') $pjpn[$ambil->Group_ID] += $detail->Amounts;
			else if ($detail->Payroll_ID == 'PY000038') $jpn[$ambil->Group_ID] += $detail->Amounts;
			else $detail->Amounts = 0;
			
			$semuaasuransi[$ambil->Group_ID] += $detail->Amounts;
		}
		$taskpegawai = $pegawai[$ambil->Group_ID];
					$grupgajian = $gaji[$ambil->Main_ID];	

			echo "<row id=\"$ambil->ID\">";
					echo "<cell>$nomor</cell>";#a
					echo "<cell>".htmlspecialchars($asuransi[$taskpegawai['Task_ID']]['PY000032']['Nomor_Kartu'])."</cell>";#b
					echo "<cell>".htmlspecialchars($ambil->Group_ID)."</cell>";#c
					echo "<cell>".htmlspecialchars($taskpegawai['KTP'])."</cell>";#d
					echo "<cell>".htmlspecialchars($taskpegawai['Location'])."</cell>";#d
					echo "<cell>".htmlspecialchars($grupgajian['Name'])."</cell>";#d
					echo "<cell>".htmlspecialchars($taskpegawai['Name'])."</cell>";#d
					echo "<cell>".htmlspecialchars($taskpegawai['Birth_Date'])."</cell>";#d
					echo "<cell>".htmlspecialchars($nilaithp[$ambil->Group_ID]+0)."</cell>";#i
					echo "<cell>".htmlspecialchars($jkk[$taskpegawai['Task_ID']]+0)."</cell>";#j
					echo "<cell>".htmlspecialchars($jkm[$taskpegawai['Task_ID']]+0)."</cell>";#j
					echo "<cell>".htmlspecialchars($pjht[$taskpegawai['Task_ID']]+0)."</cell>";#j
					echo "<cell>".htmlspecialchars($jht[$taskpegawai['Task_ID']]+0)."</cell>";#j
					echo "<cell>".htmlspecialchars($pjpn[$taskpegawai['Task_ID']]+0)."</cell>";#j
					echo "<cell>".htmlspecialchars($jpn[$taskpegawai['Task_ID']]+0)."</cell>";#j
					echo "<cell>".htmlspecialchars($semuaasuransi[$taskpegawai['Task_ID']]+0)."</cell>";#j
				echo "</row>";
				
				$nomor++;
		}
	



echo "</rows>";


?>
