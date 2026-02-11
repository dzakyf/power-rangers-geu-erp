<?php
	
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
require_once './excel/excel_reader2.php';

$fileid = $_GET['filenya'];
$filex  = "budgetfile/$fileid";

if (!file_exists($filex)) {
echo "<script>alert('Error.. Invalid File! $filex');</script>";
exit;
}

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;



function bersihkan($isi){
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
$baris=1;
$stat = 1;

echo "<script>";

require_once "PHPExcel/IOFactory.php";

$No = 1;
 
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
		$d = htmlspecialchars(($objTpl->getActiveSheet()->getCell("D$No")->getValue()));
		$e = htmlspecialchars(($objTpl->getActiveSheet()->getCell("E$No")->getValue()));
		$f = htmlspecialchars(($objTpl->getActiveSheet()->getCell("F$No")->getValue()));
		$g = htmlspecialchars(($objTpl->getActiveSheet()->getCell("G$No")->getValue()));
		$h = htmlspecialchars(($objTpl->getActiveSheet()->getCell("H$No")->getValue()));
		$ix = htmlspecialchars(($objTpl->getActiveSheet()->getCell("I$No")->getValue()));
		$j = htmlspecialchars(($objTpl->getActiveSheet()->getCell("J$No")->getValue()));
		$k = htmlspecialchars(($objTpl->getActiveSheet()->getCell("K$No")->getValue()));
		$l = $objTpl->getActiveSheet()->getCell("L$No")->getValue();
		$m = $objTpl->getActiveSheet()->getCell("M$No")->getValue();
		$n = htmlspecialchars(($objTpl->getActiveSheet()->getCell("N$No")->getValue()));
		$o = htmlspecialchars(($objTpl->getActiveSheet()->getCell("O$No")->getValue()));
		$p = htmlspecialchars(($objTpl->getActiveSheet()->getCell("P$No")->getValue()));
		$cekcoa = DB::table("branch_coa")->where("Acc_No",$b)->first();

	if ($b != ''){
	echo "
			var idbi =BudgetDetail.uid() + $baris;
			BudgetDetail.addRow(idbi,[BudgetDetail.getRowsNum()+1,'$b','$cekcoa->Description','$d','$e','$f','$g','$h','$ix','$j','$k','$l','$m','$n','$o','$p']);
			";
	}
}
echo "$.unblockUI();</script>";



?>
