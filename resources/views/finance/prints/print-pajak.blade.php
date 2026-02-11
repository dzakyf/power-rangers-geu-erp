@extends('layout.plugins')
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$Position =  Auth::user()->Position;


?>
<html>
<link type="text/css" rel="stylesheet" href="normalize.min.css" />

<!-- Load paper.css for happy printing -->
<link rel="stylesheet" href="paper.css" />

<!-- Set page size here: A5, A4 or A3 -->
<!-- Set also "landscape" if you need -->
<style type="text/css">
    @page {
        size: A4
    }
</style>
<?php
error_reporting(0);
class moneyFormat
{

    public function rupiah($angka)
    {
        $rupiah = number_format($angka, 0, '', '.');
        return $rupiah;
    }
}
function terbilang($n)
{
    if ($n < 0) return 'minus ' . terbilang(-$n);
    else if ($n < 10) {
        switch ($n) {
            case 0:
                return 'zero';
            case 1:
                return 'one';
            case 2:
                return 'two';
            case 3:
                return 'three';
            case 4:
                return 'four';
            case 5:
                return 'five';
            case 6:
                return 'six';
            case 7:
                return 'seven';
            case 8:
                return 'eight';
            case 9:
                return 'nine';
        }
    } else if ($n < 100) {
        $kepala = floor($n / 10);
        $sisa = $n % 10;
        if ($kepala == 1) {
            if ($sisa == 0) return 'ten';
            else if ($sisa == 1) return 'eleven';
            else if ($sisa == 2) return 'twelve';
            else if ($sisa == 3) return 'thirteen';
            else if ($sisa == 5) return 'fifteen';
            else if ($sisa == 8) return 'eighteen';
            else return terbilang($sisa) . 'teen';
        } else if ($kepala == 2) $hasil = 'twenty';
        else if ($kepala == 3) $hasil = 'thirty';
        else if ($kepala == 5) $hasil = 'fifty';
        else if ($kepala == 8) $hasil = 'eighty';
        else $hasil = terbilang($kepala) . 'ty';
    } else if ($n < 1000) {
        $kepala = floor($n / 100);
        $sisa = $n % 100;
        $hasil = terbilang($kepala) . ' hundred';
    } else if ($n < 1000000) {
        $kepala = floor($n / 1000);
        $sisa = $n % 1000;
        $hasil = terbilang($kepala) . ' thousand';
    } else if ($n < 1000000000) {
        $kepala = floor($n / 1000000);
        $sisa = $n % 1000000;
        $hasil = terbilang($kepala) . ' million';
    } else return false;

    if ($sisa > 0) $hasil .= ' ' . terbilang($sisa);
    return $hasil;
}

function limit_text($text, $limit)
{
    if (str_word_count($text, 0) > $limit) {
        $words = str_word_count($text, 2);
        $pos = array_keys($words);
        $text = substr($text, 0, $pos[$limit]) . '...';
    }
    return $text;
}
function penyebut($nilai)
{
    $nilai = abs($nilai);
    $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $temp = "";
    if ($nilai < 12) {
        $temp = " " . $huruf[$nilai];
    } else if ($nilai < 20) {
        $temp = penyebut($nilai - 10) . " belas";
    } else if ($nilai < 100) {
        $temp = penyebut($nilai / 10) . " puluh" . penyebut($nilai % 10);
    } else if ($nilai < 200) {
        $temp = " seratus" . penyebut($nilai - 100);
    } else if ($nilai < 1000) {
        $temp = penyebut($nilai / 100) . " ratus" . penyebut($nilai % 100);
    } else if ($nilai < 2000) {
        $temp = " seribu" . penyebut($nilai - 1000);
    } else if ($nilai < 1000000) {
        $temp = penyebut($nilai / 1000) . " ribu" . penyebut($nilai % 1000);
    } else if ($nilai < 1000000000) {
        $temp = penyebut($nilai / 1000000) . " juta" . penyebut($nilai % 1000000);
    } else if ($nilai < 1000000000000) {
        $temp = penyebut($nilai / 1000000000) . " milyar" . penyebut(fmod($nilai, 1000000000));
    } else if ($nilai < 1000000000000000) {
        $temp = penyebut($nilai / 1000000000000) . " trilyun" . penyebut(fmod($nilai, 1000000000000));
    }
    return $temp;
}

function bilangin($nilai)
{
    if ($nilai < 0) {
        $hasil = "minus " . trim(penyebut($nilai));
    } else {
        $hasil = trim(penyebut($nilai));
    }
    return $hasil;
}

function konversiketitik($nilaiangka, $kurs = "IDR")
{
    if (strtoupper($kurs) == 'RP' || strtoupper($kurs) == 'IDR' || strtoupper($kurs) == '') {
        if (isset($_GET['dec']) == '2') {
            $titik = number_format($nilaiangka, 2, ',', '.');
        } else {
            $titik = number_format($nilaiangka, 0, '', '.');
        }
    } else {
        $titik = number_format($nilaiangka, 2, ',', '.');
    }
    return ($titik);
}

