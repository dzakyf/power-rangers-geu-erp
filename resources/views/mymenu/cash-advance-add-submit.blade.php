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
foreach ($_POST as $key => $value) {

	if ($key != 'ID' && $key != 'SubmitTransfer') {
		if ($key == 'Advance_Val') $value = str_replace(",", "", $value);
		$ars .= "$key = '$value',";
		$ax .= "\"$key\",";
		$isi .= "'$value',";
	}
}
$_POST['Advance_Val'] = str_replace(",", "", $_POST['Advance_Val']);

if ($_POST['Advance_Val'] > 500000) {
	echo "Mohon Menggunakan Bank Advanced Karena Pengajuan Lebih dari Rp. 500.000";
	exit(0);
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;
$nik = Auth::user()->Emp_ID;

$ars = substr($ars, 0, -1);
$ax = substr($ax, 0, -1);
$isi = substr($isi, 0, -1);
$cekakun = DB::table('cash_handler')->where('NIK_Handler', $Saya)->first();
$Acc = $cekakun->Account_No;

$TaskID = 'CA/' . $LSP . '/' . date('ymd') . '-' . date('His');

$getlevelsx = DB::table("organization_structure")->where("NIK", $nik)->first();
if ($getlevelsx->Level1 == 1) $lepel = 1;
else if ($getlevelsx->Level2 == 1) $lepel = 2;
else if ($getlevelsx->Level3 == 1) $lepel = 3;
else if ($getlevelsx->Level4 == 1) $lepel = 4;
else if ($getlevelsx->Level5 == 1) $lepel = 5;
else if ($getlevelsx->Level6 == 1) $lepel = 6;
else if ($getlevelsx->Level7 == 1) $lepel = 7;
else if ($getlevelsx->Level8 == 1) $lepel = 8;

// dipakai pada versi selanjutnyha
//$getupper = DB::table("organization_structure")->where("NIK",  $getlevelsx->upper)->first();
$upper = $lepel - 1;

if ($upper == 3) {
	$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", $_POST['Department'])->where("Alerted", 1)->first();
	$status = "APPROVAL_UPPER";
} else {
	$getupper = DB::table("organization_structure")->where("Level2", 1)->where("Department", $getlevelsx->divisi)->where("Alerted", 1)->first();
	$status = "APPROVAL_UPPER";
}

DB::insert(DB::raw("INSERT INTO cash_advance(Location, Task_ID, Department, Acc_No, Advance_Date, Reference, Description, User_Responsibility, User_Responsibility_Name, User_Responsibility_ID, Cash_Val, Val_Used, Val_Remain, Status, Created_By, Created_Date,User_Reference) VALUES ('$LSP','$TaskID','$_POST[Department]','$_POST[Acc_No]','$_POST[Advance_Date]','$_POST[Reference]','$_POST[Description]','$_POST[User_Responsibility]','$_POST[User_Responsibility_Name]','$_POST[User_Responsibility_ID]',$_POST[Advance_Val],0,$_POST[Advance_Val],'$status','$Saya',NOW(),'$_POST[User_Reference]')"));

if ($getupper) {
	$linkkey = md5(date("Ym") . "789213123122122");
	$subjek = "IMPORTANT : APPROVAL PERMINTAAN CASH ADVANCE $TaskID # , Request By : $getlevelsx->Name";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$getupper->Name</b><Br><br>
		
					Mohon dilakukan approval untuk Permintaan Cash Advance/PUM dengan rincian sebagai berikut:<br><br>
					
					No Permintaan : $TaskID<br><br>
					User : $_POST[User_Responsibility_Name] / $_POST[User_Responsibility_ID]<br><br>
					Reference : $_POST[Reference]<br><br>
					Nilai : $nilai<br><br>
					Note : $_POST[Description]<br>
					
					<br><br>
					Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://erp.ksps.co.id/erpksps/public/approvalcashadvance?ID=$TaskID&linkkey=$linkkey&saya=$Saya\' >KLIK DISINI</a><br><br>
					
					Terima kasih<br>
					GREEN ENERGY UTAMA
					
		</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$getupper->Email','$subjek','$badan','UNSENT',NOW(),NOW())"));
}




echo "success";
