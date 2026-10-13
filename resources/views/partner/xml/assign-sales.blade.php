<?php if ((auth()->user()->Priv_ID ?? '') === 'MITRA') { abort(403); } ?>
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



$LSP = Auth::user()->Location ?? 'GEU-HO';
$DOP = Auth::user()->DOP ?? '';
$priv = Auth::user()->Priv_ID ?? '';
$saya = Auth::user()->username ?? 'admin';
$nomor = 1;

echo "<rows>";

$usname = [];
$users = DB::table('users')->where("Status", "ACTIVE")->get();
foreach ($users as $key => $value) {
	$usname[$value->username] = $value->Name;
}

$oppt = [];
$getOppt = DB::table('opportunity')->get();
foreach ($getOppt as $key => $value) {
	$oppt[$value->Task_ID] = $value->Full_Name;
}

$slsDtl = [];
$getAllName = DB::table('opportunity_sales_dtl')->get();
foreach ($getAllName as $key => $value) {
	$slsDtl[$value->Sales_ID][] = $oppt[$value->Task_ID];
}


$Query = DB::table("opportunity_sales")->where("Location", $LSP)->whereIn("Status", ["OPEN","ON PROGRESS"]);

if (!can_menu('SI00226', 'approve')) {
	$Query->where("Sales", $saya);
}

$Query = $Query->orderBy("ID", "DESC")->get();

foreach ($Query as $Fetch) {
	$dtl = implode(', ', $slsDtl[$Fetch->Sales_ID]);
	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Sales_ID) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Created_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
	echo "<cell>" . htmlspecialchars($dtl) . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
