<?php
	//error_reporting(E_ALL ^ E_NOTICE);
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



$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;
$jns = DB::table("jns_izin")->get();
foreach($jns as  $jenis){
	$jenisizin[$jenis->izin_jenis_id] = $jenis->izin_jenis_name;
}
$kats = DB::table("kategori_izin")->get();
foreach($kats as  $cats){
	$katgori[$cats->kat_izin_id] = $cats->kat_izin_nama;
}
echo "<rows>";

if ($_GET['NIK']) 
$Query = DB::table("izin")->where("NIK",$_GET['NIK'])->whereBetween('izin_tgl', [$_GET['Tgl1'], $_GET['Tgl2']])->orderBy("izin_tgl","DESC")->get();
else
$Query = DB::table("izin")->whereBetween('izin_tgl', [$_GET['Tgl1'], $_GET['Tgl2']])->orderBy("izin_tgl","DESC")->get();
foreach($Query as $Fetch){
	$kategori = $katgori[$Fetch->kat_izin_id];
	$subkategori = $jenisizin[$Fetch->izin_jenis_id];
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Employee_Name)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Employee_Category)."</cell>";
		echo "<cell>".htmlspecialchars($subkategori)."</cell>";
		echo "<cell>".htmlspecialchars($kategori)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->izin_ket_lain)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->izin_catatan)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->izin_tgl)."</cell>";
		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
