<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
$jno = "JSL-".date("Ymdhis");

$main = DB::table("salary_main")->where("Task_ID",$_GET['Task_ID'])->first();
$grup = DB::table("payrol_main_group")->where("Main_ID",$main->Salary_Group)->first();
$coatipe = $grup->COA_Type;


$Queryx = DB::table("salary_lists")->where("Task_ID",$_GET['Task_ID'])->orderBy("Payroll_ID","ASC")->get();
foreach($Queryx as $Fetchx){
	$payroll[$Fetchx->Payroll_ID] += $Fetchx->Amounts;
}

$Query = DB::table("payrol_master_lists")->orderBy("Urutan","ASC")->get();

foreach($Query as $Fetch){
	$nilai = $payroll[$Fetch->Payroll_ID];
	if ($nilai < 0) $nilai *= -1;
	
	
	if ($nilai > 0)	{
	
		$coa = $Fetch->$coatipe;
		$branchcoa = DB::table("branch_coa")->where("Acc_No",$coa)->first();
	
		if ($Fetch->Type_Payroll == 'ALLOWANCE' || $Fetch->Type_Payroll == 'BASIC'){
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			 Debet, Credit, User_Resp, Created_By, Created_Date,Department) SELECT
			Task_ID, Location, Created_Date,  Task_ID,Salary_Group, '','', '$jno', '$coa','$branchcoa->Description', '$Fetch->Payroll_ID','$Fetch->Name',Note,'$nilai',0,Created_By,'$Saya',NOW(),Department FROM salary_main where ID = $main->ID"));
			$totalbank += $nilai;
		}else{
			DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
			 Credit, Debet, User_Resp, Created_By, Created_Date,Department) SELECT
			Task_ID, Location, Created_Date,  Task_ID,Salary_Group, '','', '$jno', '$coa','$branchcoa->Description', '$Fetch->Payroll_ID','$Fetch->Name',Note,'$nilai',0,Created_By,'$Saya',NOW(),Department FROM salary_main where ID = $main->ID"));
			$totalbank -= $nilai;
		}
	}
	
	
}

$bank = DB::table("bank_acc")->where("Acc_No",$main->Bank_Acc)->first();
$coabank  = $bank->Bank_Acc;
$coabankname = $bank->Bank_Name;
DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		 Credit, Debet, User_Resp, Created_By, Created_Date,Department) SELECT
		Task_ID, Location, Created_Date,  Task_ID,Salary_Group, '','', '$jno', '$coabank','$coabankname', '$bank->Acc_No','$bank->Acc_Hold',Note,'$totalbank',0,Created_By,'$Saya',NOW(),Department FROM salary_main where ID = $main->ID"));	


DB::update(DB::raw("UPDATE salary_main set Posting=1,Posting_By='$Saya',POsting_Date=NOW() where Task_ID='$_GET[Task_ID]'"));
echo "<script>$.unblockUI();alert('Posting Success!');location.href='accounting-payroll-issued'";
?>