<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Opportunity;

$LSP = Auth::user()->Location ?? 'GEU-HO';
$Saya = Auth::user()->username ?? 'admin';

$Task_ID = 'BP/' . $LSP . '/' . date('Ymdhis') . '/' . substr(md5($Saya), 0, 3);

$input = request()->request->all();

// Bookkeeping fields that must never be persisted on the opportunity row.
$skip = ['ID', 'Task_ID', 'SubmitCustDetail', 'DelCustDetail', 'Created_Date', 'Location', '_token'];

// Only these are mandatory. The old blanket "any empty field" check produced
// false "tidak boleh kosong" prompts for optional/unchecked fields.
$required = ['Full_Name', 'Phone_Number', 'Source_Channel', 'Partnership_Interest', 'Capital_Estimation'];
foreach ($required as $field) {
    if (trim((string) ($input[$field] ?? '')) === '') {
        $label = str_replace('_', ' ', $field);
        echo "Field $label tidak boleh kosong ||" . $field;
        return;
    }
}

// Normalise the package selection. Current forms submit 1/2; the legacy
// internal form submits "booth"/"mobil" strings. Storing the raw string in
// the int column used to coerce it to 0 ("Opsi 0" in the lists).
$pi = strtolower(trim((string) $input['Partnership_Interest']));
if (in_array($pi, ['1', 'mobil', 'mobil listrik'], true)) {
    $hubType = 'mobil';
    $partnershipInterest = 1;
} elseif (in_array($pi, ['2', 'booth'], true)) {
    $hubType = 'booth';
    $partnershipInterest = 2;
} else {
    echo "Field Partnership Interest tidak valid ||Partnership_Interest";
    return;
}

$arr = [];
foreach ($input as $key => $value) {
    if (in_array($key, $skip, true)) {
        continue;
    }
    $arr[$key] = $value;
}

$arr['Partnership_Interest'] = $partnershipInterest;
$arr['Location'] = $LSP;
$arr['Created_Date'] = date('Y-m-d H:i:s');
$arr['Created_By'] = $Saya;
$arr['Task_ID'] = $Task_ID;
$arr['Classification'] = 'Fresh';
$arr['Hub_Type'] = $hubType;

$opportunity = Opportunity::create($arr);

$arr['Action_By'] = $Saya;
$arr['Action_Date'] = date('Y-m-d H:i:s');
$arr['Action_Status'] = 'CREATED_FROM_PARTNER';
$arr['Last_ID'] = $opportunity->ID;
DB::table('opportunity_log')->insert($arr);

echo "success";
