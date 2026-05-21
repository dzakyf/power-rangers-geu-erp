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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class moneyFormat
{

	public function rupiah($angka)
	{
		$rupiah = number_format($angka, 0, ',', '.');
		return $rupiah;
	}
}
function terbilang($x)
{
	$angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

	if ($x < 12)
		return " " . $angka[$x];
	elseif ($x < 20)
		return terbilang($x - 10) . " belas";
	elseif ($x < 100)
		return terbilang($x / 10) . " puluh" . terbilang($x % 10);
	elseif ($x < 200)
		return "seratus" . terbilang($x - 100);
	elseif ($x < 1000)
		return terbilang($x / 100) . " ratus" . terbilang($x % 100);
	elseif ($x < 2000)
		return "seribu" . terbilang($x - 1000);
	elseif ($x < 1000000)
		return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
	elseif ($x < 1000000000)
		return terbilang($x / 1000000) . " juta" . terbilang($x % 1000000);
}
if ($row_ar1['sum(ar.mdoc)']) {
	echo (ucwords(Terbilang($row_ar1['sum(ar.mdoc)'])));
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

$us = DB::table('users')->where('username', $salesOrder->created_by)->select(['Name','Position'])->first();


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
		height: 150px;
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

		<div>

			<div class="header">

				<div style="float:left;width:400px">
					<img src='./large-logo.png' width="80" />
				</div>
				<div align="left" class="header-kanan">
					<div style="font-weight:bold;font-size:12px;">PT. GREEN ENERGI UTAMA</div>
					<div style="padding: 2px 0">A2 Pergudangan Tanrise K-Walk,</div>
					<div>65153 Malang, East Java, Indonesia</div>


				</div>


			</div>
			<div>
				<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px;">
					<tr>
						<td width="500">
							<div style="color:#779161;font-weight:bold;font-size:12px;float:none;padding-left:5px;padding-top:3px;padding-bottom:3px;">
								SALES ORDER
							</div>
						</td>
						<td>
							<table>
								<tr>
									<td width="90" valign="top">Date</td>
									<td valign="top">: <?php echo $salesOrder->so_date ?></td>
								</tr>
								<tr>
									<td width="90" valign="top">Sales Order Code</td>
									<td valign="top">: <?php echo strtoupper($salesOrder->so_no) ?></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div class="subheader">

				<div style="float:left;width:200px">
					<div style="background:#B8E094;font-weight:bold;font-size:12px;width:100%;float:none;padding-left:5px;padding-top:3px;padding-bottom:3px;">BUYER</div>
                    <br>
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="90" valign="top">PIC</td>
							<td valign="top">: <?php echo strtoupper($salesOrder->buyer_pic) ?></td>
						</tr>
						<tr>
							<td width="90" valign="top">(Kota)</td>
							<td valign="top">: <?php echo strtoupper($salesOrder->buyer_city) ?></td>
						</tr>
						<tr>
							<td width="90" valign="top">(Provinsi)</td>
							<td valign="top">: <?php echo strtoupper($salesOrder->buyer_province) ?></td>
						</tr>
						<tr>
							<td width="90" valign="top">Phone (+62)</td>
							<td valign="top">: <?php echo strtoupper($salesOrder->buyer_phone) ?></td>
						</tr>

					</table>

				</div>
				<div align="left" class="header-kanan2">
					<div style="background:#B8E094;font-weight:bold;font-size:12px;width:100%;float:none;padding-left:5px;padding-top:3px;padding-bottom:3px;">SALES DETAIL</div>
					<table width="100%" cellpadding="0" cellspacing="8">
						<tr>
							<td width="90" valign="top">Site</td>
							<td valign="top">: <?php echo strtoupper($salesOrder->site) ?></td>
						</tr>
						<tr>
							<td width="90" valign="top">Site Address</td>
							<td valign="top">: <?php echo strtoupper($salesOrder->site_address) ?></td>
						</tr>
						<tr>
							<td width="90" valign="top">Payment Term</td>
							<td valign="top">: <?php echo strtoupper($salesOrder->Payment_Term) ?></td>
						</tr>
					</table>
				</div>


			</div>


			<div style="float:left;width:auto;margin-top:20px;">
				<div style="font-weight:bold;font-size:12px;width:100%;float:none">Please provide the following items according to the conditions listed below <Br><br></div>

			</div>


			<div class="tables">

				<div>

					<table width="100%" height="350" cellpadding="0" cellspacing="0" style="border:1px solid #000">
						<thead>
							<tr>
								<td width="50" height="20" style="padding:5px;background-color:#B8E094;vertical-align:middle;text-align:center;font-weight:bold;border-bottom:1px solid #000;border-right:1px solid #000;">No.</td>
								<td width="300" height="20" style="vertical-align:middle;background-color:#B8E094;text-align:center;font-weight:bold;border-bottom:1px solid #000;border-right:1px solid #000">DESCRIPTION</td>
								<td width="40" height="20" style="vertical-align:middle;background-color:#B8E094;text-align:center;font-weight:bold;border-bottom:1px solid #000;border-right:1px solid #000">QTY (UNIT)</td>
								<td width="100" height="20" style="vertical-align:middle;background-color:#B8E094;text-align:center;font-weight:bold;border-bottom:1px solid #000;border-right:1px solid #000">UNIT PRICE (Rp)</td>
								<td width="150" height="20" style="vertical-align:middle;background-color:#B8E094;text-align:center;font-weight:bold;border-bottom:1px solid #000;">TOTAL (Rp)</td>
							</tr>
							<tr>
								<td width="50" height="20" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
								<td height="20" width="300" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
								<td height="20" width="40" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
								<td height="20" width="100" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
								<td height="20" style="vertical-align:middle;text-align:center;">&nbsp;</td>
							</tr>
						</thead>
						<?php
						$no = 1;
						foreach ($details as $rowy) {
							$NamaProduk = $rowy->description;
							// if($Product_Code != '' && $Products) {
	
							// 	$NamaProduk = $Products->Product_Brand . " # " . $Products->Description . $specProd;
							// }


						?>
							<tr>
								<td width="50" height="20" style="vertical-align:top;text-align:center;border-right:1px solid #000;padding:10px"><?php echo $no; ?></td>
								<td height="20" width="300" style="vertical-align:middle;text-align:left;border-right:1px solid #000;padding:10px"><?php echo $NamaProduk ?></td>
								<td height="20" width="40" style="vertical-align:middle;text-align:center;border-right:1px solid #000;padding:10px"><?php echo $rowy->qty ?></td>
								<td height="20" width="100" style="vertical-align:middle;text-align:right;border-right:1px solid #000;padding:10px"><?php echo $moneyFormat->rupiah($rowy->unit_price) ?></td>
								<td height="20" style="vertical-align:middle;text-align:right;padding:10px"><?php echo $moneyFormat->rupiah($rowy->total) ?></td>
							</tr>

						<?php
							$no++;
						}



						?>

						<tr>
							<td width="50" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
							<td width="200" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
							<td width="80" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
							<td width="100" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
							<td style="vertical-align:middle;text-align:center;">&nbsp;</td>
						</tr>


						<tr>
							<td colspan="4" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#B8E094">Subtotal</td>
							<td height="20" style="vertical-align:middle;text-align:right;border-top:1px solid #000;padding-right:10px;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($salesOrder->subtotal) ?></td>
						</tr>

						<tr>
							<td colspan="4" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#B8E094;">TAX</td>
							<td height="20" style="vertical-align:middle;text-align:right;padding-right:10px;border-top:1px solid #000;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($salesOrder->tax) ?></td>
						</tr>

						<tr>
							<td colspan="4" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#B8E094">Shipping</td>
							<td height="20" style="vertical-align:middle;text-align:right;border-top:1px solid #000;padding-right:10px;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($salesOrder->shipping) ?></td>
						</tr>
						<tr>
							<td colspan="4" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#B8E094">Other</td>
							<td height="20" style="vertical-align:middle;text-align:right;border-top:1px solid #000;padding-right:10px;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($salesOrder->other) ?></td>
						</tr>
						<tr>
							<td colspan="4" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#B8E094;">Total</td>
							<td height="20" style="vertical-align:middle;text-align:right;border-top:1px solid #000;padding-right:10px;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($salesOrder->total) ?></td>
						</tr>


					</table>
				</div>

			</div>
			<div style="font-style:italic;font-size:12px;text-align:left;padding-top:10px;padding-left:10px;padding-bottom:14px;">

				<b>Terbilang </b>: <?php echo ucfirst(terbilang(round($salesOrder->total))) ?> rupiah


			</div>

			<div style="vertical-align:top;text-align:left;padding-bottom:10px;">

				<div style="float:left;width:500px;height:fit-content;;min-height:80px;">
					<div style="font-weight:bold;font-size:10px;width:100%;float:none;">REMARKS<Br></div>
					<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #000;width:500px !important;min-height:80px;">
						<tr>
							<td style="padding:5px"><?= $salesOrder->remarks ?></td>
						</tr>

					</table>

				</div>


			</div>
			
			<div align="left" style="margin-top:80px;padding-top:20px;">
				<table cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td height="120" valign="top" align="center">Prepared By,<div style="padding-top:30px;vertical-align:middle;text-align:center">
								<?php
								$nama1 = $salesOrder->created_by != '' ? $us->Name : 'Muhammad Nasikin';
								echo QrCode::size(60)->generate($nama1 . $salesOrder->so_no); ?></div>
						</td>
						<td height="120" valign="top" align="center">Approved By,<div style="padding-top:30px;vertical-align:middle;text-align:center">
								<?php
								$nama2 = 'Hani Septyana';
								echo QrCode::size(60)->generate($nama2 . $salesOrder->so_no); ?></div>
						</td>
						<td height="120" valign="top" align="center">Vendor,<div style="padding-top:30px;vertical-align:middle;text-align:center">&nbsp;</div>
						</td>
					</tr>
					<tr>
						<td width="150" valign="top" align="center"><div style="border-bottom:2px solid #000;width:200px;"><b><?= $nama1; ?></b></div></td>
						<td width="150" valign="top" align="center"><div style="border-bottom:2px solid #000;width:200px;"><b>{{ $nama2 }}</b></div></td>
						<td width="150" valign="top" align="center"><div style="border-bottom:2px solid #000;width:200px;"><b>{!! $salesOrder->buyer_pic ?? "&nbsp;" !!}</b></div></td>
					</tr>
					<tr>
						<td width="150" valign="top" align="center" style="padding-top:5px;">
							{{$us->Position ?? 'Warehouse Leader'}}
						</td>
						<td width="150" valign="top" align="center" style="padding-top:5px;">
							Commercial and Sustainability Manager
						</td>
						<td width="150" valign="top" align="center">Pembeli</td>
					</tr>
				</table>
			</div>
		</div>

	</div>

</body>

</html>