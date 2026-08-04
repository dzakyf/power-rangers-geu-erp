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
$PRApproval = Auth::user()->PR_Approval;
$Saya = Auth::user()->username;
echo "<rows>";


$kueri = DB::select(DB::raw("SELECT * FROM purchase_request WHERE PO_No IS NULL AND Location = '$LSP' ORDER BY ID DESC"));
$nomor = 1;

foreach ($kueri as $Fetch) {
	$ps = DB::table("purchase_summary")->where("PR_No", $Fetch->PR_No)->first();
	switch ($Fetch->Status) {
		case 'CREATED':
			$status = 'PR CREATED';
			break;
		case 'PROPOSED':
			$status = 'PR PROPOSED';
			break;
		case 'APPROVAL_UPPER':
			$status = 'PR APPROVAL ATASAN';
			break;
		case 'APPROVAL_FINANCE':
			$status = 'PR APPROVAL FINANCE';
			break;
		case 'APPROVAL_GA':
			$status = 'PR APPROVAL SUPPLY CHAIN';
			break;
		case 'APPROVED':
			switch ($ps->Status) {
				case 'CREATED':
					$status = 'PO ON PROGRESS';
					break;
				case 'CANCELED':
					$status = 'PO PROGRESS CANCELED';
					break;
				case 'PROPOSED':
					$status = 'INPUT HARGA dan VENDOR';
					break;
				case 'VERIFIED':
					$status = 'WAITING APPROVAL SVP';
					break;
				case 'DIRECTOR_APPROVAL':
					$status = 'WAITING APPROVAL DIRECTOR';
					break;
				case 'CLOSED':
					$status = 'PO TERBIT';
					break;
				default:
					$status = 'OUTSTANDING PO';
					break;
			}
			break;
		default:
			$status = 'CANCELED';
			break;
	}
	if ($ps->Status == "CLOSED") {
		$aging = 0;
	} else {
		$skrg = strtotime(date('c'));
		$buat = strtotime($Fetch->Created_Date);
		$kurangi = ($skrg - $buat) / 86400;
		$aging = round($kurangi, 0);
		$warna = "";
	}



	$user = DB::table("users")->where("username", $Fetch->Created_By)->first();

	$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", 'GEA')->first();
	$cekMail = DB::table("mail_logs")->where('Email_To', 'LIKE', '%' . $getupper->Email . '%')->where('Subject', 'LIKE', '%' . $Fetch->PR_No . '%')->orderBy('ID', 'DESC')->first();

	echo "<row id=\"$Fetch->PR_No\" $warna>";
	echo "<cell $warna>$nomor</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->PR_No) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->PR_Date) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($cekMail->Send_Date) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->Category) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->Sub_Category) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->Supplies_Type) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($user->Name) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->PIC) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->Note) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($status) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($aging)) . "</cell>";
	echo "</row>";

	$nomor++;
}
$nomor++;

$kueri = DB::select(DB::raw("SELECT * FROM purchase_request where (PO_Task_No='' or PO_Task_No IS NULL) AND Status!='CANCELED' ORDER BY ID DESC"));

foreach ($kueri as $Fetch) {

	$skrg = strtotime(date('c'));
	$buat = strtotime($Fetch->PR_Date);
	$kurangi = ($skrg - $buat) / 86400;
	$aging = round($kurangi, 0);
	$warna = "";
	$Row_Trx = DB::table("purchase_request")->where("PR_No", $Fetch->PR_No)->first();
	$Row_Usr = DB::select(DB::raw("SELECT Name, Department FROM users WHERE username='" . $Row_Trx->User_Trx . "'"));


	echo "<row id=\"$Fetch->Task_ID\" $warna>";
	echo "<cell $warna>$nomor</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Fetch->PR_No)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Fetch->PR_Date)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Fetch->Category)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Fetch->Sub_Category)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Fetch->Supplies_Type)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Fetch->Supplies_Type)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Row_Trx->PIC)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Row_Trx->Reference)) . "</cell>";
	echo "<cell $warna>UNDEFINED</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Fetch->Note)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Fetch->Status)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($Fetch->Created_Date)) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars(($aging)) . "</cell>";
	echo "</row>";

	$nomor++;
}
echo "</rows>";