$invoice = DB::table("invoice")->where("Inv_No", $_GET['ID'])->first();
$nomor = 1;
$customer = DB::table("customerz")->where("Customer_No", $invoice->Account_No)->first();

if ($invoice->jatuh_tempo == null) {
    $invd = strtotime($invoice->Inv_Date);
    $hari = $customer->Due_Days * 86400;
    $tempo = date("d M Y", $invd + $hari);
} else {
    $tempo = date("d M Y", strtotime($invoice->jatuh_tempo));
}

$proforma = DB::table("proforma")->where("Proforma_No", $invoice->Task_ID)->first();
$proformadetail = DB::table("proforma_detail")->where("Proforma_No", $invoice->Task_ID)->first();

//if ($invoice->Invoice_Type != 'GENERAL') $invoice->Invoice_Subject = "Management Fee " . $invoice->Invoice_Subject;
$moneyFormat = new moneyFormat();


?>
<style type="text/css">
    .sheet {
        overflow: visible;
        height: auto !important;
    }

    html,
    table {
        font-family: arial;
        font-size: 10px;
    }

    .header {

        height: 20px;
        width: auto;

    }

    .header-kanan {
        float: right;
        width: 280px;
    }

    .header-kanan2 {
        float: right;
        width: 400px;
    }

    .header-kanan3 {
        float: right;
        width: 150px;
    }

    .subheader {
        padding-top: 10px;
        height: 130px;
        width: auto;
    }

    .barisjudulpurchase_order {
        padding-top: 5px;
        height: 20px;
        width: auto;
        vertical-align: middle;
        text-align: center;
        font-weight: bold;
        font-size: 15px;
        float: none;
        border: 1px solid #000;
    }
</style>

