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

	$getLevel = DB::table('master_hr_approval')->where('username', $saya)->first();

$Query = DB::table("requisition")->where("Location",$LSP)->where("Status","!=","CLOSED")->where("Status","!=","CANCELED")->whereIn('Task_ID', function ($q) use ($saya) {
	$q->select('ra.Task_ID')
		->from('requisition_approval as ra')
		->where('ra.Approved_By', $saya)
		->where('ra.Approved_Status', 0)
		->whereNotExists(function ($sub) {
			$sub->select(DB::raw(1))
				->from('requisition_approval as prev')
				->whereColumn('prev.Task_ID', 'ra.Task_ID')
				->whereColumn('prev.Level', '<', 'ra.Level')
				->where('prev.Approved_Status', 0);
		});
})->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
	
		$buat = strtotime($Fetch->End_Date);
		$now = strtotime(date('Y-m-d'));
		$kurang = ($now-$buat)/86400;
		$hari = round($kurang,0);

	echo "<row id=\"$Fetch->ID\">";
		echo "<cell></cell>";
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
		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
