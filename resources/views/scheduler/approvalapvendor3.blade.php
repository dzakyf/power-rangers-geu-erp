<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


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
<form id="test" name="test">
	<div class="row">
		<div class="col-xl-12">
			<div class="notifications-card mb-30">
				<h5 class="noti-card-title">APPROVAL ACCOUNT PAYABLE REQUEST</h5>
			</div>
			<!-- Invoice -->
			<div class="invoice-template mb-30">
				Request Lists to Pay for Invoice Vendors
			</div>

			<div class="table-responsive">
				<table class="table m-0 table-hover">
					<thead>
						<tr>
							<th>#</th>
							<th>APPROVE</th>
							<th>Vendor</th>
							<th>No Invoice</th>
							<th class="text-center">Invoice Date</th>
							<th class="text-center">Invoice Received by Finance</th>
							<th class="text-right">Total</th>
							<th class="text-center">Description</th>
							<th class="text-center">Reference</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$nomor = 1;
						$prdtail = DB::table("account_payable")->where("Status", 'RECEIVED_APPROVAL3')->get();
						$total = 0;
						foreach ($prdtail as $dx) {
							$suppliername = DB::table("supplier")->where("Supplier_ID", $Fetch->Vendor_ID)->first();

							echo "
									<tr>
										<td>$nomor</td>
										<td><input type='checkbox' name='approval' id='approval' value='$dx->ID' checked/></td>
										<td>$dx->Inv_Vendor</td>
										<td class='text-center'>$dx->Vendor_ID $suppliername->Company_Name</td>
										<td class='text-center'>$dx->Inv_Vendor_Date</td>
										<td class='text-center'>$dx->Date_Received</td>
										<td class='text-right'>" . number_format($dx->Total, 0, ",", ".") . "</td>
										<td class='text-center'>$dx->Subject_Payment</td>
										<td class='text-center'>$dx->Reference</td>
									</tr>
									";
							$total += $dx->Total;
							$nomor++;
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

			<div class="text-center mt-6">
				<button type="button" onclick="approve();" class="btn btn-success mr-2">
					<i data-feather="printer" class="icon wh-13 mr-1"></i>
					Approve
				</button>
				<button type="button" onclick="reject();" class="btn btn-danger">Reject</button>
			</div>
		</div>

	</div>
</form>
<script>
	function reject() {
		var values = [];
		var x = document.getElementById("test");
		var txt = "";
		var i;
		for (i = 0; i < x.length; i++) {
			if (x.elements[i].value != '') {
				if (x[i].checked) {
					values.push(x.elements[i].value);

				}
			}
		}
		values.join(",");
		location.href = "approvalapvendor3_reject?ID=<?php echo $_GET['ID'] ?>&listapp=" + values;
	}

	function approve() {
		var values = [];
		var x = document.getElementById("test");
		var txt = "";
		var i;
		for (i = 0; i < x.length; i++) {
			if (x.elements[i].value != '') {
				if (x[i].checked) {
					values.push(x.elements[i].value);

				}
			}
		}
		values.join(",");


		location.href = "approvalapvendor3_approve?ID=<?php echo $_GET['ID'] ?>&listapp=" + values;
	}
</script>
