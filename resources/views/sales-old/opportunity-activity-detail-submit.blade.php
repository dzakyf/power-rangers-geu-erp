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
				
			if ($key =='Activity_Type') {
				switch($_POST[$key]) {
					case "EMAIL": $grade = 1;break;
					case "PHONE CALL": $grade = 1;break;
					case "MEETING": $grade = 1;break;
					case "SUBMIT PROPOSAL": $grade = 2;break;
					case "NEGOTIATE": $grade = 2;break;
					case "AGREED SHIPPING": $grade = 3;break;
					case "TRIAL SHIPPING": $grade = 3;break;
					case "NEW CONTRACT": $grade = 4;break;
					case "CLOSE CONTRACT": $grade = 6;break;
					case "FAILED": $grade = 6;break;
					default: $grade = 1; break;				
				}
			}
			
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
 
$kueri = "UPDATE activity set Activity_Type='$_POST[Activity_Type]',Activity_Date='$_POST[Activity_Date]',End_Date='$_POST[End_Date]',Activity_Description='$_POST[Activity_Description]',Priority='$_POST[Priority]',PIC='$_POST[PIC]',PIC_Phone='$_POST[PIC_Phone]',PIC_Function='$_POST[PIC_Function]',Note='$_POST[Note]' WHERE ID=$_POST[ID]";
DB::insert(DB::raw($kueri));

DB::update(DB::raw("UPDATE opportunity SET Grade_Status='$grade' WHERE Task_ID='$taskid'"));


echo "success";
?>