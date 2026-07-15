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

$Query = DB::table("requisition")->where("Location",$LSP)->where("Status","!=","CLOSED")->where("Status","!=","CANCELED")->orderBy("ID","DESC");
if ($priv != 'HCGA' || $priv != 'ADM') {
	$Query->where("Created_By", $saya);
}
foreach($Query->get() as $Fetch){
	
		$buat = strtotime($Fetch->End_Date);
		$now = strtotime(date('Y-m-d'));
		$kurang = ($now-$buat)/86400;
		$hari = round($kurang,0);

		$getLog = DB::table('requisition_approval')->where("Task_ID", $Fetch->Task_ID)->where("Approved_Status", '!=', 0)->orderBy('ID', "DESC")->first();

	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Subject)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Job_Position)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PO_Desc)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Requirement)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Priority)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Qty)."</cell>";
		echo "<cell>".htmlspecialchars($hari)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Start_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->End_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PO_No)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PIC_Production)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell>".htmlspecialchars($getLog->Approved_Note)."</cell>";
		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
