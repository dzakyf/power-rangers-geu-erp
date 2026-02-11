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
			if ($key == 'Deposit_Val') $value = str_replace(",","",$value);

			$ars .= "$key = '$value',";
			$ax .="\"$key\",";
			$isi.="'$value',";
		}
}
$_POST['Deposit_Val'] = str_replace(",","",$_POST['Deposit_Val']);

$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;
$dept = Auth::user()->Department;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);

$from = DB::table('cash_handler')->where('NIK_Handler',$_POST['From_Acc'])->first();
$jno='JCD-'.date('ymdhis').substr($_POST['Task_ID'],-4);

	DB::update(DB::raw("UPDATE cash_deposit SET Posting=1,Posting_By='$Saya',Posting_Date=NOW() WHERE Task_ID='$_POST[Task_ID]'"));
	
	DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
				Debet,Credit, User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
				Task_ID,Location,Deposit_Date,Task_ID,Reference,'','', '$jno',Deposit_To,'', To_Acc,'',Note,Deposit_Value,0,Created_By,'$Saya',NOW(),Task_ID,'$dept' FROM cash_deposit WHERE ID=$_POST[ID]"));	

	DB::insert(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
				Credit,Debet, User_Resp, Created_By, Created_Date,Task_Path,Department) SELECT 
				Task_ID,Location,Deposit_Date,Task_ID,Reference,'','', '$jno','$from->Account_No','', '','',Note,Deposit_Value,0,Created_By,'$Saya',NOW(),Task_ID,'$dept' FROM cash_deposit WHERE ID=$_POST[ID]"));	

		
echo "success";

?>