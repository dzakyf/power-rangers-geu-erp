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
if ($priv == 'ADM'){
$Query = DB::table("proforma")->where("Location",$LSP)->where("Status","=","ATTACHED")->orderBy("ID","DESC")->get();
}else{
$Query = DB::table("proforma")->where("Location",$LSP)->where("Status","=","ATTACHED")->where("Created_NIK","=",$saya)->orderBy("ID","DESC")->get();
	
}

foreach($Query as $Fetch){
			echo "<row id=\"$Fetch->Proforma_No\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Proforma_No)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Dateinsert)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Account_Name)."</cell>";
				echo "<cell>" . htmlspecialchars($Fetch->Subject) . "</cell>";
				echo "<cell>" . htmlspecialchars($Fetch->Periode) . "</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Proforma_Type)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Total)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Qty)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_NIK)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Dateinsert)."</cell>";
			echo "</row>";

		$nomor++;
	
}

echo "</rows>";
