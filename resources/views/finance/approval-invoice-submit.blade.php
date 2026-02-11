<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$ars = "";
$ax = "";
$isi = "";
foreach ($_POST as $key => $value) {

	if ($key != 'ID' && $key != 'SubmitTransfer') {
		$ars .= "$key = '$value',";
		$ax .= "\"$key\",";
		$isi .= "'$value',";
	}
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

function nextFaktur()
{
	$LSP = Auth::user()->Location;

	$fetch = DB::table("faktur")->where("Status", 'ACTIVE')->where("Location", $LSP)->first();
	if ($fetch->faktur_end == $fetch->Current_No) {
		$pajak = "ERROR";
		DB::update(DB::raw("UPDATE faktur SET Status='INACTIVE' WHERE ID=$fetch->ID"));
	} else {
		if ($fetch->Current_No == '' || $fetch->Current_No == 0) {

			$next = $fetch->faktur_start;
			$next = sprintf("%08d", $next);
			$pajak = $fetch->faktur_prefix . $next;
		} else {
			$next = $fetch->Current_No + 1;
			$next = sprintf("%08d", $next);
			$pajak = $fetch->faktur_prefix . $next;
		}
	}
	return $pajak;
}
$AR_No = "AR-" . date('ymdhis') . substr(md5(date('c')), -5);

DB::insert(DB::raw("INSERT INTO `account_receive` (Location,Contract_No,Cust_ID,Inv_No,Ref_Doc,Cat_AR,AR_No,AR_Date,AR_Val,TOP,Status,AR_Remain,User_Trx,Date_Trx,Inv_Date,Inv_No_KSPS,Due_Date) select Location,Task_ID,Account_No,Inv_No,Invoice_Subject,Invoice_Type,'$AR_No',Inv_Date,Total,'30','RECEIVED',Total,Created_By,Created_Date,Inv_Date,Inv_KSPS,jatuh_tempo FROM invoice where Inv_No='$_POST[Inv_No]'"));

// if ($_POST['PPN'] > 0){
// $VAT_Prefix = "040";

// $faktur = nextFaktur();
// $pajak = $VAT_Prefix . "." . $faktur;
// $exp = explode(".",$pajak);
// $currno = $exp[2];

// DB::insert(DB::raw("INSERT INTO faktur_list(No_Faktur,Reference,Used_By,number_struct,Location) VALUES
// 	('$pajak','$_POST[Inv_No]','$Saya','$faktur','$LSP')"));
// DB::update(DB::raw("update invoice set Faktur_No='$faktur',Faktur_Date=NOW(),Status='APPROVED',AR_No='$AR_No',AR_Status='CREATED' WHERE Inv_No='$_POST[Inv_No]'"));

// DB::update(DB::raw("UPDATE faktur set Current_No='$currno',used = used + 1,unused = unused-1","Status='ACTIVE' AND Location='$LSP'"));
// }else{
// 	DB::update(DB::raw("update invoice set Faktur_Date=NOW(),Status='APPROVED' WHERE Inv_No='$_POST[Inv_No]'"));


// }

DB::update(DB::raw("update invoice set Faktur_Date=NOW(),Status='APPROVED' WHERE Inv_No='$_POST[Inv_No]'"));


$inv = DB::table("invoice")->where("Inv_No", $_POST['Inv_No'])->first();


// DB::update(DB::raw("UPDATE invoice_sub_data SET Inv_No='$inv->Inv_No' WHERE Task_ID='$inv->Task_ID'"));



if ($inv->Invoice_Type == 'MANAGEMENT_FEE') {
	$proforma = DB::table("proforma")->where("Proforma_No", $inv->Task_ID)->first();

	$proformadata = DB::table("proforma_detail")->where("Proforma_No", $proforma->Proforma_No)->first();
	$oppor = DB::table("opportunity")->where("Task_ID", $proformadata->Task_ID)->first();

	$jno = "JPM-INVM-" . date("ymdhis");
	$getcust = DB::table("customerz")->where("Customer_No", $oppor->Account_No)->first();
	$accrev = $getcust->Acc_No_MF;

	$periode = $inv->periode_txt == null ?  $proforma->Periode : $inv->periode_txt;
	$remarkSubject = $inv->Invoice_Subject . ' Periode ' . $periode;

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
		Debet,  Credit, User_Resp, Created_By, Created_Date,Department) SELECT
			'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$acc','$accname', '$inv->Inv_No','','$remarkSubject',$total,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));

	DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Credit, Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
			'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$coax','$coaxname', '$inv->Inv_No','','$remarkSubject',$inv->Subtotal,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));


	if ($ppn > 0) {
		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Credit, Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
			'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '212.8','PPN Keluaran', '$inv->Inv_No','','$remarkSubject',$inv->PPN,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
	}
	if ($materai > 0) {
		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Credit, Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
			'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '611.2','Pendapatan Materai', '$inv->Inv_No','','$remarkSubject',$inv->Materai,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
	}
} else {
	if (strpos($inv->Task_ID, "UNION") != false) {
		$batch =  explode(",", $inv->Note);
		$jno = "JPM-INV-" . date("ymdhis");


		$periode = $inv->periode_txt == null ?  date("F Y") : $inv->periode_txt;
		$remarkSubject = $inv->Invoice_Subject . ' Periode ' . $periode;
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
			// 		'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$acc','$accname', '$inv->Inv_No','','$remarkSubject',$proforma->Total,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));

			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
				Credit, Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
					'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$coax','$coaxname', '$inv->Inv_No','','$remarkSubject',$proforma->Total,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));

			$total_piutang += $proforma->Total;
		}

		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Credit, Debet,  User_Resp, Created_By, Created_Date,Department) VALUES ('$inv->Task_ID', 'GEU-HO', '$inv->Inv_Date',  '$inv->Inv_KSPS','', '$inv->Account_No','$inv->Account_Name',  '$jno', '$accCus','', '$inv->Inv_No','$inv->Note','$remarkSubject',0,$total_piutang,'','$Saya',NOW(),'BUS')"));

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
			Credit,Debet,User_Resp, Created_By, Created_Date,Department) VALUES (
				'$inv->Task_ID', 'GEU-HO', '$inv->Inv_Date',  '$inv->Inv_KSPS','', '$inv->Account_No','$inv->Account_Name', '$jno', '$coax1','$coaxname1', '$inv->Inv_No','$inv->Note','$remarkSubject',$mgm_fee,0,'','$Saya',NOW(),'BUS')"));
		}

		if ($ppn > 0) {
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Credit, Debet,  User_Resp, Created_By, Created_Date,Department) VALUES ('$inv->Task_ID', 'GEU-HO', '$inv->Inv_Date',  '$inv->Inv_KSPS','', '$inv->Account_No','$inv->Account_Name',  '$jno', '212.8','PPN Keluaran', '$inv->Inv_No','$inv->Note','$remarkSubject',$inv->PPN,0,'','$Saya',NOW(),'BUS')"));
		}
		if ($materai > 0) {
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Credit, Debet,  User_Resp, Created_By, Created_Date,Department)  VALUES ('$inv->Task_ID', 'GEU-HO', '$inv->Inv_Date',  '$inv->Inv_KSPS','','$inv->Account_No','$inv->Account_Name',  '$jno', '611.2','Pendapatan Materai', '$inv->Inv_No','$inv->Note','$remarkSubject',$inv->Materai,0,'','$Saya',NOW(),'BUS')"));
		}
	} else {
		$proforma = DB::table("proforma")->where("Proforma_No", $inv->Task_ID)->first();
		$proformadata = DB::table("proforma_detail")->where("Proforma_No", $proforma->Proforma_No)->first();
		$oppor = DB::table("opportunity")->where("Task_ID", $proformadata->Task_ID)->first();

		$periode = $inv->periode_txt == null ?  $proforma->Periode : $inv->periode_txt;
		$remarkSubject = $inv->Invoice_Subject . ' Periode ' . $periode;

		$jno = "JPM-INV-" . date("ymdhis");
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
			Debet,  Credit, User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$acc','$accname', '$inv->Inv_No','','$remarkSubject',$total,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));

		DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Credit, Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$coax','$coaxname', '$inv->Inv_No','','$remarkSubject',$inv->Subtotal,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
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
			Credit,Debet,User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '$coax1','$coaxname1', '$inv->Inv_No','','$remarkSubject',$mgm_fee,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
		}

		if ($ppn > 0) {
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Credit, Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '212.8','PPN Keluaran', '$inv->Inv_No','','$remarkSubject',$inv->PPN,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
		}
		if ($materai > 0) {
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			Credit, Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
				'$proformadata->Task_ID', Location, '$inv->Inv_Date',  '$inv->Inv_KSPS','$proforma->BA_No', Account_No,Account_Name, '$jno', '611.2','Pendapatan Materai', '$inv->Inv_No','','$remarkSubject',$inv->Materai,0,Created_NIK,'$Saya',NOW(),'BUS' FROM proforma where ID = $proforma->ID"));
		}
	}
}

echo "success";
