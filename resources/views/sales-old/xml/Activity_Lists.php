<?php
	//error_reporting(E_ALL ^ E_NOTICE);
require_once "../../../../_libFunc.php";
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");



$nomor=1;
$LSP = LSP();
$Saya = Saya();
if($_GET['taskid']=="") {
	$filter = "";
} else {
	$filter = "AND TASK_ID='$_GET[taskid]'";
}


$Query = query_all("pss_marsal.activity","Location='$LSP' AND Status != 'CANCELED' $filter","ID DESC","");
echo "<rows>";

while ($Fetch = f_a($Query)){
		
	echo "<row id=\"$Fetch[ID]\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch['Created_By'])."</cell>";
		echo "<cell>".htmlspecialchars($Fetch['Activity_Date'])."</cell>";
		echo "<cell>".htmlspecialchars($Fetch['Activity_Type'])."</cell>";
		echo "<cell>".htmlspecialchars($Fetch['Priority'])."</cell>";
		echo "<cell>".htmlspecialchars(Bersih($Fetch['Activity_Description']))."</cell>";
		echo "<cell>".htmlspecialchars($Fetch['Company_Name'])."</cell>";
		echo "<cell>".htmlspecialchars($Fetch['Oppor_Subject'])."</cell>";
		echo "<cell>".htmlspecialchars($Fetch['Note'])."</cell>";
	echo "</row>";

$nomor++;
}


echo "</rows>";
?>