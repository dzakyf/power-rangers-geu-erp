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
		font-size: 12px;


	}

	.header {

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
</style>

<body class="A4">
	<div class="sheet padding-10mm">

		<div class="header">
			<div style="float:left;width:80%;text-align:center;font-weight:bold;font-size:14px;font-family:arial">

			</div>
			<div style="float:right;width:130px;vertical-align:top;padding-top:-100px;margin:0;height:70px;">

			</div>
		</div>

		<div>



			<div style="padding-bottom:5px;font-size:40px;padding-top:5px;text-align:center;font-weight:bold;padding-right:5px;width:100%;border-top:double;border-bottom:3px solid #000">
				I N V O I C E
			</div>
			<div>
				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:100%;">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>

							<td width="50%" style="padding:0px;border-right:none;height:30px;text-align:left;font-weight:bold">Kepada</td>

							<td width="20%" style="padding:10px;border-right:none;height:30px;text-align:left;">Nomor FP</td>
							<td width="5%" style="padding:10px;border-right:none;border-left:none;height:30px;text-align:left;">:</td>
							<td width="25%" style="padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $invoice->Faktur_No ?></td>
						</tr>
						<tr>

							<td width="21%" rowspan="3" style="padding:10px;border:1px solid #000;height:30px;text-align:left;"><B><?php echo $customer->Company_Name ?></b><br><br>
								<?php echo $customer->Address; ?><br>
								<?php //echo $customer->City; 
								?><br>
								<?php //echo $customer->Province; 
								?><br>
							</td>

							<td width="21%" style="padding:10px;border-right:none;height:30px;text-align:left;">Nomor</td>
							<td width="6%" style="padding:10px;border-right:none;border-left:none;height:30px;text-align:left;">:</td>
							<td width="21%" style="padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $invoice->Inv_KSPS ?></td>
						</tr>

						<tr>

							<td width="21%" style="padding:10px;border-right:none;height:30px;text-align:left;">Tanggal</td>
							<td width="6%" style="padding:10px;border-right:none;border-left:none;height:30px;text-align:left;">:</td>
							<td width="21%" style="padding:10px;border-left:none;height:30px;text-align:left;"><?php echo date("d M Y", strtotime($invoice->Inv_Date)) ?></td>
						</tr>
						<tr>

							<td width="21%" style="padding:10px;border-right:none;height:30px;text-align:left;">Jatuh Tempo</td>
							<td width="6%" style="padding:10px;border-right:none;border-left:none;height:30px;text-align:left;">:</td>
							<td width="21%" style="padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $tempo ?></td>
						</tr>
					</table>

				</div>



				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="55%" style="border:1px solid grey;padding:10px;border-right:none;height:30px;text-align:center;font-weight:bold">Deskripsi</td>

							<td width="20%" style="border:1px solid grey;padding:10px;height:30px;text-align:center;font-weight:bold">@Harga</td>
							<td width="25%" style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:center;font-weight:bold">Sub Total</td>

						</tr>
						<?php
						$subdat = DB::table("invoice_sub_data")->where("Task_ID", $invoice->Task_ID)->orderBy('ID', 'asc')->get();

						if ($invoice->Invoice_Type == 'GENERAL') {

							foreach ($subdat as $dx) {
						?>
								<tr>
									<td width="55%" style="padding-top:20px;padding-left:10px;vertical-align:top;border-right:none;height:15px;text-align:left;font-weight:none"><?php echo $dx->Deskripsi ?></td>

									<td width="20%" style="padding-top:20px;vertical-align:top;height:15px;text-align:right;font-weight:none"><?PHP echo konversiketitik($dx->Price) ?></td>
									<td width="25%" style="padding-top:20px;vertical-align:top;border-left:none;height:15px;text-align:right;font-weight:none"><?PHP echo konversiketitik($dx->Subtotal) ?></td>

								</tr>
							<?php

							}
						} else {

							foreach ($subdat as $dx) {

							?>
								<tr>
									<td width="55%" style="padding-top:20px;padding-left:10px;vertical-align:top;border-right:none;height:15px;text-align:left;font-weight:none">Management Fee <?php echo $dx->Deskripsi ?></td>

									<td width="20%" style="padding-top:20px;vertical-align:top;height:15px;text-align:right;font-weight:none"><?PHP echo konversiketitik($dx->Price_Fee) ?></td>
									<td width="25%" style="padding-top:20px;vertical-align:top;border-left:none;height:15px;text-align:right;font-weight:none"><?PHP echo konversiketitik($dx->Subtotal_Fee) ?></td>

								</tr>
						<?php

							}
						}
						?>


					</table>

				</div>


				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="45%" style="border-top:1px solid grey;padding:10px;border-right:none;height:30px;text-align:left;font-weight:bold">Keterangan</td>

							<td width="10%" style="border:none;padding:10px;height:30px;text-align:center;font-weight:bold">&nbsp;</td>
							<td width="40%" style="border-top:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;font-weight:none">Sub Total</td>
							<td width="10%" style="border-top:1px solid grey;padding:10px;padding-right:0px;border-left:none;height:30px;text-align:right;font-weight:none"><?PHP echo konversiketitik($invoice->Subtotal + $invoice->mgm_fee) ?></td>

						</tr>
						<tr>
							<td width="45%" rowspan="7" style="border-bottom:none;height:30px;text-align:left;vertical-align:top ">
								<div style="padding:10px;width:auto;height:100px; word-wrap: break-word;border:1px solid #000;">
									<?php echo strtoupper($invoice->Invoice_Subject) ?><br>

									<?php if ($invoice->periode_txt == null) : ?>
										PERIODE <?php echo strtoupper($proforma->Periode) ?><Br>
									<?php else : ?>
										PERIODE <?php echo strtoupper($invoice->periode_txt) ?><Br>
									<?php endif; ?>
									<?php if ($proforma->Man_Fee == '0.00') : ?>
										NO. PO <B><?php echo strtoupper($proformadetail->PO_No) ?></b>
									<?php endif; ?>

								</div>
								<br>
								<div style="width:auto;height:100px; word-wrap: break-word;border:1px solid #000;padding:10px;font-weight:bold">
									Note:<br>
									Harap ditransfer ke BANK MANDIRI CAB. MENARA THAMRIN A.N PT. GREEN ENERGI UTAMA A.C 103-000.714428-6
								</div>
							</td>

							<td width="10%" style="border:none;padding:10px;height:30px;text-align:center;font-weight:none">&nbsp;</td>
							<td width="40%" style="padding:10px;height:30px;text-align:left;font-weight:none">PPN (%)</td>
							<td width="10%" style="padding:10px;padding-right:0px;border-left:none;height:30px;text-align:right;font-weight:none"><?PHP echo konversiketitik($invoice->PPN) ?></td>

						</tr>
						<tr>

							<td width="10%" style="border:none;padding:10px;height:30px;text-align:center;font-weight:none">&nbsp;</td>
							<td width="40%" style="padding:10px;border-bottom:1px solid #000;height:30px;text-align:left;font-weight:none">Materai</td>
							<td width="10%" style="border-bottom:1px solid #000;padding:10px;padding-right:0px;border-left:none;height:30px;text-align:right;font-weight:none"><?PHP echo konversiketitik($invoice->Materai) ?></td>

						</tr>

						<tr>
							<td width="10%" style="border:none;padding:10px;height:30px;text-align:center;font-weight:bold">&nbsp;</td>

							<td width="40%" style="padding:10px;border-bottom:double #000;height:30px;text-align:left;font-weight:bold">Total</td>
							<td width="10%" style="border-bottom:double #000;padding:10px;padding-right:0px;border-left:none;height:30px;text-align:right;font-weight:bold"><?PHP echo konversiketitik($invoice->Total) ?></td>

						</tr>
						<tr>
							<td width="10%" style="border:none;padding:10px;height:30px;text-align:center;font-weight:bold">&nbsp;</td>

							<td width="40%" style="padding:10px;height:30px;text-align:left;font-weight:bold">Terbilang:</td>
							<td width="10%" style="border-bottom:none;padding:10px;border-left:none;height:30px;text-align:center;font-weight:bold">&nbsp;</td>

						</tr>
						<tr>
							<td width="10%" style="border:none;padding:10px;height:30px;text-align:center;font-weight:none">&nbsp;</td>

							<td width="40%" colspan="2" style="padding:10px;height:30px;text-align:left;font-weight:none;text-align:justify;"><?php echo ucfirst(bilangin($invoice->Total)); ?> rupiah</td>
							<!-- <td width="10%" style="border-bottom:none;padding:10px;border-left:none;height:30px;text-align:center;font-weight:bold">&nbsp;</td> -->

						</tr>
						<tr>
							<td width="10%" style="border:none;padding:10px;height:30px;text-align:center;font-weight:none">&nbsp;</td>

							<td width="40%" style="padding-top:30px;height:30px;text-align:center;font-weight:none">Disetujui Oleh,</td>
							<td width="10%" style="border-bottom:none;padding:10px;border-left:none;height:30px;text-align:center;font-weight:bold">&nbsp;</td>

						</tr>
						<tr>
							<td width="10%" style="border:none;padding:10px;height:130px;text-align:center;font-weight:none">&nbsp;</td>

							<td width="40%" style="border-bottom:1px solid #000;padding-top:30px;height:50px;text-align:center;font-weight:bold;vertical-align:bottom"><?php echo $invoice->TTD_By ?><Br></td>
							<td width="10%" style="padding:10px;border-left:none;height:130px;text-align:center;font-weight:bold">&nbsp;</td>

						</tr>
					</table>

				</div>


			</div>

		</div>

	</div>

</body>

</html>