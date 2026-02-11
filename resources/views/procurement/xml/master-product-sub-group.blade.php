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
$nomor = 1;
echo "<rows>";

$QAgent = DB::table('master_product_sub_group')->orderBy('Sub_Group','asc')->get();

		  foreach($QAgent as $FGL){
	
				echo "<row id=\"$FGL->ID\">";
					echo "<cell>$nomor</cell>";#a
					echo "<cell>".$FGL->Product_Group."</cell>";#b
					echo "<cell>".$FGL->Sub_Group."</cell>";#b
					echo "<cell>".$FGL->Initial."</cell>";#g
				echo "</row>";
				$nomor++;
		}
	

echo "</rows>";
?>
