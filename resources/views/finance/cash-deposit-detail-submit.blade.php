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
		
		if ($key !='ID' && $key !='SubmitTransfer'){
			if ($key == 'Deposit_Val') $value = str_replace(",","",$value);

			$ars .= "$key = '$value',";
			$ax .="\"$key\",";
			$isi.="'$value',";
		}
}
$_POST['Deposit_Val'] = str_replace(",","",$_POST['Deposit_Val']);

$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);

DB::update(DB::raw("UPDATE cash_deposit SET Status='CLOSED',Deposit_Date='$_POST[Deposit_Date]',Deposit_Value='$_POST[Deposit_Val]',Reference='$_POST[Reference]',Note='$_POST[Note]' WHERE ID=$_POST[ID]"));


echo "success";

?>