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
		font-size: 12px;


	}

	.header {
        padding-bottom: 20px;
		border-bottom: 1px solid #000;
		height: 80px;
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
    @font-face {
        font-family: 'FontAwesome';
        src: url('/geu/hcga/public/dhtmlx/fonts/font_awesome/fonts/fontawesome-webfont.eot?v=4.5.0');
        src: url('/geu/hcga/public/dhtmlx/fonts/font_awesome/fonts/fontawesome-webfont.eot?#iefix&v=4.5.0') format('embedded-opentype'), url('/geu/hcga/public/dhtmlx/fonts/font_awesome/fonts/fontawesome-webfont.woff2?v=4.5.0') format('woff2'), url('/geu/hcga/public/dhtmlx/fonts/font_awesome/fonts/fontawesome-webfont.woff?v=4.5.0') format('woff'), url('/geu/hcga/public/dhtmlx/fonts/font_awesome/fonts/fontawesome-webfont.ttf?v=4.5.0') format('truetype'), url('/geu/hcga/public/dhtmlx/fonts/font_awesome/fonts/fontawesome-webfont.svg?v=4.5.0#fontawesomeregular') format('svg');
        font-weight: normal;
        font-style: normal;
    }
    
    .fa {
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome;
        font-size: inherit;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .fa-phone:before {
        content: "\f095";
    }

    
.fa-envelope:before {
  content: "\f0e0";
}
</style>

<body class="A4">
	<div class="sheet padding-10mm" style="padding-top: 5mm">

		<div class="header">
			<div style="float:left;width:20%;text-align:center;font-weight:bold;font-size:14px;font-family:arial">
                <img src="{{ asset('assets/images/geu-logo-color.webp') }}" alt="">
            </div>
            <div style="float:right;width:420px;vertical-align:top;margin:0;height:70px;">
                <table>
                    <tr>
                        <td style="border-right:1px solid #000;text-align:right;padding-right:10px;">
                            <span style="color: rgb(95, 226, 95);font-weight:bold;font-size:12pt">PT. GREEN ENERGI UTAMA</span> <br>
                            A2 Pergudangan Tanrise K-Walk. <br>
                            65153 Malang, East Java, Indonesia <br>
                        </td>
                        <td style="padding-left: 10px;">
                            <div style="display: flex; flex-direction: row; align-items: flex-start;flex-wrap: wrap;">
                                <div style="margin-right: 10px;align-self: center;">
                                    <span class="fa fa-phone" style="color: rgb(95, 226, 95); font-size: 16px;"></span>
                                </div>
                                <div>
                                    <span>(+62) 341 306 4175</span> <br>
                                    <span>(+62) 811 371 9299</span> <br>
                                </div>
                            </div>
                            <span class="fa fa-envelope" style="color: rgb(95, 226, 95); font-size: 16px; margin-right: 5px;"></span>
                            <span>cs@greenenergiutama.com</span>
                        </td>
                    </tr>
                </table>

			</div>
		</div>

        <div>
            <table style="width:100%;margin-top:20px;">
                <tr>
                    <td>
                        INVOICE KEMITRAAN
                    </td>
                    <td>
                        <div>
                            Date : {{ date('d F Y') }}
                        </div>
                        <div>
                            INV#
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        PARTNERSHIP
                    </td>
                    <td>
                        PAYMENT DETAIL
                        <div>
                            Package : <br>
                            Size : <br>
                            Partnership Value : <br>
                            Revenue Sharing : <br>
                        </div>
                    </td>
                </tr>
                <tr>
                    <table style="width:100%;margin-top:20px;border-collapse: collapse;" border="1">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>DESCRIPTION</th>
                                <th>VALUE</th>
                                <th>PEB</th>
                                <th>AMOUNT (IDR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Pembayaran</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align:right;font-weight:bold;">TOTAL</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>
                </tr>
            </table>
        </div>

		<div>

		</div>

	</div>

</body>

</html>