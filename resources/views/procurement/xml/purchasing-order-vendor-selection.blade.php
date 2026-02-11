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


$kueri = DB::select(DB::raw("SELECT * FROM purchase_request WHERE Status='APPROVED' AND (PO_Task_No='' OR PO_Task_No IS NULL) AND Priority_Status!='UNDEFINED' ORDER BY ID ASC"));
$nomor = 1;

	foreach($kueri as $Fetch){

			echo "<row id=\"$Fetch->ID\">";
					echo "<cell $warnanya>$nomor</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->PR_No))."</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->PR_Date))."</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->Category))."</cell>"; 
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->Sub_Category))."</cell>"; 
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->Supplies_Type))."</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->User_Request_Name))."</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->PIC))."</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->Priority_Status))."</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->Priority_Note))."</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->Priority_By))."</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->Created_Date))."</cell>";
					echo "<cell $warnanya>".htmlspecialchars(($Fetch->Created_By))."</cell>";
				echo "</row>";	

			$nomor++;
	}

echo "</rows>";
?>
