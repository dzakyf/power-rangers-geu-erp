<?php
error_reporting(0);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';

function sanitizeVal($val) {
    return trim((string) $val);
}

function updatekan($rowId, $param) {
    $directorat    = sanitizeVal($param[$rowId."_c1"] ?? '');
    $department    = sanitizeVal($param[$rowId."_c2"] ?? '');
    $subsection    = sanitizeVal($param[$rowId."_c3"] ?? '');
    $position      = sanitizeVal($param[$rowId."_c4"] ?? '');
    $initialCode   = sanitizeVal($param[$rowId."_c5"] ?? '');
    $initial       = sanitizeVal($param[$rowId."_c6"] ?? '');
    $level         = (int) ($param[$rowId."_c7"] ?? 1);
    $parentId      = sanitizeVal($param[$rowId."_c8"] ?? '');
    $location      = sanitizeVal($param[$rowId."_c9"] ?? 'GEU-HO');
    $companyId     = sanitizeVal($param[$rowId."_c10"] ?? 'GEU');
    $positionType  = sanitizeVal($param[$rowId."_c11"] ?? 'STRUCTURAL');
    $status        = strtoupper(sanitizeVal($param[$rowId."_c12"] ?? 'ACTIVE'));

    $user = Auth::user()->username ?? 'system';

    $updateData = [
        'Directorat'         => $directorat,
        'Department'         => $department,
        'Subsection'         => $subsection,
        'Position'           => $position,
        'Initial_Code'       => $initialCode,
        'Initial'            => $initial,
        'Level'              => $level,
        'Parent_Position_ID' => $parentId !== '' ? (int) $parentId : null,
        'Location'           => $location ?: 'GEU-HO',
        'Company_ID'         => $companyId ?: 'GEU',
        'Position_Type'      => $positionType ?: 'STRUCTURAL',
        'Status'             => $status ?: 'ACTIVE',
        'Updated_By'         => $user,
        'Updated_Date'       => now(),
    ];

    DB::table('master_position')->where('ID', (int) $rowId)->update($updateData);

    return "update";
}

function deletekan($rowId, $param) {
    DB::table('master_position')->where('ID', (int) $rowId)->delete();
    return "delete";
}

function insertkan($rowId, $param) {
    $directorat    = sanitizeVal($param[$rowId."_c1"] ?? '');
    $department    = sanitizeVal($param[$rowId."_c2"] ?? '');
    $subsection    = sanitizeVal($param[$rowId."_c3"] ?? '');
    $position      = sanitizeVal($param[$rowId."_c4"] ?? '');
    $initialCode   = sanitizeVal($param[$rowId."_c5"] ?? '');
    $initial       = sanitizeVal($param[$rowId."_c6"] ?? '');
    $level         = (int) ($param[$rowId."_c7"] ?? 1);
    $parentId      = sanitizeVal($param[$rowId."_c8"] ?? '');
    $location      = sanitizeVal($param[$rowId."_c9"] ?? 'GEU-HO');
    $companyId     = sanitizeVal($param[$rowId."_c10"] ?? 'GEU');
    $positionType  = sanitizeVal($param[$rowId."_c11"] ?? 'STRUCTURAL');
    $status        = strtoupper(sanitizeVal($param[$rowId."_c12"] ?? 'ACTIVE'));

    $user = Auth::user()->username ?? 'system';

    if ($position === '' && $initial === '') {
        return "cannot_empty";
    }

    $insertData = [
        'Directorat'         => $directorat,
        'Department'         => $department,
        'Subsection'         => $subsection,
        'Position'           => $position,
        'Initial_Code'       => $initialCode,
        'Initial'            => $initial,
        'Level'              => $level,
        'Parent_Position_ID' => $parentId !== '' ? (int) $parentId : null,
        'Location'           => $location ?: 'GEU-HO',
        'Company_ID'         => $companyId ?: 'GEU',
        'Position_Type'      => $positionType ?: 'STRUCTURAL',
        'Status'             => $status ?: 'ACTIVE',
        'Created_By'         => $user,
        'Created_Date'       => now(),
    ];

    DB::table('master_position')->insert($insertData);

    return "insert";
}

echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$ids = explode("&", $request_body);
$param = [];
foreach ($ids as $datax) {
    $idenya = explode("=", $datax);
    if (isset($idenya[0]) && isset($idenya[1])) {
        $param[$idenya[0]] = $idenya[1];
    }
}

if (!empty($param["ids"])) {
    $checkids = explode(",", $param["ids"]);
    for ($i = 0; $i < sizeof($checkids); $i++) {
        $rowId = $checkids[$i];
        $newId = $rowId;
        $mode = $param[$rowId . "_!nativeeditor_status"] ?? '';
        $action = 'error';
        switch ($mode) {
            case "updated":
                $action = updatekan($rowId, $param);
                break;
            case "deleted":
                $action = deletekan($rowId, $param);
                break;
            case "inserted":
                $action = insertkan($rowId, $param);
                break;
        }
        echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
    }
}

echo "</data>";
