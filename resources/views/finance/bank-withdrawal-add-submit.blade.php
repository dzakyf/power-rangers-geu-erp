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
$cekakun = DB::table('cash_handler')->where('NIK_Handler',$Saya)->first();
$Acc= $cekakun->Account_No;

$TaskID = 'BW/'.$LSP.'/'.date('ymd').'-'.date('His');

$_POST['WithDrawal_Value'] = str_replace(",","",$_POST['WithDrawal_Value']);

$fpecah = explode("|",$_POST['From_Acc']);
$fromnik = $fpecah[1];
$frombank= $fpecah[0];	
$pecah = explode("|",$_POST['To_Acc']);
$tonik = $pecah[1];
$toacc= $pecah[0];
$toName = $pecah[2];
DB::insert(DB::raw("INSERT INTO bank_withdrawal(Location,Task_ID,Reference,Description,From_Bank,From_Acc,withdrawal_To,To_Acc,withdrawal_Value,withdrawal_Date,Status,Created_Date,Created_By,Department) VALUES ('$LSP','$TaskID','$_POST[Reference]','$_POST[Note]','$frombank','$fromnik','$tonik','$toacc','$_POST[WithDrawal_Value]','$_POST[WithDrawal_Date]','CREATED',NOW(),'$Saya','$_POST[Department]')"));


echo "success";

?>