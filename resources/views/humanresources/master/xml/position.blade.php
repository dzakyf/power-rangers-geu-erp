<?php
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
    header("Content-type: application/xhtml+xml");
} else {
    header("Content-type: text/xml");
}
echo "<?xml version='1.0' encoding='UTF-8'?>\n";

$nomor = 1;

echo "<rows>";

$parents = DB::table('master_position')->pluck('Position', 'ID')->all();

$query = DB::table('master_position')->orderBy('Level', 'ASC')->orderBy('Position', 'ASC')->get();

foreach ($query as $fetch) {
    $parentName = !empty($fetch->Parent_Position_ID) ? ($parents[$fetch->Parent_Position_ID] ?? $fetch->Parent_Position_ID) : '';
    $status = $fetch->Status ?: 'ACTIVE';
    $posType = $fetch->Position_Type ?: 'STRUCTURAL';

    echo "<row id=\"$fetch->ID\">";
    echo "<cell>$nomor</cell>";
    echo "<cell>" . htmlspecialchars($fetch->Directorat ?: '') . "</cell>";
    echo "<cell>" . htmlspecialchars($fetch->Department ?: '') . "</cell>";
    echo "<cell>" . htmlspecialchars($fetch->Subsection ?: '') . "</cell>";
    echo "<cell>" . htmlspecialchars($fetch->Position ?: '') . "</cell>";
    echo "<cell>" . htmlspecialchars($fetch->Initial_Code ?: '') . "</cell>";
    echo "<cell>" . htmlspecialchars($fetch->Initial ?: '') . "</cell>";
    echo "<cell>" . (int) $fetch->Level . "</cell>";
    echo "<cell>" . htmlspecialchars($fetch->Parent_Position_ID ?: '') . "</cell>";
    echo "<cell>" . htmlspecialchars($fetch->Location ?: 'GEU-HO') . "</cell>";
    echo "<cell>" . htmlspecialchars($fetch->Company_ID ?: 'GEU') . "</cell>";
    echo "<cell>" . htmlspecialchars($posType) . "</cell>";
    echo "<cell>" . htmlspecialchars($status) . "</cell>";
    echo "</row>";

    $nomor++;
}

echo "</rows>";
