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

$Query = DB::table("asset_list")->orderBy("ID","ASC")->get();

foreach($Query as $dapat){
		
				
						echo "<row id=\"$dapat->ID\">";
							echo "<cell>$nomor</cell>";
							echo "<cell>".htmlspecialchars(($dapat->Asset_No))."</cell>";
							echo "<cell>".htmlspecialchars(($dapat->Asset_Category))."</cell>";
							echo "<cell>".htmlspecialchars(($dapat->Asset_Sub_Category))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Asset_Type))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Product_Code))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Product_Description))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Product_Brand))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Sub_Product_Type))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Manufacture_Date))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Registration_No))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Buy_Date))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Buy_Val))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Asset_Location))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Asset_Location_Address))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Asset_Location_City))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Asset_Location_Department))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Leasing_Vendor))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Leasing_No))."</cell>"; 
							echo "<cell>".htmlspecialchars(($dapat->Acc_No))."</cell>"; 
						echo "</row>";	
					$nomor++;
}
		



echo "</rows>";
?>
