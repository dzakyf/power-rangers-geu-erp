<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$proforma = DB::table("proforma")->where("Proforma_No", $_GET['ID'])->first();
if ($proforma->Status == "POSTED1") {
	$proforma_detail = DB::table("proforma_detail")->where("Proforma_No", $_GET['ID'])->first();
	$oppor = DB::table("opportunity")->where("Task_ID", $proforma_detail->Task_ID)->first();
	$oppord = DB::table("opportunity_detail")->where("Task_ID", $proforma_detail->Task_ID)->first();

	DB::update(DB::raw("UPDATE proforma set Status='POSTED', Last_Update = NOW() where Proforma_No='$_GET[ID]'"));



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
					<h5 class="noti-card-title">PO #<?php echo $oppord->PO_No ?> WAS APPROVED!</h5>

				</div>
			</div>

		</div>

	<?php
} else {
	echo "Error.Data Expired!";
}
	?>