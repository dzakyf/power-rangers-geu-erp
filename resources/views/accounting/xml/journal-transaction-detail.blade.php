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


echo "<rows>";
$LSP = Auth::user()->Location;
$saya = Auth::user()->username;
$nomor = 1;

$QAgent = DB::select(DB::raw("SELECT * FROM branch_journal_temp WHERE Task_ID='$_GET[Task_ID]' ORDER BY ID ASC"));
		  foreach($QAgent as $FGL){

			$row = DB::table("branch_coa")->where("Acc_No",$FGL->Account_No)->first();

				echo "<row id=\"$FGL->ID\">";
					echo "<cell>$nomor</cell>";#a
					echo "<cell>".$FGL->Posting_Date."</cell>";#b
					echo "<cell>".$FGL->Document_No."</cell>";#c
					echo "<cell>".$FGL->External_Doc."</cell>";#d
					echo "<cell>".$FGL->Journal_No."</cell>";#d
					echo "<cell>$FGL->Account_No</cell>";#f
					echo "<cell>".htmlspecialchars($row->Description)."</cell>";#g
					echo "<cell>".$FGL->Reference_No."</cell>";#g
					echo "<cell>".$FGL->Remark."</cell>";#h
					echo "<cell>".($FGL->Debet+0)."</cell>";#i
					echo "<cell>".($FGL->Credit+0)."</cell>";#j
					echo "<cell>$FGL->Partner_Name</cell>";
					echo "<cell>$FGL->Partner_Code</cell>";
				echo "</row>";
				$nomor++;
		}
	



echo "</rows>";
?>
