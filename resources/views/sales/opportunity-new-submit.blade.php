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
$Task_ID = 'BP/' . $LSP . '/' . date('Ymdhis') . '/' . substr(md5($Saya), 0, 3);
$arr = [];

// $_POST['Estimated_Rev'] = str_replace(",","",$_POST['Estimated_Rev']);
foreach ($_POST as $key => $value) {

	if ($key != 'ID' && $key != 'Task_ID' && $key != 'SubmitCustDetail' && $key != 'DelCustDetail' && $key != 'Created_Date' && $key != 'Created_Date' && $key != 'Location' && $key != 'ID') {
		$ars .= "$key = '$value',";
		$ax .= "$key,";
		$isi .= "'$value',";
		$arr[$key] = $value;
		if ($value  == '') {
			$keys = str_replace("_", " ", $key);
			echo "Field $keys tidak boleh kosong ||" . $key;
			exit;
		}
	}
}

if (!isset($arr['Partnership_Interest'])) {
	echo "Field Partnership Interest tidak boleh kosong ||Partnership_Interest";
	exit;
}

if (!isset($arr['Capital_Estimation'])) {
	echo "Field Capital Estimation tidak boleh kosong ||Capital_Estimation";
	exit;
}

$ax = substr($ax, 0, -1);
$ars = substr($ars, 0, -1);
$isi = substr($isi, 0, -1);


$arr['Location'] = $LSP;
$arr['Created_Date'] = date('Y-m-d H:i:s');
$arr['Created_By'] = $Saya;
$arr['Task_ID'] = $Task_ID;
$arr['Classification'] = 'Fresh';
$arr['Hub_Type'] = $arr['Partnership_Interest'] == 1 ? "mobil" : "booth";
// $kueri = "INSERT INTO opportunity($ax,Location,Created_Date,Created_By,Task_ID,Classification) values ($isi,'$LSP',NOW(),'$Saya','$Task_ID','Fresh')";
// DB::insert(DB::raw($kueri));

$opportunity = Opportunity::create($arr);

$arr['Action_By'] = $Saya;
$arr['Action_Date'] = date('Y-m-d H:i:s');
$arr['Action_Status'] = 'CREATED_FROM_PARTNER';
$arr['Last_ID'] = $opportunity->ID;
DB::table('opportunity_log')->insert($arr);

echo "success";
