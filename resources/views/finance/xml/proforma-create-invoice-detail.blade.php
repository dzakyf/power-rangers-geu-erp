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
$cekinv = DB::table("invoice")->where("Task_ID",$_GET['Task_ID']);
$Query = DB::table("invoice_sub_data")->where("Task_ID",$_GET['Task_ID'])->get();

foreach($Query as $Fetch){
			echo "<row id=\"$Fetch->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Deskripsi)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Price)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Qty)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Subtotal)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Price_Fee)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Subtotal_Fee)."</cell>";
			echo "</row>";

		$nomor++;
	
}

echo "</rows>";
?>
