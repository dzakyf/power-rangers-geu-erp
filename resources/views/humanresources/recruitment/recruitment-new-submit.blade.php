<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location ?? 'GEU-HO';
	$Saya =  Auth::user()->username ?? 'guest';
	
$ars = "";
$ax = "";
$vals = "";

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'ID' && $key != 'SubmitCustDetail'){
				
			
			
			
				$ars 	.="$key,";
				
				$vals	.="'$_POST[$key]',";
				
				
				$MX .="$key = '$_POST[$key]',";
			
			}					
			

    

}

$vals = substr($vals,0,-1);
$ars = substr($ars,0,-1);
$MX = substr($MX,0,-1);


$Taskid = 'RECR/'.$LSP.'/'.date('Ymdhis').md5($Saya,0,3);
$subs = $Taskid;
$kueri = "INSERT INTO recruitment($ars,Created_By,Location,Task_ID,Created_Date) values ($vals,'$Saya','$LSP','$Taskid',NOW())";
DB::insert(DB::raw($kueri));
DB::insert(DB::raw("INSERT INTO recruitment_detail(Task_ID) VALUES ('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_document(Task_ID) VALUES ('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_akademic(Task_ID,Education_Level) VALUES ('$subs','SENIOR/VOCATIONAL SCHOOL'),('$subs','DIPLOMA'),('$subs','BACHELOR'),('$subs','MAGISTER')"));
DB::insert(DB::raw("INSERT INTO recruitment_language(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_organization(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_driving(Task_ID,Description) VALUES ('$subs','SIM A'),('$subs','SIM B'),('$subs','SIM B1'),('$subs','SIM B1 UMUM'),('$sub','SIM C')"));
DB::insert(DB::raw("INSERT INTO recruitment_health(Task_ID,Description) VALUES ('$subs','Stay on Hospital'),('$subs','Outside the hospital more than 7 days'),('$subs','Penyakit lain yang sering mengganggu'),('$subs','Abnormality')"));
DB::insert(DB::raw("INSERT INTO recruitment_experiences(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_informal(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_awards(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_other_skill(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_contact_point(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_family(Task_ID) VALUES
		('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs')"));
DB::insert(DB::raw("INSERT INTO recruitment_family_big(Task_ID) VALUES
		('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs')"));


echo "success";
?>