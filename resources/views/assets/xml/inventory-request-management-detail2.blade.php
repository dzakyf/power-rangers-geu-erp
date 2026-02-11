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
$dept = Auth::user()->Department;

echo "<rows>";


	$Query = DB::table("stock_request_dtl")->where("SR_No",'=',$_GET['SR_No'])->orderBy("ID","desc")->get();
	
	foreach($Query as $dapat){
		
			echo "<row id=\"$dapat->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Category))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Product_Code))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Specification))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Qty))."</cell>"; 
				echo "<cell>0</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Unit))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Keterangan))."</cell>"; 
			echo "</row>";	
			$nomor++;
	}

echo "</rows>";
?>