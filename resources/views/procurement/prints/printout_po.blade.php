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

$taskid = $_GET['PO_No'];
$rowc = DB::table("purchase_order")->where("PO_No", $taskid)->first();

$rowd = DB::table("supplier")->where("Supplier_ID", $rowc->Supplier)->first();


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

		<div>

			<div class="header">

				<div style="float:left;width:400px">
					<img src='./kspslogo.png' width="100" />
				</div>
				<div align="left" class="header-kanan">
					<div style="font-weight:bold;font-size:12px;">PT. GREEN ENERGI UTAMA</div>
					<div>Blok A. 9, Jl. Lb. Bulus I No.12, RT.4/RW.6</div>
					<div>Lb. Bulus, Kec. Cilandak, Kota Jakarta Selatan</div>
					<div>Daerah Khusus Ibukota Jakarta 12440, Indonesia</div>


				</div>


			</div>
			<div class="subheader">

				<div style="float:left;width:400px">
					<div style="font-weight:bold;font-size:12px;width:100%;float:none">TO<Br><br></div>
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td><b><?php echo strtoupper($rowd->Company_Name) ?></b></td>
						</tr>
						<tr>
							<td><?php echo $rowd->Address ?><Br><?php echo $rowd->City ?><Br><?php echo $rowd->Phone ?></td>
						</tr>
						<tr>
							<td>NPWP: <?php echo $rowd->NPWP ?></td>
						</tr>

					</table>

				</div>
				<div align="left" class="header-kanan">
					<table width="300" cellpadding="0" cellspacing="8">
						<tr>
							<td width="90" valign="top">PO Number</td>
							<td valign="top">: <?php echo strtoupper($rowc->PO_No) ?></td>
						</tr>
						<tr>
							<td width="90" valign="top">PO Date</td>
							<td valign="top">: <?php echo $rowc->PO_Date ?></td>
						</tr>
						<tr>
							<td width="90" valign="top">Referensi</td>
							<td valign="top">: <?php echo $rowc->PR_No; ?></td>
						</tr>
						<tr>
							<td width="90" valign="top">PO Method</td>
							<td valign="top">: <?php echo $rowc->PO_Method ?></td>
						</tr>
					</table>
				</div>


			</div>
			<div align="left" class="barisjudulpurchase_order">
				----- P U R C H A S E &nbsp;&nbsp;&nbsp; O R D E R -----
			</div>


			<div style="float:left;width:auto;margin-top:20px;">
				<div style="font-weight:bold;font-size:12px;width:100%;float:none">Please provide the following items according to the conditions listed below <Br><br></div>

			</div>


			<div class="tables">

				<div>

					<table width="100%" height="350" cellpadding="0" cellspacing="0" style="border:1px solid #000">
						<thead>
							<tr>
								<td width="50" height="20" style="padding:5px;background-color:#eee;vertical-align:middle;text-align:center;font-weight:bold;border-bottom:1px solid #000;border-right:1px solid #000;">No.</td>
								<td width="300" height="20" style="vertical-align:middle;background-color:#eee;text-align:center;font-weight:bold;border-bottom:1px solid #000;border-right:1px solid #000">Specifications of Goods / Services</td>
								<td width="40" height="20" style="vertical-align:middle;background-color:#eee;text-align:center;font-weight:bold;border-bottom:1px solid #000;border-right:1px solid #000">Quantity</td>
								<td width="100" height="20" style="vertical-align:middle;background-color:#eee;text-align:center;font-weight:bold;border-bottom:1px solid #000;border-right:1px solid #000">Unit</td>
								<td width="100" height="20" style="vertical-align:middle;background-color:#eee;text-align:center;font-weight:bold;border-bottom:1px solid #000;border-right:1px solid #000">Unit Price(Rp)</td>
								<td width="150" height="20" style="vertical-align:middle;background-color:#eee;text-align:center;font-weight:bold;border-bottom:1px solid #000;">Total Price (Rp)</td>
							</tr>
							<tr>
								<td width="50" height="20" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
								<td height="20" width="300" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
								<td height="20" width="40" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
								<td height="20" width="40" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
								<td height="20" width="100" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
								<td height="20" style="vertical-align:middle;text-align:center;">&nbsp;</td>
							</tr>
						</thead>
						<?php
						$no = 1;

						$resy = DB::table("purchase_order_dtl")->where("PO_No", $rowc->PO_No)->get();
						foreach ($resy as $rowy) {
							$Products = DB::table("product_code")->where("Product_Code", $rowy->Product_Code)->first();

							// if (trim($Products->Description) == trim($Products->Recommended_Spec)) {
							// 	$NamaProduk = $Products->Recommended_Spec;
							// } else {
							// 	$NamaProduk = trim($Products->Description) . "#" . trim($Products->Recommended_Spec);
							// }
							// if($Products->Specification != '') {
							// 	$specProd = " - " . $Products->Specification;
							// }

							$NamaProduk = $Products->Product_Brand . " # " . $Products->Description . $specProd;

							// if (trim($Products->Description) == trim($Fetch->Specification)) {
							// 	$NamaProduk = trim($Products->Description) . "#" . trim($Fetch->Recommended_Spec);
							// } else {
							// 	$NamaProduk = $Fetch->Specification. "#" .$Fetch->Recommended_Spec;
							// }


							// $row_nama = DB::table("purchase_summary_detail")->where("PR_Related", $rowy->PR_No)->where("Product_Code", $rowy->Product_Code)->first();

							// if (trim($row_nama->Specification) == trim($row_nama->Recommended_Spec)) {
							// 	$NamaProduk = $row_nama->Recommended_Spec;
							// } else {
							// 	$NamaProduk = trim($row_nama->Specification) . "#" . trim($row_nama->Recommended_Spec);
							// }

						?>
							<tr>
								<td width="50" height="20" style="vertical-align:top;text-align:center;border-right:1px solid #000;padding:10px"><?php echo $no; ?></td>
								<td height="20" width="300" style="vertical-align:middle;text-align:left;border-right:1px solid #000;padding:10px"><?php echo $NamaProduk ?></td>
								<td height="20" width="40" style="vertical-align:middle;text-align:center;border-right:1px solid #000;padding:10px"><?php echo $rowy->Qty ?></td>
								<td height="20" width="40" style="vertical-align:middle;text-align:center;border-right:1px solid #000;padding:10px"><?php echo $rowy->Unit ?></td>
								<td height="20" width="100" style="vertical-align:middle;text-align:right;border-right:1px solid #000;padding:10px"><?php echo $moneyFormat->rupiah($rowy->Price) ?></td>
								<td height="20" style="vertical-align:middle;text-align:right;padding:10px"><?php echo $moneyFormat->rupiah($rowy->Total) ?></td>
							</tr>

						<?php
							$no++;
						}



						?>

						<tr>
							<td width="50" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
							<td width="300" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
							<td width="40" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
							<td width="40" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
							<td width="100" style="vertical-align:middle;text-align:center;border-right:1px solid #000">&nbsp;</td>
							<td style="vertical-align:middle;text-align:center;">&nbsp;</td>
						</tr>


						<tr>
							<td colspan="5" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#eee">Subtotal</td>
							<td height="20" style="vertical-align:middle;text-align:right;border-top:1px solid #000;padding-right:10px;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($rowc->Subtotal) ?></td>
						</tr>

						<tr>
							<td colspan="5" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#eee">Discount</td>
							<td height="20" style="vertical-align:middle;text-align:right;border-top:1px solid #000;padding-right:10px;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($rowc->Discount) ?></td>
						</tr>

						<tr>
							<!-- <td colspan="5" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#eee;">VAT <?php echo ""; //$rowc->PPN == 0 ? "" : round(($rowc->PPN / ($rowc->Subtotal - $rowc->Discount)) * 100, 0) . "%" 
																																																												?></td> -->
							<td colspan="5" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#eee;">VAT <?= $rowc->PPN == 0 ? "" : ($rowc->PPN / ($rowc->Subtotal - $rowc->Discount)) * 100  . "%" ?></td>
							<td height="20" style="vertical-align:middle;text-align:right;padding-right:10px;border-top:1px solid #000;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($rowc->PPN) ?></td>
						</tr>

						<tr>
							<td colspan="5" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#eee">Administration Fee</td>
							<td height="20" style="vertical-align:middle;text-align:right;border-top:1px solid #000;padding-right:10px;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($rowc->Management_Fee) ?></td>
						</tr>
						<tr>
							<td colspan="5" height="20" style="vertical-align:middle;text-align:left;border-top:1px solid #000;border-right:1px solid #000;padding-left:10px;font-weight:bold;font-size:12px;background-color:#eee;">Total</td>
							<td height="20" style="vertical-align:middle;text-align:right;border-top:1px solid #000;padding-right:10px;font-weight:bold;font-size:12px;"><?php echo $moneyFormat->rupiah($rowc->Total) ?></td>
						</tr>


					</table>
				</div>

			</div>
			<div style="font-style:italic;font-size:12px;text-align:left;padding-top:10px;padding-left:10px;padding-bottom:14px;">

				<b>Terbilang </b>: <?php echo ucfirst(terbilang(round($rowc->Total))) ?> rupiah


			</div>

			<div style="vertical-align:top;text-align:left;padding-bottom:10px;">

				<div style="float:left;width:500px;height:fit-content;;min-height:80px;">
					<div style="font-weight:bold;font-size:10px;width:100%;float:none;">INFORMATION<Br></div>
					<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #000;width:500px !important;min-height:80px;">
						<tr>
							<td style="padding:5px"><?php echo $_GET['Tambahan'] ?></td>
						</tr>

					</table>

				</div>
				<div align="left" class="header-kanan3" style="padding-top:20px;width:200px;">
					<table cellpadding="0" cellspacing="0" width="200">
						<tr>
							<td width="100" valign="top" align="center">Jakarta, <?php echo ($_GET['Printed_Date'] != '') ?  date('d M Y', strtotime($_GET['Printed_Date'])) : date('d M Y'); ?></td>
						</tr>
						<tr>
							<td height="120" valign="top" align="center">PT. GREEN ENERGI UTAMA<div style="padding-top:30px;vertical-align:middle;text-align:center">
									<?php
									$nama = ($rowc->Total > 100000000) ?  "GATOT SANTOSO" : "SUCI ANDRINI";
									echo QrCode::size(60)->generate($nama . $rowc->PO_No); ?></div>
							</td>
						</tr>
						<tr>
							<td width="150" valign="top" align="center" style="border-bottom:2px solid #000"><b><?php echo $nama; ?></b></td>
						</tr>
						<tr>
							<td width="100" valign="top" align="center"></td>
						</tr>
					</table>
				</div>


			</div>

			<div style="font-style:normal;font-size:11px;text-align:left;padding-top:10px;width:500px;">

				<b>Attention! </b><Br><br>
				1. Please send order 7 days before date <?php echo ($_GET['TglDatang'] != '') ?  date('d M Y', strtotime($_GET['TglDatang'])) : date('d M Y'); ?><br>
				2. Include PO Number in the invoice.<br>
				3. If the quality of the goods does not match the demand,<br>
				&nbsp;&nbsp;&nbsp;&nbsp;the goods will be returned to the supplier at the <Br>
				&nbsp;&nbsp;&nbsp;&nbsp;expense of the supplier.



			</div>
		</div>

	</div>

</body>

</html>