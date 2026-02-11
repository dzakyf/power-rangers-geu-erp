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
			$ars .= "$key = '$value',";
			$ax .="\"$key\",";
			$isi.="'$value',";
		}
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);
$_POST['Transfer_Val'] = str_replace(",","",$_POST['Transfer_Val']);

$cekakun = DB::table('cash_handler')->where('NIK_Handler',$Saya)->first();
$Acc= $cekakun->Account_No;

$TaskID = 'BT/'.$LSP.'/'.date('ymd').'-'.date('His');
	
$pecah = explode("|",$_POST['To_Acc']);
$tonik = $pecah[1];
$tobank= $pecah[0];
$fpecah = explode("|",$_POST['From_Acc']);
$fromnik = $fpecah[1];
$frombank= $fpecah[0];

DB::insert(DB::raw("INSERT INTO bank_transfer(Location,Task_ID,From_Bank,From_Acc,To_Bank,To_Acc,Transfer_Value,Reference,Description,Status,Created_By,Created_Date,Transfer_Date,Department) VALUES ('$LSP','$TaskID','$frombank','$fromnik','$tobank','$tonik','$_POST[Transfer_Val]','$_POST[Reference]','$_POST[Note]','CREATED','$Saya',NOW(),'$_POST[Trx_Date]','$_POST[Department]')"));


echo "success";

?>