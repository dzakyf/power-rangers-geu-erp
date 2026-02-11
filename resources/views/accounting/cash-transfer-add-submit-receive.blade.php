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
$dept =  Auth::user()->Department;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);
$from = DB::table('cash_handler')->where('NIK_Handler',$_POST['From_Acc'])->first();
$to = DB::table('cash_handler')->where('NIK_Handler',$_POST['To_Acc'])->first();

$jno='CT-'.date('ymdhis').substr($_POST['Task_ID'],-4);

	DB::update(DB::raw("UPDATE cash_transfer SET Posting=1,Posting_By='$Saya',Posting_Date=NOW() WHERE Task_ID='$_POST[Task_ID]'"));
	
	DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
				Debet,Credit, User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
				Task_ID,Location,Transfer_Date,Task_ID,Reference,To_Acc,To_Acc, '$jno','$to->Account_No','', '','',Note,Cash_Val,0,Created_By,'$Saya',NOW(),Task_ID,'$dept' FROM cash_transfer WHERE Task_ID='$_POST[Task_ID]'"));	

	DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
				Debet,Credit, User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
				Task_ID,Location,Transfer_Date,Task_ID,Reference,From_Acc,From_Acc, '$jno','$from->Account_No','', '','',Note,0,Cash_Val,Created_By,'$Saya',NOW(),Task_ID,'$dept' FROM cash_transfer WHERE Task_ID='$_POST[Task_ID]'"));	
				

echo "success";

?>