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

$Query = DB::table("product_code")->orderBy("ID","ASC")->get();

foreach($Query as $dapat){
		$qry = DB::select(DB::raw("SELECT SUM(Qty) as Jumlah,Bin FROM supplies where Location = '$LSP' AND Product_Code='$dapat->Product_Code' GROUP BY Bin ORDER BY Bin"));
		
		if ($qry){
				foreach($qry as $Fetch){
				
					if ($dapat->Dimension <= $Fetch->Jumlah){
						$stock = "LIMITED";	
						
					}elseif ($dapat->Dimension > $Fetch->Jumlah){
						$stock = "READY";
					}else{
						$stock = "NOT READY";
					}
				
					$stocknya = number_format($Fetch->Jumlah,0,",",".");
						echo "<row id=\"$dapat->Product_Code\">";
							echo "<cell>$nomor</cell>";
							echo "<cell>".htmlspecialchars(($dapat->Supplies_Type))."</cell>";
							echo "<cell>".htmlspecialchars(($dapat->Product_Code))."</cell>";
							echo "<cell>".htmlspecialchars(($dapat->Description))."</cell>";
							echo "<cell>".htmlspecialchars(($Fetch->Bin))."</cell>";
							echo "<cell>".htmlspecialchars(($stocknya))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Specification))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Foto))."</cell>";
							echo "<cell>".htmlspecialchars(($dapat->Unit_Name))."</cell>";
							echo "<cell>".htmlspecialchars((number_format($dapat->UnitPrice,0,",","")))."</cell>";
						echo "</row>";	
					$nomor++;
				}
		}else{
			
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Supplies_Type))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Product_Code))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Description))."</cell>";
				echo "<cell></cell>";
				echo "<cell>0</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Specification))."</cell>"; 
				echo "<cell>".htmlspecialchars(($dapat->Foto))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->Unit_Name))."</cell>";
				echo "<cell>".htmlspecialchars(($dapat->UnitPrice))."</cell>";
			echo "</row>";	
		$nomor++;
		}


$nomor++;
}

echo "</rows>";
?>
