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
$PRApproval = Auth::user()->PR_Approval;
$Saya = Auth::user()->username;

echo "<rows>";
$kueri = DB::select(DB::raw("SELECT * FROM purchase_request_dtl WHERE PR_No='$_GET[ID]' ORDER BY ID ASC"));
$nomor = 1;

foreach($kueri as $Fetch){

		echo "<row id=\"$Fetch->ID\">";
				echo "<cell $warnanya>$nomor</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->Product_Code))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->Product_Desc))."</cell>"; 
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->Qty))."</cell>"; 
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->Specification))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->Unit))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->Note))."</cell>";
			echo "</row>";	

		$nomor++;
}

echo "</rows>";
?>
