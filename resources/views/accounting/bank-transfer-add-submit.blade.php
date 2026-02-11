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

$TaskID = 'BT/'.$LSP.'/'.date('ymd').'-'.date('His');
	
$pecah = explode("#",$_POST['To_Acc']);
$nikhandler = $pecah[1];
$_POST['To_Acc']= $pecah[0];
$toName = $pecah[2];
DB::insert(DB::raw("INSERT INTO bank_transfer(Location,Task_ID,From_Bank,To_Bank,Transfer_Value,Reference,Description,Status,Created_By,Created_Date,Transfer_Date) VALUES ('$LSP','$TaskID','$_POST[From_Acc]','$_POST[To_Acc]','$_POST[Transfer_Val]','Trf To $_POST[To_Acc] - $_POST[Reference]','$_POST[Note]','CREATED','$Saya',NOW(),'$_POST[Trx_Date]')"));


echo "success";

?>