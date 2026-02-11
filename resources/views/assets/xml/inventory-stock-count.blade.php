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

	$qry = DB::table("stock_count")->where("Status","!=",'CLOSED')->where("Status","!=",'CANCELED')->where("Location",$LSP)->get();
		foreach ($qry as $dapat){
		$now = strtotime(date('c'));
		$exp = strtotime($dapat->Created_Date);
		$aging = round(($now-$exp)/86400,0);
		$qry = DB::table("stock_count_list")->select(DB::raw("SUM(Qty) as jumlah,SUM(Real_Qty) as ambil"))->where("Task_ID",$dapat->Task_ID)->first();
		$jumlahpersen = round(($qry->ambil/$qry->jumlah)*100,2);
		
			echo "<row id=\"$dapat->Task_ID\" $color>";
				echo "<cell $color>$nomor</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Task_ID))."</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Created_Date))."</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Created_By))."</cell>"; 
				echo "<cell $color>$aging</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Status))."</cell>";
				echo "<cell $color>".htmlspecialchars(($dapat->Remark))."</cell>";
				echo "<cell $color>$jumlahpersen</cell>";
			echo "</row>";	
		$nomor++;
		}
echo "</rows>";
?>