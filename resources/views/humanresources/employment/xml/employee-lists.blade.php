<?php
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

$Query1 = DB::table("employment_akademic")->get();
$akademis = [];
foreach($Query1 as $Fetch1){
	$akademis[$Fetch1->Task_ID] = $Fetch1->Education_Major;
}
$LSP = Auth::user()->Location;

$allKeys = array_keys(employee_grid_columns());
$reqCols = isset($_GET['cols']) ? explode(',', $_GET['cols']) : [];
$keys = array_values(array_intersect($reqCols, $allKeys));
if (!count($keys)) {
	$keys = employee_grid_default_columns();
}

$nomor = 1;

echo "<rows>";

$Query = DB::table("employment")->where("Location",$LSP)->where("Status","=","ACTIVE")->where("Category","=","INTERNAL")->orderBy("Name","ASC")->get();

foreach($Query as $Fetch){
	echo "<row id=\"$Fetch->Task_ID\">";
	echo "<userdata name=\"nama\">".htmlspecialchars($Fetch->Name)."</userdata>";
	echo "<cell>$nomor</cell>";
	foreach ($keys as $k) {
		echo "<cell>".htmlspecialchars(employee_grid_cell($k, $Fetch, $akademis))."</cell>";
	}
	echo "</row>";
	$nomor++;
}

echo "</rows>";
