<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$PRApproval = Auth::user()->PR_Approval;

$Saya = Auth::user()->username;
echo "<rows>";

if ($PRApproval == 'ALL'){
$kueri = DB::select(DB::raw("SELECT * FROM purchase_summary where Status!='CREATED' AND Location = '$LSP' AND Status!='PENDING' AND Status!='CLOSED' ORDER BY ID DESC"));
}elseif($PRApproval != ''){
$PRApproval = explode(",",Auth::user()->PR_Approval);
if(count($PRApproval) == 1){
	$px = Auth::user()->PR_Approval;
	$pprovalwhere = "where Status='$px' AND Location = '$LSP'";
	
}else{
	foreach($PRApproval as $dx){
		$app.="'$dx',";
	}
	$appx = substr($app,0,-1);
	$pprovalwhere = "where Status IN($appx) AND Location = '$LSP'";
}

$kueri = DB::select(DB::raw("SELECT * FROM purchase_summary $pprovalwhere ORDER BY ID DESC"));	
}
$nomor = 1;

	foreach($kueri as $Fetch){
		
		$skrg = strtotime(date('c'));
		$buat = strtotime($Fetch->Created_Date);
		$kurangi = ($skrg-$buat)/86400;
		$aging = round($kurangi,0);
		$warna = "";
		if ($Fetch->Priority =='TOP URGENT') $warna = "style='background-color:red'";
		if ($Fetch->Priority =='URGENT') $warna = "style='background-color:#ffd700'";
		
		$Row_Trx = DB::table("purchase_request")->where("PR_No",$Fetch->PR_No)->first();
		$Row_Usr = DB::select(DB::raw("SELECT Name, Department FROM users WHERE username='".$Row_Trx->User_Trx."'"));
		
		
			echo "<row id=\"$Fetch->Task_ID\" $warna>";
				echo "<cell $warna>$nomor</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->PR_No))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Purchase_Date))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Purchase_Category))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Purchase_Sub_Category))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Row_Trx->User_Request_name))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Row_Trx->PIC))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Priority))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Priority_Note))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Status))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Created_Date))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($aging))."</cell>";
			echo "</row>";	

			$nomor++;
	}

echo "</rows>";
?>
