<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function updatekan($rowId,$param) {
	
	$xa = $param[$rowId."_c0"]; // origin
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
	$tipe = $param[$rowId."_c16"]; // lead time
	$notes = $param[$rowId."_c17"]; // lead time
	
	$taskid = $_GET['ID'];
	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	if ($xa == 1 || $xa =='On' ||$xa == true){
		
		if ($tipe == 'SALARY'){
			
			$karyawan = DB::table("employment")->where("NIK",$rowId)->first();
		
			$mainx = DB::table("payrol_main_group")->where("Main_ID",$karyawan->Salary_Group)->first();
					
			$mx = "";
				$dx = DB::table("payrol_master_group")->where("Main_ID",$mainx->Main_ID)->where("Group_ID",$rowId)->first();
				$notepajak = "";
				$grup[$dx->Main_ID] = $dx->Use_For;
			
				DB::insert(DB::raw("INSERT INTO  salary(Task_ID,Salary_Period,Main_ID,Group_ID,Group_Name,Gross_Up,Category,Use_For,Pokok,Tunjangan,Overtime,KJK,Tunjangan_PPH,Deduction,PPH,THP,Created_By)
				VALUES('$taskid','$_GET[Period]','$mainx->Main_ID','$dx->Group_ID','$dx->Group_Name','$dx->Gross_Up','$tipe','$dx->Use_For','$g','$h','$ix','$j','$k','$l','$m','$n','$Saya')"));

				
				if ($tipe == 'SALARY'){
					DB::update("UPDATE overtime_lists set Salary_ID='$taskid' WHERE Salary_ID='TEMP99999'");
					DB::update("UPDATE kjk_lists set Salary_ID='$taskid' WHERE Salary_ID='TEMP99999'");
					
					DB::insert(DB::raw("INSERT INTO  salary_lists(Task_ID, Main_ID, Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll, Amounts, Note, Created_By) SELECT '$taskid', Main_ID, Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll, Prorate, Note, '$Saya' FROM payrol_group_lists WHERE Main_ID='$mainx->Main_ID' AND Group_ID='$dx->Group_ID' and Prorate > 0"));
					
					DB::insert(DB::raw("INSERT INTO  salary_lists(Task_ID, Main_ID, Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll, Amounts, Note, Created_By) SELECT '$taskid', Main_ID, Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll, Amounts, Note, '$Saya' FROM payrol_group_lists WHERE Main_ID='$mainx->Main_ID' AND Group_ID='$dx->Group_ID' and Prorate = 0"));
				
				}else{
					$kenapotongan = "";
					$dx = DB::table("payrol_template_master_lists")->where("Main_ID",$tipe)->get();
					foreach($dx as $mx){
						
				
					DB::insert(DB::raw("INSERT INTO  salary_lists(Task_ID, Main_ID, Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll, Amounts, Note, Created_By) SELECT '$taskid', Main_ID, Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll, Amounts, Note, '$Saya' FROM payrol_group_lists WHERE Main_ID='$mainx->Main_ID' AND Payroll_ID='$mx->Payroll_ID' AND Group_ID='$dx->Group_ID'"));
					
					}
				}
		}else{
			$karyawan = DB::table("employment")->where("NIK",$rowId)->first();
		
			$mainx = DB::table("payrol_main_group")->where("Main_ID",$karyawan->Salary_Group)->first();
					
			$mx = "";
				$dx = DB::table("payrol_master_group")->where("Main_ID",$mainx->Main_ID)->where("Group_ID",$rowId)->first();
				$notepajak = "";
				$grup[$dx->Main_ID] = $dx->Use_For;
			
				DB::insert(DB::raw("INSERT INTO  salary(Task_ID,Salary_Period,Main_ID,Group_ID,Group_Name,Gross_Up,Category,Use_For,Pokok,Tunjangan,Overtime,KJK,Tunjangan_PPH,Deduction,PPH,THP,Created_By,Note)
				VALUES('$taskid','$_GET[Period]','$mainx->Main_ID','$dx->Group_ID','$dx->Group_Name','$dx->Gross_Up','$tipe','$dx->Use_For','$g','$h','$ix','$j','$k','$l','$m','$n','$Saya','$notes')"));
				
				
					
					

		}
			
	} //end if xa check


	
	
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



echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&",$request_body);
foreach($ids as $datax){
	
	$idenya = explode("=",$datax);
	$param[$idenya[0]] = $idenya[1];
	
}
$checkids = explode(",",$param["ids"]);
$ada = "";
for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$xa = $param[$rowId."_c0"]; // origin
		$l = $param[$rowId."_c14"]; // origin
		switch($mode){
			case "updated":
			
				$action = updatekan($rowId,$param);break;
		case "deleted":
				$action = deletekan($rowId,$param);break;
		case "inserted":
				$action = insertkan($rowId,$param);break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}

echo "</data>";




?>