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

$QAgent = DB::table('proyeksi_cashflow')->where("Status","!=",'CLOSED')->where("Status","!=",'CANCELED')->orderBy('ID','DESC')->get();

		  foreach($QAgent as $FGL){
			if ($FGL->Status =='FINALIZE') {
				$warna = "type='ron' style='background-color:#f28b88'";
				$warna2 =  "type='ro' style='background-color:#f28b88'";
				$isitengah = $FGL->Department;
			}else{
				$warna = "";$warna2= "";
				$isitengah = $FGL->Department_Code;
			}
				echo "<row id=\"$FGL->ID\" $warna>";
					echo "<cell $warna>$nomor</cell>";#a
					echo "<cell $warna>".$FGL->Proyeksi_Year."</cell>";#b
					echo "<cell $warna2>$isitengah</cell>";#g
					echo "<cell $warna>".$FGL->Description."</cell>";#g
					echo "<cell $warna>".$FGL->January_Value."</cell>";#g
					echo "<cell $warna>".$FGL->February_Value."</cell>";#g
					echo "<cell $warna>".$FGL->March_Value."</cell>";#g
					echo "<cell $warna>".$FGL->April_Value."</cell>";#g
					echo "<cell $warna>".$FGL->May_Value."</cell>";#g
					echo "<cell $warna>".$FGL->June_Value."</cell>";#g
					echo "<cell $warna>".$FGL->July_Value."</cell>";#g
					echo "<cell $warna>".$FGL->August_Value."</cell>";#g
					echo "<cell $warna>".$FGL->September_Value."</cell>";#g
					echo "<cell $warna>".$FGL->October_Value."</cell>";#g
					echo "<cell $warna>".$FGL->November_Value."</cell>";#g
					echo "<cell $warna>".$FGL->December_Value."</cell>";#g
					echo "<cell></cell>";#g
					echo "<cell $warna2>".$FGL->Note."</cell>";#g
					echo "<cell $warna2>".$FGL->Status."</cell>";#g
				echo "</row>";
				$nomor++;
		}
	

echo "</rows>";
?>
