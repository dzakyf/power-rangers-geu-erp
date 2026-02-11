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
$cekakun = DB::table('cash_handler')->where('NIK_Handler',$Saya)->first();
$Acc= $cekakun->Account_No;

$TaskID = 'CD/'.$LSP.'/'.date('ymd').'-'.date('His');
	
$pecah = explode("#",$_POST['To_Acc']);
$coabank = $pecah[1];
$_POST['To_Acc']= $pecah[0];
$toName = $pecah[2];
DB::insert(DB::raw("INSERT INTO bank_withdrawal(Location,Task_ID,Reference,Description,From_Acc,withdrawal_To,To_Acc,withdrawal_Value,withdrawal_Date,Status,Created_Date,Created_By) VALUES ('$LSP','$TaskID','$_POST[Reference]','BANK withdrawal','$Saya','$coabank','$_POST[To_Acc]','$_POST[WithDrawal_Val]','$_POST[WithDrawal_Date]','CREATED',NOW(),'$Saya')"));


echo "success";

?>