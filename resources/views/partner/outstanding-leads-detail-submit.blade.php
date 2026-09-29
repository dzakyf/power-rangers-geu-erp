<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Opportunity;
	
	$LSP = Auth::user()->Location ?? 'GEU-HO';
	$Saya =  Auth::user()->username ?? 'admin';
	
$ars = "";
$ax = "";
$isi = "";
$arr = [];

// $_POST['Estimated_Rev'] = str_replace(",","",$_POST['Estimated_Rev']);
foreach($_POST as $key=>$value)
{
		
		if ($key !='ID' && $key != 'SubmitCustDetail' && $key != 'DelCustDetail' && $key != 'Created_Date' && $key != 'Created_Date' && $key != 'Location'&& $key != 'ID'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
			if ($value  == 'null') {
				$value = '';
			}
			$arr[$key] = $value;
		}
}

$ax = substr($ax,0,-1);
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

// $kueri = "INSERT INTO opportunity($ax,Location,Created_Date,Created_By,Task_ID,Classification) values ($isi,'$LSP',NOW(),'$Saya','$Task_ID','Fresh')";
// DB::insert(DB::raw($kueri));

$leadService = app(\App\Services\LeadsService::class);
$leadStatus  = $_POST['Status'] ?? null;

$funnelError = $leadService->validateStage(
    $leadStatus,
    $_POST['Response'] ?? null,
    $_POST['Cancel_Stage'] ?? null,
    $_POST['Reason'] ?? null
);
if ($funnelError) {
    http_response_code(422);
    echo $funnelError;
    exit;
}

$arr['Classification'] = $leadService->deriveClassification($leadStatus, $_POST['Cancel_Stage'] ?? null);
$arr['Qualification']  = $leadService->deriveQualification($leadStatus, $_POST['Reason'] ?? null);
$arr['Cancel_Stage']   = ($leadStatus === 'Cancel') ? ($_POST['Cancel_Stage'] ?? null) : null;

$opportunity = Opportunity::where('ID', $_POST['ID'])->first();

Opportunity::where('ID', $_POST['ID'])->update($arr);

// Masukin ke log pake loop
// $arrLog = [];
// foreach ($opportunity as $key => $value) {
// 	$arrLog[$key != 'ID' ? $key : 'Last_ID'] = $value;
// }

// Masukin ke log pake manual
$arrLog = json_decode(json_encode($opportunity), true);
unset($arrLog['ID']);


$arrLog['Action_By'] = $Saya;
$arrLog['Action_Date'] = date('Y-m-d H:i:s');
$arrLog['Action_Status'] = 'UPDATED_DATA';
$arrLog['Last_ID'] = $_POST['ID'];
DB::table('opportunity_log')->insert($arrLog);

if ($_POST['Status'] == "Deal") {
	// Buat blasting ke partner
	// $username = str_replace(' ','_', strtolower($opportunity->Full_Name));
	// $cekUser = DB::table('users')->get();
	// foreach ($cekUser as $key => $value) {
	// 	if ($value->username == $username) {
	// 		$username = $username . rand(100, 999);
	// 	}
	// }
	// $pass = 'geupartner123';
	// DB::table('users')->insert([
	// 	'username' => $username,
	// 	'password' => bcrypt($pass),
	// 	'Emp_ID' => $opportunity->Task_ID,
	// 	'Priv_ID' => "PARTNER",
	// 	'Location' => $opportunity->Location,
	// 	'Name' => $opportunity->Full_Name,
	// 	'Contact' => $opportunity->Phone_Number,
	// 	'created_at' => date('Y-m-d H:i:s'),
	// 	'Area' => 'JABODETABEK',
	// 	'Status' => 'ACTIVE',
	// 	'Department' => 'PARTNER',
	// 	'Company_ID' => 'GEU',
	// ]);
}

echo "success";
?>