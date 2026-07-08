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
$vals = "";

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'ID' && $key != 'SubmitInterview'){
				
			
			
			
				$ars 	.="$key,";
				
				$vals	.="'$_POST[$key]',";
				
				
				$MX .="$key = '$_POST[$key]',";
			
			}					
			

    

}

$vals = substr($vals,0,-1);
$ars = substr($ars,0,-1);
$MX = substr($MX,0,-1);
if ($_POST['User_Interview_Status'] == 'ACCEPT'){

	$statusnya ="BUSINESS_REVIEW";
	
}elseif ($_POST['User_Interview_Status'] == 'PENDING'){
	$statusnya = 'PENDING';
}else{
	$statusnya = $_POST['User_Interview_Status'];
}

DB::update(DB::raw("UPDATE recruitment set User_Interview_File='$_POST[User_Interview_File]',User_Interview_By='$Saya',User_Interview_Date='$_POST[User_Interview_Date]',User_Interview_Result='$_POST[User_Interview_Result]',User_Interview_Status='$_POST[User_Interview_Status]',User_Interview_Note='$_POST[User_Interview_Note]',Status='$statusnya' WHERE ID=$_POST[ID]"));

DB::insert(DB::raw("INSERT INTO employment (Task_ID, NIK, Name, Initial, Birth_Place, Birth_Date, Gender, Marital_Status, PTKP_Status, kewarganegaraan, Last_Education, Last_Education_From, Education_Major, Religion, Blood, Blood_Rhesus, Status, Address, City, Address_Living, City_Living, Phone, Handphone, Email, Email_Individual, KTP, Foto, Position, Reason, Location, Alocation, Work_Area, Department, Directorat, Priority, Created_Date, Created_By, Registration_Status, Registration_Status_Date, Registration_Status_Note, Interview_Note, Interview_Date, Interview_Result, Interview_By, Employment_Status, Employment_Status_Date, Employment_Class, Employment_Grade, Salary_Accepted, Contract_No, Subsection, Start_Date, End_Date, Date_in, Note_In,  Note_Out, Salary_Group, Payment_Method, Payment_Reference, Payment_Reference2, Payment_Reference_Branch, Payment_Reference3, Notes, NPWP,Recruitment_ID,Recruitment_Status) 

SELECT Task_ID, Task_ID,  Name, '', Birth_Place, Birth_Date, Gender, Marital_Status, PTKP_Status, kewarganegaraan, Last_Education, Last_Education_From, Education_Major, Religion, '', '', Status, Address, City, Address_Living, City_Living, Phone, Handphone, Email, Email, KTP, Foto, Position, Reason, Location, '', '', Department, Directorat, Priority, Created_Date, '$Saya', Registration_Status, Registration_Status_Date, Registration_Status_Note, Interview_Note, Interview_Date, Interview_Result, Interview_By, Employment_Status, NOW(), Employment_Class, '', Salary_Accepted, '', '', Start_Date, End_Date, NOW(), '', '', '', '', '', '', '', '', '', NPWP,ID,'FRESH' FROM recruitment WHERE ID=$_POST[ID]"));

$recr = DB::table("recruitment")->where("ID", $_POST["ID"])->first();
$existingUser = DB::table("users")->where("username", $recr->Task_ID)->first();
if ($existingUser) {
    DB::table("users")->where("username", $recr->Task_ID)->update([
        "Name"     => $recr->Name,
        "email"    => $recr->Email_Individual,
        "Location" => $recr->Location,
        "Department" => $recr->Department,
        "Directorat" => $recr->Directorat,
        "Position" => $recr->Position,
        "Status"   => "ACTIVE",
    ]);
} else {
    DB::table("users")->insert([
        "Emp_ID"   => $recr->Task_ID,
        "username" => $recr->Task_ID,
        "password" => bcrypt("12345678"),
        "Name"     => $recr->Name,
        "email"    => $recr->Email_Individual,
        "Location" => $recr->Location,
        "Department" => $recr->Department,
        "Directorat" => $recr->Directorat,
        "Position" => $recr->Position,
        "Status"   => "ACTIVE",
    ]);
}

echo "success";
?>