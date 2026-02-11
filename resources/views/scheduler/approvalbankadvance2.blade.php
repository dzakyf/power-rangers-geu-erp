<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
$data = DB::table("bank_advance")->where("Task_ID",$_GET['ID'])->where("Status","APPROVAL_VP")->first();
if (!$data){
	
	echo "Data not found!";
	exit;
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
        <title>APPROVAL BANK ADVANCE REQUEST - <?php echo $_GET['ID']?></title> 
    </head>
	<div class="row">
			<div class="col-xl-12">
				<div class="notifications-card mb-30">
					<h5 class="noti-card-title">APPROVAL BANK ADVANCE REQUEST</h5>
				</div>
				<!-- Invoice -->
					<div class="invoice-template mb-30">
						<div class="invoice-header mb-4">
							<h3>Bank Advance No</h3>
							<p>#<?php echo $_GET['ID']?></p>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="text-left">
									<h3 class="fs-18">Note</h3>
									<p class="mb-2">
									<?php
									echo $data->Note;
									
									?>
									</p>
									<p class="mb-2">
										<br>Requestor: <?php echo $data->User_Responsibility_Name?>/<?php echo $data->User_Reference?>,
										<br>Description : <?php echo $data->Description?>,
										<br>Reference : <?php echo $data->Reference?>
										<br>
									</p>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="text-right xs-text-left">
									<h3 class="fs-18">Detail Request : <?php echo $purchase->Department?></h3>
									
									<p>
										<span class="fw-500">Date:</span> 
										 <?php echo date("d M Y",strtotime($data->Advance_Date))?> <br>
										<span class="fw-500">Advance Value:</span><b>Rp <?php echo number_format($data->Bank_Val,0,".",",")?></b><br>
										<span class="fw-500">Account No:</span> <?php echo $data->Acc_No?>
									</p>
								</div>
							</div>
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
function reject(){
	location.href="approvalbankadvance_reject?ID=<?php echo $_GET['ID']?>&saya=<?php echo $_GET['saya']?>";
}
function approve(){
		location.href="approvalbankadvance2_approve?ID=<?php echo $_GET['ID']?>&saya=<?php echo $_GET['saya']?>";
}

</script>		