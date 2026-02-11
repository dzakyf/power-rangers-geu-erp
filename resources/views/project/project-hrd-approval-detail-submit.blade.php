	<?php
error_reporting(0);
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
		
		if ($key !='IssueListsDetaildel' && $key !='SubmitCustDetail' && $key !='DelCustDetail'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);

DB::update(DB::raw("UPDATE recruitment set Status='CLOSED' where ID=$_POST[ID]"));
DB::insert(DB::raw("INSERT INTO employment(Task_ID, NIK, NIK_KSPS, NIK_Client, Attendant_No, Name, Initial, Birth_Place, Birth_Date, Gender, Marital_Status, PTKP_Status, kewarganegaraan, Last_Education, Last_Education_From, Education_Major, Religion, Blood, Blood_Rhesus, Status, Address, City, Address_Living, City_Living, Phone, Handphone, Email, Email_Individual, KTP, Foto, Position, Reason, Location, Alocation, Work_Area, Department, Directorat, Priority, Created_Date, Created_By, Registration_Status, Registration_Status_Date, Registration_Status_Note, Interview_Note, Interview_Date, Interview_Result, Interview_By, Employment_Status, Employment_Status_Date, Employment_Class, Employment_Grade, Salary_Accepted, Contract_No, Subsection, Start_Date, End_Date, Date_in, Note_In, Date_Out, Note_Out, Salary_Group, Payment_Method, Payment_Reference, Payment_Reference2, Payment_Reference_Branch, Payment_Reference3, Notes, NPWP, PPH_Allowance, Sisa_Cuti, Category, Prorate_Salary, Prorate_Salary_Date, Prorate_Resigned, Prorate_Resigned_Date, Resigned_Date, Resigned_Note, Recruitment_ID, Recruitment_Status, Address_Living_City, bpjs_tk, bpjs_kes, Conversion_Date, Mutation_File, Reference_No) 


SELECT Task_ID, NIK, NIK, NIK, '00000', Name, Name, Birth_Place, Birth_Date, Gender, Marital_Status, PTKP_Status, kewarganegaraan, Last_Education, Last_Education_From, Education_Major, Religion, '', '', 'ACTIVE', Address, City, Address_Living, City_Living, Phone, Handphone, Email, Email,KTP, Foto, Position, Reason, Location, '', '', '', '', Priority, Created_Date, Created_By, Registration_Status, Registration_Status_Date, Registration_Status_Note, Interview_Note, Interview_Date, Interview_Result, Interview_By, 'ACTIVE', NOW(), '','', Salary_Accepted, '', '', Start_Date, End_Date, NOW(), 'ACCEPTED', '','', Requisition_ID, '', '','','','', Notes, NPWP, 'YES', 0, 'LA', 1, '',0,'','','', Task_ID, 'CLOSED',City_Living, '', '',NOW(), '', ''

FROM recruitment 
WHERE ID=$_POST[ID]"));
DB::insert(DB::raw("INSERT INTO employment_akademic(Task_ID, Education_Level, College, Education_Major, Location, Certified_Year, GPA) 
select Task_ID, Education_Level, College, Education_Major, Location, Certified_Year, GPA from recruitment_akademic where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_awards(Task_ID, MonthYear, Place, Detail, Title) select Task_ID, MonthYear, Place, Detail, Title from recruitment_awards 
where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_contact_point(Task_ID, Name, Employee_Department, Position, Contact_No, Email, Phone_No) 
SELECT Task_ID, Name, Employee_Department, Position, Contact_No, Email, Phone_No from recruitment_contact_point where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_detail(Task_ID, Emergency_Contact_No, Emergency_Contact_Name, Emergency_Relation, Emergency_Address, Family_Big_Card_No, Family_Card_No, Location, Last_Experience, Worked_Pandu_Start, Worked_Pandu_End, Worked_Pandu_Position, Height, Weight, Longtime_Desc, Describe_Self, Left_Job, Previous_Boss, Describe_Know, Describe_Improve, Describe_Looking, Describe_Interest, Describe_Long, Describe_Target, Salary_Expectation, Facility_Expectation, Free_Time, Describe_Strong, Describe_Weak, Questions_Us, Son_Of, Worked_Pandu, Glasses, Smoker, Blood, Blood_Rhesus, Longtime, Environment, Overtime, Holiday, Shift_Work, Out_Town, Computer)  select Task_ID, Emergency_Contact_No, Emergency_Contact_Name, Emergency_Relation, Emergency_Address, Family_Big_Card_No, Family_Card_No, Location, Last_Experience, Worked_Pandu_Start, Worked_Pandu_End, Worked_Pandu_Position, Height, Weight, Longtime_Desc, Describe_Self, Left_Job, Previous_Boss, Describe_Know, Describe_Improve, Describe_Looking, Describe_Interest, Describe_Long, Describe_Target, Salary_Expectation, Facility_Expectation, Free_Time, Describe_Strong, Describe_Weak, Questions_Us, Son_Of, Worked_Pandu, Glasses, Smoker, Blood, Blood_Rhesus, Longtime, Environment, Overtime, Holiday, Shift_Work, Out_Town, Computer from recruitment_detail where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("
INSERT INTO employment_document(Task_ID, Foto_Desc, Foto, CV_Desc, CV, Ijazah_Desc, Ijazah, Sertifikat_Desc, Sertifikat, KTP_Desc, KTP, NPWP_Desc, NPWP, KK_Desc, KK, Akta_Desc, Akta, SIM_Desc, SIM, Created_By, Last_Updated) select Task_ID, Foto_Desc, Foto, CV_Desc, CV, Ijazah_Desc, Ijazah, Sertifikat_Desc, Sertifikat, KTP_Desc, KTP, NPWP_Desc, NPWP, KK_Desc, KK, Akta_Desc, Akta, SIM_Desc, SIM, Created_By, Last_Updated from recruitment_document where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_driving(Task_ID, Description, Tipe, Note) select Task_ID, Description, Tipe, Note from recruitment_driving where  Task_ID='$_POST[refid]'"));


DB::insert(DB::raw("
INSERT INTO employment_experiences(Task_ID, Start_Date, End_Date, Position, Company_Name, Last_Salary, Reason) select Task_ID, Start_Date, End_Date, Position, Company_Name, Last_Salary, Reason from recruitment_experiences where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_family(Task_ID, Family_Status, Name, Gender, Birth_Date, Last_Education, Occupation)
select Task_ID, Family_Status, Name, Gender, Birth_Date, Last_Education, Occupation from recruitment_family where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_family_big(Task_ID, Family_Status, Name, Gender, Birth_Date, Last_Education, Occupation) select Task_ID, Family_Status, Name, Gender, Birth_Date, Last_Education, Occupation from recruitment_family_big 
where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_health(Task_ID, Description, Disease, Year_Health, Days) select Task_ID, Description, Disease, Year_Health, Days from recruitment_health where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_informal(Task_ID, Training_Company, Location, Year_Training, Note) select Task_ID, Training_Company, Location, Year_Training, Note  from recruitment_informal  where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_insurances(Task_ID, Employee_Name, On_Behalf, On_Behalf_Status, Insurances, Card_No, Polis_No, Polis_Type, Polis_Benefit, Polis_Reimbursement, Polis_Status, Date_Start, Date_End, Date_Submission, Employee_Collateral, In_Salary, Salary_Component, Created_Date, Created_By) select Task_ID, Employee_Name, On_Behalf, On_Behalf_Status, Insurances, Card_No, Polis_No, Polis_Type, Polis_Benefit, Polis_Reimbursement, Polis_Status, Date_Start, Date_End, Date_Submission, Employee_Collateral, In_Salary, Salary_Component, Created_Date, Created_By from recruitment_insurances  where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_language(Task_ID, Language, Oral, Writing, Additional, Note) select Task_ID, Language, Oral, Writing, Additional, Note from recruitment_language  where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_organization(Task_ID, Organization, Position, Location, At_Year, Note)  select Task_ID, Organization, Position, Location, At_Year, Note from recruitment_organization  where Task_ID='$_POST[refid]'"));
DB::insert(DB::raw("INSERT INTO employment_other_skill(Task_ID, Category, Subject, Year_Skill, Competences) select Task_ID, Category, Subject, Year_Skill, Competences  from recruitment_other_skill  where Task_ID='$_POST[refid]'"));



echo "success";

?>