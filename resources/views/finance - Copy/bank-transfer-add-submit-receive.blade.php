<?php
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
$cekakun = DB::table('bank_handler')->where('NIK_Handler',$Saya)->first();
$Acc= $cekakun->Account_No;

	DB::update(DB::raw("UPDATE bank_transfer SET Status='CLOSED',Received_Date=NOW(),Received_By='$Saya' WHERE Task_ID='$_POST[Task_ID]'"));
	DB::update(DB::raw("UPDATE bank_handler SET Current_Saldo = Current_Saldo - $_POST[Transfer_Val] where NIK_Handler='$_POST[From_Acc]'"));
	DB::update(DB::raw("UPDATE bank_handler SET Current_Saldo = Current_Saldo + $_POST[Transfer_Val] where NIK_Handler='$Saya'"));


echo "success";

?>