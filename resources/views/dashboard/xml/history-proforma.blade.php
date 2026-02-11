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

//$QAgent = DB::select(DB::raw("SELECT * FROM proforma WHERE Dateinsert BETWEEN '$Tgl1 00:00:00' AND '$Tgl2 23:59:59' ORDER BY Dateinsert"));

$QAgent = DB::select(DB::raw("SELECT * FROM proforma WHERE Periode_Tagihan BETWEEN " . date('Ym', strtotime($Tgl1)) . " AND " . date('Ym', strtotime($Tgl2)) . " AND Status NOT IN ('EX','CANCELED','RETURN') ORDER BY Periode_Tagihan ASC"));

foreach ($QAgent as $FGL) {
	switch ($FGL->Status) {
		case 'PROPOSED':
			$status = "WAIT SUBMISSION";
			break;
		case 'PROPOSED1':
			$status = "WAITING APPROVAL VP";
			break;
		case 'POSTED1':
			$status = "WAITING APPROVAL SVP";

			break;
		case 'POSTED':
			$status = "POSTED";

			break;
		case 'RECEIVED':
			$status = "RECIEVED BY FINANCE";

			break;
		case 'RETURN':
			$status = "DIKEMBALIKAN";

			break;
		case 'CANCELED':
			$status = "DIBATALKAN";

			break;
		case 'EX':
			$status = "DIBATALKAN";
			break;
		default:
			$status = "BA PROGRESS";

			break;
	}
	if ($FGL->Invoice_No != '') {

		$statusInv = 'INVOICED';
		$aging = "inv";
	} else {
		$statusInv = 'UNFINISH';
		$aging = "-";
		// $lastupdate = strtotime(date("c"))-strtotime($FGL->Last_Update);
		// $aging = round($lastupdate/3600,2);

	}
	$date = substr_replace($FGL->Periode_Tagihan, "-", -2, -2);


	echo "<row id=\"$FGL->ID\" $warna>";
	echo "<cell $warna>$nomor</cell>"; #a
	echo "<cell $warna>" . Filter($FGL->Proforma_No) . "</cell>"; #b
	echo "<cell $warna>" . Filter($FGL->BA_No) . "</cell>"; #c
	echo "<cell $warna>" . Filter($FGL->Subject) . "</cell>"; #d
	echo "<cell $warna>" . Filter($FGL->Periode) . "</cell>"; #d
	echo "<cell $warna>" . Filter($FGL->Subtotal_PO) . "</cell>"; #d
	echo "<cell $warna>" . Filter($FGL->Man_Fee) . "</cell>"; #g
	echo "<cell $warna>" . Filter($FGL->Total) . "</cell>"; #h
	echo "<cell $warna>" . Filter($statusInv) . "</cell>"; #h
	echo "<cell $warna>" . date("F Y", strtotime($date)) . "</cell>"; #h
	echo "<cell $warna>" . $status . "</cell>"; #h
	echo "<cell $warna>" . Filter($aging) . "</cell>"; #h
	echo "</row>";

	$nomor++;
}




echo "</rows>";
