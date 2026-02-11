<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function updatekan($rowId,$jno,$param) {
	
	global $con;
	$Account_No = $_REQUEST['ID'];
	$customer = DB::table("customerz")->where("Customer_No",$Account_No)->first();
	$kastemer_name = $customer->Company_Name;
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$nik =  Auth::user()->Emp_ID;
	$Inv_No = trim($param[$rowId."_c1"]);
	$InvDate = $param[$rowId."_c2"];
	$InvType = $param[$rowId."_c3"];
	$Reference = $param[$rowId."_c4"];
	$Total_AR = $param[$rowId."_c5"];
	$Remain = $param[$rowId."_c6"];
	$Paid = $param[$rowId."_c7"];
	$Confirm = $param[$rowId."_c8"];
	
	$AfterPPH = $param[$rowId."_c9"];
	
	$Action = $param[$rowId."_c10"]; //pay val
	$SubAction = $param[$rowId."_c11"];
	$Payment_Value = $param[$rowId."_c12"];
	$DebtCol = $param[$rowId."_c13"];
	$PIC = $param[$rowId."_c14"];
	$PIC_No = $param[$rowId."_c15"];
	$PTP = $param[$rowId."_c16"];
	$Note = $param[$rowId."_c17"];
	$Claim = $param[$rowId."_c18"];
	$ClaimType = $param[$rowId."_c19"];
	$ClaimNote = $param[$rowId."_c20"];
	$BankRek = $param[$rowId."_c22"];
	$DebtColaststat=$param['laststatus'];
	$Taskid = "";
	$payval_bank = 0;
	
		
	
			
		if ($Action =='Communication'){
			DB::update(DB::raw("UPDATE account_receive SET Manual_Commitment_Date = '$PTP',Remark_Manual_Commitment = CONCAT(Remark,'--# ','$Note'),Remark=CONCAT(Remark,'--# ','$Note') WHERE ID = $rowId"));
		
			DB::insert(DB::raw("INSERT INTO  
				account_receive_log(ARNo,Method,Processed_By,Processed_Date,Action,Action_Result,Result_Output,PIC,PIC_No,
				Payment_Value,Note,Created_By,Debt_Coll,PTP,Invoice_No,Cust_ID) SELECT AR_No,'$Action','$Saya',NOW(),'$Action','$SubAction','$Note','$PIC','$PIC_No',
				'$Paid','$Note','$Saya','$DebtCol','$PTP',Inv_No,Cust_ID FROM account_receive where  ID=$rowId")); 
							
		}
				
			
	
	
			
		
	
	return "update|$payval_bank|$Taskid";
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

		$Task_ID = $_GET['ID'];

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
$Task_ID = $_GET['Task_ID'];

$jno = "JPM-AR-".date("ymdhis");
$totalpaid = 0;
for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$a = $param[$rowId."_c1"]; // origin
						$Confirm = $param[$rowId."_c8"];

		
		switch($mode){
			case "updated":
					
							$aksi = updatekan($rowId,$jno,$param);
						
							$pecah = explode("|",$aksi);
							$action = $pecah[0];
							$totalpaid += $pecah[1]+0;
							$taskids = $pecah[2];
							$SubAction = $param[$rowId."_c11"];
							$PTP = $param[$rowId."_c16"];
							$BankRek = $param[$rowId."_c22"];
					
	
				break;
		case "deleted":
				$action = updatekan($rowId,$param);
				break;
		case "inserted":
				$action = insertkan($rowId,$param);
				break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";



?>