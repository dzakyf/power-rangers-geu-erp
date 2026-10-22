<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

header('Content-Type: application/json; charset=utf-8');

// Ensure tables exist
if (!Schema::hasTable('vehicle_logbook') || !Schema::hasTable('vehicle_fuel_log')) {
    echo json_encode(['status' => 'error', 'message' => 'Tabel vehicle operations belum siap']);
    exit;
}

$action = $_GET['action'] ?? '';
$assetNo = trim($_GET['asset_no'] ?? '');
$filterMonth = trim($_GET['month'] ?? date('Y-m'));

// 1. DAFTAR KENDARAAN (dari asset_list)
if ($action === 'vehicles') {
    $vehicles = DB::table('asset_list')
        ->where(function ($q) {
            $q->whereNotNull('Jenis_Kendaraan')->where('Jenis_Kendaraan', '!=', '')
              ->orWhere('Asset_Sub_Category', 'VEHICLE');
        })
        ->select(
            'Asset_No',
            'Registration_No',
            'Unit_Name',
            'Product_Brand',
            'Model',
            'Jenis_Kendaraan',
            'Source_Production as Bahan_Bakar',
            'KM_Jadwal_PM',
            'Asset_Location_Department',
            'User_Responsible',
            'Status'
        )
        ->orderBy('Registration_No', 'ASC')
        ->get();

    // Tambahkan status perjalanan aktif (ON_GOING) dan KM terakhir riil
    $result = [];
    foreach ($vehicles as $v) {
        $activeTrip = DB::table('vehicle_logbook')
            ->where('Asset_No', $v->Asset_No)
            ->where('Status', 'ON_GOING')
            ->orderBy('id', 'DESC')
            ->first();

        // Cari KM tertinggi dari logbook, fuel log, atau master
        $lastLogKM = DB::table('vehicle_logbook')
            ->where('Asset_No', $v->Asset_No)
            ->whereNotNull('End_KM')
            ->max('End_KM');
        $lastFuelKM = DB::table('vehicle_fuel_log')
            ->where('Asset_No', $v->Asset_No)
            ->max('Current_KM');

        $latestKM = max((float)($v->KM_Jadwal_PM ?? 0), (float)$lastLogKM, (float)$lastFuelKM);

        $result[] = [
            'Asset_No' => $v->Asset_No,
            'Registration_No' => $v->Registration_No ?: $v->Asset_No,
            'Unit_Name' => $v->Unit_Name ?: ($v->Product_Brand . ' ' . $v->Model),
            'Product_Brand' => $v->Product_Brand,
            'Model' => $v->Model,
            'Jenis_Kendaraan' => $v->Jenis_Kendaraan,
            'Bahan_Bakar' => $v->Bahan_Bakar ?: 'Pertalite',
            'Department' => $v->Asset_Location_Department,
            'User_Responsible' => $v->User_Responsible,
            'Current_KM' => $latestKM,
            'Is_On_Trip' => $activeTrip ? true : false,
            'Active_Trip' => $activeTrip
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $result]);
    exit;
}

// 2. DETAIL SATU KENDARAAN
if ($action === 'vehicle_info') {
    if (!$assetNo) {
        echo json_encode(['status' => 'error', 'message' => 'Asset No tidak boleh kosong']);
        exit;
    }

    $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
    if (!$asset) {
        echo json_encode(['status' => 'error', 'message' => 'Kendaraan tidak ditemukan']);
        exit;
    }

    $activeTrip = DB::table('vehicle_logbook')
        ->where('Asset_No', $assetNo)
        ->where('Status', 'ON_GOING')
        ->orderBy('id', 'DESC')
        ->first();

    $lastLogKM = DB::table('vehicle_logbook')
        ->where('Asset_No', $assetNo)
        ->whereNotNull('End_KM')
        ->max('End_KM');
    $lastFuel = DB::table('vehicle_fuel_log')
        ->where('Asset_No', $assetNo)
        ->orderBy('Fill_Date', 'DESC')
        ->orderBy('id', 'DESC')
        ->first();

    $latestKM = max((float)($asset->KM_Jadwal_PM ?? 0), (float)$lastLogKM, (float)($lastFuel->Current_KM ?? 0));

    echo json_encode([
        'status' => 'success',
        'data' => [
            'Asset_No' => $asset->Asset_No,
            'Registration_No' => $asset->Registration_No ?: $asset->Asset_No,
            'Unit_Name' => $asset->Unit_Name ?: ($asset->Product_Brand . ' ' . $asset->Model),
            'Brand' => $asset->Product_Brand,
            'Model' => $asset->Model,
            'Jenis_Kendaraan' => $asset->Jenis_Kendaraan,
            'Bahan_Bakar' => $asset->Source_Production ?: 'Pertalite',
            'Department' => $asset->Asset_Location_Department,
            'User_Responsible' => $asset->User_Responsible,
            'Current_KM' => $latestKM,
            'Last_Fuel_KM' => $lastFuel ? (float)$lastFuel->Current_KM : $latestKM,
            'Last_Fuel_Type' => $lastFuel ? $lastFuel->Fuel_Type : ($asset->Source_Production ?: 'Pertalite'),
            'Is_On_Trip' => $activeTrip ? true : false,
            'Active_Trip' => $activeTrip
        ]
    ]);
    exit;
}

