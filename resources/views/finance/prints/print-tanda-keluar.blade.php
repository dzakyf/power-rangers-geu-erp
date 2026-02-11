@extends('layout.plugins')
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

if (!Auth::check()) {
	error_log("User Not Login");
	exit;
}

function rupiah($angka)
{
	$rupiah = number_format($angka, 0, '', '.');
	return $rupiah;
}

$type = $_GET['type'];
$id = $_GET['id'];
// $type = 'bank_advanced';
// $id = 'BA/GEU-HO/210405-023732';

switch ($type) {
	case 'bank_advanced':
		$cek = DB::table("bank_advance")->where("Task_ID", 'LIKE', "$id")->first();
		$cek_detail = DB::table("bank_advance_dtl")->where("Task_ID", $id)->get();

		$nomor = $cek->Task_ID;
		$tanggal = date("Y-m-d");
		$nomor_cek = "";
		$nomor_pum = "";
		break;
	case 'cash_advanced':
		$cek = DB::table("cash_advance")->where("Task_ID", $id)->first();
		$cek_detail = DB::table("cash_advance_dtl")->where("Task_ID", $cek->Task_ID)->get();
		$nomor = $cek->Task_ID;
		$tanggal = date("Y-m-d");
		$nomor_cek = "";
		$nomor_pum = "";
		break;
	case 'cash_in':
		$cek = DB::table("cash")->where("Task_ID", $id)->where("Cash_Type","LIKE","'CASH_IN%'")->first();
		$cek_detail = DB::table("cash_dtl")->where("Cash_No", $cek->Cash_No)->orWhere("ID", $cek->ID)->get();
		$nomor = $cek->Task_ID;
		$tanggal = date("Y-m-d");
		$nomor_cek = "";
		$nomor_pum = "";
		break;
	case 'cash_out':
		$cek = DB::table("cash")->where("Task_ID", $id)->where("Cash_Type","LIKE","'CASH_OUT%'")->first();
		$cek_detail = DB::table("cash_dtl")->where("Cash_No", $cek->Cash_No)->orWhere("ID", $cek->ID)->get();
		$nomor = $cek->Task_ID;
		$tanggal = date("Y-m-d");
		$nomor_cek = "";
		$nomor_pum = "";
		break;
	case 'bank_in':
		$cek = DB::table("bank")->where("Task_ID", $id)->where("Bank_Type","LIKE","'BANK_IN%'")->first();
		$cek_detail = DB::table("bank_dtl")->where("Bank_No", $cek->Bank_No)->orWhere("ID", $cek->ID)->get();
		$nomor = $cek->Task_ID;
		$tanggal = date("Y-m-d");
		$nomor_cek = "";
		$nomor_pum = "";
		break;
	case 'bank_out':
		$cek = DB::table("bank")->where("Task_ID", $id)->where("Bank_Type","LIKE","'BANK_OUT%'")->first();
		$cek_detail = DB::table("bank_dtl")->where("Bank_No", $cek->Bank_No)->orWhere("ID", $cek->ID)->get();
		$nomor = $cek->Task_ID;
		$tanggal = date("Y-m-d");
		$nomor_cek = "";
		$nomor_pum = "";
		break;
	default:
		$cek = false;
		break;
}
$jumlah = 0;
$ket_sum = '';
?>

<html>
<link type="text/css" rel="stylesheet" href="normalize.min.css" />

