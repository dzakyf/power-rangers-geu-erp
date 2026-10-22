<?php
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
    header("Content-type: application/xhtml+xml");
} else {
    header("Content-type: text/xml");
}
echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";

if (!Schema::hasTable('vehicle_logbook')) {
    echo "<rows></rows>";
    exit;
}

$assetNo = trim($_GET['asset_no'] ?? '');
$month = trim($_GET['month'] ?? '');
$status = trim($_GET['status'] ?? '');

$query = DB::table('vehicle_logbook')
    ->leftJoin('asset_list', 'vehicle_logbook.Asset_No', '=', 'asset_list.Asset_No')
    ->select(
        'vehicle_logbook.*',
        'asset_list.Unit_Name',
        'asset_list.Product_Brand',
        'asset_list.Model'
    )
    ->orderBy('vehicle_logbook.id', 'DESC');

if ($assetNo !== '' && $assetNo !== 'ALL') {
    $query->where('vehicle_logbook.Asset_No', $assetNo);
}

if ($month !== '' && $month !== 'ALL') {
    $query->where('vehicle_logbook.Start_Date', 'LIKE', $month . '%');
}

if ($status !== '' && $status !== 'ALL') {
    $query->where('vehicle_logbook.Status', $status);
}

$rows = $query->get();
$nomor = 1;

echo "<rows>";

foreach ($rows as $r) {
    $unitName = trim(($r->Product_Brand ? $r->Product_Brand . ' ' : '') . ($r->Model ?: $r->Unit_Name));
    
    // Status Badge
    if ($r->Status === 'ON_GOING') {
        $statusBadge = "<![CDATA[<span class='badge badge-warning text-dark font-weight-bold px-2 py-1'><i class='fa fa-clock mr-1'></i>Berjalan</span>]]>";
    } elseif ($r->Status === 'COMPLETED') {
        $statusBadge = "<![CDATA[<span class='badge badge-success font-weight-bold px-2 py-1'><i class='fa fa-check mr-1'></i>Selesai</span>]]>";
    } else {
        $statusBadge = "<![CDATA[<span class='badge badge-secondary font-weight-bold px-2 py-1'>Batal</span>]]>";
    }

    // Action button
    $aksi = "<![CDATA[<div class='btn-group btn-group-sm'>";
    if ($r->Status === 'ON_GOING') {
        $aksi .= "<button type='button' class='btn btn-success btn-sm py-0 px-2' title='Selesaikan Perjalanan' onclick='finishLogbook(" . $r->id . ", \"" . htmlspecialchars($r->Log_No, ENT_QUOTES) . "\", \"" . htmlspecialchars($r->Registration_No, ENT_QUOTES) . "\", " . (float)$r->Start_KM . ")'><i class='fa fa-check-circle'></i> Check-In</button>";
    }
    $aksi .= "<button type='button' class='btn btn-outline-danger btn-sm py-0 px-2' title='Hapus' onclick='deleteLogbook(" . $r->id . ", \"" . htmlspecialchars($r->Log_No, ENT_QUOTES) . "\")'><i class='fa fa-trash'></i></button>";
    $aksi .= "</div>]]>";

    // Foto Odometer link
    $fotoLink = "-";
    if ($r->Photo_Odometer_Start || $r->Photo_Odometer_End) {
        $fotoHtml = "";
        if ($r->Photo_Odometer_Start) {
            $fotoHtml .= "<a href='" . asset($r->Photo_Odometer_Start) . "' target='_blank' class='badge badge-info mr-1'><i class='fa fa-image'></i> Awal</a>";
        }
        if ($r->Photo_Odometer_End) {
            $fotoHtml .= "<a href='" . asset($r->Photo_Odometer_End) . "' target='_blank' class='badge badge-primary'><i class='fa fa-image'></i> Akhir</a>";
        }
        $fotoLink = "<![CDATA[" . $fotoHtml . "]]>";
    }

    echo "<row id=\"$r->id\">";
    echo "<cell>$nomor</cell>";
    echo "<cell>" . htmlspecialchars($r->Log_No ?? '') . "</cell>";
    echo "<cell>" . $statusBadge . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Registration_No ?? $r->Asset_No) . "</cell>";
    echo "<cell>" . htmlspecialchars($unitName ?: $r->Asset_No) . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Driver_Name ?? '') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Department ?? '') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Start_Date ? date('d/m/Y H:i', strtotime($r->Start_Date)) : '') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->End_Date ? date('d/m/Y H:i', strtotime($r->End_Date)) : '-') . "</cell>";
    echo "<cell>" . number_format((float)($r->Start_KM ?? 0), 1, '.', ',') . "</cell>";
    echo "<cell>" . ($r->End_KM !== null ? number_format((float)$r->End_KM, 1, '.', ',') : '-') . "</cell>";
    echo "<cell>" . ($r->Total_KM !== null ? number_format((float)$r->Total_KM, 1, '.', ',') : '-') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Destination ?? '') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Purpose ?? '') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Vehicle_Condition_In ?: ($r->Vehicle_Condition_Out ?: '-')) . "</cell>";
    echo "<cell>" . $fotoLink . "</cell>";
    echo "<cell>" . $aksi . "</cell>";
    echo "</row>";

    $nomor++;
}

echo "</rows>";
?>
