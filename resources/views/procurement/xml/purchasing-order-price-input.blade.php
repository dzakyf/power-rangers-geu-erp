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


//$kueri = DB::select(DB::raw("SELECT * FROM purchase_summary where Status!='FINAL APPROVED' AND Status!='PENDING'  AND Status!='CREATED' ORDER BY ID DESC"));
$kueri = DB::select(DB::raw("SELECT * FROM purchase_summary where Status='CREATED' ORDER BY ID DESC"));
$nomor = 1;

	foreach($kueri as $Fetch){
		
		$skrg = strtotime(date('c'));
		$buat = strtotime($Fetch->Created_Date);
		$kurangi = ($skrg-$buat)/86400;
		$aging = round($kurangi,0);
		$warna = "";
		if ($Fetch->Priority =='TOP URGENT') $warna = "style='background-color:red'";
		if ($Fetch->Priority =='URGENT') $warna = "style='background-color:#ffd700'";
		
		$Row_Trx = DB::select(DB::raw("SELECT User_Request FROM purchase_request WHERE PR_No = '".$Fetch->PR_No."'"));
		$Row_Usr = DB::select(DB::raw("SELECT Name, Department FROM users WHERE username='".$Row_Trx->User_Trx."'"));
		
		
			echo "<row id=\"$Fetch->Task_ID\" $warna>";
				echo "<cell $warna>$nomor</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->PR_No))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Purchase_Date))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Purchase_Category))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Purchase_Sub_Category))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Status))."</cell>";
				echo "<cell $warna>".htmlspecialchars((date("d-m-Y H:i", strtotime($Fetch->Created_Date))))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Created_By))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Row_Usr->Name))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Row_Usr->Department))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($aging))."</cell>";
				echo "<cell $warna>".htmlspecialchars(($Fetch->Priority))."</cell>";
			echo "</row>";	

			$nomor++;
	}

echo "</rows>";
?>
