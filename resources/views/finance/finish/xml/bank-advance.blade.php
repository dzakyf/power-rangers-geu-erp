<?php
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
	header("Content-type: application/xhtml+xml");
} else {
	header("Content-type: text/xml");
}
echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");



$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$Saya = Auth::user()->username;
$nomor = 1;


$Fetchx = DB::select(DB::raw("SELECT * FROM bank_advance WHERE Status = 'CLOSED' ORDER BY ID DESC"));

echo "<rows>";

foreach ($Fetchx as $Fetch) {
	$str = strtotime($Fetch->Advance_Date);
	$nw = strtotime(date('c'));

	if ($Fetch->Posting_Date == null) {
		$sisa = $nw - $str;
		$days = round($sisa / 86400);
	} else {
		$posting = strtotime($Fetch->Posting_Date);
		$sisa = $posting - $str;
		$days = round($sisa / 86400);
	}

	if ($Fetch->submitted_date == "0000-00-00 00:00:00" || $Fetch->submitted_date == null) {
		$date = DB::table("mail_logs")->where("Contents", 'LIKE', "%$Fetch->Task_ID%")->orderBy('ID', 'DESC')->first();

		if ($date) {
			$Submitted = $date->Send_Date;
		} else {
			$Submitted = $Fetch->Date_Payment;
		}
	} else {
		$Submitted = $Fetch->submitted_date;
	}

	$detail =  $id2 = DB::table("bank_advance_dtl")->where("Task_ID", "=", $Fetch->Task_ID)->first();

	echo "<row id=\"$Fetch->Task_ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Task_ID) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Bank_Account) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Description) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->User_Responsibility_Name) . " # " . $Fetch->User_Responsibility_ID . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Bank_Val) . "</cell>";
	echo "<cell>" . htmlspecialchars($days) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Advance_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Submitted) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Date_Payment) . "</cell>";
	echo "<cell>" . htmlspecialchars($detail->Date_Cash) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Reference) . "</cell>";

	echo "</row>";

	$nomor++;
}


echo "</rows>";
