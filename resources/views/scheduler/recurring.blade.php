<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

if ($_SERVER['REMOTE_ADDR'] != "::1") {
	error_log("Url Recurring dicoba akses dari IP " . $_SERVER['REMOTE_ADDR']);
	exit(0);
}
$skr = date("Y-m-d");
$datax = DB::table("recurring_master")->where("Period_Times", ">", "Exec_Times")->where("Status", "CREATED")->get();

if ($datax) {
	foreach ($datax as $data) {

		if ($data->Period_Times > $data->Exec_Times) {
			$jno = "JRCRA-" . $data->Recurring_ID . "-" . rand(1111999, 9999999);
			$cekdetail = DB::table("recurring_detail")->where("Journal_No", $data->Recurring_ID)->get();


			if ($data->Last_Recurring_Date != '0000-00-00' && $data->Last_Recurring_Date != null) {

				$tanggal = $data->Recurring_Date;
			} else {
				$tanggal = $data->Start_Recurring_Date;
			}

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
		}
	}
	error_log("#RECURRING :  execution recurring => $data->Recurring_ID at $skr");
	echo "excecuted recurring $data->Recurring_ID at $skr ";
} else {

	error_log("#RECURRING : FAILED RECURRING PROCESS :: NO DATA");
	echo "#RECURRING : FAILED RECURRING PROCESS :: NO DATA";
}
