<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo ('<?xml version="1.0" encoding="iso-8859-1"?>');


function updatekan($rowId, $param)
{

	$cc = $param[$rowId . "_c0"]; // origin
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

	
$inv = DB::table("invoice")->where("Inv_No", $rowId)->first();




if ($inv->Invoice_Type == 'MANAGEMENT_FEE') {
	$proforma = DB::table("proforma")->where("Proforma_No", $inv->Task_ID)->first();

	$proformadata = DB::table("proforma_detail")->where("Proforma_No", $proforma->Proforma_No)->first();
	$oppor = DB::table("opportunity")->where("Task_ID", $proformadata->Task_ID)->first();

	$jno = "CM-JPM-INVM-" . date("ymdhis");
	$getcust = DB::table("customerz")->where("Customer_No", $oppor->Account_No)->first();
	$accrev = $getcust->Acc_No_MF;

	$periode = $inv->periode_txt == null ?  $proforma->Periode : $inv->periode_txt;
	$remarkSubject = "CANCELED::".$inv->Invoice_Subject . ' Periode ' . $periode;

	$ppn = $inv->PPN;
	$total = $inv->Total;
	$materai = $inv->Materai;


	$jenis = $proforma->Proforma_Type;

	if ($jenis == 'OUTSOURCING') {
		$acc = "112.1.";
		$accname = "Piutang Usaha OS " . $getcust->Company_Name;
		$coax  = "421." . $accrev;
		$coaxname = "Management Fee OS " . $getcust->Company_Name;
	} elseif ($jenis == 'OUTTASK') {
		$acc = "112.1.";
		$accname = "Piutang Usaha  OT " . $getcust->Company_Name;
		$coax  = "422." . $accrev;
		$coaxname = "Management Fee OT " . $getcust->Company_Name;
	} else {
		$acc = "112.1.";
		$accname = "Piutang Usaha  Project " . $getcust->Company_Name;
		$coax  = "423." . $accrev;
		$coaxname = "Management Fee Project " . $getcust->Company_Name;
	}
	$acc .= $getcust->Acc_No_MF;

	DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Credit,  Debet, User_Resp, Created_By, Created_Date,Department) SELECT
			'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$acc','$accname', '$inv->Inv_No','','$remarkSubject',$total,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));

	DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
			'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$coax','$coaxname', '$inv->Inv_No','','$remarkSubject',$inv->Subtotal,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));


	if ($ppn > 0) {
		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
			'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '212.8','PPN Keluaran', '$inv->Faktur_No','','$remarkSubject',$inv->PPN,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
	}
	if ($materai > 0) {
		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
			'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '611.2','Pendapatan Materai', '$inv->Inv_No','','$remarkSubject',$inv->Materai,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
	}
} else {
	if (strpos($inv->Task_ID, "UNION") != false) {
		$batch =  explode(",", $inv->Note);
		$jno = "CM-JPM-INV-" . date("ymdhis");


		$periode = $inv->periode_txt == null ?  date("F Y") : $inv->periode_txt;
		$remarkSubject = "CANCELED::".$inv->Invoice_Subject . ' Periode ' . $periode;
		$ambilCustomer = DB::table("customerz")->where("Customer_No", $inv->Account_No)->first();
		$accCus = "112.1.";
		$accCus .= $ambilCustomer->Acc_No_MF;

		$ppn = $inv->PPN;
		$materai = $inv->Materai;
		$mgm_fee = $inv->mgm_fee;

		$total_piutang = 0 + $inv->PPN + $inv->Materai + $inv->mgm_fee;



		foreach ($batch as $key => $value) {
			$proforma = DB::table("proforma")->where("Proforma_No", $value)->first();
			$proformadata = DB::table("proforma_detail")->where("Proforma_No", $proforma->Proforma_No)->first();
			$oppor = DB::table("opportunity")->where("Task_ID", $proformadata->Task_ID)->first();



			$getcust = DB::table("customerz")->where("Customer_No", $oppor->Account_No)->first();
			$accrev = $getcust->Acc_No_MF;
			$coax  = "112.4." . $accrev;

			$coaxname = "Piutang Usaha Belum Tertagih";

			$jenis = $proforma->Proforma_Type;

			if ($jenis == 'OUTSOURCING') {
				$acc = "112.1.";
				$accname = "Piutang Usaha OS " . $getcust->Company_Name;
			} elseif ($jenis == 'OUTTASK') {
				$acc = "112.1.";
				$accname = "Piutang Usaha  OT " . $getcust->Company_Name;
			} else {
				$acc = "112.1.";
				$accname = "Piutang Usaha  Project " . $getcust->Company_Name;
			}
			$acc .= $getcust->Acc_No_MF;


			// DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			// 	Debet,  Credit, User_Resp, Created_By, Created_Date,Department) SELECT
			// 		'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$acc','$accname', '$inv->Inv_No','','$remarkSubject',$proforma->Total,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));

			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
				Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
					'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$coax','$coaxname', '$inv->Inv_No','','$remarkSubject',$proforma->Total,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));

			$total_piutang += $proforma->Total;
		}

		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet, Credit,  User_Resp, Created_By, Created_Date,Department) VALUES ('$inv->Task_ID', 'GEU-HO', NOW(),  '$inv->Inv_KSPS','', '$inv->Account_No','$inv->Account_Name',  '$jno', '$accCus','', '$inv->Inv_No','$inv->Note','$remarkSubject',0,$total_piutang,'','$Saya',NOW(),'BUS')"));

		if ($mgm_fee > 0) {
			if ($jenis == 'OUTSOURCING') {
				$coax1  = "421." . $accrev;
				$coaxname1 = "Management Fee OS " . $getcust->Company_Name;
			} elseif ($jenis == 'OUTTASK') {
				$coax1 = "422." . $accrev;
				$coaxname1 = "Management Fee OT " . $getcust->Company_Name;
			} else {
				$coax1  = "423." . $accrev;
				$coaxname1 = "Management Fee Project " . $getcust->Company_Name;
			}

			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet,Credit,User_Resp, Created_By, Created_Date,Department) VALUES (
				'$inv->Task_ID', 'GEU-HO', NOW(),  '$inv->Inv_KSPS','', '$inv->Account_No','$inv->Account_Name', '$jno', '$coax1','$coaxname1', '$inv->Inv_No','$inv->Note','$remarkSubject',$mgm_fee,0,'','$Saya',NOW(),'BUS')"));
		}

		if ($ppn > 0) {
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet, Credit,  User_Resp, Created_By, Created_Date,Department) VALUES ('$inv->Task_ID', 'GEU-HO', NOW(),  '$inv->Inv_KSPS','', '$inv->Account_No','$inv->Account_Name',  '$jno', '212.8','PPN Keluaran', '$inv->Faktur_No','$inv->Note','$remarkSubject',$inv->PPN,0,'','$Saya',NOW(),'BUS')"));
		}
		if ($materai > 0) {
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet, Credit,  User_Resp, Created_By, Created_Date,Department)  VALUES ('$inv->Task_ID', 'GEU-HO', NOW(),  '$inv->Inv_KSPS','','$inv->Account_No','$inv->Account_Name',  '$jno', '611.2','Pendapatan Materai', '$inv->Inv_No','$inv->Note','$remarkSubject',$inv->Materai,0,'','$Saya',NOW(),'BUS')"));
		}
	} else {
		$proforma = DB::table("proforma")->where("Proforma_No", $inv->Task_ID)->first();
		$proformadata = DB::table("proforma_detail")->where("Proforma_No", $proforma->Proforma_No)->first();
		$oppor = DB::table("opportunity")->where("Task_ID", $proformadata->Task_ID)->first();

		$periode = $inv->periode_txt == null ?  $proforma->Periode : $inv->periode_txt;
		$remarkSubject = "CANCELED::".$inv->Invoice_Subject . ' Periode ' . $periode;

		$jno = "CM-JPM-INV-" . date("ymdhis");
		$getcust = DB::table("customerz")->where("Customer_No", $oppor->Account_No)->first();
		$accrev = $getcust->Acc_No_MF;
		$coax  = "112.4." . $accrev;

		$coaxname = "Piutang Usaha Belum Tertagih";
		$ppn = $inv->PPN;
		$total = $inv->Total;
		$materai = $inv->Materai;
		$mgm_fee = $inv->mgm_fee;


		$jenis = $proforma->Proforma_Type;

		if ($jenis == 'OUTSOURCING') {
			$acc = "112.1.";
			$accname = "Piutang Usaha OS " . $getcust->Company_Name;
		} elseif ($jenis == 'OUTTASK') {
			$acc = "112.1.";
			$accname = "Piutang Usaha  OT " . $getcust->Company_Name;
		} else {
			$acc = "112.1.";
			$accname = "Piutang Usaha  Project " . $getcust->Company_Name;
		}
		$acc .= $getcust->Acc_No_MF;

		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Credit,  Debet, User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$acc','$accname', '$inv->Inv_No','','$remarkSubject',$total,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));

		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$coax','$coaxname', '$inv->Inv_No','','$remarkSubject',$inv->Subtotal,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
		if ($mgm_fee > 0) {
			if ($jenis == 'OUTSOURCING') {
				$coax1  = "421." . $accrev;
				$coaxname1 = "Management Fee OS " . $getcust->Company_Name;
			} elseif ($jenis == 'OUTTASK') {
				$coax1 = "422." . $accrev;
				$coaxname1 = "Management Fee OT " . $getcust->Company_Name;
			} else {
				$coax1  = "423." . $accrev;
				$coaxname1 = "Management Fee Project " . $getcust->Company_Name;
			}

			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet,Credit,User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$coax1','$coaxname1', '$inv->Inv_No','','$remarkSubject',$mgm_fee,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
		}

		if ($ppn > 0) {
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '212.8','PPN Keluaran', '$inv->Faktur_No','','$remarkSubject',$inv->PPN,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
		}
		if ($materai > 0) {
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, NOW(),  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '611.2','Pendapatan Materai', '$inv->Inv_No','','$remarkSubject',$inv->Materai,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
		}
	}
}
DB::update(DB::raw("UPDATE invoice SET Status='CANCELED',CM_Note = '$o',Canceled_By='$Saya',Canceled_Date=NOW() WHERE Inv_No='$inv->Inv_No'"));
if ($cc == 'ALL'){
	$proforma = DB::table("proforma")->where("Proforma_No", $inv->Task_ID)->first();
	DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
				Task_ID, Location, NOW(), Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Credit, Debet,  User_Resp, '$Saya', NOW(),Department FROM ho where Task_ID = $proforma->Proforma_No"));

	DB::update(DB::raw("UPDATE proforma SET Status='CANCELED',Invoice_No='' WHERE Invoice_No = '$inv->Inv_No'"));
	
}else{
	DB::update(DB::raw("UPDATE proforma SET Invoice_No='' WHERE Invoice_No = '$inv->Inv_No'"));
	
}
DB::update(DB::raw("UPDATE invoice_sub_data  SET Task_ID = NULL WHERE Inv_No='$inv->Inv_No'"));
//DB::update(DB::raw("DELETE FROM account_receive  WHERE Inv_No='$Inv_No'"));
DB::update(DB::raw("UPDATE account_receive SET Status = 'CANCELED', Remark = '$o' WHERE Inv_No='$inv->Inv_No'"));



	return "update";
}
function deletekan($rowId, $param)
{

    return "delete";
}

function insertkan($rowId, $param)
{
	
	return "insert";
}



echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&", $request_body);
foreach ($ids as $datax) {

	$idenya = explode("=", $datax);
	if (preg_match('/(gamp;)/', $idenya[1])) {
		$replace = str_replace('gamp;', '&', $idenya[1]);
		$param[$idenya[0]] = $replace;
	} else {
		$param[$idenya[0]] = $idenya[1];
	}
}
$checkids = explode(",", $param["ids"]);
$Task_ID = $_GET['Task_ID'];

for ($i = 0; $i < sizeof($checkids); $i++) {
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId . "_!nativeeditor_status"];
	$d = $param[$rowId . "_c4"]; // transport
	$a = $param[$rowId . "_c1"]; // origin
	$total +=  $param[$rowId . "_c5"] *  $param[$rowId . "_c6"];
	switch ($mode) {
		case "updated":
			$action = updatekan($rowId, $param);
			break;
		case "deleted":
			$action = updatekan($rowId, $param);
			break;
		case "inserted":
			$action = insertkan($rowId, $param);
			break;
	}

	echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
}
echo "</data>";

