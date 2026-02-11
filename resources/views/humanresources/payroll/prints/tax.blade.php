<?php
error_reporting(0);	
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
require_once "./PHPExcel/IOFactory.php";

$No = 3;
 function konversinpwp($nilaiangka){
    $titik = substr($nilaiangka,0,2).".".substr($nilaiangka,2,3).".".substr($nilaiangka,5,3).".".substr($nilaiangka,8,1)."-".substr($nilaiangka,9,3).".".substr($nilaiangka,-3);
return ($titik);
}
  
$objTpl = PHPExcel_IOFactory::load("format_tax.xlsx");
$objTpl->setActiveSheetIndex(2);  //set first sheet as active






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
$No = 1;
$fa = DB::table("salary_main")->where("Salary_Period","=",$Tgl1)->where("Status","!=",'CANCELED')->first();

				

$fetchx = DB::table("salary")->select("Main_ID")->where("Task_ID","=",$fa->Task_ID)->groupBy("Main_ID")->get();
 foreach($fetchx as $ambilx){
	$objTpl->getActiveSheet()->mergeCells("A$No:E$No");
	$objTpl->getActiveSheet()->setCellValue("A$No", "REKAP PAJAK PENGHASILAN 21");
	$No++;
	$objTpl->getActiveSheet()->mergeCells("A$No:E$No");
	$objTpl->getActiveSheet()->setCellValue("A$No", $fetch->Main_ID);
	$No += 3;
	$no2 = $No + 1;
	$objTpl->getActiveSheet()->mergeCells("A$No:A$no2");
	$No += 2;
	
	$fetch = DB::table("salary")->where("Task_ID","=",$fa->Task_ID)->where("Main_ID",$ambilx->Main_ID)->get();
	 foreach($fetch as $ambil){
			$pphdata = DB::table("salary_lists")->where("Task_ID","=",$ambil->Task_ID)->where("Group_ID","=",$ambil->Group_ID)->where("Payroll_ID",'PYH00001')->first();

			$pecahbr = explode("# Bruto :",$pphdata->Note);
			$pecahangka = explode("= ",$pecahbr[1]);
			$bruto = $pecahangka[1];
			$taskpegawai = $pegawai[$ambil->Group_ID];
			$grupgajian = $gaji[$ambil->Main_ID];	
			$taskpegawai['NPWP'] = ($taskpegawai['NPWP'] == '') ? '000000000000000' : $taskpegawai['NPWP'];
				
					$objTpl->getActiveSheet()->setCellValue("A$No", $nomor);
					$objTpl->getActiveSheet()->setCellValue("B$No", $taskpegawai['Name']);
					$objTpl->getActiveSheet()->setCellValue("C$No", konversinpwp($taskpegawai['NPWP']));
					$objTpl->getActiveSheet()->setCellValue("D$No", $bruto+0);
					$objTpl->getActiveSheet()->setCellValue("E$No", ($ambil->PPH * -1)+0);

					$No++;
					$nomor++;
			}
		
 }





$filename="TaxReport_".date('ymdhis').".xlsx"; //just some random filename
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");;
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
 
 
$objWriter = PHPExcel_IOFactory::createWriter($objTpl, 'Excel2007');  //downloadable file is in Excel 2003 format (.xls)
$objWriter->save('php://output');  //send it to user, of course you can save it to disk also!

?>