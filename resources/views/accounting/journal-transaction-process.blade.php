<?php
	
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
require_once './excel/excel_reader2.php';

$fileid = $_GET['filename'];
$filex  = "accounting_file/$fileid";

if (!file_exists($filex)) {
echo "<script>alert('Error.. Invalid File! $filex');</script>";
exit;
}
$ID = $_GET['ID'];

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;



$resq = DB::table("branch_journal_task")->where("FileName",$filename)->where("Status","!=","CANCELED")->first();
if($resq->FileName !="") {
        echo "<script>alert('Error.. File already Uploaded ! $filename');CloseAccountingJournalTransactionWindow();</script>";
        exit;                        
}

function batasin($isi){
	$X = trim($isi);
	$X2 = preg_replace('/[^(\x20-\x7F)]*/','', $X);
	$X2 = str_replace("'","",$X2);
	$X2 = str_replace("\"","",$X2);
	$X2 = str_replace("\"","",$X2);
	$X2 = str_replace("","",$X2);
	$X2 = str_replace("�"," ",$X2);
	$X2 = str_replace(""," ",$X2);
	$X2 = str_replace('"'," ",$X2);
	$X2 = str_replace('"'," ",$X2);
	$X2 = str_replace('&'," dan ",$X2);
	$X2 = str_replace("amp;","",$X2);
	return $X2;
}

function cekAkunCOA($acc){
	$acc = substr($acc,0,5);
	$row = DB::select(DB::raw("SELECT COUNT(ID)AS jml FROM branch_coa WHERE Acc_No LIKE '$acc%'"));
	
	if($row->jml = '0'){
		$lanjut = 1;
	}else{
		$lanjut = 0;
	}
	
	return $lanjut;
}
$ymd = date('ymdhis');

$kunting = 1;
$journal_no = $LSP."-".$ymd.$kunting;


function clearkan($isi){
	$hasilx = preg_replace('/[^(\x20-\x7F)]*/','', $isi);
	return $hasilx;

}

function replace_x($datx){
	$dx = trim(preg_replace("/'/", "", $datx));
	$clir=clearkan($dx);
	return ($clir);
}

$data = new Spreadsheet_Excel_Reader($filex);

$jumbaris=$data->rowcount($sheet_index=0) ;
$urut=1;
$baris=3;
$stat = 1;
$bulantahun = $_GET['bulantahun'];

echo "<script>";

require_once "PHPExcel/IOFactory.php";

$No = 1;
 
$data = new Spreadsheet_Excel_Reader($filex);

$jumbaris=$data->rowcount($sheet_index=0) ;

$objTpl = PHPExcel_IOFactory::load($filex);
$objTpl->setActiveSheetIndex(0); 
$postdate = "";
function validateDate($date, $format = 'Y-m-d H:i:s')
{
	$d = DateTime::createFromFormat($format, $c);
	return $d && $d->format($format) == $date;
} 
$catatan = "";
$catatan2 = "";
for($urut = 1;$urut < $jumbaris;$urut++){
	$baris++;
	
	$No = $baris;	
	$ix = $objTpl->getActiveSheet()->getCell("I$No")->getValue();
	if ($ix != ''){
		$a = $objTpl->getActiveSheet()->getCell("A$No")->getValue();
		$b = $objTpl->getActiveSheet()->getCell("B$No")->getValue();
		$c = $objTpl->getActiveSheet()->getCell("C$No")->getValue();
		// $c =  date($format = "Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($c));
		$getbul = date('Y-m',strtotime($c));
		if ($getbul != $bulantahun){
			
				$catatan2 .="($c , $bulantahun)->$baris,";
		
		}
	
		$validasi = validateDate($c);
		if ($validasi == 'false'){
			
			$catatan .="$baris,";
		}else{
			 $postdate =  date($format = "Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($c));
		}
		$d = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("D$No")->getValue()));
		$e = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("E$No")->getValue()));
		$f = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("F$No")->getValue()));
		$g = strtoupper(htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("G$No")->getValue())));
		$h = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("H$No")->getValue()));
		$j = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("J$No")->getValue()));
		$k = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("K$No")->getValue()));
		$l = $objTpl->getActiveSheet()->getCell("L$No")->getValue();
		$m = $objTpl->getActiveSheet()->getCell("M$No")->getValue();
		$n = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("N$No")->getValue()));
		$o = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("O$No")->getValue()));
	}

}

