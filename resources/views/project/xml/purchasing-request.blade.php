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
$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP_Class;
$Priv = Auth::user()->Priv_ID;
$nomor = 1;
function Filter($C)
{
    $DataFilter = preg_replace('/[^(\x20-\x7F)]*/', '', $C);
    return htmlspecialchars($DataFilter);
}

function Bersih($Data)
{
    $X = trim($Data);
    $X2 = Filter($X);
    $X2 = preg_replace('/[^(\x20-\x7F)]*/', '', $X2);
    return $X2;
}
echo "<rows>";
$q = DB::table("purchase_request")->where("Reference", $_GET['ID'])->get();

foreach ($q as $Data) {

    echo "<row id=\"$Data->ID\">";
    echo "<cell $warna>$nomor</cell>";
    echo "<cell  $warna>" . Bersih($Data->PR_No) . "</cell>";
    echo "<cell  $warna>" . Bersih($Data->PR_Date) . "</cell>";
    echo "<cell  $warna>" . Bersih($Data->Category) . "</cell>";
    echo "<cell  $warna>" . Bersih($Data->Sub_Category) . "</cell>";
    echo "<cell  $warna>" . Bersih($Data->Purchase_Type) . "</cell>";
    echo "<cell  $warna>" . Bersih($Data->Status) . "</cell>";
    echo "<cell  $warna>" . Bersih($Data->Remark) . "</cell>";
    echo "<cell  $warna>" . Bersih($Data->Created_Date) . "</cell>";
    echo "<cell  $warna>" . Bersih($Data->Created_By) . "</cell>";


    echo "</row>";

    $nomor++;
}


echo "</rows>";
