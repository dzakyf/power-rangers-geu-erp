<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$purchase = DB::table("purchase_request")->where("PR_No", $_GET['ID'])->first();

if ($purchase->Status == "APPROVED") {
	echo "PURCHASE REQUEST HAVE BEEN APPROVED";
	exit(0);
}

// switch ($purchase->Status) {
// 	case "APPROVAL_UPPER":

// 		$getlevelsx = DB::table("organization_structure")->where("NIK", $purchase->User_Request)->first();
// 		if ($getlevelsx->Level1 == 1) $lepel = 1;
// 		else if ($getlevelsx->Level2 == 1) $lepel = 2;
// 		else if ($getlevelsx->Level3 == 1) $lepel = 3;
// 		else if ($getlevelsx->Level4 == 1) $lepel = 4;
// 		else if ($getlevelsx->Level5 == 1) $lepel = 5;
// 		else if ($getlevelsx->Level6 == 1) $lepel = 6;
// 		else if ($getlevelsx->Level7 == 1) $lepel = 7;
// 		else if ($getlevelsx->Level8 == 1) $lepel = 8;
// 		$upper = $lepel - 1;

// 		if(strpos($purchase->Reference,"PM/KSPS") != false || strpos($purchase->Reference,"PMO/KSPS") != false) {
// 			$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", $purchase->Approval_Department)->where("Alerted", 1)->first();
// 		} else {
// 			if($upper == 3) {
// 				$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", $getlevelsx->Department)->where("Alerted", 1)->first();
				
// 			} else{
// 				$getupper = DB::table("organization_structure")->where("Level2", 1)->where("Department", $getlevelsx->divisi)->where("Alerted", 1)->first();
			
// 			}
// 		}
		

	


// 		$cekMail = DB::table("mail_logs")->where('Subject', 'LIKE', '%' . $purchase->PR_No . '%')->orderBy('ID', 'DESC')->first();
// 		if ($getupper->Email == $cekMail->Email_To) {
// 			$cekApproval =  true;
// 		} else {
// 			$cekApproval =  false;
// 		}

// 		break;
// 	case "APPROVAL_FINANCE":

// 		$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", 'FIN')->first();
// 		$cekMail = DB::table("mail_logs")->where('Subject', 'LIKE', '%' . $purchase->PR_No . '%')->orderBy('ID', 'DESC')->first();
// 		if ($getupper->Email == $cekMail->Email_To) {
// 			$cekApproval =  true;
// 		} else {
// 			$cekApproval =  false;
// 		}


// 		break;
// 	case "APPROVAL_GA":
// 		$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", 'GEA')->first();
// 		$cekMail = DB::table("mail_logs")->where('Subject', 'LIKE', '%' . $purchase->PR_No . '%')->orderBy('ID', 'DESC')->first();
// 		if ($getupper->Email == $cekMail->Email_To) {
// 			$cekApproval =  true;
// 		} else {
// 			$cekApproval =  false;
// 		}

// 		break;
// 	default:
// 		$cekApproval =  false;
// 		break;
// }

// if (!$cekApproval) {
// 	echo "Link Expired";
// 	exit(0);
// }



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
	<title>APPROVAL PURCHASE REQUEST - <?php echo $_GET['ID'] ?></title>
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
						<h3 class="fs-18">Dept Request : <?php echo $purchase->Department ?></h3>
						<p class="mb-2">
							<br>Requestor: <?php echo $purchase->User_Request_Name ?>,
							<br>Peruntukan : <?php echo $purchase->PIC ?>,
							<br>Dikirim ke : <?php echo $purchase->PIC_Address ?><br>Phone: <?php echo $purchase->Phone ?>
							<br>
						</p>
						<p>
							<span class="fw-500">PR Date:</span>
							<?php echo date("d M Y", strtotime($purchase->PR_Date)) ?> <br>
							<span class="fw-500">Priority:</span> <?php echo $purchase->Priority ?><br>
							<span class="fw-500">Current Status:</span> <?php echo $purchase->Status ?>
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

			<div class="text-right mt-3">
				<button type="button" onclick="approve();" class="btn btn-success mr-2">
					<i data-feather="printer" class="icon wh-13 mr-1"></i>
					Approve
				</button>
				<button type="button" onclick="reject();" class="btn btn-danger">Reject</button>
			</div>
		</div>

	</div>




	<script>
		function reject() {
			location.href = "approvalpr_reject?ID=<?php echo $_GET['ID'] ?>&saya=<?php echo $_GET['saya'] ?>";
		}

		function approve() {
			location.href = "approvalpr_approve?ID=<?php echo $_GET['ID'] ?>&saya=<?php echo $_GET['saya'] ?>";
		}
	</script>