// 3. KPI & ANALYTICS METRICS
if ($action === 'kpi') {
    $logQuery = DB::table('vehicle_logbook');
    $fuelQuery = DB::table('vehicle_fuel_log');

    if ($assetNo) {
        $logQuery->where('Asset_No', $assetNo);
        $fuelQuery->where('Asset_No', $assetNo);
    }

    if ($filterMonth && $filterMonth !== 'ALL') {
        $logQuery->where('Start_Date', 'LIKE', $filterMonth . '%');
        $fuelQuery->where('Fill_Date', 'LIKE', $filterMonth . '%');
    }

    $totalTrips = (clone $logQuery)->where('Status', 'COMPLETED')->count();
    $activeTrips = DB::table('vehicle_logbook')->where('Status', 'ON_GOING')->count();
    $totalKM = (float)(clone $logQuery)->where('Status', 'COMPLETED')->sum('Total_KM');

    $totalFuelCost = (float)(clone $fuelQuery)->sum('Total_Cost');
    $totalLiters = (float)(clone $fuelQuery)->sum('Liters');
    $fuelFillCount = (clone $fuelQuery)->count();

    $avgKmPerLiter = $totalLiters > 0 && $totalKM > 0 ? round($totalKM / $totalLiters, 2) : 0;
    $avgCostPerKM = $totalKM > 0 ? round($totalFuelCost / $totalKM, 2) : 0;
    $avgCostPerLiter = $totalLiters > 0 ? round($totalFuelCost / $totalLiters, 2) : 0;

    // Data tren 6 bulan terakhir untuk Chart.js
    $chartMonths = [];
    $chartKM = [];
    $chartLiters = [];
    $chartCost = [];

    for ($i = 5; $i >= 0; $i--) {
        $mKey = date('Y-m', strtotime("-$i months"));
        $mLabel = date('M Y', strtotime("-$i months"));
        $chartMonths[] = $mLabel;

        $mLogQ = DB::table('vehicle_logbook')->where('Start_Date', 'LIKE', $mKey . '%')->where('Status', 'COMPLETED');
        $mFuelQ = DB::table('vehicle_fuel_log')->where('Fill_Date', 'LIKE', $mKey . '%');
        if ($assetNo) {
            $mLogQ->where('Asset_No', $assetNo);
            $mFuelQ->where('Asset_No', $assetNo);
        }

        $chartKM[] = (float)$mLogQ->sum('Total_KM');
        $chartLiters[] = round((float)$mFuelQ->sum('Liters'), 1);
        $chartCost[] = (float)$mFuelQ->sum('Total_Cost');
    }

    // Data perbandingan efisiensi antar kendaraan untuk Chart.js
    $vehicleComparison = [];
    $allVehicles = DB::table('asset_list')
        ->where(function ($q) {
            $q->whereNotNull('Jenis_Kendaraan')->where('Jenis_Kendaraan', '!=', '')
              ->orWhere('Asset_Sub_Category', 'VEHICLE');
        })
        ->get();

    foreach ($allVehicles as $veh) {
        $vKmQ = DB::table('vehicle_logbook')->where('Asset_No', $veh->Asset_No)->where('Status', 'COMPLETED');
        $vFuelQ = DB::table('vehicle_fuel_log')->where('Asset_No', $veh->Asset_No);
        if ($filterMonth && $filterMonth !== 'ALL') {
            $vKmQ->where('Start_Date', 'LIKE', $filterMonth . '%');
            $vFuelQ->where('Fill_Date', 'LIKE', $filterMonth . '%');
        }

        $vKm = (float)$vKmQ->sum('Total_KM');
        $vLit = (float)$vFuelQ->sum('Liters');
        $vCost = (float)$vFuelQ->sum('Total_Cost');
        $vRatio = $vLit > 0 && $vKm > 0 ? round($vKm / $vLit, 2) : 0;

        if ($vKm > 0 || $vLit > 0 || $vCost > 0) {
            $vehicleComparison[] = [
                'Plate' => $veh->Registration_No ?: $veh->Asset_No,
                'Name' => $veh->Unit_Name ?: ($veh->Product_Brand . ' ' . $veh->Model),
                'KM' => $vKm,
                'Liters' => $vLit,
                'Cost' => $vCost,
                'KmPerLiter' => $vRatio,
                'CostPerKM' => $vKm > 0 ? round($vCost / $vKm, 2) : 0
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'summary' => [
                'total_trips' => $totalTrips,
                'active_trips' => $activeTrips,
                'total_km' => $totalKM,
                'total_fuel_cost' => $totalFuelCost,
                'total_liters' => $totalLiters,
                'fuel_fill_count' => $fuelFillCount,
                'avg_km_per_liter' => $avgKmPerLiter,
                'avg_cost_per_km' => $avgCostPerKM,
                'avg_cost_per_liter' => $avgCostPerLiter
            ],
            'chart_trend' => [
                'labels' => $chartMonths,
                'km' => $chartKM,
                'liters' => $chartLiters,
                'cost' => $chartCost
            ],
            'vehicle_comparison' => $vehicleComparison
        ]
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>
