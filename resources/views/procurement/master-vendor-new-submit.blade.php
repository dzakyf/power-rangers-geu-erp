<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$ars = "";
$ax = "";
$isi = "";
foreach($_POST as $key=>$value)
{
		
		if ($key !='ID' && $key !='UpdateData'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);
$cekakun = DB::table('supplier')->orderBy('ID','desc')->first();
$Acc= $cekakun->Supplier_ID;
$pecah = explode("SUP",$Acc);
$Supplier_ID = "SUP". ($pecah[1] + 1);

DB::insert(DB::raw("INSERT INTO supplier($ax,Status,Supplier_ID,Created_By,Location) VALUES($isi,'NORMAL','$Supplier_ID','$Saya','$LSP')"));


echo "success";

?>