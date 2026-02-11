<?php
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


//error_reporting(E_ALL ^ E_NOTICE);

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
	header("Content-type: application/xhtml+xml");
} else {
	header("Content-type: text/xml");
}
echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");
function Bersih($Data)
{
	$X2 = trim($Data);

	$X2 = preg_replace('/[^(\x20-\x7F)]*/', '', $X2);
	$X2 = htmlspecialchars($X2);
	return $X2;
}
function Filter($C)
{
	$Datahtmlspecialchars = preg_replace('/[^(\x20-\x7F)]*/', '', $C);
	return Bersih($Datahtmlspecialchars);
}

$Kota = $_GET["City"];
$Tgl1 = $_GET["Tgl1"];
$Tgl2 = $_GET["Tgl2"];
$Acc = $_GET['Acc'];
if ($Acc) {
	$wacc = "AND Account_No='$Acc'";
} else {
	$wacc = "";
}
$LSP = $Kota;

$nomor = 1;
echo "<rows>";
$namadatabase = "ho";
$namadatabase_sayear = $namadatabase . "_sa_year";

$QAgent = DB::select(DB::raw("SELECT * FROM $namadatabase WHERE Posting_Date BETWEEN '$Tgl1' AND '$Tgl2' $wacc ORDER BY Posting_Date ASC,ID ASC,Journal_No"));

foreach ($QAgent as $FGL) {


	$row = DB::table("branch_coa")->where("Acc_No", "$FGL->Account_No")->first();


	if ($row->Acc_No == '' || $row->Acc_No == null) {
		$warna = "bgColor='red'";
		$GLInfo = "INVALID";
	} else {
		if ($row->Listed_OnSearch == 0) {
			$warna = "bgColor='yellow'";
			$GLInfo = "HEADER/GROUP";
		} else {
			$warna = "";
			$GLInfo = "NORMAL";
		}
	}

	//if(($FGL->Account_No == '212.3') || ($FGL->External_Doc == '') ) {
	if (stripos($FGL->Journal_No, 'JPM-AP-') !== false) {
		$externalDoc = $FGL->Task_ID;
	} else {
		$externalDoc = $FGL->External_Doc;
	}

	if (stripos($FGL->Journal_No, 'JPM-BA-') !== false) {
		$proforma = DB::table("proforma")->where("BA_No", 'LIKE', "%$FGL->Document_No%")->orderBy("ID", "desc")->first();

		if ($proforma->Invoice_No == '') {
			$ref2 = Filter($FGL->Reference_Desc);
		} else {
			$invoice = DB::table("invoice")->where("Inv_No", 'LIKE', "%$proforma->Invoice_No%")->orderBy("ID", "desc")->first();

			if ($invoice) {
				$ref2 = $invoice->Inv_KSPS;
			} else {
				$ref2 = Filter($FGL->Reference_Desc);
			}
		}
	} else {
		$ref2 = Filter($FGL->Reference_Desc);
	}

	if ($FGL->Account_No == "212.8") {
		$inv = DB::table("invoice")->where("Inv_No", "$FGL->Reference_No")->orderBy("ID", "desc")->first();
		$ref1 = $inv->Faktur_No;
	} else {
		$ref1 = $FGL->Reference_No;
	}

	if (($FGL->Account_No == "212.8") && (stripos($FGL->Task_ID, 'PRF-UNION-') !== false)) {
		$ba = explode(",", $FGL->Reference_Desc);
		$externalDoc = [];

		foreach ($ba as $keyba => $valueba) {
			$prof = DB::table("proforma")->where("Proforma_No", 'LIKE', "$valueba%")->orderBy("ID", "desc")->first();
			$externalDoc[] = $prof->BA_No;
		}
		$externalDoc = implode(",", $externalDoc);
	}





	echo "<row id=\"$FGL->ID\" $warna>";
	echo "<cell $warna>$nomor</cell>"; #a
	echo "<cell $warna>" . Filter($FGL->Posting_Date) . "</cell>"; #b
	echo "<cell $warna>" . Filter($FGL->Document_No) . "</cell>"; #c
	echo "<cell $warna>" . Filter($externalDoc) . "</cell>"; #d
	echo "<cell $warna>" . Filter($FGL->Journal_No) . "</cell>"; #d
	echo "<cell $warna>$FGL->Account_No</cell>"; #f
	echo "<cell $warna>" . Filter($row->Description) . "</cell>"; #g
	echo "<cell $warna>" . Filter($FGL->Remark) . "</cell>"; #h
	echo "<cell $warna>" . Filter($FGL->Debet + 0) . "</cell>"; #i
	echo "<cell $warna>" . Filter($FGL->Credit + 0) . "</cell>"; #j
	echo "<cell $warna>" . $ref1 . "</cell>";
	echo "<cell $warna>" . $ref2 . "</cell>";
	echo "<cell $warna>$FGL->User_Resp</cell>";
	echo "<cell $warna>$FGL->Department</cell>";
	echo "<cell $warna>" . Filter($FGL->Partner_Name) . "</cell>";
	echo "<cell $warna>" . Filter($FGL->Partner_Code) . "</cell>";
	echo "<cell $warna>$GLInfo</cell>";
	echo "</row>";

	$nomor++;
}




echo "</rows>";
