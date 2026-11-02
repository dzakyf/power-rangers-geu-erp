<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

$LSP = Auth::user()->Location ?? 'GEU-HO';
$Saya = Auth::user()->username ?? 'guest';

$selectedPosition = $_POST['Position'] ?? null;
$requisitionId = $_POST['Requisition_ID'] ?? null;

// Jika Requisition_ID belum terisi atau Position dipilih dari master_position,
// cari requisition aktif (APPROVED) dengan Job_Position yang cocok
if (!empty($selectedPosition)) {
    $activeReq = DB::table('requisition')
        ->where('Status', 'APPROVED')
        ->where('Job_Position', $selectedPosition)
        ->orderBy('ID', 'DESC')
        ->first();

    if ($activeReq) {
        $requisitionId = $activeReq->Task_ID;
        $_POST['Requisition_ID'] = $requisitionId;
    }
} elseif (!empty($requisitionId)) {
    // Jika Requisition_ID dikirim, pastikan Position terisi nama jabatannya
    $req = DB::table('requisition')->where('Task_ID', $requisitionId)->first();
    if ($req && !empty($req->Job_Position)) {
        $_POST['Position'] = $req->Job_Position;
    }
}

$Taskid = 'RECR/' . $LSP . '/' . date('Ymdhis') . substr(md5($Saya . microtime()), 0, 4);
$subs = $Taskid;

$allowedCols = Schema::getColumnListing('recruitment');
$insertPayload = [
    'Created_By'   => $Saya,
    'Location'     => $LSP,
    'Task_ID'      => $Taskid,
    'Created_Date' => date('Y-m-d H:i:s'),
];

foreach($_POST as $key => $value) {
    if ($key != 'ID' && $key != 'SubmitCustDetail' && $key != '_token') {
        $insertPayload[$key] = $value;
    }
}

if (!empty($allowedCols)) {
    $insertPayload = array_intersect_key($insertPayload, array_flip($allowedCols));
}

DB::table('recruitment')->insert($insertPayload);

try {
    if (Schema::hasTable('recruitment_detail')) {
        DB::table('recruitment_detail')->insert(['Task_ID' => $subs]);
    }
    if (Schema::hasTable('recruitment_document')) {
        DB::table('recruitment_document')->insert(['Task_ID' => $subs]);
    }
    if (Schema::hasTable('recruitment_akademic')) {
        DB::insert(DB::raw("INSERT INTO recruitment_akademic(Task_ID, Education_Level) VALUES ('$subs','SENIOR/VOCATIONAL SCHOOL'),('$subs','DIPLOMA'),('$subs','BACHELOR'),('$subs','MAGISTER')"));
    }
    if (Schema::hasTable('recruitment_language')) {
        DB::insert(DB::raw("INSERT INTO recruitment_language(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
    }
    if (Schema::hasTable('recruitment_organization')) {
        DB::insert(DB::raw("INSERT INTO recruitment_organization(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
    }
    if (Schema::hasTable('recruitment_driving')) {
        DB::insert(DB::raw("INSERT INTO recruitment_driving(Task_ID, Description) VALUES ('$subs','SIM A'),('$subs','SIM B'),('$subs','SIM B1'),('$subs','SIM B1 UMUM'),('$subs','SIM C')"));
    }
    if (Schema::hasTable('recruitment_health')) {
        DB::insert(DB::raw("INSERT INTO recruitment_health(Task_ID, Description) VALUES ('$subs','Stay on Hospital'),('$subs','Outside the hospital more than 7 days'),('$subs','Penyakit lain yang sering mengganggu'),('$subs','Abnormality')"));
    }
    if (Schema::hasTable('recruitment_experiences')) {
        DB::insert(DB::raw("INSERT INTO recruitment_experiences(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
    }
    if (Schema::hasTable('recruitment_informal')) {
        DB::insert(DB::raw("INSERT INTO recruitment_informal(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
    }
    if (Schema::hasTable('recruitment_awards')) {
        DB::insert(DB::raw("INSERT INTO recruitment_awards(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
    }
    if (Schema::hasTable('recruitment_other_skill')) {
        DB::insert(DB::raw("INSERT INTO recruitment_other_skill(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
    }
    if (Schema::hasTable('recruitment_contact_point')) {
        DB::insert(DB::raw("INSERT INTO recruitment_contact_point(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs')"));
    }
    if (Schema::hasTable('recruitment_family')) {
        DB::insert(DB::raw("INSERT INTO recruitment_family(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs')"));
    }
    if (Schema::hasTable('recruitment_family_big')) {
        DB::insert(DB::raw("INSERT INTO recruitment_family_big(Task_ID) VALUES ('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs'),('$subs')"));
    }
} catch (\Exception $e) {
    // abaikan jika tabel pendukung berbeda
}

echo "success";
?>
