<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


$listapp = $_GET['listapp'];
if (!$listapp){
	echo "no data found";
	exit;
}


$ismobile = 0;
$container = $_SERVER['HTTP_USER_AGENT'];

$useragents = array ( 
'Blazer' ,
'Palm' ,
'Handspring' ,
'Nokia' ,
'Kyocera',
'Samsung' ,
'Motorola' ,
'Smartphone', 
'Windows CE' ,
'Blackberry' ,
'WAP' ,
'SonyEricsson',
'PlayStation Portable', 
'LG', 
'MMP',
'OPWV',
'Symbian',
'EPOC',
); 

	foreach ( $useragents as $useragents ) { 
	 if(strstr($container,$useragents)) {
	   $ismobile = 1;
	 }
	}
	if ( $ismobile == 1 ) {
	$saya =  "mobile device => ";
	$saya .=  $_SERVER['HTTP_USER_AGENT'];
	}
	

$notes = date("Y-m-d H:i:s")." : User  $saya => REJECT Payment # $_GET[Note]";

	
$adaapprove = "";
$adareject = "";
$pecah = explode(",",$listapp);
foreach($pecah as $data){
	$ap = DB::table("account_payable")->where("ID",$data)->first();

		$notes = date("Y-m-d H:i:s")." : User  $saya => Reject Request Payment # $_GET[Note]";

		DB::update(DB::raw("UPDATE account_payable SET Status='RECEIVED_APPROVAL1',Note=CONCAT('$notes \n',Note) WHERE ID=$data "));
		$adaapprove .="# Nomor Invoice $ap->Inv_Vendor Tanggal $ap->Inv_Vendor_Date, nomor Referensi $ap->Reference => $ap->Subject_Payment<br><br>";
		
		
	
	
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
        <title>APPROVAL MANAGEMENT</title> 
    </head>
		<div class="row">
			<div class="col-xl-12">
				<div class="notifications-card mb-30">
					<h5 class="noti-card-title">APPROVAL ACCOUNT PAYABLE REQUEST</h5>
				</div>
				<!-- Invoice -->
					<div class="invoice-template mb-30">
						The Request Account Payables WERE REJECTED!
					</div>

						
					
			</div>
