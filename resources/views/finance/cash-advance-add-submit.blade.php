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
			if ($key == 'Advance_Val') $value = str_replace(",","",$value);
			$ars .= "$key = '$value',";
			$ax .="\"$key\",";
			$isi.="'$value',";
		}
}
$_POST['Advance_Val'] = str_replace(",","",$_POST['Advance_Val']);
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);
$cekakun = DB::table('cash_handler')->where('NIK_Handler',$Saya)->first();
$Acc= $cekakun->Account_No;

$TaskID = 'CA/'.$LSP.'/'.date('ymd').'-'.date('His');


DB::insert(DB::raw("INSERT INTO cash_advance(Location, Task_ID, Department, Acc_No, Advance_Date, Reference, Description, User_Responsibility, User_Responsibility_Name, User_Responsibility_ID, Cash_Val, Val_Used, Val_Remain, Status, Created_By, Created_Date,User_Reference) VALUES ('$LSP','$TaskID','$_POST[Department]','$_POST[Acc_No]','$_POST[Advance_Date]','$_POST[Reference]','$_POST[Description]','$_POST[User_Responsibility]','$_POST[User_Responsibility_Name]','$_POST[User_Responsibility_ID]',$_POST[Advance_Val],0,$_POST[Advance_Val],'CREATED','$Saya',NOW(),'$_POST[User_Reference]')"));


echo "success";

?>