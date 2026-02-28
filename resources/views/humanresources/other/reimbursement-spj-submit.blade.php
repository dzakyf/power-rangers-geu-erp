<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;

$ars = "";
$ax = "";
$isi = "";
foreach ($_POST as $key => $value) {

	if ($key != 'ID' && $key != 'SubmitTransfer') {
		if ($key == 'Total') $value = str_replace(",", "", $value);
		$ars .= "$key = '$value',";
		$ax .= "\"$key\",";
		$isi .= "'$value',";
	}
}
$nilai = $_POST['Total'];
$_POST['Total'] = str_replace(",", "", $_POST['Total']);
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars, 0, -1);
$ax = substr($ax, 0, -1);
$isi = substr($isi, 0, -1);


$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", $_POST['Department'])->where("Alerted", 1)->first();
$status = "APPROVAL_UPPER";
if (!$getupper) {
	$getupper = DB::table("organization_structure")->where("Level2", 1)->where("Department", 'OPS')->where("Alerted", 1)->first();
	$status = "APPROVAL_VP";
}
DB::beginTransaction();
try {
	DB::insert(DB::raw("INSERT INTO reimbursement(Location, Reimbursement_No, Reference_No, User_Responsibility_ID, User_Responsibility_Name, User_Reference,Currency, Reimbursement_Date, Department, Description, Status, Total, Created_By, Created_Date)
VALUES ('$LSP','$_POST[Task_ID]','$_POST[Reference_No]','$_POST[User_Responsibility_ID]','$_POST[User_Responsibility_Name]','$_POST[User_Reference]','Rp','$_POST[Reimbursement_Date]','$_POST[Department]','$_POST[Description]','APPROVAL_UPPER','$_POST[Total]','$Saya',NOW())"));

	DB::update(DB::raw("UPDATE spj SET status='DONE', updated_at = NOW(), updated_by = '$Saya', Reimbursment_No = '$_POST[Task_ID]', submitted_at = NOW(), submitted_by = '$saya' WHERE Task_ID='$_POST[id_Task]' "));


	$salt = sha1(uniqid(time(), true));
	$subjek = "IMPORTANT : APPROVAL PERMINTAAN REIMBURSEMENT $_POST[Task_ID] # , Request By : $_POST[User_Responsibility_Name]";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$getupper->Name</b><Br><br>
		
					Mohon dilakukan approval untuk Permintaan Reimbursement dengan rincian sebagai berikut:<br><br>
					
					No Permintaan : $_POST[Task_ID]<br><br>
					User : $_POST[User_Responsibility_Name] / $_POST[User_Responsibility_ID]<br><br>
					Reference : $_POST[Reference_No]<br><br>
					Nilai : $nilai<br><br>
					Note : $_POST[Description]<br>
					
					<br><br>
					Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://erp.ksps.co.id/erpksps/public/approvalreimbursement?ID=$_POST[Task_ID]&linkkey=$salt&saya=$Saya\' >KLIK DISINI</a><br><br>
					
					Terima kasih<br>
					PT. GREEN ENERGI UTAMA
					
		</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date, linkkey) VALUES('$getupper->Email','$subjek','$badan','UNSENT',NOW(),NOW(),$salt)"));
	DB::commit();

	echo "success";
} catch (\Throwable $e) {
	DB::rollback();
	throw $e;
}
