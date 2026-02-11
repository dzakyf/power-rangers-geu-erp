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
$saya = Auth::user()->username;
$nomor = 1;

$City = $_GET['City'];
        $Query = DB::table("branch_journal_task")
					->where("Status","=",'CLOSED')
					->orderBy("ID","DESC")->get();

echo "<rows>";

foreach ($Query as $Fetch){
	
	echo "<row id=\"$Fetch->Task_ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Location)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
		echo "<cell></cell>";
		echo "<cell>".htmlspecialchars()."</cell>";
	echo "</row>";

$nomor++;
}


echo "</rows>";
?>
