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
					case "SUBMIT QUOTATION": $grade = 3;break;
					case "CONTRACT": $grade = 4;break;
					case "GUARANTEE CLAIM": $grade = 5;break;
					case "CLOSE CONTRACT": $grade = 7;break;
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
 
$kueri = "INSERT INTO activity($ars,Task_ID,Account_No,Created_By,Location,Created_Date,Oppor_Subject,Company_Name) VALUES 
		($isi,'$_POST[Task_ID]','$Account_No','$Saya','$LSP',NOW(),'$subjek','$companyname')";
DB::insert(DB::raw($kueri));

DB::update(DB::raw("UPDATE opportunity SET Grade_Status='$grade' WHERE Task_ID='$taskid'"));


echo "success";
?>