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
$tgl =  $_GET["Tgl1"];
$date = Filter($tgl);
echo "<rows>";

$Query = DB::table("opportunity")->where('Status', '!=', 'CANCELED')->orderBy("ID", "DESC")->get();
foreach ($Query as $Fetch) {
	//$data = DB::table("opportunity_detail")->where("Task_ID", "$Fetch->Task_ID")->first();
	$data = DB::table("opportunity_detail")->where("Task_ID", "$Fetch->Task_ID")
		//->where('End_Date', 'LIKE', "'$date%'")
		//		->whereRaw("(Start_Date LIKE '$date%' OR End_Date LIKE '$date%')")
		->first();

	echo "<row id=\"$Fetch->Task_ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Oppor_Subject) . "</cell>";
	echo "<cell>" . htmlspecialchars($data->Start_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($data->End_Date) . "</cell>";
	if (date("Y", strtotime($data->End_Date)) >= $date) {
		echo "<cell>" . htmlspecialchars($data->Total) . "</cell>";
	} else {
		echo "<cell>0</cell>";
	}




	$proforma = DB::select(DB::raw("
	select `proforma`.`Bill_Type` as `tipe`,`proforma`.`Periode_Tagihan` as `Periode_Tagihan`, `proforma`.`Total` as `Total` from `proforma_detail` left join `proforma` on `proforma_detail`.`Proforma_No` = `proforma`.`Proforma_No` where `proforma_detail`.`Task_ID` LIKE '$Fetch->Task_ID' and `proforma`.`Periode_Tagihan` LIKE '$date%' AND proforma.Status IN('POSTED', 'RECEIVED') ORDER BY proforma.Periode_Tagihan ASC
	"));
	// $proforma = DB::table("proforma_detail")
	// 	->join('proforma', 'proforma.Proforma_No', '=', 'proforma_detail.Proforma_No')
	// 	->where("proforma_detail.Task_ID", "'$Fetch->Task_ID'")
	// 	->where("proforma.Periode_Tagihan", 'LIKE', "'$date%'")
	// 	->where("proforma.Bill_Type", '=',  "'RUTIN'")
	// 	->select('proforma.Periode_Tagihan', 'proforma.total')
	// 	->orderBy("proforma.Periode_Tagihan", "ASC")->get();
	$data2 = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
	$data3 = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
	if ($proforma) {
		foreach ($proforma as $dataSkunder) {

			$periode = substr_replace($dataSkunder->Periode_Tagihan, "-", -2, -2);
			$bulan = date('n', strtotime($periode));
			if ($dataSkunder->tipe == 'RUTIN') {
				$data2[$bulan] += $dataSkunder->Total + 0;
			} else {
				$data3[$bulan] += $dataSkunder->Total + 0;
			}
		}
	}


	for ($i = 1; $i < 13; $i++) {

		echo "<cell>" . htmlspecialchars($data2[$i]) . "</cell>";
		echo "<cell>" . htmlspecialchars($data3[$i]) . "</cell>";
	}

	echo "</row>";
	$nomor++;
}

echo "</rows>";