if ($catatan2 != ""){
	echo "alert('Upload Gagal! Periksa Periode Journal Transaksi! kesalahan pada baris ke : $catatan2');</script>";
	exit;	
}

if ($catatan != ""){
	echo "alert('Upload Gagal! ada kesalahan pada tanggal di baris nomor : $catatan');</script>";
	exit;	
}

$data = new Spreadsheet_Excel_Reader($filex);

$jumbaris=$data->rowcount($sheet_index=0) ;
$urut=1;
$baris=3;
$stat = 1;
$bulantahun = $_GET['bulantahun'];

$data = new Spreadsheet_Excel_Reader($filex);

$jumbaris=$data->rowcount($sheet_index=0) ;

$objTpl = PHPExcel_IOFactory::load($filex);
$objTpl->setActiveSheetIndex(0); 

for($urut = 1;$urut < $jumbaris;$urut++){
	$baris++;
	
	$No = $baris;	
	$a = $objTpl->getActiveSheet()->getCell("A$No")->getValue();
	$b = $objTpl->getActiveSheet()->getCell("B$No")->getValue();
	$c = $objTpl->getActiveSheet()->getCell("C$No")->getValue();
	//$c = date($format = "Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($c)); 
	$d = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("D$No")->getValue()));
	$e = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("E$No")->getValue()));
	$f = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("F$No")->getValue()));
	$g = strtoupper(htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("G$No")->getValue())));
	$h = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("H$No")->getValue()));
	$ix = $objTpl->getActiveSheet()->getCell("I$No")->getValue();
	$j = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("J$No")->getValue()));
	$k = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("K$No")->getValue()));
	$l = $objTpl->getActiveSheet()->getCell("L$No")->getValue();
	$m = $objTpl->getActiveSheet()->getCell("M$No")->getValue();
	$n = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("N$No")->getValue()));
	$o = htmlspecialchars(batasin($objTpl->getActiveSheet()->getCell("O$No")->getValue()));

	if ($l == '') $l = 0;
	if ($m == '') $m = 0;
	$l = floor($l);
	$m = floor($m);
	
	if ($ix == ''){

	}else{
		
		$stat *= cekAkunCOA($j);
		
		if(strlen(trim($j))>0){
			if(trim($j) != '-'){
				$stat *= cekAkunCOA($j);
			}
		}
		
		DB::insert(DB::raw("INSERT INTO branch_journal_temp (Task_ID,Location,Group_No, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name,
			   Department, Journal_No, Account_No, Account_Name, Remark, Debet, Credit, User_Resp, Created_By, Created_Date,Task_Path,Reference_No) 
			   VALUES('$journal_no','$LSP','$b','$c','$d','$e','$f','$g','$h','$journal_no','$ix','$j','$k','$l','$m','$n','$saya',NOW(),'$filex','$o')"));


		
	}
}



$recount = DB::select(DB::raw("SELECT SUM(Debet-Credit) FROM branch_journal_temp WHERE Task_ID = '$journal_no'"));

$hasilrecount = count($recount);
if ($hasilrecount[0] != 0){
	if ($hasilrecount[0] < 0){
		$debx = $hasilrecount[0] * -1;
		$credx = 0;
	}else{
		$credx = $hasilrecount[0];
		$debx = 0;
	}
	$postdates = $bulantahun."-01";
	DB::insert(DB::raw("INSERT INTO branch_journal_temp (Task_ID,Location,Group_No, Posting_Date, Document_No, External_Doc, 
			Partner_Code, Partner_Name,Department, Journal_No, Account_No, Account_Name, Remark, Debet, Credit,
   			User_Resp, Created_By, Created_Date,Task_Path) 
			VALUES('$journal_no','$LSP','','$postdates','','','','','','$journal_no','89000','Extraordinary Items','Auto additional balance','$debx','$credx','$Saya','$Saya',NOW(),'$filex')"));

	
}
DB::insert(DB::raw("INSERT INTO branch_journal_task(Task_ID,Task_Date,Location,Status,Created_By,FileName)
	   VALUES ('$journal_no',NOW(),'$LSP','CREATED','$Saya','$filename')"));
	   
DB::insert(DB::raw("INSERT INTO branch_journal_task_history(Task_ID,Location,Responsible,Description,Actions,Status,Created_By)
	   VALUES ('$journal_no','$LSP','$Saya','Create Journal Task $logs ','NEW_DATA','CREATED','$Saya')"));

echo "alert('Upload Completed!');location.href='accounting-journal-transaction';</script>";



?>
