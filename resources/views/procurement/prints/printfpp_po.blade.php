@extends('layout.plugins')
<?php
setlocale(LC_TIME, 'id_ID');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Nama =  Auth::user()->Name;
$Saya =  Auth::user()->username;
$Position =  Auth::user()->Position;
$taskid = $_GET['ID'];
$proforma = DB::table("account_payable")->where("ID", $_GET['ID'])->first();
$user = DB::table('users')->where('username', $proforma->Created_By)->first();

$getlevelsx  = DB::table("organization_structure")->where("NIK", $user->Emp_ID)->first();
if ($getlevelsx->Level1 == 1) $lepel = 1;
else if ($getlevelsx->Level2 == 1) $lepel = 2;
else if ($getlevelsx->Level3 == 1) $lepel = 3;
else if ($getlevelsx->Level4 == 1) $lepel = 4;
else if ($getlevelsx->Level5 == 1) $lepel = 5;
else if ($getlevelsx->Level6 == 1) $lepel = 6;
else if ($getlevelsx->Level7 == 1) $lepel = 7;
else if ($getlevelsx->Level8 == 1) $lepel = 8;

$upper = $lepel - 1;

if ($upper == 3) {
	$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", $$getlevelsx->Department)->where("Alerted", 1)->first();
} else {
	$getupper = DB::table("organization_structure")->where("Level2", 1)->where("Department", $getlevelsx->divisi)->where("Alerted", 1)->first();
}

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
function terbilang($number)
{
	$base    = array('nol', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan');
	$numeric = array('1000000000000000', '1000000000000', '1000000000000', 1000000000, 1000000, 1000, 100, 10, 1);
	$unit    = array('kuadriliun', 'triliun', 'biliun', 'milyar', 'juta', 'ribu', 'ratus', 'puluh', '');
	$str     = null;

	$i = 0;

	if ($number == 0) {
		$str = 'nol';
	} else {
		while ($number != 0) {
			$count = (int)($number / $numeric[$i]);

			if ($count >= 10) {
				$str .= terbilang($count) . ' ' . $unit[$i] . ' ';
			} elseif ($count > 0 && $count < 10) {
				$str .= $base[$count] . ' ' . $unit[$i] . ' ';
			}

			$number -= $numeric[$i] * $count;

			$i++;
		}

		$str = preg_replace('/satu puluh (\w+)/i', '\1 belas', $str);
		$str = preg_replace('/satu (ribu|ratus|puluh|belas)/', 'se\1', $str);
		$str = preg_replace('/\s{2,}/', ' ', trim($str));
	}

	return $str;
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


$getpo = DB::table("purchase_order")->where("PO_No", $proforma->Reference)->first();
$supp = DB::table("supplier")->where("Supplier_ID", $proforma->Vendor_ID)->first();
$namasupplier = $supp->Company_Name;
$tgl = date("d M Y", strtotime($proforma->Last_Update));
$nomor = 1;
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

if ($proforma->Department == 'OPERATIONAL') {
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
				PT. GREEN ENERGY UTAMA<br>
				FORMULIR PERMOHONAN PEMBAYARAN<br><br>
				NO <?= $proforma->Task_ID ?>
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
							<td width="21%" style="border:1px solid grey;padding:10px;border-right:none;height:30px;text-align:left;">Biaya Operasional</td>
							<td width="6%" style="border:1px solid grey;padding:10px;border-right:none;border-left:none;height:30px;text-align:left;">&nbsp;</td>
							<td width="21%" style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;">
								<div style="width:10px;height:10px;border:1px solid #000" id="Rutin"></div>
							</td>
						</tr>
						<tr>
							<td width="21%" style="border:1px solid grey;border-top:none;padding:10px;border-right:none;height:30px;text-align:left;">Dari</td>
							<td width="6%" style="border:1px solid grey;padding:10px;border-right:none;border-top:none;border-left:none;height:30px;text-align:left;">:</td>
							<td width="21%" style="border:1px solid grey;padding:10px;border-top:none;border-left:none;height:30px;text-align:left;"><b>CORSEC GA</b></td>
							<td width="4%" style="border:1px solid grey;padding:10px;border-right:none;border-top:none;border-left:none;height:30px;text-align:left;">&nbsp;</td>
							<td width="21%" style="border:1px solid grey;border-top:none;padding:10px;border-right:none;height:30px;text-align:left;">Biaya Overhead</td>
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

							<td width="30%" style="border:1px solid grey;padding:10px;height:30px;text-align:left;">Biaya</td>
							<td width="45%" style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;">Pembayaran Tagihan <?php echo $namasupplier ?><br> untuk&nbsp;<?php echo $proforma->Subject_Payment ?></td>

						</tr>
						<tr>

							<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">Periode</td>
							<td width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;"><?php echo date("M Y", strtotime($proforma->Date_Received)) ?>&nbsp;</td>

						</tr>
						<tr>

							<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">Dasar Kontrak / PO</td>
							<td width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;"><?php echo $proforma->Reference ?>&nbsp;</td>

						</tr>
						<tr>

							<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">SPK </td>
							<td width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;">&nbsp;</td>

						</tr>


					</table>

				</div>
				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
					<table width="100%" height="300" cellpadding="0" cellspacing="0">
						<tr>
							<td width="150" rowspan="3" style="vertical-align:top;border:1px solid grey;padding:10px;border-right:none;height:30px;text-align:left;">BUKTI TAGIHAN</td>

							<td width="180" style="vertical-align:top;border:1px solid grey;padding:10px;height:30px;text-align:left;">1. Pembayaran Kepada / Rekanan Jumlah (Rp/USD)</td>
							<td style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;">
								<?php echo $namasupplier ?><Br><br><b><?php echo number_format($proforma->Total, 2, ".", ",") ?></b>
							</td>

						</tr>
						<tr>

							<td width="180" style="vertical-align:top;border:1px solid grey;padding:10px;height:30px;text-align:left;">2. Bukti Pembayaran/Kwitansi No.<br> Kontak Person/ No Telepon<br><br>
								Nomor Rekening</td>
							<td style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;">
								Invoice No : <?php echo $proforma->Inv_Vendor ?>
								<Br>
								Nama Bank : <?php echo $proforma->Bank_Name ?><br>
								Nomor Akun : <?php echo $proforma->Bank_Acc ?><br>
								Nama Akun Bank : <?php echo $proforma->Bank_Acc_Name ?><br>
								Mode Pembayaran : <?php echo $proforma->Bank_Type ?><br>

							</td>

						</tr>

						<tr>

							<td width="180" style="vertical-align:top;border:1px solid grey;padding:10px;height:30px;text-align:left;">3. Lampiran</td>
							<td style="border:1px solid grey;padding:10px;border-left:none;height:30px;text-align:left;">
								<?php echo str_replace("\n", "<br>", $proforma->Description) ?>


							</td>

						</tr>

					</table>

				</div>
				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td style="vertical-align:bottom;height:30px;text-align:left;">Tanggal Pengajuan: <?php echo strftime("%d %B %Y", strtotime($proforma->Dateinsert)) ?></td>



						</tr>


					</table>

				</div>
				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="60%" style="vertical-align:top;text-align:center;height:30px;">Diajukan Oleh;</td>

							<td width="40%" style="vertical-align:top;text-align:center;height:30px;">Disetujui Oleh;</td>


						</tr>
						<tr>
							<td colspan="3" style="vertical-align:top;text-align:center;height:30px;text-align:left;">&nbsp;</td>
						</tr>
						<tr>
							<td width="60%" style="vertical-align:bottom;text-align:center;height:30px;">
								<div style="height:60;text-align:center"><?php echo QrCode::size(60)->generate($user->Name); ?></div>Nama: <?php echo $user->Name ?>
							</td>

							<td width="40%" style="vertical-align:bottom;text-align:center;height:30px;">
								<div style="height:60;text-align:center"><?php if ($nama2 != '') echo QrCode::size(60)->generate($getupper->Name); ?></div>Nama: <?php echo $getupper->Name ?>
							</td>


						</tr>
						<tr>
							<td width="60%" style="vertical-align:top;text-align:center;height:30px;padding-top:0px">Jabatan: <?php echo 'Supply Chain Management' ?></td>

							<td width="40%" style="vertical-align:top;text-align:center;height:30px;padding-top:0px;">Jabatan: <?php echo "Senior Vice President" ?></td>


						</tr>

					</table>

				</div>

			</div>

		</div>

	</div>

</body>

</html>