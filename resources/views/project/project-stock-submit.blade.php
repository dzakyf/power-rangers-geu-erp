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

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'Task_ID' && $key != 'Created_Date' && $key != 'Created_By' && $key != 'Location' && $key != 'Company_Name'){
				
			
				$ars 	.="$key,";
				
				$isi	.="'$_POST[$key]',";
				
				
				$MX .="$key = $_POST[$key],";
			
			}					
			

    

}

$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

	$fetch = DB::table("opportunity")->where("Task_ID",$taskid)->first();
	$subjek = $fetch->Oppor_Subject;
	$companyname = $fetch->Company_Name;
	$Account_No = $fetch->Account_No;
 
$kueri = "INSERT INTO stock_request (Location, WH_Dest, Process_By, Process_By_NIK, Stock_Category, SR_No, FABNo, SR_Date, Request_By, Department, Status, Reference, Remark, Date_Pickup, Date_Trx, User_Trx) 
VALUES ('$LSP','$_POST[WH_Dest]', '$Saya', '$NIK', '$_POST[Sub_Inventory]', '$_POST[SR_No]', '$_POST[Reference_No]', NOW(), '$_POST[User_Request_Name]', '$_POST[Department]', 'SUBMITED', '$_POST[Reference_No]', '$_POST[Note]', NOW(), CURRENT_TIMESTAMP, '$Saya')";
DB::insert(DB::raw($kueri));



echo "success";
?>