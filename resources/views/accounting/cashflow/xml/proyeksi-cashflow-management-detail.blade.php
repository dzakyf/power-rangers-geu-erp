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

$QAgent = DB::table('proyeksi_cashflow_detail')->where("Task_ID","=",$_GET['ID'])->orderBy('COA','ASC')->get();

		  foreach($QAgent as $FGL){
	
				echo "<row id=\"$FGL->ID\">";
					echo "<cell>$nomor</cell>";#a
					echo "<cell>".$FGL->COA."</cell>";#b
					echo "<cell>".htmlspecialchars($FGL->Description)."</cell>";#b
					echo "<cell>".$FGL->YM."</cell>";#g
					echo "<cell>".$FGL->Day_1."</cell>";#g
					echo "<cell>".$FGL->Day_2."</cell>";#g
					echo "<cell>".$FGL->Day_3."</cell>";#g
					echo "<cell>".$FGL->Day_4."</cell>";#g
					echo "<cell>".$FGL->Day_5."</cell>";#g
					echo "<cell>".$FGL->Day_6."</cell>";#g
					echo "<cell>".$FGL->Day_7."</cell>";#g
					echo "<cell>".$FGL->Day_8."</cell>";#g
					echo "<cell>".$FGL->Day_9."</cell>";#g
					echo "<cell>".$FGL->Day_10."</cell>";#g
					echo "<cell>".$FGL->Day_11."</cell>";#g
					echo "<cell>".$FGL->Day_12."</cell>";#g
					echo "<cell>".$FGL->Day_13."</cell>";#g
					echo "<cell>".$FGL->Day_14."</cell>";#g
					echo "<cell>".$FGL->Day_15."</cell>";#g
					echo "<cell>".$FGL->Day_16."</cell>";#g
					echo "<cell>".$FGL->Day_17."</cell>";#g
					echo "<cell>".$FGL->Day_18."</cell>";#g
					echo "<cell>".$FGL->Day_19."</cell>";#g
					echo "<cell>".$FGL->Day_20."</cell>";#g
					echo "<cell>".$FGL->Day_21."</cell>";#g
					echo "<cell>".$FGL->Day_22."</cell>";#g
					echo "<cell>".$FGL->Day_23."</cell>";#g
					echo "<cell>".$FGL->Day_24."</cell>";#g
					echo "<cell>".$FGL->Day_25."</cell>";#g
					echo "<cell>".$FGL->Day_26."</cell>";#g
					echo "<cell>".$FGL->Day_27."</cell>";#g
					echo "<cell>".$FGL->Day_28."</cell>";#g
					echo "<cell>".$FGL->Day_29."</cell>";#g
					echo "<cell>".$FGL->Day_30."</cell>";#g
					echo "<cell>".$FGL->Day_31."</cell>";#g
					echo "<cell></cell>";#g
				echo "</row>";
				$nomor++;
		}
	

echo "</rows>";
