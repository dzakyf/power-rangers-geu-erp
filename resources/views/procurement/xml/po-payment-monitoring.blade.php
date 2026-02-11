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


$kueri = DB::select(DB::raw("SELECT * FROM purchase_request where Status!='CANCELED'ORDER BY ID DESC"));
$nomor = 1;

	foreach($kueri as $Fetch){
		
	$Row_Usr = DB::select(DB::raw("SELECT Name, Department FROM users WHERE username='".$Fetch->User_Trx."'"));

	$Row_Sum = DB::table("purchase_summary")->where("PR_No",$Fetch->PR_No)->first();
	if(count($Sql_Sum)>0){
		if(count($Sql_Sum)>1) $subgrid = 1; else $subgrid = 0;
		$Statusnya = $Row_Sum->Status;
	}else{
		$subgrid = 0;
		$Statusnya = $Fetch->Status;
		
	}
	
	$datadesc = "";
	$sql_detail = DB::table("purchase_request_dtl")->where("PR_No",$Fetch->PR_No)->get();
	foreach($sql_detail as $row_detail){
		$datadesc .= $row_detail->Product_Desc . " (". $row_detail->Qty ."),";
	}
	
	$datadesc = rtrim($datadesc,",");
	
	
	
	if($Statusnya=='REJECTED' || $Statusnya=='CANCELED'){
		$warnanya = " bgColor='#CC0000' style='font-style:italic;color:#ffffff;font-weight:bold;'";
	}else{
		$warnanya = "";
	}
	
	$peruntukkan = str_replace("<br>"," | ",$Fetch->Remark);
	$hasil = strrev($peruntukkan);
	$asli = strpos($hasil,">=");
	$hasilnya = substr($hasil,0,$asli);
	$hasil = strrev($hasilnya);
	
	//purchase received
	$slr = DB::table("purchase_received_log")->where("PR_Related",$Fetch->PR_No)->first();
	if(count($slr)>0){
		$last_received = date("Y-m-d", strtotime($ror->Received_Date));
	}else{
		$last_received = "-";
	}
	
	
	
	//purchase order
	$Row_Ord = DB::table("purchase_order")->where("PO_No",$Fetch->PO_No)->first();
	$Row_Sup = DB::table("supplier")->where("Supplier_ID",$Row_Ord->Supplier)->first();

	if($Row_Ord->Received_Date<>'0000-00-00 00:00:00') $Received_Date = $Row_Ord->Received_Date; else $Received_Date = "";
	if($Row_Ord->Date_Trx<>'0000-00-00 00:00:00') $Date_Trx = $Row_Ord->Date_Trx; else $Date_Trx = "";
	
	if ($Fetch->Status =='CANCELED') $Fetch->PO_Status = 'CANCELED';
	if ($Fetch->PO_Status =='NORMAL') $Fetch->PO_Status = 'WAITING';
	
	
		$skrg = strtotime(date('c'));
		$buat = strtotime($Fetch->Created_Date);
		$kurangi = ($skrg-$buat)/86400;
		$aging = round($kurangi,0);
		$warna = "";
		if ($Fetch->Priority =='TOP URGENT') $warna = "style='background-color:red'";
		if ($Fetch->Priority =='URGENT') $warna = "style='background-color:#ffd700'";
		
		$Row_Trx = DB::table("purchase_request")->where("PR_No",$Fetch->PR_No)->first();
		
		
			echo "<row id=\"$Fetch->PR_No\">";
				echo "<cell $warnanya>$nomor</cell>";
				
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->PR_Date))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->PR_No))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars((ucwords(strtolower($Fetch->User_Request_Name))))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars((ucwords(strtolower($Fetch->Department))))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars((ucwords(strtolower($Fetch->PIC))))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->PO_No))."</cell>"; 
				echo "<cell $warnanya>".htmlspecialchars((number_format($Row_Ord->Total,0,".",",")))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Row_Sup->Company_Name))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Received_Date))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Date_Trx))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Statusnya))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($datadesc))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars((strtoupper($hasil)))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($subgrid))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
				echo "<cell $warnanya>".htmlspecialchars(($last_received))."</cell>";
			echo "</row>";

			$nomor++;
	}

echo "</rows>";
?>
