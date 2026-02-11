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
//$tgl1 = $_GET['tgl1'];
$tgl2 = $_GET['tgl2'];

$nomor = 1;
echo "<rows>";

if (isset($_GET['Metode'])) {
	$metode = $_GET['Metode'];
} else {
	$metode = 'total';
}



$q = DB::select(DB::raw("SELECT * FROM  account_receive WHERE Location='$LSP' AND Status IN('RECEIVED','PARTIAL') AND Inv_Date <= '$tgl2'"));

foreach ($q as $Data) {
	$now = strtotime(date('c'));
	$jumhari = 0;
	if ($Data->Date_Receive == '0000-00-00') {
		$selisih = 0;
		$Data->Due_Date = date("Y-m-d", strtotime($Data->AR_Date) +  (14 * 86400));
	}

	if (($Data->Due_Date < "2018-01-01") || $Data->Due_Date == '' || $Data->Due_Date == null) {
		$Data->Due_Date = date("Y-m-d", strtotime($Data->Inv_Date) +  (14 * 86400));
	}
	$dilay = $now - strtotime($Data->Due_Date);
	$jumhari = round($dilay / 86400);

	if ($metode == 'QTY') {
		if ($Data->Delivered == 1) {
			if ($jumhari > 150) {
				$delay = "Over 150 Hari";
				$ax[$Data->Cust_ID]['br5'] += 1;
			} elseif ($jumhari >= 91 && $jumhari <= 150) {
				$ax[$Data->Cust_ID]['br4'] += 1;
				$delay = "91-150 Hari";
			} elseif ($jumhari >= 61 && $jumhari <= 90) {
				$ax[$Data->Cust_ID]['br3'] += 1;
				$delay = "61-90 Hari";
			} elseif ($jumhari >= 31 && $jumhari <= 60) {
				$delay = "31-60 Hari";
				$ax[$Data->Cust_ID]['br2'] += 1;
			} elseif ($jumhari >= 0 && $jumhari <= 30) {
				$ax[$Data->Cust_ID]['br1'] += 1;
				$delay = "Under 30 Hari";
			} else {
				$ax[$Data->Cust_ID]['current'] += 1;
				$delay = "Not Yet Due";
			}
		} else {
			$ax[$Data->Cust_ID]['undelivered'] += 1;
			$delay = "On Process";
		}


		$ax[$Data->Cust_ID]['total'] += 1;
	} else {
		if ($Data->Delivered == 1) {
			if ($jumhari > 150) {
				$delay = "Over 150 Hari";
				$ax[$Data->Cust_ID]['br5'] += $Data->AR_Remain + 0;
			} elseif ($jumhari >= 91 && $jumhari <= 150) {
				$ax[$Data->Cust_ID]['br4'] += $Data->AR_Remain + 0;
				$delay = "91-150 Hari";
			} elseif ($jumhari >= 61 && $jumhari <= 90) {
				$ax[$Data->Cust_ID]['br3'] += $Data->AR_Remain + 0;
				$delay = "61-90 Hari";
			} elseif ($jumhari >= 31 && $jumhari <= 60) {
				$delay = "31-60 Hari";
				$ax[$Data->Cust_ID]['br2'] += $Data->AR_Remain + 0;
			} elseif ($jumhari >= 0 && $jumhari <= 30) {
				$ax[$Data->Cust_ID]['br1'] += $Data->AR_Remain + 0;
				$delay = "Under 30 Hari";
			} else {
				$ax[$Data->Cust_ID]['current'] += $Data->AR_Remain + 0;
				$delay = "Not Yet Due";
			}
		} else {
			$ax[$Data->Cust_ID]['undelivered'] += $Data->AR_Remain + 0;
			$delay = "On Process";
		}
		$ax[$Data->Cust_ID]['total'] += $Data->AR_Remain;
	}
}
ksort($ax);
$fadata = DB::table("customerz")->get();
foreach ($fadata as $cust) {
	$kastemer[$cust->Customer_No]['name'] = $cust->Company_Name;
}
$jum = count($ax);
foreach ($ax as $key => $value) {
	echo "<row id=\"$nomor\">";
	echo "<cell>ar-aging-report-detail?ID=$key</cell>";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . ($key) . " - " . $kastemer[$key]['name'] . "</cell>";
	echo "<cell>" . ($ax[$key]['undelivered']) . "</cell>";
	echo "<cell>" . ($ax[$key]['current']) . "</cell>";
	echo "<cell>" . ($ax[$key]['br1']) . "</cell>";
	echo "<cell>" . ($ax[$key]['br2']) . "</cell>";
	echo "<cell>" . ($ax[$key]['br3']) . "</cell>";
	echo "<cell>" . ($ax[$key]['br4']) . "</cell>";
	echo "<cell>" . ($ax[$key]['br5']) . "</cell>";
	echo "<cell>" . ($ax[$key]['total']) . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
