<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$nama =  Auth::user()->Name;


// if (!isset($_GET['tanggal'])) {
//     $tanggal = date("Y-m-d");
// } else {
//     $tanggal = filter_var($_GET['tanggal']);
// }
$tanggal = '2021-06-01';

function bagi($pembilang, $penyebut)
{
    if ($penyebut != 0) {
        return $pembilang / $penyebut;
    } else {
        return 0;
    }
}

function persen($pembilang, $penyebut)
{
    $persen = bagi($pembilang, $penyebut);
    return $persen * 100;
}

//bugdet Aktiva
$bulan = date("F", strtotime($tanggal)) . "_Value";

$budget_aktiva = DB::select(DB::raw("
select tahun,  SUM($bulan) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '11%' group by tahun
"));
$budget_aktiva_end = DB::select(DB::raw("
select tahun,  SUM(Total) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '11%' group by tahun
"));

$budget_aktiva_utama = DB::select(DB::raw("
select tahun,  SUM($bulan) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '11%' group by tahun
"));
$budget_aktiva_utama_end = DB::select(DB::raw("
select tahun,  SUM(Total) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '11%' group by tahun
"));
$budget_hut_jangka_pendek = DB::select(DB::raw("
select tahun,  SUM($bulan) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '21%' group by tahun
"));
$budget_hut_jangka_pendek_end = DB::select(DB::raw("
select tahun,  SUM(Total) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '21%' group by tahun
"));

$budget_hut_jangka_panjang = DB::select(DB::raw("
select tahun,  SUM($bulan) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '22%' group by tahun
"));
$budget_hut_jangka_panjang_end = DB::select(DB::raw("
select tahun,  SUM(Total) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '22%' group by tahun
"));
$budget_pendapatan = DB::select(DB::raw("
select tahun,  SUM($bulan) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '4%' group by tahun
"));
$budget_pendapatan_end = DB::select(DB::raw("
select tahun,  SUM(Total) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '4%' group by tahun
"));

$budget_pengeluaran = DB::select(DB::raw("
select tahun,  SUM($bulan) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '5%' group by tahun
"));
$budget_pengeluaran_end = DB::select(DB::raw("
select tahun,  SUM(Total) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '5%' group by tahun
"));

$budget_pendapatan_lain = DB::select(DB::raw("
select tahun,  SUM($bulan) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '6%' group by tahun
"));
$budget_pendapatan_lain_end = DB::select(DB::raw("
select tahun,  SUM(Total) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '6%' group by tahun
"));

$budget_ekuitas = DB::select(DB::raw("
select tahun,  SUM($bulan) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '3%' group by tahun
"));
$budget_ekuitas_end = DB::select(DB::raw("
select tahun,  SUM(Total) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '3%' group by tahun
"));

$budget_penyusutan = DB::select(DB::raw("
select tahun,  SUM($bulan) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '522.7%' group by tahun
"));
$budget_penyusutan_end = DB::select(DB::raw("
select tahun,  SUM(Total) as nilai FROM budget_detail WHERE tahun = " . date('Y', strtotime($tanggal)) . " AND COA LIKE '522.7%' group by tahun
"));

$saldoawal_ekuitas = DB::select(DB::raw("
select Journal_Year , Acc_No, SUM(Journal_Value) as nilai FROM ho_sa_year WHERE Journal_Year = " . (date('Y', strtotime($tanggal)) - 1) . " AND Acc_No LIKE '3%' group by Journal_Year, Acc_No
"));


//Aktiva Realisasi
$saldoawal_aktiva1 = DB::select(DB::raw("
select Journal_Year , Acc_No, SUM(Journal_Value) as nilai FROM ho_sa_year WHERE Journal_Year = " . (date('Y', strtotime($tanggal)) - 1) . " AND Acc_No LIKE '11%' group by Journal_Year, Acc_No
"));

$saldoawal_aktiva_utama = DB::select(DB::raw("
select Journal_Year , Acc_No, SUM(Journal_Value) as nilai FROM ho_sa_year WHERE Journal_Year = " . (date('Y', strtotime($tanggal)) - 1) . " AND Acc_No LIKE '1%' group by Journal_Year, Acc_No
"));


$aktiva_lancar = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-m-t', strtotime($tanggal . " -1 Month")) . "' and `Account_No` LIKE '11%' group by `tanggal`
"));


$aktiva_lancar_end = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-12-31', strtotime($tanggal)) . "' and `Account_No` LIKE '11%' group by `tanggal`
"));

$aktiva_utama = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-m-t', strtotime($tanggal . " -1 Month")) . "' and `Account_No` LIKE '1%' group by `tanggal`
"));


$aktiva_utama_end = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-12-31', strtotime($tanggal)) . "' and `Account_No` LIKE '1%' group by `tanggal`
"));


//Hutang Jangka Pendek Realisasi
$saldoawal_hutpendek = DB::select(DB::raw("
select Journal_Year , SUM(Journal_Value) as nilai FROM ho_sa_year WHERE Journal_Year = " . (date('Y', strtotime($tanggal)) - 1) . " AND Acc_No LIKE '21%' group by Journal_Year
"));

$saldoawal_hutpanjang = DB::select(DB::raw("
select Journal_Year , SUM(Journal_Value) as nilai FROM ho_sa_year WHERE Journal_Year = " . (date('Y', strtotime($tanggal)) - 1) . " AND Acc_No LIKE '22%' group by Journal_Year
"));

$saldo_hutang_jangka_pendek = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-m-t', strtotime($tanggal)) . "' and `Account_No` LIKE '21%' group by `tanggal`
"));

$saldo_hutang_jangka_pendek_end = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-12-31', strtotime($tanggal)) . "' and `Account_No` LIKE '21%' group by `tanggal`
"));

$saldo_hutang_jangka_panjang = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-m-t', strtotime($tanggal)) . "' and `Account_No` LIKE '22%' group by `tanggal`
"));

$saldo_hutang_jangka_panjang_end = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-12-31', strtotime($tanggal)) . "' and `Account_No` LIKE '22%' group by `tanggal`
"));


$aktiva = $saldoawal_aktiva1[0]->nilai + $aktiva_lancar[0]->nilai;
$aktiva_end = $saldoawal_aktiva1[0]->nilai + $aktiva_lancar_end[0]->nilai;
$hut_jangka_pendek = $saldoawal_hutpendek[0]->nilai + $saldo_hutang_jangka_pendek[0]->nilai;
$hut_jangka_pendek_end = $saldoawal_hutpendek[0]->nilai + $saldo_hutang_jangka_pendek_end[0]->nilai;
$hut_jangka_panjang = $saldoawal_hutpanjang[0]->nilai + $saldo_hutang_jangka_panjang[0]->nilai;
$hut_jangka_panjang_end = $saldoawal_hutpanjang[0]->nilai + $saldo_hutang_jangka_panjang_end[0]->nilai;
//operating ratio
$pendapatan = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-m-t', strtotime($tanggal)) . "' and `Account_No` LIKE '4%' group by `tanggal`
"));

$pendapatan_end = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-12-31', strtotime($tanggal)) . "' and `Account_No` LIKE '4%' group by `tanggal`
"));

$pengeluaran = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-m-t', strtotime($tanggal)) . "' and `Account_No` LIKE '5%' group by `tanggal`
"));

$pengeluaran_end = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-12-31', strtotime($tanggal)) . "' and `Account_No` LIKE '5%' group by `tanggal`
"));

$pendapatan_lain = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-m-t', strtotime($tanggal)) . "' and `Account_No` LIKE '6%' group by `tanggal`
"));

$pendapatan_lain_end = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-12-31', strtotime($tanggal)) . "' and `Account_No` LIKE '6%' group by `tanggal`
"));

$ekuitas = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-m-t', strtotime($tanggal)) . "' and `Account_No` LIKE '3%' group by `tanggal`
"));

$ekuitas_end = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-12-31', strtotime($tanggal)) . "' and `Account_No` LIKE '3%' group by `tanggal`
"));

$penyusutan = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-m-t', strtotime($tanggal)) . "' and `Account_No` LIKE '3%' group by `tanggal`
"));

$penyusutan_end = DB::select(DB::raw("
select DATE_FORMAT(Posting_Date, '%Y') as tanggal, sum(Debet)-sum(Credit) as nilai from `ho` where `Posting_Date` BETWEEN '" . date('Y-01-01', strtotime($tanggal)) . "' AND '" . date('Y-12-31', strtotime($tanggal)) . "' and `Account_No` LIKE '3%' group by `tanggal`
"));


$budget_net_margin = persen(($budget_pendapatan[0]->nilai - $budget_pengeluaran[0]->nilai) + $budget_pendapatan_lain[0]->nilai, $budget_pendapatan[0]->nilai);

$net_margin = persen(($pendapatan[0]->nilai - $pengeluaran[0]->nilai) + $pendapatan_lain[0]->nilai, $pendapatan[0]->nilai);

$budget_net_margin_end = persen(($budget_pendapatan_end[0]->nilai - $budget_pengeluaran_end[0]->nilai) + $budget_pendapatan_lain_end[0]->nilai, $budget_pendapatan_end[0]->nilai);

$net_margin_end =  persen(($pendapatan_end[0]->nilai - $pengeluaran_end[0]->nilai) + $pendapatan_lain_end[0]->nilai, $pendapatan_end[0]->nilai);

$budget_ebitda_amount = $budget_net_margin + $budget_penyusutan[0]->nilai;
$budget_ebitda_amount_end = $budget_net_margin_end + $budget_penyusutan_end[0]->nilai;
$ebitda_amount = $net_margin + $penyusutan[0]->nilai;
$ebitda_amount_end = $net_margin_end + $penyusutan_end[0]->nilai;

$budget_pasiva = $budget_hut_jangka_pendek[0]->nilai + $budget_hut_jangka_panjang[0]->nilai;
$budget_pasiva_end = $budget_hut_jangka_pendek_end[0]->nilai + $budget_hut_jangka_panjang_end[0]->nilai;
$pasiva = $hut_jangka_pendek + $hut_jangka_panjang;
$pasiva_end = $hut_jangka_pendek_end + $hut_jangka_panjang_end;

var_dump($bulan);
?>


@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Accounting</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard1">Laporan Keuangan</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Rasio</span>
        </li>
    </ol>
</div>
<!-- End Main Content Header -->
<div class="row">
    <form method="get" action="<?= $_SERVER['PHP_SELF'] ?>">

        Periode<input type="text" id="tanggal" size="10" value="<?php echo date('Y-m-d') ?>" readonly="readonly" />
        <input type="button" id="SubmitCheck" value="Submit" class="btn btn-success mt-2 mr-2" />
    </form>


</div>
<table class="table m-0 table-striped dark table-hover">
    <thead>
        <tr>
            <th rowspan="2" colspan="1">Uraian</th>
            <th colspan="2"><?= date("F-Y", strtotime($tanggal)) ?></th>
            <th colspan="2">s/d Desember <?= date("Y") ?></th>
        </tr>
        <tr>

            <th>ANGGARAN</th>
            <th>REALISASI</th>
            <th>ANGGARAN</th>
            <th>REALISASI</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Likuiditas</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Current Ratio</td>
            <td><?= number_format(persen($budget_aktiva[0]->nilai, $budget_hut_jangka_pendek[0]->nilai), 2, ",", ".") . "%" . $budget_aktiva[0]->nilai . "/" . $budget_hut_jangka_pendek[0]->nilai ?></td>
            <td><?= number_format(persen($aktiva, $hut_jangka_pendek), 2, ",", ".") . "%" . $aktiva . "/" . $hut_jangka_pendek ?></td>
            <td><?= number_format(persen($budget_aktiva_end[0]->nilai, $budget_hut_jangka_pendek_end[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($aktiva_end, $hut_jangka_pendek_end), 2, ",", ".") . "%"  ?></td>

        <tr>
            <td>Rentabilitas</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Operating Ratio</td>
            <td><?= number_format(persen($budget_pengeluaran[0]->nilai, $budget_pendapatan[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($pengeluaran[0]->nilai, $pendapatan[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($budget_pengeluaran_end[0]->nilai, $budget_pendapatan_end[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($pengeluaran_end[0]->nilai, $pendapatan_end[0]->nilai), 2, ",", ".") . "%" ?></td>
        </tr>
        <tr>
            <td>Net Margin Ratio</td>
            <td><?= number_format($budget_net_margin, 2, ",", ".") . "%" ?></td>
            <td><?= number_format($net_margin, 2, ",", ".") . "%" ?></td>
            <td><?= number_format($budget_net_margin_end, 2, ",", ".") . "%" ?></td>
            <td><?= number_format($net_margin_end, 2, ",", ".") . "%" ?></td>
        </tr>
        <tr>
            <td>Return On Equtiy</td>
            <td><?= number_format(persen($budget_net_margin, $budget_ekuitas[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($net_margin, $saldoawal_ekuitas[0]->nilai + $ekuitas[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($budget_net_margin_end, $budget_ekuitas_end[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($net_margin_end, $saldoawal_ekuitas[0]->nilai + $ekuitas_end[0]->nilai), 2, ",", ".")  . "%" ?></td>
        </tr>
        <tr>
            <td>Return On Asset</td>
            <td><?= number_format(persen($budget_net_margin, $budget_aktiva_utama[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($net_margin, $saldoawal_aktiva_utama[0]->nilai + $aktiva_utama[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($budget_net_margin_end, $budget_aktiva_utama_end[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($net_margin_end, $saldoawal_aktiva_utama[0]->nilai + $aktiva_utama_end[0]->nilai), 2, ",", ".") . "%"  ?></td>
        </tr>

        <tr>
            <td>Ebitda</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>EBITDA AMOUNT</td>
            <td><?= number_format($budget_ebitda_amount, 2, ",", ".")  ?></td>
            <td><?= number_format($ebitda_amount, 2, ",", ".")  ?></td>
            <td><?= number_format($budget_ebitda_amount_end, 2, ",", ".")   ?></td>
            <td><?= number_format($ebitda_amount_end, 2, ",", ".")   ?></td>
        </tr>
        <tr>
            <td>EBITDA Ratio</td>
            <td><?= number_format(persen($budget_ebitda_amount, $budget_pendapatan[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($ebitda_amount, $pendapatan[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($budget_ebitda_amount_end, $budget_pendapatan_end[0]->nilai), 2, ",", ".") . "%"   ?></td>
            <td><?= number_format(persen($ebitda_amount_end, $pendapatan_end[0]->nilai), 2, ",", ".") . "%"  ?></td>
        </tr>

        <tr>
            <td>SOLVABILITAS</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>

        <tr>
            <td>Debt to Equity Ratio</td>
            <td> <?= number_format(persen($budget_pasiva, $budget_ekuitas[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($pasiva, $ekuitas[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($budget_pasiva_end, $budget_ekuitas_end[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($pasiva_end, $ekuitas_end[0]->nilai), 2, ",", ".") . "%" ?></td>
        </tr>


        <tr>
            <td>Debt to Asset Ratio</td>
            <td> <?= number_format(persen($budget_pasiva, $pendapatan[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($pasiva, $pendapatan[0]->nilai), 2, ",", ".") ?></td>
            <td><?= number_format(persen($budget_pasiva_end, $budget_pendapatan_end[0]->nilai), 2, ",", ".") . "%" ?></td>
            <td><?= number_format(persen($pasiva_end, $pendapatan_end[0]->nilai), 2, ",", ".") . "%" ?></td>
        </tr>
    </tbody>
</table>

<script>
    var SHPREVDAY_Cal;
    SHPREVDAY_Cal = new dhtmlXCalendarObject(["tanggal"]);
    SHPREVDAY_Cal.setDateFormat("%Y-%m");
</script>

@endsection