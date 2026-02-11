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

$SQuery = DB::table("branch_coa")->get();

foreach($SQuery as $Fetchs){
	$akun[$Fetchs->Acc_No] = $Fetchs->Description;
}
$nomor = 1;
$id2 = DB::table("bank")->where("ID", "=", $_GET['ID'])->first();
$Query = DB::table("bank_dtl")->where("Bank_No", $_GET['ID'])->orWhere("Bank_No", $id2->Bank_No)->get();
echo "<rows>";

foreach($Query as $Fetch){
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Acc_No. " -  ".$akun[$Fetch->Acc_No])."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Date_Bank)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reference)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Remark)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Bank_Val)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Bal_Acc)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
	echo "</row>";

$nomor++;
}


echo "</rows>";
