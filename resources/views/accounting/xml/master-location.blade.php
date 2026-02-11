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

$QAgent = DB::table('location')->get();

		  foreach($QAgent as $FGL){
	
				echo "<row id=\"$FGL->ID\">";
					echo "<cell>$nomor</cell>";#a
					echo "<cell>".$FGL->Name."</cell>";#b
					echo "<cell>".htmlspecialchars($FGL->Location_ID)."</cell>";#c
					echo "<cell>".$FGL->Address.", ".$FGL->Street."</cell>";#d
					echo "<cell>".$FGL->City."</cell>";#d
					echo "<cell>".$FGL->Phone."</cell>";#g
					echo "<cell>".$FGL->PIC."</cell>";#h
					echo "<cell>".$FGL->MainCity."</cell>";#g
					echo "<cell>".$FGL->Latitude."</cell>";#g
					echo "<cell>".$FGL->Longitude."</cell>";#g
				echo "</row>";
				$nomor++;
		}
	

echo "</rows>";
?>
