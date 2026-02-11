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

$tanggal1 = $_GET['Tgl1'];
$tanggal2 = $_GET['Tgl2'];

$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$Saya = Auth::user()->username;
$nomor = 1;
if ($priv == 'FIN') {
	$Fetchx = DB::select(DB::raw("SELECT * FROM bank where (Bank_Type='BANK_OUT' OR Bank_Type='BANK_OUT_REIMBURSEMENT') AND Created_Date BETWEEN '$tanggal1' and '$tanggal2' ORDER BY ID DESC"));
} elseif ($priv != 'ADM') {
	$Fetchx = DB::select(DB::raw("SELECT * FROM bank where (Bank_Type='BANK_OUT' OR Bank_Type='BANK_OUT_REIMBURSEMENT') AND Created_By='$Saya' AND Created_Date BETWEEN '$tanggal1' and '$tanggal2' ORDER BY ID DESC"));
} else {
	$Fetchx = DB::select(DB::raw("SELECT * FROM bank where (Bank_Type='BANK_OUT' OR Bank_Type='BANK_OUT_REIMBURSEMENT') AND Created_Date BETWEEN '$tanggal1' and '$tanggal2' ORDER BY ID DESC"));
}
echo "<rows>";

foreach ($Fetchx as $Fetch) {

	$nilai = $kursval[$Fetch->Currency];
	if ($nilai == '') $nilai = 1;
	echo "<row id=\"$Fetch->ID\">";

	if($Fetch->Bank_Type =='BANK_OUT_REIMBURSEMENT') {
		$id2 = DB::table("reimbursement")->where("Reimbursement_No", "=","$Fetch->Reference")->first();
		$pengajuan = $id2->submitted_date;
		$note = $id2->Description;
	} else {
		$pengajuan = '0000-00-00';
		$bankDtl = DB::table("bank_dtl")->where("Bank_No", $Fetch->ID)->orderBy('ID')->first();
		$note = $bankDtl->Remark;
	}

 
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Bank_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Bank_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Bank_Acc) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Currency) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Total * $nilai) . "</cell>";
	echo "<cell>" . htmlspecialchars($pengajuan) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Created_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Created_By) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Reference) . "</cell>";
	echo "<cell>" . htmlspecialchars($note) . "</cell>";
	echo "</row>";

	$nomor++;
}


echo "</rows>";
