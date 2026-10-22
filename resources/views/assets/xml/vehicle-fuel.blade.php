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

if (!Schema::hasTable('vehicle_fuel_log')) {
    echo "<rows></rows>";
    exit;
}

$assetNo = trim($_GET['asset_no'] ?? '');
$month = trim($_GET['month'] ?? '');

$query = DB::table('vehicle_fuel_log')
    ->leftJoin('asset_list', 'vehicle_fuel_log.Asset_No', '=', 'asset_list.Asset_No')
    ->select(
        'vehicle_fuel_log.*',
        'asset_list.Unit_Name',
        'asset_list.Product_Brand',
        'asset_list.Model'
    )
    ->orderBy('vehicle_fuel_log.Fill_Date', 'DESC')
    ->orderBy('vehicle_fuel_log.id', 'DESC');

if ($assetNo !== '' && $assetNo !== 'ALL') {
    $query->where('vehicle_fuel_log.Asset_No', $assetNo);
}

if ($month !== '' && $month !== 'ALL') {
    $query->where('vehicle_fuel_log.Fill_Date', 'LIKE', $month . '%');
}

$rows = $query->get();
$nomor = 1;

echo "<rows>";

foreach ($rows as $r) {
    $unitName = trim(($r->Product_Brand ? $r->Product_Brand . ' ' : '') . ($r->Model ?: $r->Unit_Name));

    // Struk link
    $buktiHtml = "-";
    if ($r->Receipt_Photo) {
        $buktiHtml = "<![CDATA[<a href='" . asset($r->Receipt_Photo) . "' target='_blank' class='badge badge-info p-1'><i class='fa fa-receipt mr-1'></i>Struk</a>]]>";
    }

    // Efisiensi color badge
    $efisiensiBadge = "-";
    if ($r->KM_Per_Liter !== null && $r->KM_Per_Liter > 0) {
        $val = (float)$r->KM_Per_Liter;
        $color = $val >= 10 ? 'success' : ($val >= 7 ? 'warning' : 'danger');
        $efisiensiBadge = "<![CDATA[<span class='badge badge-$color px-2 py-1'>" . number_format($val, 2) . " KM/L</span>]]>";
    }

    // Action button
    $aksi = "<![CDATA[<button type='button' class='btn btn-outline-danger btn-sm py-0 px-2' title='Hapus' onclick='deleteFuel(" . $r->id . ", \"" . htmlspecialchars($r->Fuel_No, ENT_QUOTES) . "\")'><i class='fa fa-trash'></i></button>]]>";

    echo "<row id=\"$r->id\">";
    echo "<cell>$nomor</cell>";
    echo "<cell>" . htmlspecialchars($r->Fuel_No ?? '') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Fill_Date ? date('d/m/Y H:i', strtotime($r->Fill_Date)) : '') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Registration_No ?? $r->Asset_No) . "</cell>";
    echo "<cell>" . htmlspecialchars($unitName ?: $r->Asset_No) . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Driver_Name ?? '') . "</cell>";
    echo "<cell>" . number_format((float)($r->Current_KM ?? 0), 1, '.', ',') . "</cell>";
    echo "<cell>" . ($r->Distance_KM !== null ? number_format((float)$r->Distance_KM, 1, '.', ',') : '-') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Fuel_Type ?? '') . "</cell>";
    echo "<cell>" . number_format((float)($r->Liters ?? 0), 2, '.', ',') . "</cell>";
    echo "<cell>" . number_format((float)($r->Total_Cost ?? 0), 0, '.', ',') . "</cell>";
    echo "<cell>" . ($r->Cost_Per_Liter ? number_format((float)$r->Cost_Per_Liter, 0, '.', ',') : '-') . "</cell>";
    echo "<cell>" . $efisiensiBadge . "</cell>";
    echo "<cell>" . ($r->Cost_Per_KM ? number_format((float)$r->Cost_Per_KM, 0, '.', ',') : '-') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Gas_Station ?? '') . "</cell>";
    echo "<cell>" . htmlspecialchars($r->Payment_Method ?? '') . "</cell>";
    echo "<cell>" . $buktiHtml . "</cell>";
    echo "<cell>" . $aksi . "</cell>";
    echo "</row>";

    $nomor++;
}

echo "</rows>";
?>
