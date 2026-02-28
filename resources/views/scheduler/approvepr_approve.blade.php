<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$cek = DB::table("purchase_request")->where("PR_No", $_GET['ID'])->first();
if ($cek) {

	$dept = DB::table("master_product_sub_sub_group")->where("Product_Group", $cek->Category)->where("Sub_Group", $cek->Sub_Category)->where("Sub_Sub_Group", $cek->Supplies_Type)->first();
	$LSP = $cek->Location;

	$saya = $_GET['saya'];
	$ars = substr($ars, 0, -1);
	$ax = substr($ax, 0, -1);
	$isi = substr($isi, 0, -1);
	switch ($cek->Status) {
		case "CREATED":
			$nstatus = "APPROVAL_UPPER";
			break;
		case "APPROVAL_UPPER":

			$nstatus = "APPROVAL_FINANCE";
			$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", "FIN")->first();

			break;
		case "APPROVAL_FINANCE":

			$nstatus = "APPROVAL_GA";
			$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", "GEA")->first();

			break;
		case "APPROVAL_GA":

			$nstatus = "APPROVED";

			break;
		default:
			$nstatus = 'APPROVAL_UPPER';
			break;
	}
	$notes = date("Y-m-d H:i:s") . " : User  $saya => Input Price Vendor # $Note";

	DB::update(DB::raw("UPDATE purchase_request SET Note = CONCAT('$notes <br>',Note),Status='$nstatus' WHERE PR_No='$_GET[ID]'"));

	DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$cek->PR_No', '$cek->Category', '$cek->Purchase_Category', '$cek->Purchase_Type', 'PURCHASE-REQUEST', '$nstatus', 'Mobile Approval', NOW(), '$Saya')"));

	$subjek = "IMPORTANT : PURCHASE REQUEST APPROVAL REQUEST PR NO $cek->PR_No # , Request By : $cek->User_Request_Name";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$getupper->Name</b><Br><br>
		
					Mohon dilakukan approval untuk Purchase Request tersebut. persetujuan dapat dilakukan melalui system ERP pada menu Procurement -> Request ->Approval<br><br>
					
					No PR : $cek->PR_No<br><br>
					Reference : $cek->Reference<br><br>
					Jenis Request : $cek->Category // $cek->Purchase_Category // $cek->Supplies_Type<br><br>
					Note : $cek->Note<br>
					
					<br><br>
					Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://erp.ksps.co.id/erpksps/public/approvalpr?ID=$cek->PR_No&linkkey=$linkkey&saya=$saya\' >KLIK DISINI</a><br><br>
					
					Terima kasih<br>
					PT. GREEN ENERGI UTAMA
					
		</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$getupper->Email','$subjek','$badan','UNSENT',NOW(),NOW())"));



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
		<title>APPROVAL PURCHASE SUMMARY(ORDER) - <?php echo $_GET['ID'] ?></title>
	</head>
	<div class="row">
		<div class="col-xl-12">
			<div class="notifications-card mb-30">
				<h5 class="noti-card-title">APPROVAL PURCHASE REQUEST</h5>
			</div>
			<!-- Invoice -->
			<div class="invoice-template mb-30">
				<div class="invoice-header mb-4">
					<h3>Purchase Request No</h3>
					<p>#<?php echo $_GET['ID'] ?></p>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<div class="text-left">
							<h3 class="fs-18">Note Process</h3>
							<p class="mb-2">
								<?php
								$prlog = DB::table("purchase_log")->where("Task_ID", $_GET['ID'])->get();

								foreach ($prlog as $dm) {
									echo $dm->Date_Trx . " => " . $dm->User_Trx . " Status: " . $dm->Status . " # " . $dm->Remark . "<br>";
								}

								?>
							</p>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="text-right xs-text-left">
							<h3 class="fs-18">Dept Request : <?php echo $cek->Department ?></h3>
							<p class="mb-2">
								<br>Requestor: <?php echo $cek->User_Request_Name ?>,
								<br>Peruntukan : <?php echo $cek->PIC ?>,
								<br>Dikirim ke : <?php echo $cek->PIC_Address ?><br>Phone: <?php echo $cek->Phone ?>
								<br>
							</p>
							<p>
								<span class="fw-500">PR Date:</span>
								<?php echo date("d M Y", strtotime($cek->PR_Date)) ?> <br>
								<span class="fw-500">Priority:</span> <?php echo $cek->Priority ?><br>
								<span class="fw-500">Current Status:</span> <?php echo $cek->Status ?>
							</p>
						</div>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table m-0 table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th class="text-center">Category</th>
								<th class="text-center">Sub Category</th>
								<th class="text-center">Type</th>
								<th>Product Code</th>
								<th class="text-center">Product</th>
								<th class="text-center">Qty Request</th>
								<th class="text-right">Measurement</th>
								<th class="text-right">Note</th>

							</tr>

						</thead>
						<tbody>
							<?php
							$nomor = 1;
							$purchasedtail = DB::table("purchase_request_dtl")->where("PR_No", $_GET['ID'])->get();
							$total = 0;
							foreach ($purchasedtail as $dx) {

								echo "
									<tr>
										<td>$nomor</td>
										<td class='text-center'>$dx->Category</td>
										<td class='text-center'>$dx->Purchase_Category</td>
										<td class='text-center'>$dx->Purchase_Type</td>
										<td>$dx->Product_Code</td>
										<td class='text-center'>$dx->Specification</td>
										<td class='text-center'>$dx->Qty</td>
										<td class='text-center'>$dx->Unit</td>
										<td class='text-center'>$dx->Remark</td>
									
									</tr>";
							}
							?>


						</tbody>
					</table>
				</div>

				<!-- <div class="text-right mt-3">
					<button type="button" onclick="approve();" class="btn btn-success mr-2">
						<i data-feather="printer" class="icon wh-13 mr-1"></i>
						Approve
					</button>
					<button type="button" onclick="reject();" class="btn btn-danger">Reject</button>
				</div> -->
			</div>

		</div>

		<div class="col-lg-6">
			<div class="notifications-card mb-30">
				<h5 class="noti-card-title">Proses Approval Purchase Request Telah Berhasil!</h5>

			</div>
		</div>

	</div>

<?php
} else {
	echo "Error.Data Expired!";
}
?>