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



function Accx($acc)
{

	$QProd = DB::select(DB::raw("SELECT * from branch_coa WHERE Acc_No='$acc'"));


	return $QProd;
}
$nomor = 1;
$stylenormalnonpadding = "style = 'padding-left:0px;text-transform: uppercase;'";
$styleboldnonepadding = "style = 'padding-left:0px;font-weight:bold;background-color:#eee;color:#000;text-transform: uppercase;'";
$stylenormalpadding = "style = 'padding-left:15px;text-transform: uppercase;'";
$styleboldpadding = "style = 'padding-left:15px;font-weight:bold;background-color:#eee;color:#000;text-transform: uppercase;'";


$tgl1 = $_GET['Tgl1'];
$tgl2 = $_GET['Tgl2'];
$LSP = $_GET['City'];
$monthbefore = strtotime($tgl1) - 2505600;
$lastmonth = date('Y-m', $monthbefore);
$lastyear = date('Y', strtotime($tgl1)) - 1;
$startmonth = date('Y', strtotime($tgl1)) . "-01-01";

$datestring = "$tgl1 last day of last month";
$dt = date_create($datestring);
$endmonth =  $dt->format('Y-m-t'); //2011-02

$mmm = DB::table("branch_coa")->orderBy("IATA_Code", "Asc")->get();
foreach ($mmm as $master) {

	$kelas[$master->Acc_No] = $master->Klass_Ref;
}

$namadatabase = "ho";
$namadatabase_sayear = $namadatabase . "_sa_year";

$skrgx = date('Y-m%');
$wdate = " WHERE Posting_Date BETWEEN '$tgl1' AND '$tgl2'";

$Querys = DB::select(DB::raw("SELECT * FROM $namadatabase $wdate ORDER BY Account_No"));

foreach ($Querys as $Fetchs) {
	$coades = Accx($Fetchs->Account_No);

	if ($coades->Normal == 'Debet') {
		$coa[$Fetchs->Account_No]['none'][$LSP]['Net_Mutation'] += ($Fetchs->Debet - $Fetchs->Credit) + 0;
	} else {
		$coa[$Fetchs->Account_No]['none'][$LSP]['Net_Mutation'] += ($Fetchs->Credit - $Fetchs->Debet) + 0;
	}
	$coa[$Fetchs->Account_No]['none'][$LSP]['Debet'] += $Fetchs->Debet + 0;
	$coa[$Fetchs->Account_No]['none'][$LSP]['Credit'] += $Fetchs->Credit + 0;
	$coa[$Fetchs->Account_No]['none'][$LSP]['Ending_Balance'] += ($Fetchs->Debet - $Fetchs->Credit) + 0;
}

