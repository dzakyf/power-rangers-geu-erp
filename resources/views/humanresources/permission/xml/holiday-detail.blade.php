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


$Query = DB::table("libur")->orderBy("libur_tgl","ASC")->get();
echo "<data>";
foreach($Query as $Fetch){
	echo "<event id='$Fetch->libur_tgl'>
			<start_date>$Fetch->libur_tgl</start_date>
			<end_date>$Fetch->libur_tgl</end_date>
			<text>$Fetch->libur_keterangan</text>
			<details>$Fetch->libur_keterangan</details>
	</event>";
}
echo "</data>";
?>
