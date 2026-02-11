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

$QAgent = DB::table('bank_acc')->where("Location_ID",$LSP)->orderBy("Bank_Acc","asc")->get();

		  foreach($QAgent as $FGL){
	
				echo "<row id=\"$FGL->ID\">";
					echo "<cell>$nomor</cell>";#a
					echo "<cell>".$FGL->Bank_Name."</cell>";#b
					echo "<cell>".htmlspecialchars($FGL->Bank_Acc)."</cell>";#c
					echo "<cell>".$FGL->Acc_No."</cell>";#d
					echo "<cell>".$FGL->Bank_Branch."</cell>";#d
					echo "<cell>".$FGL->Currency."</cell>";#g
					echo "<cell>".$FGL->Remark."</cell>";#h
					echo "<cell>".$FGL->Status."</cell>";#g
					echo "<cell>".$FGL->User_Trx."</cell>";#g
					echo "<cell>".$FGL->Date_Trx."</cell>";#g
				echo "</row>";
				$nomor++;
		}
	

echo "</rows>";
?>
