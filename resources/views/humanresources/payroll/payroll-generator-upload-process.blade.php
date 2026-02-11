<?php
	
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
require_once './excel/excel_reader2.php';

$fileid = $_GET['filename'];
$filex  = "pygn/$fileid";

if (!file_exists($filex)) {
echo "<script>alert('Error.. Invalid File! $filex');</script>";
exit;
}

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;



$data = new Spreadsheet_Excel_Reader($filex);

$jumbaris=$data->rowcount($sheet_index=0) ;
$urut=1;
$baris=3;
$stat = 1;

echo "<script>";

require_once "PHPExcel/IOFactory.php";

$No = 1;
 
$data = new Spreadsheet_Excel_Reader($filex);

$jumbaris=$data->rowcount($sheet_index=0) ;

$objTpl = PHPExcel_IOFactory::load($filex);
$objTpl->setActiveSheetIndex(0); 
$postdate = "";

for($urut = 1;$urut < $jumbaris;$urut++){
	$baris++;
	
	$No = $baris;	
	$a = $objTpl->getActiveSheet()->getCell("A$No")->getValue();
	if ($a != ''){
		$b = $objTpl->getActiveSheet()->getCell("B$No")->getValue();
		$c = $objTpl->getActiveSheet()->getCell("C$No")->getValue();
		echo "EmployeeSalary.cells('$a',7).setValue('$b');";
	}

}

echo "alert('Upload Completed!');CloseAccountingJournalTransactionWindow();</script>";



?>
