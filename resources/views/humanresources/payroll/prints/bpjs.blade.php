<?php
error_reporting(0);	
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
require_once "./PHPExcel/IOFactory.php";

$No = 3;
 
  
$objTpl = PHPExcel_IOFactory::load("format_bpjs.xlsx");
$objTpl->setActiveSheetIndex(0);  //set first sheet as active


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
$Tgl1 = $_GET["Tgl1"];
$cekkurang = str_replace("-","",$Tgl1);
$cekkurang -= 1;
$tglsblmnya = substr($cekkurang,0,4)."-".substr($cekkurang,-2);
$nomor = 1;

$fax = DB::table("salary_main")->where("Salary_Period","=",$tglsblmnya)->where("Status","!=",'CANCELED')->first();

$fetchx = DB::table("salary")->where("Task_ID","=",$fax->Task_ID)->get();
 foreach($fetchx as $sblmnya){
	 
	 $sit2[$sblmnya->Group_ID]['upah'] = $sblmnya->THP ;
 }
 
$fa = DB::table("salary_main")->where("Salary_Period","=",$Tgl1)->where("Status","!=",'CANCELED')->first();

$No = 11;

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
			else if ($detail->Payroll_ID == 'PY000023') $bpjsp = $detail->Amounts;
			else if ($detail->Payroll_ID == 'PY000032') $bpjsk = $detail->Amounts;
			else if ($detail->Payroll_ID == 'PY000001') $gapok = $detail->Amounts;
			else $detail->Amounts = 0;
			
			$semuaasuransi[$ambil->Group_ID] += $detail->Amounts;
		}
		$nomorkartu = $asuransi[$taskpegawai['Task_ID']]['PY000032']['Nomor_Kartu'];
		$taskpegawai = $pegawai[$ambil->Group_ID];
		$grupgajian = $gaji[$ambil->Main_ID];	
		$NJKK = $jkk[$taskpegawai['Task_ID']]+0;
		$NJKM = $jkm[$taskpegawai['Task_ID']]+0;
		$NPJHT = $pjht[$taskpegawai['Task_ID']]+0;
		$NJHT = $jht[$taskpegawai['Task_ID']]+0;
		$NPJPN = $pjpn[$taskpegawai['Task_ID']]+0;
		$NJPN = $jpn[$taskpegawai['Task_ID']]+0;
		$NTOTAL = $semuaasuransi[$taskpegawai['Task_ID']]+0;
		$upahbulanini = $nilaithp[$ambil->Group_ID]+0;
			
		$kesehatan[$ambil->Group_ID]['nama'] = $taskpegawai['Name'];
		$kesehatan[$ambil->Group_ID]['nik'] = $taskpegawai['KTP'];
		$kesehatan[$ambil->Group_ID]['jabatan'] = $taskpegawai['Position'];
		$kesehatan[$ambil->Group_ID]['status'] = $taskpegawai['Status'];
		$kesehatan[$ambil->Group_ID]['joindate'] = $taskpegawai['Date_in'];
		$kesehatan[$ambil->Group_ID]['lokasi'] = $taskpegawai['Work_Area'];
		$kesehatan[$ambil->Group_ID]['jabatan'] = $taskpegawai['Department'];
		$kesehatan[$ambil->Group_ID]['ktp'] = $taskpegawai['KTP'];
		$kesehatan[$ambil->Group_ID]['nokartu'] = $nomorkartu;
		$kesehatan[$ambil->Group_ID]['gapok'] = $gapok;
		$kesehatan[$ambil->Group_ID]['tt'] = $upahbulanini -  $gapok;
		$kesehatan[$ambil->Group_ID]['total'] = $upahbulanini;
		$kesehatan[$ambil->Group_ID]['bpjsp'] = $bpjsp;
		$kesehatan[$ambil->Group_ID]['bpjsk'] = $bpjsk;
		

	
			$objTpl->getActiveSheet()->setCellValue("A$No", $Nomor);
			$objTpl->getActiveSheet()->setCellValue("B$No", stripslashes($nomorkartu));
			$objTpl->getActiveSheet()->setCellValue("C$No", stripslashes($ambil->Group_ID));
			$objTpl->getActiveSheet()->setCellValue("D$No", stripslashes($taskpegawai['KTP']));
			$objTpl->getActiveSheet()->setCellValue("E$No", stripslashes($taskpegawai['Location']));
			$objTpl->getActiveSheet()->setCellValue("F$No", stripslashes($taskpegawai['Name']));
			$objTpl->getActiveSheet()->setCellValue("G$No", stripslashes($taskpegawai['Birth_Date']));
			$objTpl->getActiveSheet()->setCellValue("H$No", stripslashes($upahbulanini));
			$objTpl->getActiveSheet()->setCellValue("I$No", stripslashes($NJKK));
			$objTpl->getActiveSheet()->setCellValue("J$No", stripslashes($NJKM));
			$objTpl->getActiveSheet()->setCellValue("K$No", stripslashes($NPJHT));
			$objTpl->getActiveSheet()->setCellValue("L$No", stripslashes($NJHT));
			$objTpl->getActiveSheet()->setCellValue("M$No", stripslashes($NPJPN));
			$objTpl->getActiveSheet()->setCellValue("N$No", stripslashes($NJPN));
			$objTpl->getActiveSheet()->setCellValue("O$No", stripslashes($NTOTAL));

			$Nomor++;
			$No ++;
			$totupahbulanini += $upahbulanini;
			$TNJKK += $NJKK;
			$TNJKM += $NJKM;
			$TNPJHT += $NPJHT;
			$TNJHT += $NJHT;
			$TNPJPN += $NPJPN;
			$TNJPN += $NJPN;
			$TTOTAL += $NTOTAL;
			
		}
	
				$objTpl->getActiveSheet()->setCellValue("I6", $Nomor);
				$objTpl->getActiveSheet()->setCellValue("M6", $Tgl1);
