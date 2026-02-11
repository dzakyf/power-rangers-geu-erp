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


$Sql = "SELECT * FROM update_inventory WHERE Product_Code='$_GET[IdProd]' AND Qty !='0'  ORDER BY Created_Date DESC";
$Exe = DB::select(DB::raw($Sql));
$no = 1;
foreach($Exe as $Row){
	if($Row->Qty <= 0){ 
		$Remark = "OUT";
		$rowU = DB::select(DB::raw("SELECT User_Trx FROM stock_request WHERE SR_No='$Row->PO_No'"));
		$userReq = $rowU->User_Trx;
	}else{
		$Remark = "IN";
		$userReq = $Row->Created_By;
	}


	if($Row->PO_No=='STOCK OPNAME') $warnanya = " bgColor='#66FFB2'"; else $warnanya = "";
	if(substr($Row->PO_No,0,2)=='SR' && $Remark == "IN") { 
		$PO_No = "KOREKSI STOK"; 
		$warnanya = " bgColor='#FFFFA1'";
	}else{ 
		$PO_No = $Row->PO_No;
		$warnanya = $warnanya;
	}

		echo "<row id=\"$no\" $warnanya>";
			echo "<cell $warnanya>$no</cell>";
			echo "<cell $warnanya>".htmlspecialchars($Row->Created_Date)."</cell>";
			echo "<cell $warnanya>".htmlspecialchars($Remark)."</cell>";
			echo "<cell $warnanya>".htmlspecialchars($PO_No)."</cell>";
			echo "<cell $warnanya>".htmlspecialchars($Row->Created_By)."</cell>";
			echo "<cell $warnanya>".htmlspecialchars($userReq)."</cell>";
			echo "<cell $warnanya>".htmlspecialchars($Row->Qty)."</cell>";
			echo "<cell $warnanya>".htmlspecialchars($Row->Qty_Stock)."</cell>";
			echo "</row>";
			$no++;
}

echo "</rows>";
?>