<!-- Load paper.css for happy printing -->
<link rel="stylesheet" href="paper.css" />
<style type="text/css">
	@page {
		size: A4
	}

	.sheet {
		overflow: visible;
		height: auto !important;
	}

	html,
	table {
		font-family: arial;
		font-size: 14px;


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
	<section class="sheet padding-10mm">

		<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
			<tr>
				<td width="40%">
					<img src="{{ asset('kspslogo.png') }}" width="120px" class="no-border" alt="Logo">
				</td>
				<td width="59%">
					<h2>PT. GREEN ENERGY UTAMA</h2>
					Gedung Kopkarla 1 Jl. Lebak Bulus Raya No. 7 RT. 003 RW.002 <br />
					Lebak Bulus Cilandak Kota Administrasi Jakarta Selatan <br />
					DKI Jakarta, 12310,Indonesia
				</td>
			</tr>
		</table>
		<div style="width: 100%;">
			<div style="width:30%;float:left">
				<span style="padding-bottom:5px;font-size:14px;padding-top:5px;text-align:left;font-weight:bold;padding-right:5px;border-top:3px solid #000000;border-bottom:double;float:left;width:100%;">
				Payee
				</span> <br>
				<?= $cek->User_Responsibility_Name ?>
			</div>

			
			<div style="float:left;width:60%">
				<div style="margin-left:25%;padding-bottom:5px;font-size:25px;padding-top:5px;text-align:center;padding-right:5px;width:90%;border-top:3px solid #000000;border-bottom:double">
					Pengeluaran Kas
				</div>
			</div>
		</div>

		<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
			<tr>
				<td width="46%">&nbsp;</td>
				<td width="10%">Nomor PUM</td>
				<td width="1%">:</td>
				<td><?= $nomor; ?></td>
			</tr>
			<tr>
				<td width="46%">&nbsp;</td>
				<td width="10%">Tanggal</td>
				<td width="1%">:</td>
				<td><?= $tanggal; ?></td>
			</tr>
			<tr>
				<td width="46%">&nbsp;</td>
				<td width="10%">No. Cek</td>
				<td width="1%">:</td>
				<td><?= $nomor_cek; ?></td>
			</tr>
			<!-- <tr>
				<td width="46%">&nbsp;</td>
				<td width="10%">No. PUM</td>
				<td width="1%">:</td>
				<td><?= $nomor_pum; ?></td>
			</tr> -->
		</table>

		<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">

		<tr>
					<td width="15%" style="padding : 5px 0 5px 0 ;border-top: #000000 2px solid;border-bottom: #000000 2px solid;">Akun No</td>
					<td width="58%" style="padding : 5px 0 5px 0 ;border-top: #000000 2px solid;border-bottom: #000000 2px solid;">Keterangan</td>
					<td width="20%" style="padding : 5px 0 5px 0 ;border-top: #000000 2px solid;border-bottom: #000000 2px solid;text-align: right;">Nilai</td>
				</tr>
				<?php foreach($cek_detail as $data) { 
					if( $data->Acc_No != '') { 
				?>
				<tr>
					<td width="15%"><?php echo $data->Acc_No; ?></td>
					<td width="58%"><?php echo $data->Remark; ?></td>
					<td width="20%" style="text-align:right"><?php echo rupiah($data->Bank_Val); ?></td>
				</tr>	
				<?php 
					$jumlah += $data->Bank_Val + 0;
					} else {
						$ket_sum = $data->Remark;
				?>
				 <tr>
					<td width="15%"><?php echo $cek->Acc_No; ?></td>
					<td width="58%"><?php echo $data->Remark; ?></td>
					<td width="20%" style="text-align:right"><?php echo rupiah(-1*$cek->Bank_Val); ?></td>
				</tr>	
				<?php 			
					}
				 } ?>
				
				<tr>
					<td width="15%" style="padding : 5px 0 0 0 ;border-top: #000000 2px solid;">&nbsp</td>
					<td width="58%" style="padding : 5px 0 0 0 ;border-top: #000000 2px solid;">&nbsp</td>
					<td width="20%" style="padding : 5px 0 0 0 ;border-top: #000000 2px solid;">&nbsp</td>
				</tr>

		</table>

		<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">
			<tr>
				<td width="40%" style="padding : 5px 0 5px 0 ;border-top: #000000 2px solid;border-bottom: #000000 2px solid;">Keterangan</td>
				<td width="20%" style="padding : 5px 0 5px 0 ;text-align: right;">Total</td>
				<td width="40%" style="padding : 5px 0 5px 0 ;text-align: right;"><?= rupiah($jumlah - $cek->Bank_Val) ?></td>
			</tr>
			<tr>
				<td width="40%" style="padding : 5px 0 15px 0 ;border-bottom: #000000 2px dashed;"><?= $ket_sum ?></td>
				<td width="20%" style="padding : 5px 0 5px 0 ;">&nbsp;</td>
				<td width="40%" style="padding : 5px 0 5px 0 ;">&nbsp;</td>
			</tr>
		</table>
		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td style="width: 30%;text-align: center;">Dibuat Oleh,</td>
				<td style="width: 30%;text-align: center;">Disetujui Oleh,</td>
				<td style="width: 30%;text-align: center;">Diterima Oleh,</td>
			</tr>
			<tr>
				<td style="width: 30%;text-align: center;"><br /><br /><br /><br /></td>
				<td style="width: 30%;text-align: center;"><br /><br /><br /><br /></td>
				<td style="width: 30%;text-align: center;"><br /><br /><br /><br /><?= $cek->User_Responsibility_Name ?>
			</tr></td> 
			<tr>
				<td style="width: 30%;">
					<hr width="90%">&nbsp;&nbsp;Tgl.
				</td>
				<td style="width: 30%;">
					<hr width="90%">&nbsp;&nbsp;Tgl.
				</td>
				<td style="width: 30%;">
					<hr width="90%">&nbsp;&nbsp;Tgl.
				</td>
			</tr>
		</table>
	</section>

</body>

</html>