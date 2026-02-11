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
			if ($key == 'Total') $value = str_replace(",","",$value);
			$ars .= "$key = '$value',";
			$ax .="\"$key\",";
			$isi.="'$value',";
		}
}
$nilai = $_POST['Total'];
$_POST['Total'] = str_replace(",","",$_POST['Total']);
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;
$nik = Auth::user()->Emp_ID;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);


$getlevelsx  = DB::table("organization_structure")->where("NIK", $nik)->first();
if ($getlevelsx->Level1 == 1) $lepel = 1;
else if ($getlevelsx->Level2 == 1) $lepel = 2;
else if ($getlevelsx->Level3 == 1) $lepel = 3;
else if ($getlevelsx->Level4 == 1) $lepel = 4;
else if ($getlevelsx->Level5 == 1) $lepel = 5;
else if ($getlevelsx->Level6 == 1) $lepel = 6;
else if ($getlevelsx->Level7 == 1) $lepel = 7;
else if ($getlevelsx->Level8 == 1) $lepel = 8;

$upper = $lepel - 1;

if($upper == 3) {
	$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", $_POST['Department'])->where("Alerted", 1)->first();
	$status = "APPROVAL_UPPER";
	$link = 'approvalreimbursement';
} else{
	$getupper = DB::table("organization_structure")->where("Level2", 1)->where("Department", $getlevelsx->divisi)->where("Alerted", 1)->first();
	$status = "APPROVAL_VP";
	$link = 'approvalreimbursement2';
}

DB::insert(DB::raw("INSERT INTO reimbursement(Location, Reimbursement_No, Reference_No, User_Responsibility_ID, User_Responsibility_Name, User_Reference,Currency, Reimbursement_Date, Department, Description, Status, Total, Created_By, Created_Date)
VALUES ('$LSP','$_POST[Task_ID]','$_POST[Reference_No]','$_POST[User_Responsibility_ID]','$_POST[User_Responsibility_Name]','$_POST[User_Reference]','Rp','$_POST[Reimbursement_Date]','$_POST[Department]','$_POST[Description]','$status','$_POST[Total]','$Saya',NOW())"));


$linkkey = md5(date("Ym")."789213123122122");
$subjek = "IMPORTANT : APPROVAL PERMINTAAN REIMBURSEMENT $_POST[Task_ID] # , Request By : $getlevelsx->Name";
		$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$getupper->Name</b><Br><br>
		
					Mohon dilakukan approval untuk Permintaan Reimbursement dengan rincian sebagai berikut:<br><br>
					
					No Permintaan : $_POST[Task_ID]<br><br>
					User : $_POST[User_Responsibility_Name] / $_POST[User_Responsibility_ID]<br><br>
					Reference : $_POST[Reference_No]<br><br>
					Nilai : $nilai<br><br>
					Note : $_POST[Description]<br>
					
					<br><br>
					Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://erp.ksps.co.id/erpksps/public/$link?ID=$_POST[Task_ID]&linkkey=$linkkey&saya=$Saya\' >KLIK DISINI</a><br><br>
					
					Terima kasih<br>
					GREEN ENERGY UTAMA
					
		</div>";	
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$getupper->Email','$subjek','$badan','UNSENT',NOW(),NOW())"));
	
	
echo "success";

?>