$No++;

							$objTpl->getActiveSheet()->setCellValue("H$No", $totupahbulanini);
							$objTpl->getActiveSheet()->setCellValue("I$No", $TNJKK);
							$objTpl->getActiveSheet()->setCellValue("J$No", $TNJKM);
							$objTpl->getActiveSheet()->setCellValue("K$No", $TNPJHT);
							$objTpl->getActiveSheet()->setCellValue("L$No", $TNJHT);
							$objTpl->getActiveSheet()->setCellValue("M$No", $TNPJPN);
							$objTpl->getActiveSheet()->setCellValue("N$No", $TNJPN);
							$objTpl->getActiveSheet()->setCellValue("O$No", $TTOTAL);
	$No += 4;
					
			$objTpl->getActiveSheet()->setCellValue("F$No", "Hendra Januar");
			$objTpl->getActiveSheet()->setCellValue("I$No", "Direktur");
			$objTpl->getActiveSheet()->setCellValue("N$No", date("d M Y"));
		$No++;
			$objTpl->getActiveSheet()->setCellValue("F$No", "Nama & tanda tangan pimpinan perusahaan");
			$objTpl->getActiveSheet()->setCellValue("I$No", "Jabatan");
			$objTpl->getActiveSheet()->setCellValue("N$No", "Tanggal");

	$No += 2;
			$objTpl->getActiveSheet()->setCellValue("B$No", "Form ini wajib dilaporankan setiap bulannya dalam bentuk softcopy dan hardcopy.");
		$No++;
			$objTpl->getActiveSheet()->setCellValue("B$No", "Laporan harap dikirimkan kepada pembina perusahaan sebelum perusahaan melakukan pembayaran iuran.");
		$No++;
			$objTpl->getActiveSheet()->setCellValue("B$No", "pastikan laporan yang dikirimkan sudah benar dan tidak terdapat kesalahan/revisi.");
		
		$No++;
			$objTpl->getActiveSheet()->setCellValue("B$No", "kesalahan pada pelaporan data bulanan sepenuhnya menjadi tanggung jawab perusahaan");




$objTpl->setActiveSheetIndex(4);  //set first sheet as active
$No = 4;
$Nomor = 1;
foreach($kesehatan as $data){
			$objTpl->getActiveSheet()->setCellValue("A$No", $Nomor);
			$objTpl->getActiveSheet()->setCellValue("B$No", stripslashes(" ".$data['nama']));
			$objTpl->getActiveSheet()->setCellValue("C$No", stripslashes(" ".$data['nik']));
			$objTpl->getActiveSheet()->setCellValue("D$No", stripslashes($data['jabatan']));
			$objTpl->getActiveSheet()->setCellValue("E$No", stripslashes(" ".$data['status']));
			$objTpl->getActiveSheet()->setCellValue("F$No", stripslashes(" ".$data['joindate']));
			$objTpl->getActiveSheet()->setCellValue("G$No", stripslashes(" ".$data['lokasi']));
			$objTpl->getActiveSheet()->setCellValue("H$No", stripslashes(" ".$data['jabatan']));
			$objTpl->getActiveSheet()->setCellValue("I$No", stripslashes(" ".$data['ktp']));
			$objTpl->getActiveSheet()->setCellValue("J$No", stripslashes(" ".$data['nokartu']));
			$objTpl->getActiveSheet()->setCellValue("K$No", stripslashes($data['gapok']));
			$objTpl->getActiveSheet()->setCellValue("L$No", stripslashes($data['tt']));
			$objTpl->getActiveSheet()->setCellValue("M$No", stripslashes($data['total']));
			$objTpl->getActiveSheet()->setCellValue("N$No", stripslashes($data['bpjsp']));
			$objTpl->getActiveSheet()->setCellValue("O$No", stripslashes($data['bpjsk']));
		$No++;
		$Nomor++;
		$tgapok += $data['gapok'];
		$ttt += $data['tt'];
		$ttotal += $data['total'];
		$tbpjsp += $data['bpjsp'];
		$tbpjsk += $data['bpjsk'];
}
			$objTpl->getActiveSheet()->setCellValue("K$No", $tgapok);
			$objTpl->getActiveSheet()->setCellValue("L$No", $ttt);
			$objTpl->getActiveSheet()->setCellValue("M$No", $ttotal);
			$objTpl->getActiveSheet()->setCellValue("N$No", $tbpjsp);
			$objTpl->getActiveSheet()->setCellValue("O$No", $tbpjsk);


$filename="BPJSExport_".date('ymdhis').".xlsx"; //just some random filename
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