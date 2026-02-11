<?php
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$Tgl1 = $_GET["Tgl1"];

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
	header("Content-type: application/xhtml+xml");
} else {
	header("Content-type: text/xml");
}
echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

function diffDate($start, $end)
{
	$diff = round(abs(strtotime($end) - strtotime($start)) / 86400 / 30);

	return  $diff == 0 ? 1 : $diff;
}
$mQuery = DB::table("project_mgt")->where("Source_Reference", "!=", "")->get();

foreach ($mQuery as $fproject) {

	$projecttask[$fproject->Source_Reference] = $fproject->Task_ID;
	$proj[$fproject->Source_Reference] = 1;
	$target[$fproject->Source_Reference] = $fproject->Target_Finish;
}
$mqac = DB::table("project_activity")->get();

foreach ($mqac as $aktif) {

	$activity[$aktif->Task_ID] = $aktif->Activity_Type;
}

$mQuery1 = DB::table("opportunity_detail")->whereRaw("DATE_FORMAT(Start_Date, '%Y') <= ? AND DATE_FORMAT(End_Date, '%Y') >= ?", [$Tgl1, $Tgl1])->get();
$adendum = DB::table("opportunity_detail_addendum")->whereRaw("DATE_FORMAT(Start_Date, '%Y') <= ? AND DATE_FORMAT(End_Date, '%Y') >= ?", [$Tgl1, $Tgl1])->get();


foreach ($mQuery1 as $fpo) {
	$data = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
	$month_total = 0;
	$po[$fpo->Task_ID]['No'] = $fpo->PO_No;
	$po[$fpo->Task_ID]['PO'] += 1;
	$po[$fpo->Task_ID]['Start'] = $fpo->Start_Date;
	$po[$fpo->Task_ID]['End'] = $fpo->End_Date;
	$po[$fpo->Task_ID]['Total'] = 0;
	$po[$fpo->Task_ID]['Pencatatan'] = $fpo->Pencatatan;
	$target[$fproject->Source_Reference] = $fproject->Target_Finish;
	$po[$fpo->Task_ID]['bulan'] = $data;
	if (date("Y", strtotime($fpo->End_Date)) >= $Tgl1) {
		$po[$fpo->Task_ID]['Total'] = $fpo->Total;

		if ($fpo->Pencatatan == "MONTHLY") {
			$bagi = diffDate($fpo->Start_Date, $fpo->End_Date);
			$totaltagihan = round($fpo->Total / $bagi, 0);

			$periode_start = date("Y", strtotime($fpo->Start_Date)) < $Tgl1 ? 1 : date("n", strtotime($fpo->Start_Date));
			$periode_end =  date("Y", strtotime($fpo->End_Date)) > $Tgl1 ? 12 : date("n", strtotime($fpo->End_Date));
			for ($i = $periode_start; $i < $periode_end + 1; $i++) {
				$data[$i] += $totaltagihan + 0;
			}
			$month_total = array_sum($data);
		} else {

			$periode_start = date("Y", strtotime($fpo->Start_Date)) < $Tgl1 ? 0 : date("n", strtotime($fpo->Start_Date));

			$data[$periode_start] += $fpo->Total + 0;

			$month_total = array_sum($data);
		}
	} else {
		$data = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
	}
	$po[$fpo->Task_ID]['bulan'] = $data;
	$po[$fpo->Task_ID]['bulan_total'] = 	$month_total;
}

