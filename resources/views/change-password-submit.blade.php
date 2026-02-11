<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

$ars = "";
$ax = "";
$isi = "";
	$Saya =  Auth::user()->username;

$old = Hash::make($_POST['password']);
$New_Password = $_POST['New_Password'];
$Confirm_Password = $_POST['Confirm_Password'];
$getpass= DB::table("users")->where("username",$Saya)->first();

if (strcmp($New_Password,$Confirm_Password)!= 0){
	echo "notmatch";

}elseif ($New_Password == '' || $Confirm_Password == '' || $old == ''){
	echo "empty";

}else{
	
$pass = bcrypt($Confirm_Password);
DB::update(DB::raw("UPDATE users set password='$pass' where username='$Saya'"));


echo "success";
}
?>