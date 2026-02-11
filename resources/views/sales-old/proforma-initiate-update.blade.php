<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function updatekan($rowId,$proformano,$param) {
	
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // destination
	$c = $param[$rowId."_c3"]; // destination name
	$d = $param[$rowId."_c4"]; // transport
	$e = $param[$rowId."_c5"]; // service
	$f = $param[$rowId."_c6"]; // treshold
	$g = $param[$rowId."_c7"]; // unit price
	$h = $param[$rowId."_c8"]; // additional
	$ix = $param[$rowId."_c9"]; // lead time
	$j = $param[$rowId."_c10"]; // lead time
	$k = $param[$rowId."_c11"]; // lead time
	$l = $param[$rowId."_c12"]; // lead time
	$m = $param[$rowId."_c13"]; // lead time
	$n = $param[$rowId."_c14"]; // lead time
	$o = $param[$rowId."_c15"]; // lead time
	$p = $param[$rowId."_c16"]; // lead time

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;

	DB::insert(DB::raw("INSERT INTO proforma_detail(Proforma_No,Proforma_Date,Proforma_By,Task_ID, PO_No, Project_Type, PO_Date, Contract_No, Reference_No, Customer_No, Description, Department, Alocation, Note, Subtotal, Discount, PPN, Total, PPH, Uang_Muka, Termin, PO_PIC, Email, Due_Days, Created_Date, Created_By, Path_File, Start_Date, End_Date) 
	SELECT '$proformano',NOW(),'$Saya',Task_ID, PO_No, Project_Type, PO_Date, Contract_No, Reference_No, Customer_No, Description, Department, Alocation, Note, Subtotal, Discount, PPN, Total, PPH, Uang_Muka, Termin, PO_PIC, Email, Due_Days, Created_Date, Created_By, Path_File, Start_Date, End_Date from opportunity_detail WHERE ID=$rowId"));
		
		
	
	return "update";
}
function deletekan($rowId,$param){

	
	return "delete";
}

function insertkan($rowId,$param){
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // destination
	$c = $param[$rowId."_c3"]; // destination name
	$d = $param[$rowId."_c4"]; // transport
	$e = $param[$rowId."_c5"]; // service
	$f = $param[$rowId."_c6"]; // treshold
	$g = $param[$rowId."_c7"]; // unit price
	$h = $param[$rowId."_c8"]; // additional
	$ix = $param[$rowId."_c9"]; // lead time
	$j = $param[$rowId."_c10"]; // lead time
	$k = $param[$rowId."_c11"]; // lead time
	$l = $param[$rowId."_c12"]; // lead time
	$m = $param[$rowId."_c13"]; // lead time
	$n = $param[$rowId."_c14"]; // lead time
	$o = $param[$rowId."_c15"]; // lead time
	$p = $param[$rowId."_c16"]; // lead time

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;

		
		
	
	
	return "insert";
	
}

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;


echo "<data>";
$Proforma_No = "PRF-$LSP-".date('ym-dhis');
$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&",$request_body);
foreach($ids as $datax){
	
	$idenya = explode("=",$datax);
	$param[$idenya[0]] = $idenya[1];
	
}
$checkids = explode(",",$param["ids"]);
$Task_ID = $_GET['Task_ID'];
$qty = 1;
for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$a = $param[$rowId."_c1"]; // origin
		$nomorx = $param[$rowId."_c0"];
		$akun  = $param[$rowId."_c7"];
		$akunname  = $param[$rowId."_c8"];
		$tipe  = $param[$rowId."_c10"];
		if($nomorx == 1 || $nomorx == 'On'){
			switch($mode){
				case "updated":
					$action = updatekan($rowId,$Proforma_No,$param);
					break;
			case "deleted":
					$action = updatekan($rowId,$param);
					break;
			case "inserted":
					$action = insertkan($rowId,$param);
					break;
			}
			$qty++;
			$total += $param[$rowId."_c5"];
		}
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
$periode = date("Ym");
DB::insert(DB::raw("INSERT INTO proforma (Location, Proforma_No, Invoice_No, Account_No, Account_Name, Qty, Proforma_Type, Total, Periode, Status, Last_Update, Created_NIK, Dateinsert, invoice_Category, Currency, Validation, Validation_By, Need_Rekon, Nilai_Rekon, Selisih) VALUES('$LSP', '$Proforma_No', '', '$akun', '$akunname', '$qty', '$tipe', '$total', '$periode', 'CREATED', NOW(), '$Saya', NOW(), 'GENERAL', '1', '0',  '', 0, 0, 0)"));
	
	
	
echo "</data>";

?>