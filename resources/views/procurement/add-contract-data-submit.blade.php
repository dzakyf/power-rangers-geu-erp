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
		
		if ($key !='ID' && $key !='UpdateData' && $key !='Recurring_Day' && $key !='Recurring_Month'){
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
if ($_POST['Recurring_Type'] == 'MONTH') $rekuring =$_POST['Recurring_Day'] ;else $rekuring =$_POST['Recurring_Month'] ." ".$_POST['Recurring_Day'] ;
DB::insert(DB::raw("INSERT INTO contract($ax,Created_By,Created_Date,Recurring_Date) values($isi,'$Saya',now(),'$rekuring')"));


echo "success";

?>