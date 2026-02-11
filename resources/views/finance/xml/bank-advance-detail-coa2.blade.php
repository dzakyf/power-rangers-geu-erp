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


$nomor = 1;
$QAgent = DB::table("branch_coa")->where("Listed_OnSearch", "=", 1)->where("Acc_No", "LIKE", '111%')->get();
echo "<rows>";

foreach ($QAgent as $Fetch) {

	if ($Fetch->Klass_Ref == 'BANK') {

		$qy = DB::table("bank_acc")->where("Bank_Acc", $Fetch->Acc_No)->orderBy("Bank_ID")->get();

		if (count($qy)) {
			foreach ($qy as $ax) {

				$norek = $ax->Acc_No;

				echo "<row id=\"$Fetch->ID|$norek\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>" . htmlspecialchars($Fetch->Acc_No) . "</cell>";
				echo "<cell>" . htmlspecialchars($Fetch->Description) . "</cell>";
				echo "<cell>" . htmlspecialchars($norek) . "</cell>";
				echo "</row>";
				$nomor++;
			}
		}
	} else {


		echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>" . htmlspecialchars($Fetch->Acc_No) . "</cell>";
		echo "<cell>" . htmlspecialchars($Fetch->Description) . "</cell>";
		echo "<cell></cell>";
		echo "</row>";
		$nomor++;
	}
}





echo "</rows>";
