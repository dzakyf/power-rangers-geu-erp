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

function Products($PID)
{

	$fx = DB::table("product_code")->where("Product_Code", $PID)->first();
	return $fx;
}
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$PRApproval = Auth::user()->PR_Approval;
$Saya = Auth::user()->username;

$nomor = 1;
if (isset($_GET['src'])) $src = $_GET['src'];
else $src = 1;

$Task_ID = $_GET['ID'];
$kueri = DB::select(DB::raw("SELECT * FROM purchase_summary_detail WHERE Task_ID='$Task_ID' ORDER BY ID ASC"));
echo "<rows>";
foreach ($kueri as $Fetch) {

	$Products = Products($Fetch->Product_Code);

	if (trim($Products->Description) == trim($Fetch->Specification)) {
		$NamaProduk = trim($Products->Description) . "#" . trim($Fetch->Recommended_Spec);
	} else {
		$NamaProduk = $Fetch->Specification. "#" .$Fetch->Recommended_Spec;
	}

	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->Product_Code)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($NamaProduk . " [" . $Fetch->Unit_Name . "]")) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->Qty)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->Proposed)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Products->UnitPrice)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V1_Propose)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V1_TOP)) . "</cell>";
	echo "<cell>1</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V2_Propose)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V2_TOP)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V2_Win)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V3_Propose)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V3_TOP)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V3_Win)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V4_Propose)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V4_TOP)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V4_Win)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V5_Propose)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V5_TOP)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->V6_Win)) . "</cell>";
	echo "<cell>" . htmlspecialchars(($Fetch->PR_Related)) . "</cell>";
	echo "</row>";
	$nomor++;
}
echo "</rows>";
