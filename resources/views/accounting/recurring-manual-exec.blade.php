<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$data = DB::table("recurring_master")->where("Period_Times", ">", "Exec_Times")->where("Status", "CREATED")->where("ID", $_GET['ID'])->first();

if ($data) {

	$jno = "JRCR-" . $data->Recurring_ID . "-" . rand(1111999, 9999999);
	$cekdetail = DB::table("recurring_detail")->where("Journal_No", $data->Recurring_ID)->get();


	if ($data->Last_Recurring_Date != '0000-00-00' && $data->Last_Recurring_Date != null) {

		$tanggal = $data->Recurring_Date;
	} else {
		$tanggal = $data->Start_Recurring_Date;
	}

	// $ymt = date("Y-m-t", strtotime($tanggal));
	// $pecah = explode("-", $tanggal);
	// $thn = $pecah[0];
	// $bln = $pecah[1];
	// $tgl = $pecah[2];
	// if ($ymt == $tanggal) {
	// 	$ymawal = date("Y-m-01", strtotime($tanggal));
	// 	$tanggal = date("Y-m-t", strtotime($ymawal . " +1 month"));
	// } else {
	// 	if ((int)$tgl > 28 && (int)$bln == 2) {
	// 		$tanggal = date("Y-m-t", strtotime("$thn-$bln-01 +1 month"));
	// 	} else {
	// 		if ((int)$tgl == 31 || (int)$tgl == 30) {
	// 			$tanggal = date("Y-m-t", strtotime("$thn-$bln-01 +1 month"));
	// 		} else {
	// 			$mx = date("Y-m-t", strtotime("$thn-$bln-01 +1 month"));
	// 			$tanggal = date("Y-m-$tgl", strtotime($mx));
	// 		}
	// 	}
	// }
	$start = new DateTime($tanggal);
	$start->modify("first day of next month");
	$nextDate = $start->format("Y-m-t");
	foreach ($cekdetail as $dx) {
		DB::insert(DB::raw("INSERT INTO $data->Table_Target(Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		   Debet,  Credit, User_Resp, Created_By, Created_Date,Department) SELECT
			   Task_ID, Location, '$data->Recurring_Date', Document_No, External_Doc, Partner_Code, Partner_Name, '$jno', Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		   Debet,  Credit, Created_By, 'RECURRING-SYSTEM', NOW(),Department FROM recurring_detail where ID = $dx->ID"));
	}

	DB::update(DB::raw("UPDATE recurring_master set Exec_Times=Exec_Times+1,Last_Process=NOW(),Last_Recurring_Date='$data->Recurring_Date', Recurring_Date='$nextDate' WHERE ID=$data->ID"));


	echo "<script>alert('Recurring Executed for Task No $data->Task_ID! $nextDate');location.href='accounting-recurring-data';</script>";
} else {

	echo "<script>alert('Selected Recurring Failed to Execute!');</script>";
}
