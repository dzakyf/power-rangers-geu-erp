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
function Filter($C) {
	$DataFilter = preg_replace('/[^(\x20-\x7F)]*/','', $C);
	return htmlspecialchars($DataFilter);
}

function Bersih($Data){
	$X = trim($Data);
	$X2 = Filter($X);
	$X2 = preg_replace('/[^(\x20-\x7F)]*/','', $X2);
	return $X2;
}
echo "<rows>";

$saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$nomor = 1;

$pjm = DB::table("project_mgt")->where("Task_ID",$_GET['ID'])->first();
$po = DB::table("opportunity_detail")->where("Task_ID",$pjm->Source_Reference)->first();

$q = DB::table("project_reimbursement")->where("Project_ID",$_GET['ID'])->orderBy("ID","Desc")->get();
foreach($q as $Data){
	$mx = DB::table("project_reimbursement_dtl")->where("Task_ID",$Data->Task_ID)->sum('project_reimbursement_dtl.Cash_Val');

	

	echo "<row id=\"$Data->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell >".Bersih($Data->Task_ID)."</cell>";
		echo "<cell >".Bersih($Data->Reimbursement_Type)."</cell>";
		echo "<cell >".Bersih(date("Y-m-d",strtotime($Data->Created_Date)))."</cell>";
		echo "<cell >".$mx."</cell>";
	
	echo "</row>";

$nomor++;
}
if ($po->PO_No !=''){
		
	$qx = DB::table("reimbursement")->where("Reference_No",$po->PO_No)->orderBy("ID","Desc")->get();
	if($qx == null) {
		$po = DB::table("opportunity_detail_adendum")->where("Task_ID",$pjm->Source_Reference)->first();
		$qx = DB::table("reimbursement")->where("Reference_No",$po->PO_No)->orderBy("ID","Desc")->get();
	}
	foreach($qx as $Data){

		

		echo "<row id=\"$Data->ID\">";
			echo "<cell>$nomor</cell>";
			echo "<cell >".Bersih($Data->Reimbursement_No)."</cell>";
			echo "<cell >INDIVIDUAL REIMBURSEMENT</cell>";
			echo "<cell >".Bersih(date("Y-m-d",strtotime($Data->Created_Date)))."</cell>";
			echo "<cell >".$Data->Total."</cell>";
		
		echo "</row>";

	$nomor++;
	}

	$qx2 = DB::table("bank")->where("Bank_Type",'BANK_OUT')->where("Reference",$po->PO_No)->orderBy("ID","Desc")->get();

	if($qx2 == null) {
		$po = DB::table("opportunity_detail_adendum")->where("Task_ID",$pjm->Source_Reference)->first();
		$qx2 = DB::table("bank")->where("Bank_Type",'BANK_OUT')->where("Reference",$po->PO_No)->orderBy("ID","Desc")->get();
	}
	foreach($qx2 as $Data){

		

		echo "<row id=\"$Data->ID\">";
			echo "<cell>$nomor</cell>";
			echo "<cell >".Bersih($Data->Bank_No)."</cell>";
			echo "<cell >".Bersih($Data->Bank_Type)."</cell>";
			echo "<cell >".Bersih(date("Y-m-d",strtotime($Data->Created_Date)))."</cell>";
			echo "<cell >".$Data->Total."</cell>";
		
		echo "</row>";

	$nomor++;
	}

	$qx22 = DB::table("cash")->where("Cash_Type",'CASH_OUT')->where("Voucher_No",$po->PO_No)->orderBy("ID","Desc")->get();

	
	if($qx22 == null) {
		$po = DB::table("opportunity_detail_adendum")->where("Task_ID",$pjm->Source_Reference)->first();
		$qx22 = DB::table("cash")->where("Cash_Type",'CASH_OUT')->where("Voucher_No",$po->PO_No)->orderBy("ID","Desc")->get();
	}
	foreach($qx22 as $Data){

		

		echo "<row id=\"$Data->ID\">";
			echo "<cell>$nomor</cell>";
			echo "<cell >".Bersih($Data->Cash_No)."</cell>";
			echo "<cell >".Bersih($Data->Cash_Type)."</cell>";
			echo "<cell >".Bersih(date("Y-m-d",strtotime($Data->Created_Date)))."</cell>";
			echo "<cell >".$Data->Total."</cell>";
		
		echo "</row>";

	$nomor++;
	}

	$qx2a = DB::table("cash_advance")->where("Reference",$po->PO_No)->orderBy("ID","Desc")->get();
	if($qx2a == null) {
		$po = DB::table("opportunity_detail_adendum")->where("Task_ID",$pjm->Source_Reference)->first();
		$qx2a = DB::table("cash_advance")->where("Reference",$po->PO_No)->orderBy("ID","Desc")->get();
	}
	foreach($qx2a as $Data){

		

		echo "<row id=\"$Data->ID\">";
			echo "<cell>$nomor</cell>";
			echo "<cell >".Bersih($Data->Task_ID)."</cell>";
			echo "<cell >CASH ADVANCE</cell>";
			echo "<cell >".Bersih(date("Y-m-d",strtotime($Data->Created_Date)))."</cell>";
			echo "<cell >".$Data->Cash_Val."</cell>";
		
		echo "</row>";

	$nomor++;
	}

	$qx2ab = DB::table("bank_advance")->where("Reference",$po->PO_No)->orderBy("ID","Desc")->get();
	if($qx2ab == null) {
		$po = DB::table("opportunity_detail_adendum")->where("Task_ID",$pjm->Source_Reference)->first();
		$qx2ab = $qx2ab = DB::table("bank_advance")->where("Reference",$po->PO_No)->orderBy("ID","Desc")->get();
	}
	foreach($qx2ab as $Data){

		

		echo "<row id=\"$Data->ID\">";
			echo "<cell>$nomor</cell>";
			echo "<cell >".Bersih($Data->Task_ID)."</cell>";
			echo "<cell >BANK ADVANCE</cell>";
			echo "<cell >".Bersih(date("Y-m-d",strtotime($Data->Created_Date)))."</cell>";
			echo "<cell >".$Data->Bank_Val."</cell>";
		
		echo "</row>";

	$nomor++;
	}
}
echo "</rows>";
?>
