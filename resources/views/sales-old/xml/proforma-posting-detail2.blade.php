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

$Query = DB::table("proforma_detail_file")->where("Proforma_No",$_GET['Task_ID'])->get();

foreach($Query as $Fetch){

			echo "<row id=\"$Fetch->ID\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars($Fetch->File_Category)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Reference_No)."</cell>";
				echo "<cell>".htmlspecialchars('edit.png')."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Path_File)."</cell>";
			echo "</row>";

		$nomor++;
	
}

$data = DB::table("proforma_detail")->where("Proforma_No",$_GET['Task_ID'])->first();
$data2 = DB::table("project_mgt")->where("Source_Reference",$data->Task_ID)->first();

$query2 = DB::table("project_documents")->where("Task_ID",$data2->ID)->get();


foreach($query2 as $Fetch2) {
	if($Fetch2->Document_No == $data->PO_No) {
	echo "<row id=\"$Fetch2->ID-doc\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->File_Category)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->Document_No)."</cell>";
				echo "<cell>".htmlspecialchars('edit.png')."</cell>";
				echo "<cell>".htmlspecialchars($Fetch2->Path_File)."</cell>";
			echo "</row>";

		$nomor++;
	}
}


echo "</rows>";
?>
