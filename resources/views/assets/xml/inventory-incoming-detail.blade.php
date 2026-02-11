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


$Querys = DB::table("product_code")->get();
foreach($Querys as $prod){
	$produk[$prod->Product_Code]= $prod->Description;
}

$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("good_receive_dtl")->where('GR_No',$_GET['ID'])->get();

foreach($Query as $dapat){
		$supp =DB::table("purchase_order")->where("GR_No",$dapat->PO_No)->first();
			echo "<row id=\"$dapat->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Category))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Product_Code))."</cell>";
				echo "<cell>".htmlspecialchars(($produk[$dapat->Product_Code]))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->SN))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Qty_PO))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Qty_Receive))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Qty_PO-$dapat->Qty_Receive))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Category))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Value_PO))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->PO_No))."</cell>";
				echo "<cell>".htmlspecialchars(($supp->Supplier))."</cell>";
			echo "</row>";	
		$nomor++;

}
echo "</rows>";
?>