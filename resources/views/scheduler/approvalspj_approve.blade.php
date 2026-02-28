<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\cekxbase\Eloquent\Builder;




$cekx = DB::table("spj")->where("Task_ID", $_GET['ID'])->where("status", "APPROVAL_UPPER")->first();
if ($cekx) {



	$LSP = $cekx->Location;
	$saya = filter_var($_GET['saya'], FILTER_SANITIZE_STRING);


	$status = $cekx->Status;

	DB::update(DB::raw("UPDATE spj SET status='SUBMITTED', approved_at = NOW(), approved_by = '$saya'  WHERE ID=$cekx->id"));



	$salt = sha1(uniqid(time(), true));
	$subjek = "IMPORTANT : PENGAJUAN SPJ DENGAN NOMOR $_POST[Task_ID]";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>Ibu Febriana Farida</b><Br><br>
		
					Telah Diajukan SPJ dengan kterangan sebagai berikut:<br><br>
					
					No Permintaan : $_POST[Task_ID]<br><br>
					Subject : $cekx->subject <br><br>
					Nilai :$cekx->total<br><br>
					Note : $cekx->note<br>
					
					<br><br>
					Anda dapat melihat pengajuan melalui link berikut ini :<a href=\'https://erp.ksps.co.id/erpksps/public/submitted-spj\' >KLIK DISINI</a><br><br>
					
					Terima kasih<br>
					PT. GREEN ENERGI UTAMA
					
		</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date, linkkey) VALUES('febriana.farida@ksps.co.id','$subjek','$badan','UNSENT',NOW(),NOW(),$salt)"));



?>

	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- All Library CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="assets/css/LineIcons.css">
		<link rel="stylesheet" href="assets/css/viewer.min.css">
		<link rel="stylesheet" href="assets/css/icofont.min.css">
		<link rel="stylesheet" href="assets/css/calendar.css">
		<!-- Custom CSS -->
		<link rel="stylesheet" href="assets/css/styles.css">
		<link rel="stylesheet" href="assets/css/responsive.css">
		<!-- Favicon -->
		<link rel="shortcut icon" href="assets/img/favicon.png">
		<title>APPROVAL SPJ REQUEST - <?php echo $_GET['ID'] ?></title>
	</head>
	<div class="row">
		<div class="col-xl-12">
			<div class="notifications-card mb-30">
				<h5 class="noti-card-title">APPROVAL SPJ REQUEST</h5>
			</div>
			<!-- Invoice -->
			<div class="invoice-template mb-30">
				<div class="invoice-header mb-4">
					<h3>SPJ No</h3>
					<p>#<?php echo $_GET['ID'] ?></p>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<div class="text-left">
							<h3 class="fs-18">Note</h3>
							<p class="mb-2">
								<?php
								echo $cekx->Note;

								?>
							</p>
							<p class="mb-2">
								<br>Requestor: <?php echo $cekx->created_by ?>
								<br>Reference : <?php echo $cekx->reference ?>
								<br>
							</p>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="text-right xs-text-left">
							<h3 class="fs-18">Detail Request : <?php echo $cekx->Department ?></h3>

							<p>
								<span class="fw-500">Date:</span>
								<?php echo date("d M Y", strtotime($cekx->created_at)) ?> <br>
								<span class="fw-500">Total:</span><b>Rp <?php echo number_format($cekx->total, 0, ".", ",") ?></b><br>
							</p>
						</div>
					</div>
				</div>


				<div class="col-lg-6">
					<div class="notifications-card mb-30">
						<h5 class="noti-card-title">Proses Approval Purchase Request Telah Berhasil!</h5>

					</div>
				</div>
			</div>

		</div>

	<?php

} else {
	echo "Data not found!";
	exit;
}
	?>