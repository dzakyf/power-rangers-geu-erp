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
$coa = DB::table("branch_coa")->get();
foreach($coa as $cx){
	
	$akun[$cx->Acc_No]['grupbal'] = $cx->Group_Bal;
	$akun[$cx->Acc_No]['normal'] = $cx->Normal;
	$akun[$cx->Acc_No]['iata'] = $cx->IATA_Code;
	$akun[$cx->Acc_No]['des'] = $cx->Description;
}



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

	
	
		
			DB::insert(DB::raw("INSERT INTO ho_sa_year (Acc_No, Description, Location, Group_Bal, Region_Level, IATA_Code, Journal_Value, Currency, Remark, Group_Report, Grouping_ID, Grouping_ID_Desc, Journal_Year, Created_Date, Created_By)

		SELECT Acc_No, Description, '$LSP', Group_Bal, Region_Level,IATA_Code, '$e', '1', Remark, Group_Report,Grouping_ID,Grouping_ID_Desc,'$f', NOW(), '$Saya' from branch_coa where Acc_No='$b'"));


		
	
}


echo "alert('Upload Completed!');location.href='accounting-beginning-balance';</script>";



?>
