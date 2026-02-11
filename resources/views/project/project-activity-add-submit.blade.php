<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
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

	$fetch = DB::table("project_mgt")->where("Task_ID",$_POST['Task_ID'])->first();
	$subjek = $fetch->Description;
	$companyname = $fetch->Company_Name;
	$Account_No = $fetch->Customer_No;
 
$kueri = "INSERT INTO project_activity($ars,Task_ID,Account_No,Created_By,Location,Created_Date,Oppor_Subject,Company_Name) VALUES 
		($isi,'$_POST[Task_ID]','$Account_No','$Saya','$LSP',NOW(),'$subjek','$companyname')";
DB::insert(DB::raw($kueri));



echo "success";
?>