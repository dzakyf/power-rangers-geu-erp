<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$proforma = DB::table("proforma")->where("Proforma_No", $_GET['ID'])->first();
$proforma_detail = DB::table("proforma_detail")->where("Proforma_No", $_GET['ID'])->first();
$oppor = DB::table("opportunity")->where("Task_ID", $proforma_detail->Task_ID)->first();
$oppord = DB::table("opportunity_detail")->where("Task_ID", $proforma_detail->Task_ID)->first();
$syarat = DB::table("opportunity_detail_attachment")->where("Task_ID", $proforma_detail->Task_ID)->get();
$tgl = date("d M Y", strtotime($proforma->Last_Update));
$cust = DB::table("customerz")->where("Customer_No", $proforma->Account_No)->first();
if ($proforma->Status != 'POSTED1') {
	echo 'APPROVAL SUDAH DILAKUKAN';
	exit(0);
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
	<title>APPROVAL MANAGEMENT - <?php echo $_GET['ID'] ?></title>
</head>
<div class="row">
	<div class="col-xl-12">
		<div class="notifications-card mb-30">
			<h5 class="noti-card-title">APPROVAL REQUEST</h5>
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

			<div class="table-responsive">
				<table class="table m-0 table-hover">
					<thead>
						<tr>
							<th>#</th>
							<th>BA No</th>
							<th>PO No</th>
							<th class="text-center">Project Type</th>
							<th class="text-center">Detail</th>
							<th class="text-right">Total</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$nomor = 1;
						$prdtail = DB::table("proforma_detail")->where("Proforma_No", $_GET['ID'])->get();
						$total = 0;
						foreach ($prdtail as $dx) {

							echo "
									<tr>
										<td>$nomor</td>
										<td>$proforma->BA_No</td>
										<td>$dx->PO_No</td>
										<td class='text-center'>$dx->Project_Type</td>
										<td class='text-center'>$proforma->Subject</td>
										<td class='text-right'>" . number_format($proforma->Total, 0, ",", ".") . "</td>
									</tr>
									";
							$total += $proforma->Total;
						}
						?>

						<tr class="active">
							<td colspan="4"></td>
							<td class="text-center fs-18 black-text"><b>Total:</b></td>
							<td class="text-right fs-18 black-text"><b><?php echo number_format($total, 0, ",", ".") ?></b></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="text-right mt-3">
				<button type="button" onclick="approve();" class="btn btn-success mr-2">
					<i data-feather="printer" class="icon wh-13 mr-1"></i>
					Approve
				</button>
				<button type="button" onclick="reject();" class="btn btn-danger">Reject</button>
			</div>
		</div>

	</div>
	<div class="col-lg-6">
		<div class="notifications-card mb-30">
			<h5 class="noti-card-title">PO Detail #<?php echo $oppord->PO_No ?></h5>
			<div class="list-group">

				<div class="list-group-item">
					<table class="table m-0 table-hover">
						<thead>
							<tr>
								<th>Parameter</th>
								<th class="text-left">Detail Info</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>PO No</td>
								<td class='text-left'>: <?php echo $oppord->PO_No ?></td>
							</tr>
							<tr>
								<td>Subject</td>
								<td class='text-left'>: <?php echo $oppord->Subject ?></td>
							</tr>
							<tr>
								<td>Description</td>
								<td class='text-left'>: <?php echo $oppord->Description ?></td>
							</tr>
							<tr>
								<td>PO Date</td>
								<td class='text-left'>: <?php echo $oppord->PO_Date ?></td>
							</tr>
							<tr>
								<td>Customer</td>
								<td class='text-left'>: <?php echo $oppor->Company_Name ?></td>
							</tr>
							<tr>
								<td>Contract No</td>
								<td class='text-left'>: <?php echo $oppord->Contract_No ?></td>
							</tr>
							<tr>
								<td>Reference </td>
								<td class='text-left'>: <?php echo $oppord->Reference_No ?></td>
							</tr>
							<tr>
								<td>Project Type</td>
								<td class='text-left'>: <?php echo $oppord->Project_Type ?></td>
							</tr>
							<tr>
								<td>Start - End Date</td>
								<td class='text-left'>: <?php echo $oppord->Start_Date ?> - <?php echo $oppord->End_Date ?></td>
							</tr>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="card mb-30">
			<div class="card-body">

				<div class="notifications-card mb-30">
					<h5 class="noti-card-title">Proforma Attachment</h5>
				</div>
				<?php
				$nomor = 1;
				$prdtail = DB::table("proforma_detail_file")->where("Proforma_No", $_GET['ID'])->orderBy("ID")->get();
				$total = 0;
				foreach ($prdtail as $dx) {
					if ($dx->Path_File != '') {

						$info = new SplFileInfo($dx->Path_File);
						$extfile = strtoupper($info->getExtension());

						if ($extfile != 'PDF' && $extfile != 'DOC' && $extfile != 'DOCX' && $extfile != 'XLS' && $extfile != 'XLSX') {
							echo "
												<div class=\"gallery-content\">
													<div class=\"notification-card mb-30\">
														$nomor. $dx->File_Category # $dx->Reference_No
														
														
													</div>
													<div class=\"media-body\">
														<div class=\"single-image\"> 
															<img  maxheight='400' data-original=\"proformadoc/$dx->Path_File\" src=\"proformadoc/$dx->Path_File\" alt=\"$dx->File_Category\">
														</div>
													</div>
												</div>

												";
						} else {

							echo "
												<div class=\"card-body\">
													<div class=\"notification-card-one media\">
														<a href=\"#\">
															$nomor. 	<img width=\"30\" height=\"30\" src=\"./file.png\" class=\"mr-3 img-thumbnail\">
														</a>
														<div class=\"media-body\">
															<h5 class=\"fs-14 m-0\">
																<a class=\"global-color\" href=\"proformadoc/$dx->Path_File\">$dx->File_Category</a>
															</h5>
														</div>
													</div>
												</div>

												";
						}
					} else {
						echo "
												<div class=\"card mb-30\">
													<div class=\"notification-card-one media\">
															<span class='text-dark'>
															$nomor. 		FILE FOR $dx->File_Category NOT UPLOADED
															</span>
														</div>
													
												</div>


												";
					}
					$nomor++;
				}
				?>


			</div>
		</div>

	</div>
	<script>
		function reject() {
			location.href = "approveproformafinal_reject?ID=<?php echo $_GET['ID'] ?>";
		}

		function approve() {
			location.href = "approveproformafinal_approve?ID=<?php echo $_GET['ID'] ?>";
		}
	</script>