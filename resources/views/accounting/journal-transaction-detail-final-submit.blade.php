<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$d = $_GET['Status'];
$Task_ID = $_GET['ID'];

$dapatkankota = DB::select(DB::raw("SELECT * FROM branch_journal_task  WHERE Task_ID='$Task_ID'"));

	$city = $dapatkankota->Location;
	$namadatabase = "ho";

	
	DB::insert(DB::raw("INSERT INTO $namadatabase (Task_ID, Location, Group_No, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Department, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark, Debet, Credit, User_Resp, Created_By, Created_Date, Task_Path) 
	SELECT Task_ID, Location, Group_No, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Department, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark, Debet, Credit, User_Resp, Created_By, Created_Date, Task_Path FROM branch_journal_temp WHERE Task_ID='$Task_ID'"));
	
	DB::insert(DB::raw("UPDATE branch_journal_task set Status='CLOSED' WHERE Task_ID='$Task_ID'"));
	DB::insert(DB::raw("INSERT INTO branch_journal_task_history(Task_ID,Location,Responsible,Description,Actions,Status,Created_By)
		   SELECT Task_ID,Location,'$saya','FINAL SUBMIT TO POSTING JOURNAL','FINAL POSTING','$nStatus','$saya'
			from branch_journal_task WHERE Task_ID='$Task_ID'"));
		
	echo "<script>
	location.href='accounting-journal-transaction';
	
	</script>";


?>