<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");
function Filter($C) {
	$DataFilter = preg_replace('/[^(\x20-\x7F)]*/','', $C);
	return htmlspecialchars($DataFilter);
}

function Bersih($Data){
	$X = trim($Data);
	$X2 = Filter($X);
	$X2 = preg_replace('/[^(\x20-\x7F)]*/','', $X2);
	return $X2;
}

$saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$q = DB::table("reimbursement")->orderBy("ID","Desc")->get();
$nomor = 1;
echo "<rows>";
foreach($q as $Fetch){

	$str = strtotime($Fetch->Reimbursement_Date);
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
		$date = DB::table("mail_logs")->where("Subject", 'LIKE', "%$Fetch->Task_ID%")->orderBy('ID', 'DESC')->first();

		if ($date) {
			$Submitted = $date->Send_Date;
		} else {
			$Submitted = $Fetch->Payment_Date;
		}
	} else {
		$Submitted = $Fetch->submitted_date;
	}

	if($Fetch->Status == "CREATED") {
		$Submitted = $Submitted;
	} else {
		$Submitted = "0000-00-00";
	}
	echo "<row id=\"$Fetch->Reimbursement_No\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reimbursement_No)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reimbursement_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->User_Reference)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reference_No)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Total)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->User_Responsibility_Name)."</cell>";
		echo "<cell>".htmlspecialchars($Submitted)."</cell>";
		echo "<cell>".htmlspecialchars($days)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Currency)."</cell>";

	echo "</row>";
	$nomor++;
}

echo "</rows>";