foreach ($adendum as $adpo) {

	if ($po[$adpo->Task_ID]['PO'] == 0) {
		$d = DB::table("opportunity_detail")->where("Task_ID", $adpo->Task_ID)->first();
		$data = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
		$month_total = 0;
		$po[$adpo->Task_ID]['No'] = $adpo->PO_No;
		$po[$adpo->Task_ID]['PO'] += 1;
		$po[$adpo->Task_ID]['Start'] = $adpo->Start_Date;
		$po[$adpo->Task_ID]['End'] = $adpo->End_Date;
		$po[$adpo->Task_ID]['Total'] = 0;
		$po[$adpo->Task_ID]['Pencatatan'] = $d->Pencatatan;
		$po[$adpo->Task_ID]['bulan'] = $data;
	}
	$po[$adpo->Task_ID]['Start'] = $adpo->Start_Date;

	if (date("Y", strtotime($adpo->End_Date)) >= $Tgl1) {
		$po[$adpo->Task_ID]['Total'] += $adpo->Total + 0;
		if ($po[$adpo->Task_ID]['Pencatatan'] == "MONTHLY") {
			$bagi = diffDate($adpo->Start_Date, $adpo->End_Date);
			$totaltagihan = round($adpo->Total / $bagi, 0);

			$periode_start = date("Y", strtotime($adpo->Start_Date)) < $Tgl1 ? 1 : date("n", strtotime($adpo->Start_Date));
			$periode_end =  date("Y", strtotime($adpo->End_Date)) > $Tgl1 ? 12 : date("n", strtotime($adpo->End_Date));
			for ($i = $periode_start; $i < $periode_end + 1; $i++) {
				$po[$adpo->Task_ID]['bulan'][$i] += $totaltagihan + 0;
			}
			$month_total = array_sum($po[$adpo->Task_ID]['bulan']);
		} else {
			$periode_start = date("Y", strtotime($adpo->Start_Date)) < $Tgl1 ? 0 : date("n", strtotime($adpo->Start_Date));

			$po[$adpo->Task_ID]['bulan'][$periode_start] += $adpo->Total + 0;

			$month_total = array_sum($po[$adpo->Task_ID]['bulan']);
		}

		$po[$adpo->Task_ID]['bulan_total'] = $month_total;
	}
}

$ba = DB::table('proforma_detail')
	->rightJoin('proforma', 'proforma_detail.Proforma_No', '=', 'proforma.Proforma_No')
	->selectRaw("proforma_detail.Task_ID as Task_ID, sum(proforma.Total) as total, DATE_FORMAT(proforma.Posting_Date,'%c') as bulan")
	->whereRaw("Status NOT IN ('EX','CANCELED','RETURN') AND proforma.Posting_Date LIKE '$Tgl1%'")
	->groupBy('Task_ID', 'bulan')
	->orderBy('Task_ID', 'asc')
	->orderBy('bulan', 'desc')
	->get();


if ($ba) {

	foreach ($ba as $dataBa) {

		if ($po[$dataBa->Task_ID]['BA'] == 0) {
			for ($i = 1; $i <= $dataBa->bulan; $i++) {
				$po[$dataBa->Task_ID]['bulan'][$i] = 0;
			}
			$po[$dataBa->Task_ID]['BA'] = 1;
		}
		$po[$dataBa->Task_ID]['bulan'][intval($dataBa->bulan)] = $dataBa->total;

		$month_total = array_sum($po[$dataBa->Task_ID]['bulan']);
		$po[$dataBa->Task_ID]['bulan_total'] = $month_total;
	}
}






$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("opportunity")->where("Location", $LSP)->where("Status", "!=", "CANCELED")->orderBy("ID", "DESC")->get();

foreach ($Query as $Fetch) {


	if ($po[$Fetch->Task_ID]['PO']  > 0) {



		echo "<row id=\"$Fetch->ID\">";
		echo "<cell $warna>$nomor</cell>";
		echo "<cell $warna>" . htmlspecialchars($Fetch->Oppor_Subject) . "</cell>";
		echo "<cell $warna>" . htmlspecialchars($Fetch->Company_Name) . "</cell>";
		echo "<cell $warna>" . htmlspecialchars($po[$Fetch->Task_ID]['No']) . "</cell>";
		echo "<cell $warna>" . htmlspecialchars($po[$Fetch->Task_ID]['Start']) . "</cell>";
		echo "<cell $warna>" . htmlspecialchars($po[$Fetch->Task_ID]['End']) . "</cell>";
		echo "<cell $warna>" . htmlspecialchars($po[$Fetch->Task_ID]['Total']) . "</cell>";



		for ($i = 1; $i < 13; $i++) {

			echo "<cell>" . htmlspecialchars($po[$Fetch->Task_ID]['bulan'][$i]) . "</cell>";
		}
		echo "<cell>" . htmlspecialchars($po[$Fetch->Task_ID]['bulan_total']) . "</cell>";
		echo "</row>";

		$nomor++;
	}
}

echo "</rows>";