$QSAYear = DB::select(DB::raw("SELECT Acc_No,Remark,Journal_Value from $namadatabase_sayear
	 where  Journal_Year=$lastyear GROUP BY Acc_No,Remark,Journal_Value order by Acc_No,Remark"));


foreach ($QSAYear as $begFetchx) {
	if ($begFetchx->Remark == '') $begFetchx->Remark = 'none';

	$beg[$begFetchx->Acc_No][$begFetchx->Remark] += $begFetchx->Journal_Value;
}

$qBegFetch = DB::select(DB::raw("SELECT Account_No,Reference_No,SUM(Debet-Credit) as Account_Value ,SUM(Debet) as sumdebet,
		SUM(Credit) AS sumcredit from $namadatabase	 where Posting_Date BETWEEN '$startmonth' AND  '$endmonth' 
		GROUP BY Account_No,Reference_No order by Account_No,Reference_No"));

// $kemarin = date("Y-m-d", strtotime($tgl1 . " -1 day"));

// $qBegFetch = DB::select(DB::raw("SELECT Account_No,Reference_No,SUM(Debet-Credit) as Account_Value ,SUM(Debet) as sumdebet,
// 		SUM(Credit) AS sumcredit from $namadatabase	 where Posting_Date BETWEEN '$startmonth' AND  '$kemarin' 
// 		GROUP BY Account_No,Reference_No order by Account_No,Reference_No"));

foreach ($qBegFetch  as $begFetch) {

	$kelasx = $kelas[$begFetch->Account_No];
	if ($kelasx == 'BANK') {

		if ($normalNormal == 'Debet') {
			$saldokredit2 = $beg[$begFetch->Account_No][$begFetch->Reference_No];
			$beg[$begFetch->Account_No][$begFetch->Reference_No] =

				($saldokredit2 + $begFetch->sumdebet) - $begFetch->sumcredit;
		} else {

			$saldokredit2 = $beg[$begFetch->Account_No][$begFetch->Reference_No] * -1;

			$beg[$begFetch->Account_No][$begFetch->Reference_No] =

				($saldokredit2 + $begFetch->sumcredit) - $begFetch->sumdebet;


			$beg[$begFetch->Account_No][$begFetch->Reference_No] *= -1;
		}
	} else {
		if ($normalNormal == 'Debet') {
			$saldokredit = $beg[$begFetch->Account_No]["none"];
			$beg[$begFetch->Account_No] =

				($saldokredit + $begFetch->sumdebet) - $begFetch->sumcredit;
		} else {

			$saldokredit = $beg[$begFetch->Account_No]["none"] * -1;

			$beg[$begFetch->Account_No]["none"] =

				($saldokredit + $begFetch->sumcredit) - $begFetch->sumdebet;


			$beg[$begFetch->Account_No]["none"] *= -1;
		}
	}
}



$Tampilan = $_GET['Tampilan'];

if ($Tampilan == 'WITHOUT') {
	$wtampilan = "WHERE r.Listed_OnSearch=1 ";
} elseif ($Tampilan == 'ONLY') {
	$wtampilan = "WHERE r.Listed_OnSearch=0 ";
} else {
	$wtampilan = "";
}
echo "<rows>";

$qBegFetch = DB::select(DB::raw("SELECT Account_No,Reference_No,SUM(Debet-Credit) as Account_Value ,SUM(Debet) as sumdebet,
		SUM(Credit) AS sumcredit from $namadatabase	 where Posting_Date BETWEEN '$tgl1' AND  '$tgl2' 
		GROUP BY Account_No,Reference_No order by Account_No,Reference_No"));

foreach ($qBegFetch  as $begFetch) {

	$kelasref = $kelas[$begFetch->Account_No];
	if ($kelasref == 'BANK') {

		$kredit2[$begFetch->Account_No][$begFetch->Reference_No] += $begFetch->sumcredit;
		$debit2[$begFetch->Account_No][$begFetch->Reference_No] += $begFetch->sumdebet;
	} else {
		$kredit[$begFetch->Account_No] += $begFetch->sumcredit;
		$debit[$begFetch->Account_No] += $begFetch->sumdebet;
	}
}
if ($_GET['Tampilan'] == 'ONLY') {

	$Query = DB::table("branch_coa")->orderBy("IATA_Code", "Asc")->where("Listed_OnSearch", 0)->get();
} elseif ($_GET['Tampilan'] == 'WITHOUT') {
	$Query = DB::table("branch_coa")->orderBy("IATA_Code", "Asc")->where("Listed_OnSearch", 1)->get();
} else {
	$Query = DB::table("branch_coa")->orderBy("IATA_Code", "Asc")->get();
}

foreach ($Query as $Fetch) {

	if ($Fetch->Klass_Ref == 'BANK') {
		$coades = Accx($Fetch->Acc_No);



		$qy = DB::table("bank_acc")->where("Bank_Acc", $Fetch->Acc_No)->get();

		if (count($qy)) {
			foreach ($qy as $ax) {
				$SaldoTahunLalu = 0;
				if ($ax->Acc_No == 'DUMMY') $ax->Acc_No = '';

				$SaldoAWAL2 = $beg[$Fetch->Acc_No][$ax->Acc_No] + 0;

				$debet = $debit2[$Fetch->Acc_No][$ax->Acc_No] + 0;
				$credit = $kredit2[$Fetch->Acc_No][$ax->Acc_No] + 0;
				if ($Fetch->Normal == 'Debet') {

					$mutasi = ($SaldoAWAL2 + $debet) - $credit;
				} else {

					$saldokredit2 = $SaldoAWAL2 * -1;


					$mutasi = -1 * (($saldokredit2 + $credit) - $debet);
				}
				$netmutasi = $mutasi;



				echo "
					<row id=\"$Fetch->Acc_No|$ax->Acc_No\" $stylemode>
						<cell $stylemode>" . $nomor . "</cell>
						<cell $stylemode>" . htmlspecialchars($Fetch->Acc_No) . "</cell>
						<cell $stylemode>" . htmlspecialchars($Fetch->Description . " # " . $ax->Acc_No) . "</cell>
						<cell $stylemode>$SaldoAWAL2</cell>
						<cell $stylemode>$debet</cell>
						<cell $stylemode>$credit</cell>
						<cell $stylemode>$mutasi</cell>
						<cell $stylemode>$SaldoTahunLalu</cell>
						<cell $stylemode>$netmutasi</cell>
						<cell $stylemode></cell>
					</row>";


				$nomor++;
			}
		}
	} else {
		$coades = Accx($Fetch->Acc_No);
		$SaldoAWAL = $beg[$Fetch->Acc_No]["none"] + 0;

		$debet = $debit[$Fetch->Acc_No] + 0;
		$credit = $kredit[$Fetch->Acc_No] + 0;
		$SaldoTahunLalu = 0;

		if ($Fetch->Normal == 'Debet') {

			$mutasi = ($SaldoAWAL + $debet) - $credit;
		} else {

			$saldokredit = $SaldoAWAL * -1;


			$mutasi = -1 * (($saldokredit + $credit) - $debet);
		}
		$netmutasi = $mutasi;



		echo "
				<row id=\"$Fetch->Acc_No\" $stylemode>
					<cell $stylemode>" . $nomor . "</cell>
					<cell $stylemode>" . htmlspecialchars($Fetch->Acc_No) . "</cell>
					<cell $stylemode>" . htmlspecialchars($Fetch->Description) . "</cell>
					<cell $stylemode>$SaldoAWAL</cell>
					<cell $stylemode>$debet</cell>
					<cell $stylemode>$credit</cell>
					<cell $stylemode>$mutasi</cell>
					<cell $stylemode>$SaldoTahunLalu</cell>
					<cell $stylemode>$netmutasi</cell>
					<cell $stylemode></cell>
				</row>";

		$nomor++;
	}
}

echo "</rows>";
