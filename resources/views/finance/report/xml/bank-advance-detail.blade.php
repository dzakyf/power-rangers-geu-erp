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
$Saya = Auth::user()->username;
$nomor = 1;
$nama  = $_GET['nama'];
$Department  = $_GET['Department'];


// $Fetchx = DB::select(DB::raw("SELECT `User_Responsibility_Name`,`Department`, sum(`Bank_Val`) as total, count(`User_Responsibility_Name`) as hitung FROM `bank_advance` WHERE Status = 'CREATED' GROUP BY `User_Responsibility_Name` "));

$bank = DB::table('bank_advance')->select(DB::raw("Task_ID, Advance_Date, submitted_date, User_Responsibility_Name,Description,Date_Payment, Bank_Val as nilai"))->where('Status', "CREATED")->where('User_Responsibility_Name', "$nama")->where('Department', "$Department");

$Fetchx = DB::table('cash_advance')->select(DB::raw("Task_ID, Advance_Date, submitted_date, User_Responsibility_Name,Description,Date_Payment, Cash_Val as nilai"))->where('Status', "CREATED")->where('User_Responsibility_Name', "$nama")->where('Department', "$Department")->unionAll($bank)->get();




echo "<rows>";

foreach ($Fetchx as $Fetch) {


	echo "<row id=\"$nomor\">";
	echo "<cell>" . htmlspecialchars($nomor) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Task_ID) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->User_Responsibility_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Advance_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->nilai) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Description) . "</cell>";
	echo "</row>";
	$nomor++;
}


echo "</rows>";
