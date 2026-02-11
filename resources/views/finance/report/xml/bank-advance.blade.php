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
$tanggal1 = '2021-01-01';
$tanggal2 = date("Y-m-d");
// $tanggal1 = !empty($_GET['tanggal1']) ? $_GET['tanggal1'] : date("Y-m-d", strtotime('first day of january this year'));
// $tanggal2 = !empty($_GET['tanggal2']) ? $_GET['tanggal2'] : date("Y-m-d");

// $Fetchx = DB::select(DB::raw("SELECT `User_Responsibility_Name`,`Department`, sum(`Bank_Val`) as total, count(`User_Responsibility_Name`) as hitung FROM `bank_advance` WHERE Status = 'CREATED' GROUP BY `User_Responsibility_Name` "));

// $bank = DB::table('bank_advance')->select(DB::raw("Department, User_Responsibility_Name, sum(Bank_Val) as total, count(User_Responsibility_Name) as hitung"))->where('Status', "CREATED")
// 	->groupBy('User_Responsibility_Name', 'Department');

// $cash = DB::table('cash_advance')->select(DB::raw("Department, User_Responsibility_Name, sum(Cash_Val) as total, count(User_Responsibility_Name) as hitung"))->where('Status', "CREATED")
// 	->groupBy('User_Responsibility_Name', 'Department')
// 	->unionAll($bank)
// 	->get();
$Fetchx =	DB::select(DB::raw("SELECT Department, User_Responsibility_Name, sum(total) as total, sum(hitung) as hitung FROM ( SELECT Department, User_Responsibility_Name, sum(Bank_Val) as total, count(User_Responsibility_Name) as hitung FROM bank_advance WHERE Status ='CREATED' GROUP By User_Responsibility_Name, Department UNION ALL SELECT Department, User_Responsibility_Name, sum(Cash_Val) as total, count(User_Responsibility_Name) as hitung FROM cash_advance WHERE Status ='CREATED' GROUP By User_Responsibility_Name, Department ) x GROUP by User_Responsibility_Name, Department"));


// $Fetchx =	DB::select(DB::raw("SELECT Department, User_Responsibility_Name, sum(total) as total, sum(hitung) as hitung FROM ( SELECT Department, User_Responsibility_Name, sum(Bank_Val) as total, count(User_Responsibility_Name) as hitung FROM bank_advance WHERE Date_Payment Between '$tanggal1' AND '$tanggal2' AND Posting <> 1 AND STATUS IN ('CREATED', 'CLOSED') GROUP By User_Responsibility_Name, Department UNION ALL SELECT Department, User_Responsibility_Name, sum(Cash_Val) as total, count(User_Responsibility_Name) as hitung FROM cash_advance WHERE Date_Payment Between '$tanggal1' AND '$tanggal2' AND Posting <> 1 AND STATUS IN ('CREATED', 'CLOSED') GROUP By User_Responsibility_Name, Department ) x GROUP by User_Responsibility_Name, Department"));


echo "<rows>";

foreach ($Fetchx as $Fetch) {

	$departmen = DB::table('master_department')->where('Initial', "$Fetch->Department")->first();
	echo "<row id=\"$nomor\">";
	echo "<cell>" . htmlspecialchars($departmen->Department) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->User_Responsibility_Name) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->total) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->hitung) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
	echo "</row>";
	$nomor++;
}


echo "</rows>";
