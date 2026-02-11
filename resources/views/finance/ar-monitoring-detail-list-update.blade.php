<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo ('<?xml version="1.0" encoding="iso-8859-1"?>');


function updatekan($rowId, $jno, $param)
{

	global $con;
	$Account_No = $_REQUEST['ID'];
	$customer = DB::table("customerz")->where("Customer_No", $Account_No)->first();
	$kastemer_name = $customer->Company_Name;
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$nik =  Auth::user()->Emp_ID;
	$Inv_No = trim($param[$rowId . "_c1"]);
	$PTP = $param[$rowId . "_c2"];
	$Payment_Type = $param[$rowId . "_c3"];
	$Description = $param[$rowId . "_c4"];
	$Amount = $param[$rowId . "_c5"];
	$Amountcr = $param[$rowId . "_c6"];
	$Action = $param[$rowId . "_c7"];
	$Reference_No = $param[$rowId . "_c8"];
	$Payment_Bank_Val = $param[$rowId . "_c9"];

	$Debt_Coll = $param[$rowId . "_c10"];

	$PIC = $param[$rowId . "_c11"]; //pay val
	$PIC_No = $param[$rowId . "_c12"];
	$Note = $param[$rowId . "_c13"];
	$tipe = $param[$rowId . "_c14"];
	$Reference_Desc = $Note;

	$InvAr = explode("INV_", $Inv_No);
	$InvAr = $InvAr[1];

	$ar = DB::table("account_receive")->where("Inv_No", $InvAr)->first();
	if ($ar->Contract_No != '') $isipilihantask = "Contract_No,";
	else $isipilihantask = "AR_No,";
	if ($Payment_Type == 'AP VENDOR') {

		$cekap = DB::table("account_payable")->where("Inv_Vendor", $Reference_No)->first();
		$nilaisisa = $cekap->Remain_Val - $Amount;
		if ($nilaisisa <= 0) {
			$statusap = "CLOSED";
		} else {
			$statusap = "PARTIAL";
		}

		$proforma = DB::table("proforma")->where("Invoice_No", $Inv_No)->first();
		$proformadata = DB::table("proforma_detail")->where("Proforma_No", $proforma->Proforma_No)->first();

		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
		Task_ID, Location, '$PTP',  Inv_Vendor,'$proformadata->PO_No', Vendor_ID,Vendor_Name, '$jno', '211.1','Hutang Usaha IDR', '$Reference_Desc','$Reference_Desc',CONCAT('$Note ::', Subject_Payment),'$Amount','$Amountcr','$Debt_Coll','$Saya',NOW(),'BUS' FROM account_payable where Inv_Vendor = $Reference_No"));


		DB::update(DB::raw("UPDATE account_payable set Remain_Val=$nilaisisa,Status='$statusap',Payment_Type='OFFSET',Payment_Reference='$Inv_No',Payment_Note='$Note' where Inv_Vendor = $Reference_No"));


		DB::insert(DB::raw("INSERT INTO account_payable_payment_log (Task_ID, Payment_Type, Payment_Task_ID, Payment_Reference, Payment_Acc_No, Payment_Value, Note, Reference, Created_By, Created_Date)SELECT 
			
		ID, 'OFFSET', '$Inv_No', '$Inv_No', '$Inv_No', '$Amount', '$Note', Reference, '$Saya', NOW() FROM account_payable where Inv_Vendor = $Reference_No"));
	} else {
		if ($tipe == 'Credit Memorandum (CM)' || $tipe == 'CM') {
			if (substr($Inv_No, 0, 4) == 'INV_') {
				$explod = explode("INV_", $Inv_No);
				$Inv_No = $explod[1];
				if ($Payment_Type == 'CMTOTAL') {
					$prof = DB::table("proforma")->where("Invoice_No", $Inv_No)->first();

					DB::update(DB::raw("UPDATE proforma SET Status='CANCELED' WHERE Invoice_No = '$Inv_No'"));
					DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
					Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
					Task_ID, Location, '$PTP', Document_No, External_Doc, Partner_Code, Partner_Name, 'CM-$jno', Account_No, Account_Name, Reference_No, Reference_Desc, CONCAT('CM TOTAL from Journal No 	',Journal_No,' # ',Remark),
					Debet, Credit,  User_Resp, Created_By, Created_Date,Department FROM ho where Task_ID = '$prof->Task_ID'"));
					DB::update(DB::raw("UPDATE invoice SET Note = CONCAT(Note,'--$Payment_Type-- ','$Note'),Status='CM' WHERE Inv_No = '$Inv_No'"));
					DB::update(DB::raw("DELETE FROM account_receive  WHERE Inv_No='$Inv_No'"));
				} else {

					$getcust = DB::table("customerz")->where("Customer_No", $ar->Cust_ID)->first();
					$accrev = $getcust->Acc_No_MF;


					if ($ar->Cat_AR == 'MANAGEMENT_FEE') {
						$proforma = DB::table("proforma")->where("Invoice_No", $Inv_No)->first();
						$jenis = $proforma->Proforma_Type;

						if ($jenis == 'OUTSOURCING') {
							$coax  = "421." . $accrev;
						} elseif ($jenis == 'OUTTASK') {
							$coax  = "422." . $accrev;
						} else {
							$coax  = "423." . $accrev;
						}
					} else {
						$coax  = "112.4." . $accrev;
					}

					DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
					Credit,Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
						Inv_No, Location, '$PTP',  Inv_No_KSPS,'', Cust_ID,'$kastemer_name', 'CM-$jno', '$Payment_Type','$Description', '$Reference_No',Inv_No_KSPS,CONCAT('$Note ::', Ref_Doc),'$Amount','$Amountcr','$Debt_Coll','$Saya',NOW(),'BUS' FROM account_receive where Inv_No = '$Inv_No'"));

					DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
						Credit,Debet,  User_Resp, Created_By, Created_Date,Department) SELECT 
						'$Inv_No', '$LSP', '$PTP',  '','', Customer_No,Company_Name, 'CM-$jno', '$coax','$Description', '$Reference_No','$Reference_Desc','$Note','$Amountcr','$Amount','$Debt_Coll','$Saya',NOW(),'BUS' FROM customerz where Customer_No = '$_GET[ID]' LIMIT 1"));



					DB::update(DB::raw("UPDATE proforma SET Invoice_No='' WHERE Invoice_No = '$Inv_No'"));
					DB::update(DB::raw("UPDATE invoice SET CM_Note = CONCAT('--$Payment_Type-- ','$Note'),Status='CM' WHERE Inv_No = '$Inv_No'"));
					DB::update(DB::raw("UPDATE invoice_sub_data  SET Task_ID = NULL WHERE Inv_No='$Inv_No'"));
					//DB::update(DB::raw("DELETE FROM account_receive  WHERE Inv_No='$Inv_No'"));
					DB::update(DB::raw("UPDATE account_receive SET Status = 'CANCELED', Remark = CONCAT('--$Payment_Type-- ','$Note') WHERE Inv_No='$Inv_No'"));
				}
			}
		} elseif (substr($Inv_No, 0, 4) == 'INV_') {
			$explod = explode("INV_", $Inv_No);
			$Inv_No = $explod[1];

			$proforma = DB::table("proforma")->where("Invoice_No", $Inv_No)->first();
			$proformadata = DB::table("proforma_detail")->where("Proforma_No", $proforma->Proforma_No)->first();


			if ($proformadata->PO_No != '') $externaldoc = "'$proformadata->PO_No'";
			else $externaldoc = "Inv_No_KSPS";
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
			$isipilihantask Location, '$PTP',  Inv_No_KSPS,$externaldoc, Cust_ID,'$kastemer_name', '$jno', '$Payment_Type','$Description', '$Reference_No',Inv_No_KSPS,CONCAT('$Note ::', Ref_Doc),'$Amount','$Amountcr','$Debt_Coll','$Saya',NOW(),'BUS' FROM account_receive where Inv_No = '$Inv_No'"));

			$sisaar = $ar->AR_Remain - $Amountcr;
			if ($sisaar > 0) {
				$statusar = "PARTIAL";
			} else {
				$statusar = 'CLOSED';
			}
			DB::update(DB::raw("UPDATE account_receive SET PTP = '$PTP',Remark = CONCAT(Remark,'--# ','$Note'),Payment=$Amountcr,AR_Paid=AR_Paid + $Amountcr,AR_Remain=$sisaar,Status='$statusar' WHERE ID = $ar->ID"));
		} else {
			if (substr($Inv_No, 0, 4) != 'CID_') {
				$explod = explode("INV_", $Inv_No);
				$Inv_No = $explod[1];

				DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT 
			'$Inv_No', '$LSP', '$PTP',  '','', Customer_No,Company_Name, '$jno', '$Payment_Type','$Description', '$Reference_No','','$Note','$Amount','$Amountcr','$Debt_Coll','$Saya',NOW(),'BUS' FROM customerz where Customer_No = '$_GET[ID]' LIMIT 1"));
			} else {
				if ($Action == 'CM') {
					$coaCM = 'CM-' . $jno;
				} else {
					$coaCM = $jno;
				}

				DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet,Credit,  User_Resp, Created_By, Created_Date,Department) SELECT 
			'$Inv_No', '$LSP', '$PTP', '','', Customer_No,Company_Name, '$coaCM', '$Payment_Type','$Description', '$Reference_No','$Reference_Desc','$Note','$Amount','$Amountcr','$Debt_Coll','$Saya',NOW(),'BUS' FROM customerz where Customer_No = '$_GET[ID]' LIMIT 1"));
			}
		}
	}





	return "update";
}
function deletekan($rowId, $param)
{
	return "delete";
}
function insertkan($rowId, $param)
{
	$a = $param[$rowId . "_c1"]; // origin
	$b = $param[$rowId . "_c2"]; // destination
	$c = $param[$rowId . "_c3"]; // destination name
	$d = $param[$rowId . "_c4"]; // transport
	$e = $param[$rowId . "_c5"]; // service
	$f = $param[$rowId . "_c6"]; // treshold
	$g = $param[$rowId . "_c7"]; // unit price
	$h = $param[$rowId . "_c8"]; // additional
	$ix = $param[$rowId . "_c9"]; // lead time
	$j = $param[$rowId . "_c10"]; // lead time
	$k = $param[$rowId . "_c11"]; // lead time
	$l = $param[$rowId . "_c12"]; // lead time
	$m = $param[$rowId . "_c13"]; // lead time
	$n = $param[$rowId . "_c14"]; // lead time
	$o = $param[$rowId . "_c15"]; // lead time
	$p = $param[$rowId . "_c16"]; // lead time

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;



	return "insert";
}



echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&", $request_body);
foreach ($ids as $datax) {

	$idenya = explode("=", $datax);
	$param[$idenya[0]] = $idenya[1];
}
$checkids = explode(",", $param["ids"]);

$jno = "JPM-AR-" . date("ymdhis");
$totalpaid = 0;
for ($i = 0; $i < sizeof($checkids); $i++) {
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId . "_!nativeeditor_status"];
	$d = $param[$rowId . "_c4"]; // transport
	$a = $param[$rowId . "_c1"]; // origin
	$Confirm = $param[$rowId . "_c8"];


	switch ($mode) {
		case "updated":

			$action = updatekan($rowId, $jno, $param);




			break;
		case "deleted":
			$action = updatekan($rowId, $param);
			break;
		case "inserted":
			$action = updatekan($rowId, $jno, $param);
			break;
	}

	echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
}
echo "</data>";
