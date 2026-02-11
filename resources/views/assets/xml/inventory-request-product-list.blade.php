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
$Saya = Auth::user()->username;
$nomor = 1;

$Fetchx = DB::select(DB::raw("SELECT * FROM product_code where Main_Category='CONSUMABLE'  AND Supplies_Type='$_GET[Supplies_Type]' order by Product_Code"));
 
echo "<rows>";

foreach($Fetchx as $dapat){
		$cekstok = DB::table("supplies")->selectRaw("SUM(Qty) as jums")->where("Product_Code",$dapat->Product_Code)->first();
		
		echo "<row id=\"$dapat->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>0</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Product_Code))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Description))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Specification))."</cell>";
				echo "<cell>".($cekstok->jums+0)."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Unit_Name))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Foto))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Dimension))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Price))."</cell>";
			echo "</row>";	

$nomor++;
}


echo "</rows>";
?>
