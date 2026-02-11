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

	$qry = DB::table("stock_count_list")->where("Task_ID","=",$_GET['ID'])->get();
		foreach ($qry as $dapat){
		$now = strtotime(date('c'));
		$exp = strtotime($dapat->Created_Date);
		$aging = round(($now-$exp)/86400,0);

			echo "<row id=\"$dapat->ID\" $color>";
				echo "<cell $color>$nomor</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Supplies_Type))."</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Product_Code))."</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Product_Description))."</cell>"; 
				echo "<cell $color>".htmlspecialchars(($dapat->Unit))."</cell>"; 
				echo "<cell $color>".htmlspecialchars(($dapat->Qty))."</cell>"; 
				echo "<cell $color>0</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Real_Qty+0))."</cell>";
				echo "<cell $color></cell>";
				echo "<cell $color></cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Real_Note))."</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Barcode))."</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Supplies_ID))."</cell>";
			echo "</row>";	
		$nomor++;
		}
echo "</rows>";
?>