<?php
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
	header("Content-type: application/xhtml+xml");
} else {
	header("Content-type: text/xml");
}
echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";

$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

$allCols = recruitment_data_columns();
$defaultCols = recruitment_data_default_columns();
$activeCols = $defaultCols;
if (request()->filled('cols')) {
	$parsed = array_values(array_intersect(explode(',', (string) request('cols')), array_keys($allCols)));
	if (count($parsed)) {
		$activeCols = $parsed;
	}
}

echo "<rows>";

$Query = DB::table("recruitment")
	->where("Location", $LSP)
	->where("Status", "!=", "BUSINESS_REVIEW")
	->where("Status", "!=", "CLOSED")
	->where("Status", "!=", "CANCELED")
	->orderBy("ID", "DESC")
	->get();

foreach ($Query as $Fetch) {
	$rekusisi = "";
	$Requisition = null;
	$pecahin = explode(",", (string) $Fetch->Requisition_ID);
	
	foreach ($pecahin as $pecahreq) {
		$r = DB::table("requisition")->where("Task_ID", $pecahreq)->first();
		if ($r) {
			$Requisition = $r;
			$rekusisi .= $r->Job_Position . ",";
		}
	}
	$rekusisi = rtrim($rekusisi, ',');

	$hari = '';
	if ($Requisition && !empty($Requisition->End_Date)) {
		$buat = strtotime($Requisition->End_Date);
		$now = strtotime(date('Y-m-d'));
		$kurang = ($now - $buat) / 86400;
		$hari = round($kurang, 0);
	}

	$age = '';
	if (!empty($Fetch->Birth_Date) && $Fetch->Birth_Date !== '0000-00-00') {
		$timeBirth = strtotime($Fetch->Birth_Date);
		if ($timeBirth > 0) {
			$age = (string) floor((time() - $timeBirth) / 31556926);
		}
	}

	$cellData = [
		'KTP' => (string) ($Fetch->KTP ?? ''),
		'NIK' => (string) ($Fetch->NIK ?? ''),
		'Task_ID' => (string) ($Fetch->Task_ID ?? ''),
		'Name' => (string) ($Fetch->Name ?? ''),
		'Initial' => (string) ($Fetch->Initial ?? ''),
		'Gender' => (string) ($Fetch->Gender ?? ''),
		'Birth_Place' => (string) ($Fetch->Birth_Place ?? ''),
		'Birth_Date' => (string) ($Fetch->Birth_Date ?? ''),
		'Age' => $age,
		'Marital_Status' => (string) ($Fetch->Marital_Status ?? ''),
		'PTKP_Status' => (string) ($Fetch->PTKP_Status ?? ''),
		'kewarganegaraan' => (string) ($Fetch->kewarganegaraan ?? ''),
		'Religion' => (string) ($Fetch->Religion ?? ''),
		'Blood' => (string) ($Fetch->Blood ?? ''),
		'Blood_Rhesus' => (string) ($Fetch->Blood_Rhesus ?? ''),
		'Requisition' => (string) ($rekusisi !== '' ? $rekusisi : ($Fetch->Position ?? '')),
		'Position' => (string) ($Fetch->Position ?? ''),
		'Department' => (string) ($Fetch->Department ?? ''),
		'Directorat' => (string) ($Fetch->Directorat ?? ''),
		'Location' => (string) ($Fetch->Location ?? ''),
		'Priority' => (string) ($Fetch->Priority ?? ''),
		'Recruitment_Type' => (string) ($Fetch->Recruitment_Type ?? ''),
		'Last_Education' => (string) ($Fetch->Last_Education ?? ''),
		'Education_Major' => (string) ($Fetch->Education_Major ?? ''),
		'Last_Education_From' => (string) ($Fetch->Last_Education_From ?? ''),
		'Phone' => (string) ($Fetch->Phone ?? ''),
		'Handphone' => (string) ($Fetch->Handphone ?? ''),
		'Email' => (string) ($Fetch->Email ?? ''),
		'Email_Individual' => (string) ($Fetch->Email_Individual ?? ''),
		'Address' => (string) ($Fetch->Address ?? ''),
		'City' => (string) ($Fetch->City ?? ''),
		'Address_Living' => (string) ($Fetch->Address_Living ?? ''),
		'City_Living' => (string) ($Fetch->City_Living ?? ''),
		'NPWP' => (string) ($Fetch->NPWP ?? ''),
		'BPJS_Kesehatan' => (string) ($Fetch->BPJS_Kesehatan ?? ''),
		'BPJS_TK' => (string) ($Fetch->BPJS_TK ?? ''),
		'Salary_Accepted' => (string) ($Fetch->Salary_Accepted ?? ''),
		'Status' => (string) ($Fetch->Status ?? ''),
		'Document_Status' => (string) ($Fetch->Document_Status ?? ''),
		'Document_Date' => (string) ($Fetch->Document_Date ?? ''),
		'Psikotest_Status' => (string) ($Fetch->Psikotest_Status ?? ''),
		'Psikotest_Score' => (string) ($Fetch->Psikotest_Score ?? ''),
		'Psikotest_Date' => (string) ($Fetch->Psikotest_Date ?? ''),
		'Interview_By' => (string) ($Fetch->Interview_By ?? ''),
		'Interview_Status' => (string) ($Fetch->Interview_Status ?? ''),
		'Interview_Date' => (string) ($Fetch->Interview_Date ?? ''),
		'User_Interview_By' => (string) ($Fetch->User_Interview_By ?? ''),
		'User_Interview_Status' => (string) ($Fetch->User_Interview_Status ?? ''),
		'User_Interview_Date' => (string) ($Fetch->User_Interview_Date ?? ''),
		'Technical_Test_Status' => (string) ($Fetch->Technical_Test_Status ?? ''),
		'Technical_Score' => (string) ($Fetch->Technical_Score ?? ''),
		'Technical_Date' => (string) ($Fetch->Technical_Date ?? ''),
		'MCU_Status' => (string) ($Fetch->MCU_Status ?? ''),
		'MCU_Schedule' => (string) ($Fetch->MCU_Schedule ?? ''),
		'Offering_Status' => (string) ($Fetch->Offering_Status ?? ''),
		'PO_Desc' => (string) ($Requisition->PO_Desc ?? ''),
		'Remaining_Days' => (string) $hari,
		'Created_Date' => (string) ($Fetch->Created_Date ?? ''),
		'Created_By' => (string) ($Fetch->Created_By ?? ''),
	];

	echo "<row id=\"$Fetch->ID\">";
	echo "<cell>$nomor</cell>";
	foreach ($activeCols as $ck) {
		$val = $cellData[$ck] ?? '';
		echo "<cell>" . htmlspecialchars($val) . "</cell>";
	}
	echo "</row>";

	$nomor++;
}

echo "</rows>";
?>