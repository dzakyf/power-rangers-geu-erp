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

$QAgent = DB::table('budget_detail')->where("Task_ID","=",$_GET['ID'])->orderBy('COA','ASC')->get();

		  foreach($QAgent as $FGL){
	
				echo "<row id=\"$FGL->ID\">";
					echo "<cell>$nomor</cell>";#a
					echo "<cell>".$FGL->COA."</cell>";#b
					echo "<cell>".htmlspecialchars($FGL->Description)."</cell>";#b
					echo "<cell>".$FGL->January_Value."</cell>";#g
					echo "<cell>".$FGL->February_Value."</cell>";#g
					echo "<cell>".$FGL->March_Value."</cell>";#g
					echo "<cell>".$FGL->April_Value."</cell>";#g
					echo "<cell>".$FGL->May_Value."</cell>";#g
					echo "<cell>".$FGL->June_Value."</cell>";#g
					echo "<cell>".$FGL->July_Value."</cell>";#g
					echo "<cell>".$FGL->August_Value."</cell>";#g
					echo "<cell>".$FGL->September_Value."</cell>";#g
					echo "<cell>".$FGL->October_Value."</cell>";#g
					echo "<cell>".$FGL->November_Value."</cell>";#g
					echo "<cell>".$FGL->December_Value."</cell>";#g
					echo "<cell></cell>";#g
				echo "</row>";
				$nomor++;
		}
	

echo "</rows>";
?>
