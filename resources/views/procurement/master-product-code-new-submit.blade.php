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

$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$prod1 = DB::table("master_product_group")->where("Product_Group", $_POST['Main_Category'])->first();
$prod2 = DB::table("master_product_sub_group")->where("Product_Group", $_POST['Main_Category'])->where("Sub_Group", $_POST['Category'])->first();
$prod3 = DB::table("master_product_sub_sub_group")->where("Product_Group", $_POST['Main_Category'])->where("Sub_Sub_Group", $_POST['Supplies_Type'])->where("Sub_Group", $_POST['Category'])->first();

$P1 = $prod1->Initial;
$P2 = $prod2->Initial;
$P3 = $prod3->Initial;
$gab = $P1 . $P2 . $P3;
$jmlah = DB::table('product_code')->where("Product_Code", "LIKE", "$gab%")->orderBy('Product_Code', 'desc')->first();
$raw = preg_replace("/\D+/", "",  $jmlah->Product_Code);
$next = $raw + 1;


$_POST['Product_Code'] = $gab . sprintf("%'03d", $next);

foreach ($_POST as $key => $value) {

	if ($key != 'ID' && $key != 'UpdateData') {
		$ars .= "$key = '$value',";
		$ax .= "$key,";
		$isi .= "'$value',";
	}
}

$ars = substr($ars, 0, -1);
$ax = substr($ax, 0, -1);
$isi = substr($isi, 0, -1);
DB::insert(DB::raw("INSERT INTO product_code($ax,Status,Supplier_ID,Created_By,Location) VALUES($isi,'NORMAL','$Supplier_ID','$Saya','$LSP')"));


echo "success";
