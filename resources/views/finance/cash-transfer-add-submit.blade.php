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
			if ($key == 'Transfer_Val') $value = str_replace(",","",$value);

			$ars .= "$key = '$value',";
			$ax .="\"$key\",";
			$isi.="'$value',";
		}
}
$_POST['Transfer_Val'] = str_replace(",","",$_POST['Transfer_Val']);

$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);
$cekakun = DB::table('cash_handler')->where('NIK_Handler',$Saya)->first();
$Acc= $cekakun->Account_No;

$TaskID = 'CT/'.$LSP.'/'.date('ymd').'-'.date('His');
	
$pecah = explode("#",$_POST['To_Acc']);
$nikhandler = $pecah[1];
$_POST['To_Acc']= $pecah[0];
$toName = $pecah[2];
DB::insert(DB::raw("INSERT INTO cash_transfer(Location,Task_ID,From_Acc,To_Acc,Cash_Val,Reference,Note,Status,Created_By,Created_Date,Received_Location,Transfer_Date) VALUES ('$LSP','$TaskID','$Saya','$nikhandler','$_POST[Transfer_Val]','Trf To $toName - $_POST[Reference]','$_POST[Note]','CREATED','$Saya',NOW(),'$LSP','$_POST[Transfer_Date]')"));


echo "success";

?>