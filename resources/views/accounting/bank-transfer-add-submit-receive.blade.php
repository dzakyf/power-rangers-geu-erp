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
$cekakun = DB::table('cash_handler')->where('NIK_Handler',$Saya)->first();
$Acc= $cekakun->Account_No;
$jno='JBT-'.date('ymdhis').substr($_POST['Task_ID'],0,-4);

DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Credit, Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
		Task_ID, Location, Transfer_Date, Task_ID, Reference, '','', '$jno', From_Acc,'',From_Bank,'',Description,Transfer_Value,0,Created_By,'$Saya',NOW(),Department FROM bank_transfer WHERE Task_ID='$_POST[Task_ID]'"));	
	
DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet,Credit, User_Resp, Created_By, Created_Date,Department) SELECT
		Task_ID, Location, Transfer_Date, Task_ID, Reference, '','', '$jno', To_Acc,'',To_Bank,'',Description,Transfer_Value,0,Created_By,'$Saya',NOW(),Department FROM bank_transfer WHERE Task_ID='$_POST[Task_ID]'"));	
	


	
DB::update(DB::raw("UPDATE bank_transfer SET Posting_By='$Saya',Posting=1,Posting_Date=NOW() WHERE Task_ID='$_POST[Task_ID]'"));
	


echo "success";

?>