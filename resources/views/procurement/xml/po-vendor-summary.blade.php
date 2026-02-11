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
$nomor =1 ;
$kueri = "SELECT Supplier,count(ID)as jumlah,sum(Total)as Total,sum(Total_Paid)as sdhbayar 
FROM purchase_order 
WHERE Supplier!=''  AND Status='CLOSED' AND  PO_Date BETWEEN '$_GET[Tgl1]' AND '$_GET[Tgl2]'
GROUP BY Supplier ORDER BY Supplier ASC";
$kuerikan = DB::select(DB::raw($kueri));
foreach ($kuerikan as $row) {
		$rows = DB::table("supplier")->where("Supplier_ID",$row->Supplier)->first();

	
		$vendor_name = $rows->Company_Name;
	
			echo "<row id=\"$row->Supplier\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars(($row->Supplier))."</cell>";
				echo "<cell>".htmlspecialchars((strtoupper($vendor_name)))."</cell>";
				echo "<cell>".htmlspecialchars(($row->jumlah))."</cell>";
				echo "<cell>".htmlspecialchars(($row->Total))."</cell>";
				echo "<cell>".htmlspecialchars(($row->sdhbayar))."</cell>";
				echo "<cell>".htmlspecialchars(($row->Total-$row->sdhbayar))."</cell>";
			echo "</row>";	
		$nomor++;
}
echo "</rows>";
