<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
$purchase = DB::table("purchase_summary")->where("Task_ID",$_GET['ID'])->first();

if($purchase->Status == "CLOSED") {
	echo "PO TELAH DI APPROVE";
	exit(0);
}

$cekMailLog =  DB::table("mail_logs")->where("Contents", 'LIKE', "%$_GET[ID]%")->orderBy('ID', 'DESC')->first();

$cekmail = DB::table("purchase_mail")->where("Status", $purchase->Status)->first();

if($cekmail->Email_To != $cekMailLog->Email_To) {
	echo "PO TELAH DI APPROVE";
	exit(0);
}

$pr = DB::table("purchase_request")->where("PR_No",$purchase->PR_No)->first();

$getsupp = DB::table("supplier")->get();
foreach($getsupp as $vendor){
	
	$vendors[$vendor->Supplier_ID]['name'] = $vendor->Company_Name;
	$vendors[$vendor->Supplier_ID]['tempo'] = $vendor->Payment;
	
}
if ($purchase->V5_ID != ''){
	$jumvendor = 5;
}elseif ($purchase->V4_ID != ''){
	$jumvendor = 4;
}elseif ($purchase->V3_ID != ''){
	$jumvendor = 3;
}elseif ($purchase->V2_ID != ''){
	$jumvendor = 2;
}else{
	$jumvendor = 1;
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
        <title>APPROVAL PURCHASE ORDER - <?php echo $_GET['ID']?></title> 
    </head>
<div class="row">
			<div class="col-xl-12">
				<div class="notifications-card mb-30">
					<h5 class="noti-card-title">APPROVAL PURCHASE SUMMARY (ORDER)</h5>
				</div>
				<!-- Invoice -->
					<div class="invoice-template mb-30">
						<div class="invoice-header mb-4">
							<h3>Purchase Summary No</h3>
							<p>#<?php echo $_GET['ID']?></p>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="text-left">
									<h3 class="fs-18">Note Process</h3>
									<p class="mb-2">
										<?php echo $purchase->Note?>,
									</p>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="text-right xs-text-left">
									<h3 class="fs-18">PR No : <?php echo $purchase->PR_No?></h3>
									<p class="mb-2">
										<br>Requestor: <?php echo $pr->User_Request_Name?>,
										<br>Peruntukan : <?php echo $pr->PIC?>,
										<br>Dikirim ke : <?php echo $pr->PIC_Address?>
										<br>
									</p>
									<p>
										<span class="fw-500">PR Date:</span> 
										 <?php echo date("d M Y",strtotime($pr->PR_Date))?> <br>
										<span class="fw-500">Priority:</span> <?php echo $purchase->Priority?><br>
										<span class="fw-500">Current Status:</span> <?php echo $purchase->Status?>
									</p>
								</div>
							</div>
						</div>

						<div class="table-responsive">
							<table class="table m-0 table-hover">
								<thead>
									<tr>
										<th rowspan="2">#</th>
										<th rowspan="2">Product Code</th>
										<th  rowspan="2" class="text-center">Product</th>
										<th rowspan="2" class="text-center">Qty Request</th>
										<th rowspan="2" class="text-right">Default Price</th>
										<?php 
											for ($vnomor = 1;$vnomor <= $jumvendor;$vnomor++){
												if ($vnomor = 1) echo "<th class=\"text-center\" colspan='3'>".$vendors[$purchase->V1_ID]['name']."</th>";
												if ($vnomor = 2) echo "<th class=\"text-center\" colspan='3'>".$vendors[$purchase->V2_ID]['name']."</th>";
												if ($vnomor = 3) echo "<th class=\"text-center\" colspan='3'>".$vendors[$purchase->V3_ID]['name']."</th>";
												if ($vnomor = 4) echo "<th class=\"text-center\" colspan='3'>".$vendors[$purchase->V4_ID]['name']."</th>";
												if ($vnomor = 5) echo "<th class=\"text-center\" colspan='3'>".$vendors[$purchase->V5_ID]['name']."</th>";
												

												
											}
										
										?>
									</tr>
									<tr>
									
										<?php 
											for ($vnomor = 1;$vnomor <= $jumvendor;$vnomor++){
												echo "<th class=\"text-center\">Price @Unit</th>";
												echo "<th class=\"text-center\">Total</th>";
												echo "<th class=\"text-center\">Recommended</th>";

												
											}
										
										?>
									</tr>
								</thead>
								<tbody>
								<?php
								$nomor = 1;
								$prdtail = DB::table("purchase_summary_detail")->where("Task_ID",$_GET['ID'])->get();
								$total = 0;
								foreach($prdtail as $dx){
									
									echo "
									<tr>
										<td>$nomor</td>
										<td>$dx->Product_Code</td>
										<td class='text-center'>$dx->Specification</td>
										<td class='text-center'>$dx->Qty</td>
										<td class='text-right'>".number_format($dx->Default_Price,0,",",".")."</td>
									
									";
									
									for ($vnomor = 1;$vnomor <= $jumvendor;$vnomor++){
										if ($vnomor = 1) {
											$basik =  $dx->V1_Propose;
											$ttl =  $dx->V1_TOP;
											$win =  ($dx->V1_Win == 1)? "YES":"NO";
											echo "<td class='text-right'>".number_format($basik,0,",",".")."</td>";
											echo "
												<td class='text-right'>".number_format($ttl,0,",",".")."</td>
												<td class='text-center'>$win</td>

											";
										}
										if ($vnomor = 2) {
											$basik =  $dx->V2_Propose;
											$ttl =  $dx->V2_TOP;
											$win =  ($dx->V2_Win == 1)? "YES":"NO";
											echo "<td class='text-right'>".number_format($basik,0,",",".")."</td>";
											echo "
												<td class='text-right'>".number_format($ttl,0,",",".")."</td>
												<td class='text-center'>$win</td>

											";
										}
										if ($vnomor = 3) {
											$basik =  $dx->V3_Propose;
											$ttl =  $dx->V3_TOP;
											$win =  ($dx->V3_Win == 1)? "YES":"NO";
											echo "<td class='text-right'>".number_format($basik,0,",",".")."</td>";
											echo "
												<td class='text-right'>".number_format($ttl,0,",",".")."</td>
												<td class='text-center'>$win</td>

											";

										}
										if ($vnomor = 4) {
											$basik =  $dx->V4_Propose;
											$ttl =  $dx->V4_TOP;
											$win =  ($dx->V4_Win ==1)? "YES":"NO";
											echo "<td class='text-right'>".number_format($basik,0,",",".")."</td>";
											echo "
												<td class='text-right'>".number_format($ttl,0,",",".")."</td>
												<td class='text-center'>$win</td>

											";

										}
										if ($vnomor = 5) {
											$basik =  $dx->V5_Propose;
											$ttl =  $dx->V5_TOP;
											$win =  ($dx->V5_Win == 1)? "YES":"NO";
											echo "<td class='text-right'>".number_format($basik,0,",",".")."</td>";
											echo "
												<td class='text-right'>".number_format($ttl,0,",",".")."</td>
												<td class='text-center'>$win</td>

											";

										}
										
										
										

										
									}
										
										
										
									echo "</tr>";
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
			
			<div class="col-lg-6">
				<div class="notifications-card mb-30">
					<h5 class="noti-card-title">Vendor Detail  #<?php echo $purchase->V1_ID?></h5>
					<div class="list-group">
						
						<div class="list-group-item">
							<table class="table m-0 table-hover">
								<thead>
									<tr>
										<th>Vendor <?php echo $vendors[$purchase->V1_ID]['name']?></th>
										<th class="text-left">Detail Info</th>
									</tr>
								</thead>
								<tbody>
								<tr>
										<td>Term of Payment</td>
										<td  class='text-left'>: <?php echo $vendors[$purchase->V1_ID]['tempo']?></td>
								</tr>
								<tr>
										<td>Currency</td>
										<td  class='text-left'>: <?php echo $purchase->V1_Cur?></td>
								</tr>
								<tr>
										<td>Fee Management</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V1_Fee_Mgt,0,",",".")?></td>
								</tr>
								<tr>
										<td>PPN(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V1_VAT?></td>
								</tr>
								<tr>
										<td>PPH(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V1_PPH?></td>
								</tr>
								<tr>
										<td>Delivery Cost</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V1_DC,0,",",".")?></td>
								</tr>
								<tr>
										<td>Discount </td>
										<td  class='text-left'>: <?php echo $purchase->V1_Discount?></td>
								</tr>
								<tr>
										<td>Note</td>
										<td  class='text-left'>: <?php echo $purchase->V1_Note?></td>
								</tr>
								<tr>
										<td>Estimated Total PO</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V1_Total,0,",",".")?></td>
								</tr>
								
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<?php if ($jumvendor >= 2){
			?>	
			<div class="col-lg-6">
				<div class="notifications-card mb-30">
					<h5 class="noti-card-title">Vendor Detail  #<?php echo $purchase->V2_ID?></h5>
					<div class="list-group">
						
						<div class="list-group-item">
							<table class="table m-0 table-hover">
								<thead>
									<tr>
										<th>Vendor <?php echo $vendors[$purchase->V2_ID]['name']?></th>
										<th class="text-left">Detail Info</th>
									</tr>
								</thead>
								<tbody>
								<tr>
										<td>Term of Payment</td>
										<td  class='text-left'>: <?php echo $vendors[$purchase->V2_ID]['tempo']?></td>
								</tr>
								<tr>
										<td>Currency</td>
										<td  class='text-left'>: <?php echo $purchase->V2_Cur?></td>
								</tr>
								<tr>
										<td>Fee Management</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V2_Fee_Mgt,0,",",".")?></td>
								</tr>
								<tr>
										<td>PPN(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V2_VAT?></td>
								</tr>
								<tr>
										<td>PPH(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V2_PPH?></td>
								</tr>
								<tr>
										<td>Delivery Cost</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V2_DC,0,",",".")?></td>
								</tr>
								<tr>
										<td>Discount </td>
										<td  class='text-left'>: <?php echo $purchase->V2_Discount?></td>
								</tr>
								<tr>
										<td>Note</td>
										<td  class='text-left'>: <?php echo $purchase->V2_Note?></td>
								</tr>
								<tr>
										<td>Estimated Total PO</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V2_Total,0,",",".")?></td>
								</tr>
								
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>	
				
			<?php	
			}
			?>
			<?php if ($jumvendor >= 3){
			?>	
			<div class="col-lg-6">
				<div class="notifications-card mb-30">
					<h5 class="noti-card-title">Vendor Detail  #<?php echo $purchase->V3_ID?></h5>
					<div class="list-group">
						
						<div class="list-group-item">
							<table class="table m-0 table-hover">
								<thead>
									<tr>
										<th>Vendor <?php echo $vendors[$purchase->V3_ID]['name']?></th>
										<th class="text-left">Detail Info</th>
									</tr>
								</thead>
								<tbody>
								<tr>
										<td>Term of Payment</td>
										<td  class='text-left'>: <?php echo $vendors[$purchase->V3_ID]['tempo']?></td>
								</tr>
								<tr>
										<td>Currency</td>
										<td  class='text-left'>: <?php echo $purchase->V3_Cur?></td>
								</tr>
								<tr>
										<td>Fee Management</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V3_Fee_Mgt,0,",",".")?></td>
								</tr>
								<tr>
										<td>PPN(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V3_VAT?></td>
								</tr>
								<tr>
										<td>PPH(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V3_PPH?></td>
								</tr>
								<tr>
										<td>Delivery Cost</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V3_DC,0,",",".")?></td>
								</tr>
								<tr>
										<td>Discount </td>
										<td  class='text-left'>: <?php echo $purchase->V3_Discount?></td>
								</tr>
								<tr>
										<td>Note</td>
										<td  class='text-left'>: <?php echo $purchase->V3_Note?></td>
								</tr>
								<tr>
										<td>Estimated Total PO</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V3_Total,0,",",".")?></td>
								</tr>
								
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>	
				
			<?php	
			}
			?>
			<?php if ($jumvendor >= 4){
			?>	
			<div class="col-lg-6">
				<div class="notifications-card mb-40">
					<h5 class="noti-card-title">Vendor Detail  #<?php echo $purchase->V4_ID?></h5>
					<div class="list-group">
						
						<div class="list-group-item">
							<table class="table m-0 table-hover">
								<thead>
									<tr>
										<th>Vendor <?php echo $vendors[$purchase->V4_ID]['name']?></th>
										<th class="text-left">Detail Info</th>
									</tr>
								</thead>
								<tbody>
								<tr>
										<td>Term of Payment</td>
										<td  class='text-left'>: <?php echo $vendors[$purchase->V4_ID]['tempo']?></td>
								</tr>
								<tr>
										<td>Currency</td>
										<td  class='text-left'>: <?php echo $purchase->V4_Cur?></td>
								</tr>
								<tr>
										<td>Fee Management</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V4_Fee_Mgt,0,",",".")?></td>
								</tr>
								<tr>
										<td>PPN(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V4_VAT?></td>
								</tr>
								<tr>
										<td>PPH(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V4_PPH?></td>
								</tr>
								<tr>
										<td>Delivery Cost</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V4_DC,0,",",".")?></td>
								</tr>
								<tr>
										<td>Discount </td>
										<td  class='text-left'>: <?php echo $purchase->V4_Discount?></td>
								</tr>
								<tr>
										<td>Note</td>
										<td  class='text-left'>: <?php echo $purchase->V4_Note?></td>
								</tr>
								<tr>
										<td>Estimated Total PO</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V4_Total,0,",",".")?></td>
								</tr>
								
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>	
				
			<?php	
			}
			?>
			<?php if ($jumvendor >= 5){
			?>	
			<div class="col-lg-6">
				<div class="notifications-card mb-50">
					<h5 class="noti-card-title">Vendor Detail  #<?php echo $purchase->V5_ID?></h5>
					<div class="list-group">
						
						<div class="list-group-item">
							<table class="table m-0 table-hover">
								<thead>
									<tr>
										<th>Vendor <?php echo $vendors[$purchase->V5_ID]['name']?></th>
										<th class="text-left">Detail Info</th>
									</tr>
								</thead>
								<tbody>
								<tr>
										<td>Term of Payment</td>
										<td  class='text-left'>: <?php echo $vendors[$purchase->V5_ID]['tempo']?></td>
								</tr>
								<tr>
										<td>Currency</td>
										<td  class='text-left'>: <?php echo $purchase->V5_Cur?></td>
								</tr>
								<tr>
										<td>Fee Management</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V5_Fee_Mgt,0,",",".")?></td>
								</tr>
								<tr>
										<td>PPN(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V5_VAT?></td>
								</tr>
								<tr>
										<td>PPH(%)</td>
										<td  class='text-left'>: <?php echo $purchase->V5_PPH?></td>
								</tr>
								<tr>
										<td>Delivery Cost</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V5_DC,0,",",".")?></td>
								</tr>
								<tr>
										<td>Discount </td>
										<td  class='text-left'>: <?php echo $purchase->V5_Discount?></td>
								</tr>
								<tr>
										<td>Note</td>
										<td  class='text-left'>: <?php echo $purchase->V5_Note?></td>
								</tr>
								<tr>
										<td>Estimated Total PO</td>
										<td  class='text-left'>: <?php echo number_format($purchase->V5_Total,0,",",".")?></td>
								</tr>
								
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>	
				
			<?php	
			}
			?>
			

<script>
function reject(){
	location.href="approvalpo_reject?ID=<?php echo $_GET['ID']?>&saya=<?php echo $_GET['saya']?>";
}
function approve(){
		location.href="approvalpo_approve?ID=<?php echo $_GET['ID']?>&saya=<?php echo $_GET['saya']?>";
}

</script>		