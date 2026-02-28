<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$NIK =  Auth::user()->Emp_ID;
$Saya =  Auth::user()->username;

$ars = "";
$ax = "";
$isi = "";
$array = [];
foreach ($_POST as $key => $value) {
	$getValue = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
	$ars 	.= "$key,";

	$isi	.= "'$getValue',";
	$array[$key] = $getValue;
}
$ars = substr($ars, 0, -1);
$isi = substr($isi, 0, -1);

$fetch = DB::table("opportunity")->where("Task_ID", $array['source'])->first();
$subjek = $fetch->Oppor_Subject;
$companyname = $fetch->Company_Name;
$Account_No = $fetch->Account_No;

$Task_ID = 'SPJPM/GEU-HO/' . date('Ymdhis') . md5($Saya, 0, 3);
try {
	$kueri = "INSERT INTO `spj`(`Task_ID`, `subject`, `start`, `end`, `kota`,`reference`, `source_reference`,  `total`, `note`, `file`, `status`, `Department`,`company_name`, `company_no`, `created_at`, `created_by`, `updated_at`, `updated_by`, `project_id`) VALUES ('$Task_ID', '$array[subject]' ,'$array[start]','$array[end]','$array[kota]','$array[reference]','$array[source]','$array[total_submit]','$array[note_submit]','$array[file_submit]','CREATED','BUS','$companyname', '$Account_No', NOW(), '$Saya', NOW(), '$Saya','$array[project_id]')";
	$query =  DB::insert(DB::raw($kueri));
} catch (Throwable $e) {
	report($e);

	return false;
}

if ($query) {
	$salt = sha1(uniqid(time(), true));
	$getlevelsx = DB::table("organization_structure")->where("Department", "BUS")->where("Level3", "1")->where("Alerted", "1")->first();
	$mailto = $getlevelsx->Email;

	$subjek = "MOBILE APPROVAL SPJ PROJECT NO $Task_ID";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>" . $getlevelsx->Name . "</b><Br><br>
			
						" . $Saya . " telah mengajukan approval SPJ PROJECT. Mohon kiranya dilakukan approval untuk spj tersebut. Persetujuan dapat dilakukan menggunakan link berikut ini :<br><br>
						
						<a href=\"https://erp.ksps.co.id/erpksps/public/approvalspj?ID=$Task_ID&key=$salt&saya=$getlevelsx->Name\">Approval SPJ</a>
						
						
						<br><br>
						
						Terima kasih
						PT. GREEN ENERGI UTAMA
						
			</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date,linkkey) VALUES('$mailto','$subjek','$badan','UNSENT',NOW(),NOW(),'$salt')"));
	echo "success";
} else {
	echo "error";
}
