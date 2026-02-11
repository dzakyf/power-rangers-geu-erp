<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	

function konversinpwp($nilaiangka){
    $titik = substr($nilaiangka,0,2).".".substr($nilaiangka,2,3).".".substr($nilaiangka,5,3).".".substr($nilaiangka,8,1)."-".substr($nilaiangka,9,3).".".substr($nilaiangka,-3);
return ($titik);
}

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
	$pegawai[$employes->NIK]['Salary_Group'] = $employes->Salary_Group;
	$pegawai[$employes->NIK]['Name'] = $employes->Name;
	$pegawai[$employes->NIK]['NPWP'] = $employes->NPWP;
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
		$pphdata = DB::table("salary_lists")->where("Task_ID","=",$ambil->Task_ID)->where("Group_ID","=",$ambil->Group_ID)->where("Payroll_ID",'PYH00001')->first();

		$pecahbr = explode("# Bruto :",$pphdata->Note);
		$pecahangka = explode("= ",$pecahbr[1]);
		$bruto = $pecahangka[1];
		$taskpegawai = $pegawai[$ambil->Group_ID];
		$grupgajian = $gaji[$ambil->Main_ID];	
		$taskpegawai['NPWP'] = ($taskpegawai['NPWP'] == '') ? '000000000000000' : $taskpegawai['NPWP'];
			echo "<row id=\"$ambil->ID\">";
					echo "<cell>$nomor</cell>";#a
					echo "<cell>".htmlspecialchars($ambil->Group_ID)."</cell>";#c
					echo "<cell>".htmlspecialchars($taskpegawai['Name'])."</cell>";#d
					echo "<cell>".htmlspecialchars(konversinpwp($taskpegawai['NPWP']))."</cell>";#d
					echo "<cell>".htmlspecialchars($grupgajian['Name'])."</cell>";#d
					echo "<cell>".htmlspecialchars($bruto+0)."</cell>";#i
					echo "<cell>".htmlspecialchars(($ambil->PPH * -1)+0)."</cell>";#i
				
				echo "</row>";
				
				$nomor++;
		}
	



echo "</rows>";


?>
