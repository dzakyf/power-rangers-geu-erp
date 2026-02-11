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

	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$nik =  Auth::user()->Emp_ID;
	$Inv_No = trim($param[$rowId."_c1"]);
	$PTP = $param[$rowId."_c2"];
	$Payment_Type = $param[$rowId."_c3"];
	$Description = $param[$rowId."_c4"];
	$Amount = $param[$rowId."_c5"];
	$Amountcr = $param[$rowId."_c6"];
	$Action = $param[$rowId."_c7"];
	$Reference_No = $param[$rowId."_c8"];
	$Payment_Bank_Val = $param[$rowId."_c9"];
	$Reference_Desc = $Description;
	$Note = $param[$rowId."_c10"];
	if ($Payment_Type != 'REJECT'){
			
				
				if ($Payment_Type == 'BANK_ADVANCE'){
					$cekap = DB::table("bank_advance")->where("Task_ID",$Reference_No)->first();

					
						
						$nilaisisa = $cekap->Val_Remain - $Amountcr;
						$sisaar = $nilaisisa;
						if ($nilaisisa <= 0){
							$statusx = "CLOSED";
						}else{
							$statusx = "CREATED";
						}
					
						DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
						Task_ID, Location, '$PTP',  Task_ID,Reference, User_Responsibility,User_Responsibility_Name, '$jno', Acc_No,'PUM Karyawan', Task_ID,Reference,'$Note','$Amount','$Amountcr','$Saya','$Saya',NOW(),Department FROM bank_advance where ID = $cekap->ID"));	
						
						DB::update(DB::raw("UPDATE bank_advance SET Val_Used=Val_Used+$Amountcr,Val_Remain=$sisaar,Status='$statusx', Posting = 1, Posting_date = NOW(), Posting_By='$Saya' WHERE ID = $cekap->ID"));
						
							DB::insert(DB::raw("INSERT INTO bank_advance_dtl(Task_ID,Acc_No,Date_bank,Reference,Remark,Bank_Val,Bal_Acc,Created_By,Last_Update,Department,COA_Lawan,Reference_Lawan,Nilai,Sisa) select Task_ID,Acc_No,'$PTP',Reference,'$Note','$Amountcr',Bank_Acc,'$Saya',NOW(),Department,Acc_No,User_Responsibility,'$Amountcr','$nilaisisa' FROM bank_advance where ID = $cekap->ID"));

					}	
				if ($Payment_Type == 'CASH_ADVANCE'){
					$cekap = DB::table("cash_advance")->where("Task_ID",$Reference_No)->first();

					
						
						$nilaisisa = $cekap->Val_Remain - $Amountcr;
						if ($nilaisisa <= 0){
							$statusx = "CLOSED";
						}else{
							$statusx = "CREATED";
						}
					
						DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
						Task_ID, Location, '$PTP',  Task_ID,Reference, User_Responsibility,User_Responsibility_Name, '$jno', Acc_No,'PUM Karyawan', Task_ID,Reference,'$Note','$Amount','$Amountcr','$Saya','$Saya',NOW(),Department FROM cash_advance where ID = $cekap->ID"));	
						
						DB::update(DB::raw("UPDATE cash_advance SET Val_Used=Val_Used+$Amountcr,Val_Remain=$sisaar,Status='$statusx', Posting = 1, Posting_date = NOW(), Posting_By='$Saya' WHERE ID = $cekap->ID"));
						
							DB::insert(DB::raw("INSERT INTO cash_advance_dtl(Task_ID,Acc_No,Date_bank,Reference,Remark,Bank_Val,Bal_Acc,Created_By,Last_Update,Department,COA_Lawan,Reference_Lawan,Nilai,Sisa) select Task_ID,Acc_No,'$PTP',Reference,'$Note','$Amountcr',Bank_Acc,'$Saya',NOW(),Department,Acc_No,User_Responsibility,'$Amountcr','$nilaisisa' FROM cash_advance where ID = $cekap->ID"));
					
				}elseif ($Payment_Type == 'AR VENDOR'){
					$cekap = DB::table("account_receive")->where("ID",$Reference_No)->first();

					$inv = DB::table("invoice")->where("Inv_No",$cekap->Inv_No)->first();
						
					if ($inv->Invoice_Category == 'MANAGEMENT_FEE'){
					$proforma = DB::table("proforma")->where("Management_Fee",$inv->Inv_No)->first();
					}else{
						$proforma = DB::table("proforma")->where("Invoice_No",$inv->Inv_No)->first();
					}
						$customer = DB::table("customerz")->where("Customer_No",$cekap->Cust_ID)->first();
						$proformadata = DB::table("proforma_detail")->where("Proforma_No",$proforma->Proforma_No)->first();
						$jenis = $proforma->Proforma_Type;
							$acc = "112.1.". $customer->Acc_No_MF;
							$accname = "Piutang Usaha IDR " .$customer->Company_Name;
						
						
						$nilaisisa = $cekap->AR_Remain - $Amountcr;
						if ($nilaisisa <= 0){
							$statusap = "CLOSED";
						}else{
							$statusap = "PARTIAL";
						}
						$kastemer_name = $customer->Company_Name;
						DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
						Contract_No, Location, '$PTP',  Inv_No,'$proformadata->PO_No', Cust_ID,'$kastemer_name', '$jno', '$acc','$accname', '$cekap->Inv_No','$cekap->Ref_Doc','$Note','$Amount','$Amountcr','$Saya','$Saya',NOW(),'BUS' FROM account_receive where ID = $Reference_No"));		
						
					
						$ar = DB::table("account_receive")->where('ID',$Reference_No)->first();
						$sisaar = $ar->AR_Remain - $Amountcr ;
						if ($sisaar > 0){
							$statusar = "PARTIAL";
						}else{
							$statusar = 'CLOSED';
						}
						DB::update(DB::raw("UPDATE account_receive SET PTP = '$PTP',Remark = CONCAT(Remark,'--# ','$Note'),Payment=$Amountcr,AR_Paid=AR_Paid + $Amountcr,AR_Remain=$sisaar,Status='$statusar' WHERE ID = $ar->ID"));
					
					
					
					
				}else{
						
					if ($Payment_Type =='AP'){
					$explod = explode("AP_",$Inv_No);
					$Inv_No = $explod[1];

					
					$arX = DB::table("account_payable")->where("Inv_Vendor",$Inv_No)->first();
						$vendors = DB::table("supplier")->where("Supplier_ID",$arX->Vendor_ID)->first();

						DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
						Task_ID, Location, '$PTP',  Inv_Vendor,Reference, Vendor_ID,'$vendors->Company_Name', '$jno', '211.1','Hutang Usaha IDR', '$Reference_No','$Reference_Desc',CONCAT('$Note ::', Subject_Payment),'$Amount','$Amountcr','$Saya','$Saya',NOW(),Department FROM account_payable  WHERE ID = $arX->ID"));				
					
						$sisaar = $arX->Remain_Val - $Amount ;
						if ($sisaar > 0){
							$statusar = "PARTIAL";
						}else{
							$statusar = 'CLOSED';
						}
						DB::update(DB::raw("UPDATE account_payable SET Payment_Type = '$Payment_Type',Payment_Note = CONCAT(Payment_Note,'--# ','$Note'),Payment_Reference=$Amount,Paid=Paid + $Amount,Remain_Val=$sisaar,Status='$statusar' WHERE ID = $arX->ID"));
						
					}else{
						
						
						DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT 
						'$Inv_No', '$LSP', '$PTP',  '','', Supplier_ID,Company_Name, '$jno', '$Action','', '$Reference_No','$Reference_Desc','$Note','$Amount','$Amountcr','$Saya','$Saya',NOW(),'BUS' FROM supplier where Supplier_ID = '$_GET[ID]' LIMIT 1"));	
					}
				}
				
		
	}else{
		
		if ($Action != ''){
			$skrgs = date('c');
			$arX = DB::table("account_payable")->where("Inv_Vendor",$Reference_No)->first();
			DB::update(DB::raw("UPDATE account_payable SET Status = 'CREATED',Payment_Note =  'REJECT BY $Saya  AT $skrgs  --# $Note' WHERE ID = $arX->ID"));
				
		}	
		
					
	}
					
		
	
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

$jno = "JPM-AP-".date("ymdhis");
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
				$action = updatekan($rowId,$jno,$param);
				break;
			case "deleted":
				$action = updatekan($rowId,$param);
				break;
			case "inserted":
				$action = updatekan($rowId,$jno,$param);
				break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";




?>