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
$arr = [];

foreach($_POST as $key=>$value)
{
	if ($key != 'ID' && $key != 'SubmitBtn' && $key != 'Job_Position' && $key != '_token'){
		$ars 	.="$key,";
		$vals	.="'$_POST[$key]',";
		$MX .="$key = '$_POST[$key]',";
		if ($key == 'Start_Date' || $key == 'End_Date') {
			$arr[$key] = date('Y-m-d', strtotime($value));
		} else {
			$arr[$key] = $value;
		}
	}
}

$vals = substr($vals,0,-1);
$ars = substr($ars,0,-1);
$MX = substr($MX,0,-1);
$jobPosition = DB::table('master_position')->where("ID", $_POST['Job_Position'])->first();

$arr['Created_By'] = $Saya;
$arr['Location'] = $LSP;
$arr['Created_Date'] = now();
$arr['Status'] = "CREATED";
$arr['Requisition_Type'] = "NON_PO";

$arr['Job_Position'] = $jobPosition->Position;
$arr['Department'] = $jobPosition->Department;


$Taskid = 'REQ/'.$LSP.'/'.date('Ymdhis').md5($Saya,0,3);
$arr['Task_ID'] = $Taskid;
$reqId = DB::table('requisition')->insertGetId($arr);

$arr["Action_By"] = $Saya;
$arr['Action_Date'] = now();
$arr['Action_Status'] = "CREATE FROM REQUISITION";
$arr['Last_ID'] = $reqId;

DB::table('requisition_log')->insert($arr);

$master = DB::table('master_hr_approval')
	->where("Level", 1)
	->where("Location", $LSP)
	->where('Department', Auth::user()->Department)
	->first();

if ($master) {
	DB::table('requisition_approval')->insert([
		"Location" => $LSP,
		"Task_ID" => $Taskid,
		'Level' => 1,
		"Approved_By" => $master->username,
		"Approved_Status" => 0,
		"Approved_Date" => null,
		"Approved_Note" => null
	]);
}

$master = DB::table('master_hr_approval')
	->whereIn("Level", [2])
	->where("Location", $LSP)
	->first();

if ($master) {
	DB::table('requisition_approval')->insert([
		"Location" => $LSP,
		"Task_ID" => $Taskid,
		'Level' => 2,
		"Approved_By" => $master->username,
		"Approved_Status" => 0,
		"Approved_Date" => null,
		"Approved_Note" => null
	]);
}

if (Str::contains($jobPosition->Level,[1,2,3,4,5,6])) {
	$master = DB::table('master_hr_approval')
		->whereIn("Level", [3,4])
		->where("Location", $LSP)
		->first();
	
	if ($master) {
		DB::table('requisition_approval')->insert([
			"Location" => $LSP,
			"Task_ID" => $Taskid,
			'Level' => 2,
			"Approved_By" => $master->username,
			"Approved_Status" => 0,
			"Approved_Date" => null,
			"Approved_Note" => null
		]);
	}

}


// $kueri = "INSERT INTO recruitment($ars,Created_By,Location,Task_ID,Created_Date) values ($vals,'$Saya','$LSP','$Taskid',NOW())";
// DB::insert(DB::raw($kueri));
// DB::insert(DB::raw("INSERT INTO recruitment_detail(Task_ID) VALUES ('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_document(Task_ID) VALUES ('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_akademic(Task_ID,Education_Level) VALUES ('$subs','SENIOR/VOCATIONAL SCHOOL'),('$subs','DIPLOMA'),('$subs','BACHELOR'),('$subs','MAGISTER')"));
// DB::insert(DB::raw("INSERT INTO recruitment_language(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_organization(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_driving(Task_ID,Description) VALUES ('$subs','SIM A'),('$subs','SIM B'),('$subs','SIM B1'),('$subs','SIM B1 UMUM'),('$sub','SIM C')"));
// DB::insert(DB::raw("INSERT INTO recruitment_health(Task_ID,Description) VALUES ('$subs','Stay on Hospital'),('$subs','Outside the hospital more than 7 days'),('$subs','Penyakit lain yang sering mengganggu'),('$subs','Abnormality')"));
// DB::insert(DB::raw("INSERT INTO recruitment_experiences(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_informal(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_awards(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_other_skill(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_contact_point(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_family(Task_ID) VALUES
// 		('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs')"));
// DB::insert(DB::raw("INSERT INTO recruitment_family_big(Task_ID) VALUES
// 		('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs')"));


echo "success";
?>