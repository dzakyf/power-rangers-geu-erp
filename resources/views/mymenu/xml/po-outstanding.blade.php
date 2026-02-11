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
$Nik =  Auth::user()->Emp_ID;
echo "<rows>";

$struktur = DB::table("organization_structure")->where("NIK", $Nik)->first();
if ($struktur->Level1 == 1) $lepel = 1;
else if ($struktur->Level2 == 1) $lepel = 2;
else if ($struktur->Level3 == 1) $lepel = 3;
else if ($struktur->Level4 == 1) $lepel = 4;
else if ($struktur->Level5 == 1) $lepel = 5;
else if ($struktur->Level6 == 1) $lepel = 6;
else if ($struktur->Level7 == 1) $lepel = 7;
else if ($struktur->Level8 == 1) $lepel = 8;

if ($lepel >= 4) {
	$kueri = DB::select(DB::raw("SELECT * FROM purchase_request where User_Request_Name = '$Saya' ORDER BY ID DESC"));
} elseif ($lepel == 3) {
	$kueri = DB::select(DB::raw("SELECT * FROM purchase_request where Department = '$struktur->Department' ORDER BY ID DESC"));
} else {
	$kueri = DB::select(DB::raw("SELECT * FROM purchase_request ORDER BY ID DESC"));
}


$nomor = 1;

foreach ($kueri as $Fetch) {

	$skrg = strtotime(date('c'));
	$buat = strtotime($Fetch->Created_Date);
	$kurangi = ($skrg - $buat) / 86400;
	$aging = round($kurangi, 0);
	$warna = "";
	if ($Fetch->Priority == 'URGENT') $warna = "style='background-color:#ffd700'";


	//$user = DB::table("user")->where("Emp_ID",$Fetch->User_Request)->first();
	//$Row_Usr = DB::select(DB::raw("SELECT Name, Department FROM users WHERE username='".$Row_Trx->User_Trx."'"));

	switch ($Fetch->Status) {
		case 'CREATED':
			$status = 'CREATED';
			break;
		case 'PROPOSED':
			$status = 'PROPOSED';
			break;
		case 'APPROVAL_UPPER':
			$status = 'APPROVAL ATASAN';
			break;
		case 'APPROVAL_FINANCE':
			$status = 'APPROVAL FINANCE';
			break;
		case 'APPROVAL_GA':
			$status = 'APPROVAL SUPPLY CHAIN';
			break;
		case 'APPROVED':
			$ps = DB::table("purchase_summary")->where("PR_No", $Fetch->PR_No)->first();
			switch ($ps->Status) {
				case 'CREATED':
					$status = 'PO ON PROGRESS';
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
	$user = DB::table("users")->where("username", $Fetch->Created_By)->first();
	echo "<row id=\"$Fetch->PR_No\" $warna>";
	echo "<cell $warna>$nomor</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->PR_No) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->PR_Date) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->Category) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->Sub_Category) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->Supplies_Type) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->PIC) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->Reference) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($Fetch->Priority) . "</cell>";
	//echo "<cell $warna>" . htmlspecialchars($Fetch->Note) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($status) . "</cell>";
	echo "<cell $warna>" . htmlspecialchars($user->Name) . "</cell>";
	//	echo "<cell $warna>" . htmlspecialchars('aaaaa') . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
