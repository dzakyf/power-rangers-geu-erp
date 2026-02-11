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

// $Query = DB::table("invoice")->where("Location",$LSP)->where("Status","!=","APPROVED")->where("Status","!=","CANCELED")->where("Status","!=","RETURN")->orderBy("ID","DESC")->get();

$Query = DB::table("invoice")->where("Location", $LSP)->where("Status", "=", "UPDATED")->orderBy("ID", "DESC")->get();

// foreach($Query as $Fetch){
// 			echo "<row id=\"$Fetch->Inv_No\">";
// 				echo "<cell>$nomor</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Inv_No)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Inv_KSPS)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Inv_Date)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Account_No)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Account_Name)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Invoice_Type)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Total)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
// 				echo "<cell>".htmlspecialchars($Fetch->Created_Date)."</cell>";
// 			echo "</row>";

// 		$nomor++;

// }


foreach ($Query as $Fetch) {

	if (stripos($Fetch->Task_ID, 'PRF-UNION-') !== false) {

		$ba = explode(",", $Fetch->Note);
		$externalDoc = [];

		foreach ($ba as $keyba => $valueba) {
			$prof = DB::table("proforma")->where("Proforma_No", 'LIKE', "$valueba%")->orderBy("ID", "desc")->first();
			$externalDoc[] = $prof->BA_No;
		}
		$baNo = implode(",", $externalDoc);
	} else {
		$invoice_to = DB::table("proforma")->where("Proforma_No", "=", $Fetch->Task_ID)->first();
		$baNo = $invoice_to->BA_No;
	}

	echo "<row id=\"$Fetch->Inv_No\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_KSPS) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Account_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Invoice_Subject) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Invoice_Category) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->periode_txt) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Subtotal + $Fetch->mgm_fee) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->PPN) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Faktur_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Created_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($invoice_to->Invoice_To) . "</cell>";
	echo "<cell>0</cell>";
	echo "<cell>" . htmlspecialchars($baNo) . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
