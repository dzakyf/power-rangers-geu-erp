<?php
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
	header("Content-type: application/xhtml+xml");
} else {
	header("Content-type: text/xml");
}
echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");



$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("opportunity")->where("Location", $LSP)->where("Status", "!=", "CANCELED")->orderBy("ID", "DESC")->get();

foreach ($Query as $Fetch) {
	$po = DB::table("opportunity_detail")->where("Task_ID", $Fetch->Task_ID)->first();
	if ($po != null) {
		
		if($po->Subject == '') {
			$subject = $Fetch->Oppor_Subject;
		} else {
			$subject = $po->Subject;
		}
		$noPo = $po->PO_No;
	} else {
		$subject = $Fetch->Oppor_Subject;
		$noPo = "";
	}
	$data = DB::table("project_mgt")->where("Source_Reference",$Fetch->Task_ID)->first();
	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Task_ID) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Created_By) . "</cell>";
	echo "<cell>" . htmlspecialchars($subject) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Company_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($noPo) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Created_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Estimated_Rev) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Category) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Expected_Closing_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Note) . "</cell>";
	echo "<cell>" . htmlspecialchars($po->PO_Initial) . "</cell>";
	echo "<cell>" . htmlspecialchars($data->ID) . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
