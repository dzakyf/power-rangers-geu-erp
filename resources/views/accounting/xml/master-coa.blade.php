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

$QAgent = DB::table('branch_coa')->orderBy("IATA_Code","asc")->get();
foreach($QAgent as $Fetch){
		
	if ($Fetch->Klass_Ref == 'BANK'){
		
		$qy = DB::table("bank_acc")->where("Location_ID",$LSP)->where("Bank_Acc",$Fetch->Acc_No)->orderBy("Urutan")->get();
		if (count($qy)){
			foreach ($qy as $ax){
		
			$norek = $ax->Acc_No;
			 
				echo "<row id=\"$Fetch->ID|$norek\">";
					echo "<cell>$nomor</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Acc_No)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Description)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Grouping_ID)."</cell>";
					echo "<cell>$norek</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Normal)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Klass_Ref)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Listed_OnSearch)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->IATA_Code)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
				echo "</row>";

			}
		}else{
				echo "<row id=\"$Fetch->ID\">";
					echo "<cell>$nomor</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Acc_No)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Description)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Grouping_ID)."</cell>";
					echo "<cell></cell>";
					echo "<cell>".htmlspecialchars($Fetch->Group_Report)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Klass_Ref)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Listed_OnSearch)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->IATA_Code)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
				echo "</row>";

		}
		}else{
			xData:
			echo "<row id=\"$Fetch->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Acc_No)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Description)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Grouping_ID)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Group_Report)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Normal)."</cell>";
				echo "<cell></cell>";
					echo "<cell>".htmlspecialchars($Fetch->Listed_OnSearch)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->IATA_Code)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
			echo "</row>";
			
		}

	$nomor++;
	
}

echo "</rows>";
?>
