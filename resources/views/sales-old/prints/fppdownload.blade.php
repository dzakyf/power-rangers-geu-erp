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

$taskid = $_GET['PO_No'];
$proforma = DB::table("proforma")->where("Proforma_No", $_GET['ID'])->first();
$proforma_detail = DB::table("proforma_detail")->where("Proforma_No", $_GET['ID'])->first();
$oppor = DB::table("opportunity")->where("Task_ID", $proforma_detail->Task_ID)->first();
$oppord = DB::table("opportunity_detail")->where("Task_ID", $proforma_detail->Task_ID)->first();
$syarat = DB::table("opportunity_detail_attachment")->where("Task_ID", $proforma_detail->Task_ID)->get();
$tgl = date("d M Y", strtotime($proforma->Last_Update));
$nomor = 1;
$fppno = str_replace("BA", "FPP", $proforma->BA_No);

echo "<script>
setTimeout(function(){
";
foreach ($syarat as $att) {

	echo "
		document.getElementById('div_lampiran$nomor').style.backgroundColor ='#000';
		document.getElementById('isi_lampiran$nomor').innerHTML ='$att->Attachment_Name';
	";
	$nomor++;
}
echo "
},2000);
setTimeout(function(){
";

if ($oppord->Bill_Type == 'RUTIN') {
	echo "
		document.getElementById('Rutin').style.backgroundColor ='#000';";
} else {
	echo "
		document.getElementById('Tidak_Rutin').style.backgroundColor ='#000';";
}
echo "
},2000);
</script>";

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
		font-size: 11px;


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
				PT. GREEN ENERGI UTAMA<br>
				FORMULIR PERMOHONAN PENAGIHAN<Br>
				<p style="font-weight:bold;font-size:13px;font-family:arial">No. <?php echo $fppno ?></p>
			</div>
			<div style="float:right;width:130px;vertical-align:top;padding-top:-100px;margin:0;">
				<img src='kspslogo.png' height="70" />
			</div>
		</div>

		<div>



			<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%;border:double">
				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%;">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="21%" style="border:1px solid grey;padding:10px;border-right:none;height:30px;text-align:left;">Kepada</td>
							<td width="6%" style="border:1px solid grey;padding:10px;border-right:none;border-left:none;height:30px;text-align:left;">:</td>
							<td width="21%" style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;"><b>BAGIAN KEUANGAN</b></td>
							<td width="4%" style="border:1px solid grey;padding:10px;border-right:none;border-left:none;border-bottom:none;height:30px;text-align:left;">&nbsp;</td>
							<td width="21%" style="border:1px solid grey;padding:10px;border-right:none;height:30px;text-align:left;">Penagihan Rutin</td>
							<td width="6%" style="border:1px solid grey;padding:10px;border-right:none;border-left:none;height:30px;text-align:left;">&nbsp;</td>
							<td width="21%" style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;">
								<div style="width:10px;height:10px;border:1px solid #000" id="Rutin"></div>
							</td>
						</tr>
						<tr>
							<td width="21%" style="border:1px solid grey;border-top:none;padding:10px;border-right:none;height:30px;text-align:left;">Dari</td>
							<td width="6%" style="border:1px solid grey;padding:10px;border-right:none;border-top:none;border-left:none;height:30px;text-align:left;">:</td>
							<td width="21%" style="border:1px solid grey;padding:10px;border-top:none;border-left:none;height:30px;text-align:left;"><b>BISNIS</b></td>
							<td width="4%" style="border:1px solid grey;padding:10px;border-right:none;border-top:none;border-left:none;height:30px;text-align:left;">&nbsp;</td>
							<td width="21%" style="border:1px solid grey;border-top:none;padding:10px;border-right:none;height:30px;text-align:left;">Penagihan Tidak Rutin</td>
							<td width="6%" style="border:1px solid grey;padding:10px;border-right:none;border-top:none;border-left:none;height:30px;text-align:left;">&nbsp;</td>
							<td width="21%" style="border:1px solid grey;padding:10px;border-top:none;border-left:none;height:30px;text-align:left;">
								<div id="Tidak_Rutin" style="width:10px;height:10px;border:1px solid #000"></div>
							</td>
						</tr>



					</table>

				</div>



				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="25%" rowspan="7" style="vertical-align:top;border:1px solid grey;padding:10px;border-right:none;height:30px;text-align:left;">PERIHAL</td>

							<td width="30%" style="border:1px solid grey;padding:10px;height:30px;text-align:left;">Penagihan Atas</td>
							<td width="45%" style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $proforma->Subject; ?>&nbsp;</td>

						</tr>
						<tr>

							<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">Periode</td>
							<td width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $proforma->Periode ?>&nbsp;</td>

						</tr>
						<tr>

							<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">Dasar Kontrak / PKS</td>
							<td width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $proforma_detail->Contract_No ?>&nbsp;</td>

						</tr>
						<tr>

							<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">SPK / PO</td>
							<td width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $proforma_detail->PO_No ?>&nbsp;</td>

						</tr>
						<tr>

							<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">Customer</td>
							<td width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $proforma->Account_Name ?>&nbsp;</td>

						</tr>
						<tr>

							<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">Contact Person</td>
							<td width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $oppor->Invoice_To ?>&nbsp;</td>

						</tr>
						<tr>

							<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">Total</td>
							<td width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;">Rp. <?php echo number_format($proforma->Total, 0, ",", ".") ?>&nbsp;</td>

						</tr>

					</table>

				</div>
				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="150" style="vertical-align:top;border:1px solid grey;padding:10px;border-right:none;height:30px;text-align:left;">SYARAT PENAGIHAN</td>

							<td width="180" style="vertical-align:top;border:1px solid grey;padding:10px;height:30px;text-align:left;">DOKUMEN TERLAMPIR</td>
							<td style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;">
								<table cellpadding="2" cellspacing="0" width="100%">
									<tr>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran1"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran1"></div>
										</td>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran2"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran2"></div>
										</td>
									</tr>
									<tr>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran3"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran3"></div>
										</td>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran4"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran4"></div>
										</td>
									</tr>
									<tr>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran5"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran5"></div>
										</td>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran6"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran6"></div>
										</td>
									</tr>
									<tr>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran7"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran7"></div>
										</td>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran8"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran8"></div>
										</td>
									</tr>
									<tr>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran9"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran9"></div>
										</td>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran10"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran10"></div>
										</td>
									</tr>
									<tr>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran11"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran11"></div>
										</td>
										<td width="20">
											<div style="width:20px;height:20px;border:1px solid #000" id="div_lampiran12"></div>
										</td>
										<td width="150">
											<div style="padding-left:4px;" id="isi_lampiran12"></div>
										</td>
									</tr>
								</table>
							</td>

						</tr>


					</table>

				</div>
				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td style="vertical-align:bottom;height:30px;text-align:left;">Tanggal Pengajuan: <?php echo $tgl ?></td>



						</tr>


					</table>

				</div>
				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="30%" style="vertical-align:top;text-align:center;height:30px;">Diajukan Oleh;</td>

							<td width="30%" style="vertical-align:top;text-align:center;height:30px;">Diketahui Oleh;</td>

							<td width="30%" style="vertical-align:top;text-align:center;height:30px;">Diterima Oleh;</td>
						</tr>
						<tr>
							<td colspan="3" style="vertical-align:top;text-align:center;height:30px;text-align:left;">&nbsp;</td>
						</tr>
						<tr>
							<td width="30%" style="vertical-align:bottom;text-align:left;height:30px;text-align:left;">
								<div style="height:60;text-align:center"><?php echo QrCode::size(60)->generate($Saya); ?></div>Nama: <?php echo $Saya ?>
							</td>

							<td width="30%" style="vertical-align:bottom;text-align:left;height:30px;text-align:left;">
								<div style="height:60;text-align:center"><?php if ($nama2 != '') echo QrCode::size(60)->generate($nama2); ?></div>Nama: <?php echo $nama2 ?>
							</td>

							<td width="30%" style="vertical-align:bottom;text-align:left;height:30px;text-align:left;">
								<div style="height:60;text-align:center"><?php if ($nama3 != '') echo QrCode::size(60)->generate($nama3); ?></div>Nama: <?php echo $nama3 ?>
							</td>
						</tr>
						<tr>
							<td width="30%" style="vertical-align:top;text-align:left;height:30px;padding-top:0px">Jabatan: <?php echo $Position ?></td>

							<td width="30%" style="vertical-align:top;text-align:left;height:30px;padding-top:0px;">Jabatan: <?php echo $jabatan2 ?></td>

							<td width="30%" style="vertical-align:top;text-align:left;height:30px;padding-top:0px">Jabatan: <?php echo $jabatan3 ?></td>
						</tr>

					</table>

				</div>

			</div>

		</div>

	</div>

</body>

</html>