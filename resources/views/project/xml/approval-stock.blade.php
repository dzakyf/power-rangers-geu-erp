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
$dept = Auth::user()->Department;
$getnik = Auth::user()->Emp_ID;

echo "<rows>";

$getlevelsx = DB::table("organization_structure")->where("NIK",$getnik)->first();

if ($getlevelsx->Level1 == 1) $lepel = 1;
else if ($getlevelsx->Level2 == 1) $lepel = 2;
else if ($getlevelsx->Level3 == 1) $lepel = 3;
else if ($getlevelsx->Level4 == 1) $lepel = 4;
else if ($getlevelsx->Level5 == 1) $lepel = 5;
else if ($getlevelsx->Level6 == 1) $lepel = 6;
else if ($getlevelsx->Level7 == 1) $lepel = 7;
else if ($getlevelsx->Level8 == 1) $lepel = 8;

	if ($saya == 'admin'){
		$Query = DB::table("stock_request")->where("Status",'=','SUBMITED')->orderBy("ID","desc")->get();


		foreach($Query as $dapat){
			
				echo "<row id=\"$dapat->ID\">";
					echo "<cell>$nomor</cell>";
					echo "<cell>".htmlspecialchars(($dapat->SR_No))."</cell>";
					echo "<cell>".htmlspecialchars(($dapat->SR_Date))."</cell>";
					echo "<cell>".htmlspecialchars(($dapat->Department))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Request_By))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Reference))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Stock_Category))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Date_Pickup))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Remark))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Status))."</cell>"; 
				echo "</row>";	
				$nomor++;
		}
	}
if ($lepel >= 3){
$Query = DB::table("stock_request")->where("Status",'=','CREATED')->where("Department",$dept)->orderBy("ID","desc")->get();

	foreach($Query as $dapat){
		
			echo "<row id=\"$dapat->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars(($dapat->SR_No))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->SR_Date))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Department))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Request_By))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Reference))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Stock_Category))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Date_Pickup))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Remark))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Status))."</cell>"; 
			echo "</row>";	
			$nomor++;
	}
}

if ($dept  == 'CGA'){
	$Query = DB::table("stock_request")->where("Status",'=','PROPOSED')->orderBy("ID","desc")->get();

	foreach($Query as $dapat){
		
			echo "<row id=\"$dapat->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars(($dapat->SR_No))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->SR_Date))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Department))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Request_By))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Reference))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Stock_Category))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Date_Pickup))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Remark))."</cell>"; 
					echo "<cell>".htmlspecialchars(($dapat->Status))."</cell>"; 
			echo "</row>";	
			$nomor++;
	}
}
echo "</rows>";
?>