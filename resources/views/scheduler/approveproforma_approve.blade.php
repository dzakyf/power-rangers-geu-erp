<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

if (!(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	exit();
}

$proforma = DB::table("proforma")->where("Proforma_No", $_GET['ID'])->first();
if ($proforma) {
	$proforma_detail = DB::table("proforma_detail")->where("Proforma_No", $_GET['ID'])->first();
	$oppor = DB::table("opportunity")->where("Task_ID", $proforma_detail->Task_ID)->first();
	$oppord = DB::table("opportunity_detail")->where("Task_ID", $proforma_detail->Task_ID)->first();

	$salt = md5(date('c'));
	$getlevelsx = DB::table("organization_structure")->where("Department", "OPS")->where("Level2", "1")->where("Alerted", "1")->first();
	$mailto = $getlevelsx->Email;
	$subjek = "MOBILE FINAL APPROVAL BAST NO $proforma->BA_No # DATE : $proforma->BA_Date";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>" . $getlevelsx->Name . "</b><Br><br>
		
					Mohon dilakukan Final approval untuk BAST tersebut. persetujuan dapat dilakukan menggunakan link berikut ini :<br><br>
					
					<a href=\"https://erp.ksps.co.id/erpksps/public/approveproformafinal?ID=$proforma->Proforma_No&key=$salt\">Approval</a>
					
					
					<br><br>
					
					Terima kasih<br>
					PT. GREEN ENERGY UTAMA
					
		</div>";
	DB::update(DB::raw("UPDATE proforma set Status='POSTED1', Last_Update = NOW() where Proforma_No='$_GET[ID]' AND Status='PROPOSED1'"));
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$mailto','$subjek','$badan','UNSENT',NOW(),NOW())"));



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
		<title>APPROVAL MANAGEMENT - <?php echo $_GET['ID'] ?></title>


		<script>
			// if (location.protocol !== 'https:') {
			// 	location.replace('https:${location.href.substring(location.protocol.length)}');
			// }
		</script>
	</head>
	<div class="row">
		<div class="col-xl-12">
			<div class="notifications-card mb-30">
				<h5 class="noti-card-title">APPROVAL REQUEST - APPROVE</h5>
			</div>
			<!-- Invoice -->
			<div class="invoice-template mb-30">
				<div class="invoice-header mb-4">
					<h3>Proforma No</h3>
					<p>#<?php echo $_GET['ID'] ?></p>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<div class="text-left">
							<h3 class="fs-18"><?php echo $proforma->Account_Name ?></h3>
							<p class="mb-2">
								<?php echo $oppor->Address ?>,
								<br><?php echo $oppor->Street ?>,
								<br><?php echo $oppor->City ?>,

							</p>
							<p>
								<span class="fw-500">Email:</span>
								<?php echo $oppor->Email ?> <br>
								<span class="fw-500">Phone:</span> <?php echo $oppor->Phone ?>
							</p>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="text-right xs-text-left">
							<h3 class="fs-18">Customer PIC : <?php echo $oppor->PIC ?></h3>
							<p class="mb-2"><?php echo $oppor->PIC_Function ?>,
								<br>PH. <?php echo $oppor->PIC_Phone ?>,
								<br>Ref : <?php echo $proforma_detail->Reference_No ?>,
								<br>Contract No : <?php echo $proforma_detail->Contract_No ?>
								<br>
							</p>
							<p>
								<span class="fw-500">Proforma Date:</span>
								<?php echo date("d M Y", strtotime($proforma->Dateinsert)) ?> <br>
								<span class="fw-500">Term Mode:</span> <?php echo $proforma_detail->Termin ?>
							</p>
						</div>
					</div>
				</div>


			</div>
			<div class="col-lg-6">
				<div class="notifications-card mb-30">
					<h5 class="noti-card-title">PO Detail #<?php echo $oppord->PO_No ?> WAS APPROVED!</h5>

				</div>
			</div>

		</div>

	<?php
} else {
	echo "Error.Data Expired!";
}
	?>