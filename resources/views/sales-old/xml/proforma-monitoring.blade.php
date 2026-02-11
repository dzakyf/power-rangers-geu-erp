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
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";
//$Query = DB::table("proforma")->where("Location",$LSP)->where("Status","=","POSTED")->where("Created_NIK","=",$saya)->orderBy("ID","DESC")->get();
$Query = DB::table("proforma")->where("Location", $LSP)->whereNotNull('BA_No')->whereBetween("Dateinsert", [$_GET['Tgl1'] . " 00:00:00", $_GET['Tgl2'] . " 23:59:59"])->orderBy("ID", "DESC")->get();
foreach ($Query as $Fetch) {
	$dateFpp = "";
	switch ($Fetch->Status) {
		case 'PROPOSED':
			$status = "WAIT SUBMISSION";
			break;
		case 'PROPOSED1':
			$status = "WAITING APPROVAL VP";
			$datA = DB::table("mail_logs")->where("Contents", 'LIKE', "%$Fetch->Proforma_No%")->orderBy('ID', 'ASC')->first();
			if ($Fetch->Last_Update == $Fetch->Dateinsert) {
				$date = $datA->Send_Date;
			} else {
				$date = $Fetch->Last_Update;
			}

			if ($Fetch->fpp_date == null) {
				$dateFpp = $datA->Inserted_Date;
			} else {
				$dateFpp = $Fetch->fpp_date;
			}
			break;
		case 'POSTED1':
			$status = "WAITING APPROVAL FINANCE";
			$datA = DB::table("mail_logs")->where("Contents", 'LIKE', "%$Fetch->Proforma_No%")->orderBy('ID', 'ASC')->first();
			if ($Fetch->Last_Update == $Fetch->Dateinsert) {
				$date = $datA->Send_Date;
			} else {
				$date = $Fetch->Last_Update;
			}
			if ($Fetch->fpp_date == null) {
				$dateFpp = $datA->Inserted_Date;
			} else {
				$dateFpp = $Fetch->fpp_date;
			}
			break;
		case 'POSTED':
			$status = "POSTED";
			$datA = DB::table("mail_logs")->where("Contents", 'LIKE', "%$Fetch->Proforma_No%")->orderBy('ID', 'ASC')->first();
			if ($Fetch->Last_Update == $Fetch->Dateinsert) {
				$date = $datA->Send_Date;
			} else {
				$date = $Fetch->Last_Update;
			}
			if ($Fetch->fpp_date == null) {
				$dateFpp = $datA->Inserted_Date;
			} else {
				$dateFpp = $Fetch->fpp_date;
			}
			break;
		case 'RECEIVED':
			$status = "RECIEVED BY FINANCE";
			$date = $Fetch->Posting_Date;
			if ($Fetch->fpp_date == null) {
				$datA = DB::table("mail_logs")->where("Contents", 'LIKE', "%$Fetch->Proforma_No%")->orderBy('ID', 'ASC')->first();
				$dateFpp = $datA->Inserted_Date;
			} else {
				$dateFpp = $Fetch->fpp_date;
			}
			break;
		case 'RETURN':
			$status = "DIKEMBALIKAN";
			$date = $Fetch->Posting_Date;
			break;
		case 'CANCELED':
			$status = "DIBATALKAN";
			$date = $Fetch->Last_Update;
			break;
		case 'EX':
			$status = "DIBATALKAN";
			$date = $Fetch->Last_Update;
			break;
		default:
			$status = "BA PROGRESS";
			$dateFpp = "";
			break;
	}
	$inv_no = "";
	$mgm = "";
	if (empty($Fetch->Invoice_No)) {
		$invo = "Invoice NOT CREATED";
	} else {
		$invo = "Invoice CREATED";
		$no_inv = DB::table("invoice")->where("Inv_No", $Fetch->Invoice_No)->first();
		$no_mgm_fee = DB::table("invoice")->where("Inv_No", $Fetch->Management_Fee)->first();

		$inv_no = $no_inv->Inv_KSPS;
		$mgm =  $no_mgm_fee->Inv_KSPS;
	}
	$sistem = substr_replace($Fetch->Periode_Tagihan, "-", -2, -2);
	$Query2 = DB::table("proforma_detail")->where("Proforma_No", $Fetch->Proforma_No)->first();
	echo "<row id=\"$Fetch->Proforma_No\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Proforma_Type) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->BA_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Query2->PO_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Subject) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Periode) . "</cell>";
	echo "<cell>" . htmlspecialchars($sistem) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Proforma_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($status) . "</cell>";
	echo "<cell>" . htmlspecialchars($dateFpp) . "</cell>";
	echo "<cell>" . htmlspecialchars($date) . "</cell>";
	echo "<cell>" . htmlspecialchars($inv_no) . "</cell>";
	echo "<cell>" . htmlspecialchars($mgm) . "</cell>";
	echo "<cell>" . htmlspecialchars($invo) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Dateinsert) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Account_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Invoice_To) . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
