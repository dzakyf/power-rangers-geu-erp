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
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$POX = DB::table("opportunity_detail")->where("Task_ID",$_GET['Task_ID'])->first();
$Query = DB::table("opportunity_detail_requisition")->where("Task_ID",$_GET['Task_ID'])->where("Req_ID",NULL)->orWhere("Req_ID",'')->get();

foreach($Query as $Fetch){
		
	echo "<row id=\"$POX->ID|$Fetch->ID\" >";
		echo "<cell>0</cell>";
		echo "<cell type='ro'>$POX->PO_No</cell>";
		echo "<cell type='ro'>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell type='ro'>".htmlspecialchars($Fetch->Position)."</cell>";
		echo "<cell type='ro'>".htmlspecialchars($Fetch->Qty)."</cell>";
	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
