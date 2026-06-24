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

$Query = DB::table("recruitment")->where("Location",$LSP)->where("Status","!=","BUSINESS_REVIEW")->where("Status","!=","CLOSED")->where("Status","!=","CANCELED")->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
		$Requisition = DB::table("requisition")->where("Task_ID",$Fetch->Requisition_ID)->first();

		$buat = strtotime($Requisition->End_Date);
		$now = strtotime(date('Y-m-d'));
		$kurang = ($now-$buat)/86400;
		$hari = round($kurang,0);
		if ($Fetch->Notification_By!='') $notif = 1; else $notif = 0;
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Gender)."</cell>";
		echo "<cell>".htmlspecialchars($Requisition->Subject." # " . $Requisition->Job_Position )."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Email)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Phone)."</cell>";
		echo "<cell>$notif</cell>";
		echo "<cell>$Fetch->Incoming_Date</cell>";
		echo "<cell>$Fetch->Notification_By</cell>";
		echo "<cell>".($Fetch->Notification_Language ?? 'IDN')."</cell>";
		echo "<cell>$Fetch->Notification_Template</cell>";
		echo "<cell>$Fetch->Notification_Date</cell>";
		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
