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

$Query = DB::table("opportunity")->where("Location",$LSP)->where("Status","!=","CANCELED")->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
	$detail = DB::table("opportunity_detail")->where("Task_ID",$Fetch2->Task_ID)->first();

	$Query2 = DB::table("opportunity_detail_requisition")->where("Task_ID",$Fetch->Task_ID)->orderBy("ID","DESC")->get();
	
	foreach($Query2 as $Fetch2){
		
		$requisition = DB::table("requisition")->where("Task_ID",$Fetch2->Req_ID)->first();

		if($Fetch2->Req_ID != ''){
			$jumrecruit = DB::select(DB::raw("SELECT COUNT(ID) as Jumlah,Status FROM recruitment where Requisition_ID='$Fetch2->Task_ID'  GROUP BY Status"));
			foreach($jumrecruit as $rekrut){
				if($rekrut->Status == 'CLOSED'){
					$rekruit['CLOSED'] += 1;	
				}elseif($rekrut->Status == 'CANCELED'){
					$rekruit['CANCEL'] += 1;
				}else{
					$rekruit['OPEN'] += 1;
				}
				
			}
		}else{
			$rekruit['CLOSED'] = 0;
			$rekruit['CANCEL'] = 0;
			$rekruit['CLOSED'] = 0;
		}
		
		foreach($Query2 as $Fetch2){

			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Oppor_Subject)."</cell>";
				echo "<cell>".htmlspecialchars($requisition->Job_Position)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->Qty)."</cell>";
				echo "<cell>".htmlspecialchars($detail->PO_No)."</cell>";
				echo "<cell>".htmlspecialchars($requisition->End_Date)."</cell>";
				echo "<cell>".htmlspecialchars($rekruit['OPEN']+0)."</cell>";
				echo "<cell>".htmlspecialchars($rekruit['CLOSED']+0)."</cell>";
				echo "<cell>".htmlspecialchars($rekruit['CANCEL']+0)."</cell>";
			echo "</row>";

		$nomor++;
		}
		
	}
}

echo "</rows>";
?>
