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
$nomor =1;
echo "<rows>";

$Query = DB::table("mail_logs")->orderBy("Inserted_Date","DESC")->get();

foreach($Query as $Fetch){
	echo "<row id=\"$Fetch->ID\" >";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Email_To)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Subject)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Contents)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
        echo "<cell>".htmlspecialchars($Fetch->Send_Date)."</cell>";
        echo "<cell>".htmlspecialchars($Fetch->Inserted_Date)."</cell>";
        echo "<cell>".htmlspecialchars($Fetch->linkkey)."</cell>";

	echo "</row>";

$nomor++;
}

echo "</rows>";
?>