<body class="A4">
    <div class="sheet padding-10mm">
        <div class="header">
            <div style="float:left;width:70%;text-align:center;font-weight:bold;font-size:14px;font-family:arial">
            </div>
            <div style="float:right;width:130px;vertical-align:top;padding-top:-100px;margin:0;height:70px;">
            </div>
        </div>
        <div style="padding-bottom:5px;font-size:30px;padding-right:5px;text-align:end;font-weight:bold;width:100%;border-top:single;border-bottom:3px solid #000">
            KONFIRMASI PEMOTONGAN PAJAK
        </div>
        <div style="padding-left:20px;padding-right:20px;width:100%;">
            <table width="100%" height="20%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="border-right:none;height:30px;text-align:left;">Atas transaksi berikut ini</td>
                    <td width="5%" style="border-right:none;border-left:none;height:30px;text-align:left;">:</td>
                    <td width="55%" style="border-left:none;height:30px;text-align:left;"> <?php echo strtoupper($invoice->Invoice_Subject) ?></td>
                </tr>
                <tr>
                    <td width="20%" style="border-right:none;height:30px;text-align:left;">No. PO/Kontrak</td>
                    <td width="5%" style="border-right:none;border-left:none;height:30px;text-align:left;">:</td>
                    <td width="55%" style="border-left:none;height:30px;text-align:left;"><?php echo strtoupper($proformadetail->PO_No) ?></td>
                </tr>
                <tr>
                    <td width="20%" style="border-right:none;height:30px;text-align:left;">Tanggal PO/Kontrak</td>
                    <td width="5%" style="border-right:none;border-left:none;height:30px;text-align:left;">:</td>
                    <td width="55%" style="border-left:none;height:30px;text-align:left;"><?php echo strtoupper($proforma->Periode) ?></td>
                </tr>
                <tr>
                    <td width="20%" style="border-right:none;height:30px;text-align:left;">No Invoice</td>
                    <td width="5%" style="border-right:none;border-left:none;height:30px;text-align:left;">:</td>
                    <td width="55%" style="border-left:none;height:30px;text-align:left;"><?php echo $invoice->Inv_KSPS ?></td>
                </tr>
                <tr>
                    <td width="20%" style="border-right:none;height:30px;text-align:left;">Tanggal Invoice</td>
                    <td width="5%" style="border-right:none;border-left:none;height:30px;text-align:left;">:</td>
                    <td width="55%" style="border-left:none;height:30px;text-align:left;"><?php echo date("d M Y", strtotime($invoice->Inv_Date)) ?></td>
                </tr>
                <tr>
                    <td width="20%" style="border-right:none;height:30px;text-align:left;">Nama Perusahaan</td>
                    <td width="5%" style="border-right:none;border-left:none;height:30px;text-align:left;">:</td>
                    <td width="55%" style="border-left:none;height:30px;text-align:left;">PT Green Energy Utama</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:20px;padding-right:20px;width:100%;">
            <table width="100%" height="10%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="55%" style="border-left:none;height:30px;text-align:left;"> Terdapat objek pemotongan pajak yaitu imbalan sehubungan dengan jasa outsourcing <?php echo strtoupper($invoice->Invoice_Subject) ?></td>
                </tr>
                <tr>
                    <td width="55%" style="border-left:none;height:30px;text-align:left;">Berdasarkan hal tersebut, kami <b>bersedia</b> dipotong pajak dengan</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:20px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="border-right:none;height:30px;text-align:left;">&bullet; Dasar Pengenaan Pajak (DPP)</td>
                    <td width="5%" style="border-right:none;border-left:none;height:30px;text-align:left;">=</td>
                    <td width="50%" style="border-left:none;height:30px;text-align:left;"><u>Rp <?php echo konversiketitik($invoice->Subtotal) ?></u> *)</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:20px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="border-right:none;height:30px;text-align:left;">&bullet; Tarif Pajak**)</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:40px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">- PPh Pasal 21</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:60px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">&#9733; Berkesinambungan</td>
                    <td width="20%" style="height:30px;text-align:left;">&#9733; Tidak Berkesinambungan</td>
                </tr>
            </table>
        </div>


        <div style="padding-left:70px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:20px;text-align:left;">&#8414; &nbsp; 2,5% dengan NPWP</td>
                    <td width="20%" style="height:20px;text-align:left;">&#8414; &nbsp; 5% dengan NPWP</td>
                </tr>
                <tr>
                    <td width="20%" style="height:20px;text-align:left;">&#8414; &nbsp; 3% dengan NPWP</td>
                    <td width="20%" style="height:20px;text-align:left;">&#8414; &nbsp; 6% dengan NPWP</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:40px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">- PPh Pasal 23</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:60px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">&#8414; &nbsp; ( Bunga/Deviden/Royalty*) sebesar 15%</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:60px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">&#9733; Jasa Lain</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:70px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:20px;text-align:left;">&#8414; &nbsp; 2% dengan NPWP</td>
                    <td width="20%" style="height:20px;text-align:left;">&#8414; &nbsp; 4% tanpa NPWP</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:40px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">- PPh Pasal 4(2)</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:60px;;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">&#8414; &nbsp; (Ruang/Lahan/Listrik*) sebesar 10%</td>
                </tr>
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">&#9733; Konstruksi</td>
                </tr>
            </table>
        </div>

        <div style="padding-left:70px;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">&#11088; Pelaksanaan</td>
                    <td width="20%" style="height:30px;text-align:left;">&#11088; Perencanaaan/Pengawasan/Konsultasi</td>
                </tr>
                <tr>
                    <td width="20%" style="padding-left:30px;height:20px;text-align:left;">&#8414; &nbsp; 1,75% klasifikasi kecil </td>
                    <td width="20%" style="padding-left:30px;height:20px;text-align:left;">&#8414; &nbsp; 3,5% semua klasifikasi</td>
                </tr>
                <tr>
                    <td width="20%" style="padding-left:30px;height:20px;text-align:left;">&#8414; &nbsp; 2,65% klasifikasi menengah dan besar</td>
                    <td width="20%" style="padding-left:30px;height:20px;text-align:left;">&#8414; &nbsp; 6% tidak memiliki kualifikasi</td>
                <tr>
                    <td width="20%" style="height:20px;padding-left:30px;text-align:left;">&#8414; &nbsp; 4% tidak memiliki kualifikasi</td>
                </tr>
                <tr>
                    <td width="20%" style="height:20px;text-align:left;">&#11088; Pelaksanaan Kontruksi Terintegrasi</td>
                </tr>
                <tr>
                    <td width="20%" style="height:20px;padding-left:30px;text-align:left;">&#8414; &nbsp; 2,65% memiliki SBU</td>
                </tr>
                <tr>
                    <td width="20%" style="height:20px;padding-left:30px;text-align:left;">&#8414; &nbsp; 4% tidak memiliki SBU</td>
                </tr>
            </table>
        </div>


        <div style="padding-left:20px;padding-right:20px;width:100%;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">Menyetujui,</td>
                    <td width="40%" style="height:30px;text-align:left;"></td>
                    <td width="55%" style="height:30px;text-align:left;"><?php echo "<img src='../public/images/logo_ksps.png'>"  ?></td>
                </tr>
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">Nama</td>
                    <td width="40%" style="height:30px;text-align:left;">:</td>
                    <td width="55%" style="height:30px;text-align:left;">Irawan Aprianto</td>
                </tr>
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">Tanda Tangan dan Cap Perusahaan</td>
                    <td width="5%" style="height:30px;text-align:left;">:</td>
                    <td width="55%" style="height:30px;text-align:left;"></td>
                </tr>
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">*) Coret yang tidakperlu</td>
                </tr>
                <tr>
                    <td width="20%" style="height:30px;text-align:left;">**) Pilih salah satu</td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>