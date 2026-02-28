<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$cekx = DB::table("reimbursement")->where("Reimbursement_No", $_GET['ID'])->where("Status", "APPROVAL_DIRECTOR")->first();
$data = DB::table("reimbursement")->where("Reimbursement_No", $_GET['ID'])->first();
if ($cekx) {
	DB::update(DB::raw("UPDATE reimbursement SET Status='REJECT_DIRECTOR' WHERE ID=$cekx->ID"));

	$subjek = "REJECT : REIMBURSEMENT REQUEST NO $cekx->Reimbursement_No # :: Request By : $cekx->User_Responsibility_Name";

	$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", $data->Department)->where("Alerted", 1)->first();

	$linkkey = md5(date("Ym") . "789213123122124");
	//$subjek = "IMPORTANT : APPROVAL PERMINTAAN REIMBURSEMENT $cekx->Reimbursement_No # , Request By : $Saya";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>Bapak/Ibu $cekx->User_Responsibility_Name</b><Br><br>
		
					Permintaan Reimbursement dengan rincian sebagai berikut:<br><br>
					
					No Permintaan : $cekx->Reimbursement_No<br><br>
					User : $cekx->User_Responsibility_Name / $cekx->User_Responsibility_ID<br><br>
					Reference : $cekx->Reference<br><br>
					Nilai : $cekx->Bank_Val<br><br>
					Note : $cekx->Description<br>
					
					<br><br>
					<b>Telah Ditolak Oleh Direktur</b>
					
					Terima kasih<br>
					GREEN ENERGI UTAMA
					
		</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$getupper->Email','$subjek','$badan','UNSENT',NOW(),NOW())"));
}


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
	<title>APPROVAL REIMBURSEMENT REQUEST - <?php echo $_GET['ID'] ?></title>
</head>
<div class="row">
	<div class="col-xl-12">
		<div class="notifications-card mb-30">
			<h5 class="noti-card-title">APPROVAL REIMBURSEMENT REQUEST</h5>
		</div>
		<!-- Invoice -->
		<div class="invoice-template mb-30">
			<div class="invoice-header mb-4">
				<h3>Reimbursement No</h3>
				<p>#<?php echo $_GET['ID'] ?></p>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<div class="text-left">
						<h3 class="fs-18">Note</h3>
						<p class="mb-2">
							<?php
							echo $data->Description;

							?>
						</p>
						<p class="mb-2">
							<br>Requestor: <?php echo $data->User_Responsibility_Name ?>/<?php echo $data->User_Responsibility_ID ?>,
							<br>Description : <?php echo $data->Description ?>,
							<br>Reference : <?php echo $data->Reference_No ?>
							<br>
						</p>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="text-right xs-text-left">
						<h3 class="fs-18">Detail Request : <?php echo $data->Department; ?></h3>

						<p>
							<span class="fw-500">Date:</span>
							<?php echo date("d M Y", strtotime($data->Reimbursement_Date)) ?> <br>
							<span class="fw-500">Amount:</span><b>Rp <?php echo number_format($data->Total, 0, ".", ",") ?></b><br>
							<span class="fw-500">Account No:</span> <?php echo $data->User_Reference ?>
						</p>
					</div>
				</div>
			</div>


			<div class="col-lg-6">
				<div class="notifications-card mb-30">
					<h5 class="noti-card-title">Proses Approval Reimbursement Telah Ditolak!</h5>

				</div>
			</div>
		</div>

	</div>