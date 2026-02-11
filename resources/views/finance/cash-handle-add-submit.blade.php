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
$cekakun = DB::table('users')->where('username',$_POST['username'])->first();
$Acc= $cekakun->Emp_ID;
if ($Acc != ''){
		

	DB::insert(DB::raw("INSERT INTO cash_handler(Position,Location,Account_No,NIK_Handler,Name_Handler,Name,Start_Saldo,Current_Saldo,Last_Saldo,Main_Location,Status,Created_Date,Created_By,Location_Old) VALUES('$_POST[Position]','$LSP','$_POST[Account_No]','$cekakun->Emp_ID','$cekakun->username','$cekakun->Name',0,0,0,'$LSP','ACTIVE',NOW(),'$Saya','')"));


	echo "success";
	}else{
	echo "error";
}

?>