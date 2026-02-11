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



function bersihkan($isi)
{
	$X = trim($isi);
	$X2 = preg_replace('/[^(\x20-\x7F)]*/', '', $X);
	$X2 = str_replace("'", "", $X2);
	$X2 = str_replace("\"", "", $X2);
	$X2 = str_replace("\"", "", $X2);
	$X2 = str_replace("", "", $X2);
	$X2 = str_replace("�", " ", $X2);
	$X2 = str_replace("", " ", $X2);
	$X2 = str_replace('"', " ", $X2);
	$X2 = str_replace('"', " ", $X2);
	$X2 = str_replace('&', " dan ", $X2);
	$X2 = str_replace("amp;", "", $X2);
	return $X2;
}


function clearkan($isi)
{
	$hasilx = preg_replace('/[^(\x20-\x7F)]*/', '', $isi);
	return $hasilx;
}

function replace_x($datx)
{
	$dx = trim(preg_replace("/'/", "", $datx));
	$clir = clearkan($dx);
	return ($clir);
}

$data = new Spreadsheet_Excel_Reader($filex);

$jumbaris = $data->rowcount($sheet_index = 0);
$urut = 1;
$baris = 1;
$stat = 1;

echo "<script>";

require_once "PHPExcel/IOFactory.php";

$No = 1;

$data = new Spreadsheet_Excel_Reader($filex);

$jumbaris = $data->rowcount($sheet_index = 0);

$objTpl = PHPExcel_IOFactory::load($filex);
$objTpl->setActiveSheetIndex(0);

for ($urut = 1; $urut < $jumbaris; $urut++) {
	$baris++;

	$No = $baris;

	$a = $objTpl->getActiveSheet()->getCell("A$No")->getValue();
	$b = $objTpl->getActiveSheet()->getCell("B$No")->getValue();

	//	$c = $objTpl->getActiveSheet()->getCell("C$No")->getValue();
	$d = htmlspecialchars(($objTpl->getActiveSheet()->getCell("D$No")->getValue())) + 0;
	$e = htmlspecialchars(($objTpl->getActiveSheet()->getCell("E$No")->getValue())) + 0;
	$f = htmlspecialchars(($objTpl->getActiveSheet()->getCell("F$No")->getValue())) + 0;
	$g = htmlspecialchars(($objTpl->getActiveSheet()->getCell("G$No")->getValue())) + 0;
	$h = htmlspecialchars(($objTpl->getActiveSheet()->getCell("H$No")->getValue())) + 0;
	$ix = htmlspecialchars(($objTpl->getActiveSheet()->getCell("I$No")->getValue())) + 0;
	$j = htmlspecialchars(($objTpl->getActiveSheet()->getCell("J$No")->getValue())) + 0;
	$k = htmlspecialchars(($objTpl->getActiveSheet()->getCell("K$No")->getValue())) + 0;
	$l = $objTpl->getActiveSheet()->getCell("L$No")->getValue() + 0;
	$m = $objTpl->getActiveSheet()->getCell("M$No")->getValue() + 0;
	$n = htmlspecialchars(($objTpl->getActiveSheet()->getCell("N$No")->getValue())) + 0;
	$o = htmlspecialchars(($objTpl->getActiveSheet()->getCell("O$No")->getValue())) + 0;
	$p = htmlspecialchars(($objTpl->getActiveSheet()->getCell("P$No")->getValue())) + 0;
	$q = htmlspecialchars(($objTpl->getActiveSheet()->getCell("Q$No")->getValue())) + 0;
	$r = htmlspecialchars(($objTpl->getActiveSheet()->getCell("R$No")->getValue())) + 0;
	$s = htmlspecialchars(($objTpl->getActiveSheet()->getCell("S$No")->getValue())) + 0;
	$t = htmlspecialchars(($objTpl->getActiveSheet()->getCell("T$No")->getValue())) + 0;
	$u = htmlspecialchars(($objTpl->getActiveSheet()->getCell("U$No")->getValue())) + 0;
	$v = htmlspecialchars(($objTpl->getActiveSheet()->getCell("V$No")->getValue())) + 0;
	$w = htmlspecialchars(($objTpl->getActiveSheet()->getCell("W$No")->getValue())) + 0;
	$x = htmlspecialchars(($objTpl->getActiveSheet()->getCell("X$No")->getValue())) + 0;
	$y = htmlspecialchars(($objTpl->getActiveSheet()->getCell("Y$No")->getValue())) + 0;
	$z = htmlspecialchars(($objTpl->getActiveSheet()->getCell("Z$No")->getValue())) + 0;
	$aa = htmlspecialchars(($objTpl->getActiveSheet()->getCell("AA$No")->getValue())) + 0;
	$ab = htmlspecialchars(($objTpl->getActiveSheet()->getCell("AB$No")->getValue())) + 0;
	$ac = htmlspecialchars(($objTpl->getActiveSheet()->getCell("AC$No")->getValue())) + 0;
	$ad = htmlspecialchars(($objTpl->getActiveSheet()->getCell("AD$No")->getValue())) + 0;
	$ae = htmlspecialchars(($objTpl->getActiveSheet()->getCell("AE$No")->getValue())) + 0;
	$af = htmlspecialchars(($objTpl->getActiveSheet()->getCell("AF$No")->getValue())) + 0;
	$ag = htmlspecialchars(($objTpl->getActiveSheet()->getCell("AG$No")->getValue())) + 0;
	$ah = htmlspecialchars(($objTpl->getActiveSheet()->getCell("AH$No")->getValue())) + 0;
	$ai = htmlspecialchars(($objTpl->getActiveSheet()->getCell("AI$No")->getValue()));
	$cekcoa = DB::table("branch_coa")->where("Acc_No", $b)->first();

	if ($b != '') {
		echo "
			var idbi =ProyeksiDetail.uid() + $baris;
			ProyeksiDetail.addRow(idbi,[ProyeksiDetail.getRowsNum()+1,'$b','$cekcoa->Description','$ai','$d','$e','$f','$g','$h','$ix','$j','$k','$l','$m','$n','$o','$p','$q','$r','$s','$t','$u','$v','$w','$x','$y','$z','$aa','$ab','$ac','$ad','$ae','$af','$ag','$ah']);
			";
	}
}
echo "$.unblockUI();</script>";
