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

$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";
$Query = DB::table("spj")->where("project_id", $_GET['ID'])->orderBy("id", "DESC")->get();

foreach ($Query as $Fetch) {

	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Task_ID) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->subject) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->kota) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->start) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->end) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->status) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->note) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->file) . "</cell>";
	echo "</row>";

	$nomor++;
}

echo "</rows>";
