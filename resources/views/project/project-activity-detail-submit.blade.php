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

	$fetch = DB::table("project_mgt")->where("Task_ID",$taskid)->first();
	$subjek = $fetch->Description;
	$companyname = $fetch->Company_Name;
	$Account_No = $fetch->Customer_No;
 
$kueri = "UPDATE project_activity set Activity_Type='$_POST[Activity_Type]',Activity_Date='$_POST[Activity_Date]',End_Date='$_POST[End_Date]',Activity_Description='$_POST[Activity_Description]',Priority='$_POST[Priority]',PIC='$_POST[PIC]',PIC_Phone='$_POST[PIC_Phone]',PIC_Function='$_POST[PIC_Function]',Note='$_POST[Note]' WHERE ID=$_POST[ID]";
DB::insert(DB::raw($kueri));


echo "success";
?>