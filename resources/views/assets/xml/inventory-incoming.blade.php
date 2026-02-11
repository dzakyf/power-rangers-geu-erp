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

$Query = DB::table("good_receive")->where("Status",'!=','CANCELED')->where("Status",'!=','CLOSED')->where('Location',$LSP)->orderBy("ID","ASC")->get();

foreach($Query as $dapat){
	
		echo "<row id=\"$dapat->ID\">";
			echo "<cell>$nomor</cell>";
			echo "<cell>".htmlspecialchars(($dapat->GR_No))."</cell>";
			echo "<cell>".htmlspecialchars(($dapat->PO_No))."</cell>";
			echo "<cell>".htmlspecialchars(($dapat->Reference))."</cell>"; 
			echo "<cell>".htmlspecialchars(($dapat->PR_No))."</cell>"; 
			echo "<cell>".htmlspecialchars(($dapat->Status))."</cell>"; 
			echo "<cell>".htmlspecialchars(($dapat->Apply_To))."</cell>"; 
			echo "<cell>".htmlspecialchars(($dapat->Date_Created))."</cell>"; 
		echo "</row>";	
		$nomor++;
}
echo "</rows>";
?>