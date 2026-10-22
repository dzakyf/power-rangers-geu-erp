<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class VehicleOperationsController extends Controller
{
    /**
     * Memastikan tabel yang dibutuhkan sudah ada di database.
     */
    protected function ensureTablesExist()
    {
        if (!Schema::hasTable('vehicle_logbook')) {
            Schema::create('vehicle_logbook', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('Log_No', 50)->unique();
                $table->string('Asset_No', 60)->index();
                $table->string('Registration_No', 50)->nullable()->index();
                $table->string('Driver_Name', 120);
                $table->string('Department', 100)->nullable();
                $table->dateTime('Start_Date');
                $table->dateTime('End_Date')->nullable();
                $table->decimal('Start_KM', 12, 1)->default(0);
                $table->decimal('End_KM', 12, 1)->nullable();
                $table->decimal('Total_KM', 12, 1)->nullable();
                $table->string('Destination', 255);
                $table->text('Purpose')->nullable();
                $table->string('Vehicle_Condition_Out', 150)->nullable();
                $table->string('Vehicle_Condition_In', 150)->nullable();
                $table->enum('Status', ['ON_GOING', 'COMPLETED', 'CANCELLED'])->default('ON_GOING');
                $table->string('Photo_Odometer_Start', 255)->nullable();
                $table->string('Photo_Odometer_End', 255)->nullable();
                $table->text('Notes')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('vehicle_fuel_log')) {
            Schema::create('vehicle_fuel_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('Fuel_No', 50)->unique();
                $table->string('Asset_No', 60)->index();
                $table->string('Registration_No', 50)->nullable()->index();
                $table->dateTime('Fill_Date');
                $table->string('Driver_Name', 120);
                $table->decimal('Current_KM', 12, 1)->default(0);
                $table->decimal('Previous_KM', 12, 1)->nullable();
                $table->decimal('Distance_KM', 12, 1)->nullable();
                $table->string('Fuel_Type', 50);
                $table->decimal('Liters', 10, 2);
                $table->decimal('Cost_Per_Liter', 12, 2)->nullable();
                $table->decimal('Total_Cost', 14, 2);
                $table->decimal('KM_Per_Liter', 10, 2)->nullable();
                $table->decimal('Cost_Per_KM', 12, 2)->nullable();
                $table->string('Gas_Station', 150)->nullable();
                $table->string('Payment_Method', 80)->nullable();
                $table->string('Receipt_Photo', 255)->nullable();
                $table->string('Odometer_Photo', 255)->nullable();
                $table->tinyInteger('Full_Tank')->default(1);
                $table->text('Notes')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Memastikan menu terdaftar di tabel menu_lists.
     */
    protected function ensureMenuRegistered()
    {
        if (Schema::hasTable('menu_lists')) {
            $exists = DB::table('menu_lists')->where('Path_Loc', 'vehicle-operations')->exists();
            if (!$exists) {
                DB::table('menu_lists')->insert([
                    'Menu_ID' => 'SI00163B',
                    'Name' => 'Vehicle Log & Fuel',
                    'Path_Loc' => 'vehicle-operations',
                    'Position' => 4,
                    'Parent_Menu' => 'SS00039',
                    'Urutan' => 11,
                    'Modules' => 'ASET'
                ]);
            }
        }
    }

    /**
     * Tampilan utama workspace Operasional Kendaraan (Logbook, Fuel, & Analytics).
     */
    public function index(Request $request)
    {
        $this->ensureTablesExist();
        $this->ensureMenuRegistered();

        // Ambil daftar kendaraan dari asset_list sesuai filter kendaraan
        $vehicles = DB::table('asset_list')
            ->where(function ($q) {
                $q->whereNotNull('Asset_Type')->where('Asset_Type', '!=', '')
                  ->orWhere('Asset_Sub_Category', 'VEHICLE');
            })
            ->select('Asset_No', 'Registration_No', 'Unit_Name', 'Product_Brand', 'Model', 'Asset_Type', 'Source_Production')
            ->orderBy('Registration_No', 'ASC')
            ->get();

        return view('assets.vehicle-operations', compact('vehicles'));
    }

    /**
     * Endpoint XML untuk DHTMLX Grid Logbook Perjalanan.
     */
    public function logbookData(Request $request)
    {
        $this->ensureTablesExist();

        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";

        $assetNo = trim($request->input('asset_no', ''));
        $month = trim($request->input('month', ''));
        $status = trim($request->input('status', ''));

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
                $aksi .= "<button type='button' class='btn btn-success btn-sm py-0 px-2' title='Check-In / Selesaikan' onclick='finishLogbook(" . $r->id . ", \"" . htmlspecialchars($r->Log_No, ENT_QUOTES) . "\", \"" . htmlspecialchars($r->Registration_No, ENT_QUOTES) . "\", " . (float)$r->Start_KM . ")'><i class='fa fa-check-circle mr-1'></i>Check-In</button>";
            }
            $aksi .= "<button type='button' class='btn btn-outline-danger btn-sm py-0 px-2' title='Hapus' onclick='deleteLogbook(" . $r->id . ", \"" . htmlspecialchars($r->Log_No, ENT_QUOTES) . "\")'><i class='fa fa-trash'></i></button>";
            $aksi .= "</div>]]>";

            // Foto link
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
    }

    /**
     * Endpoint XML untuk DHTMLX Grid Fuel Tracking.
     */
    public function fuelData(Request $request)
    {
        $this->ensureTablesExist();

        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";

        $assetNo = trim($request->input('asset_no', ''));
        $month = trim($request->input('month', ''));

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

            $buktiHtml = "-";
            if ($r->Receipt_Photo) {
                $buktiHtml = "<![CDATA[<a href='" . asset($r->Receipt_Photo) . "' target='_blank' class='badge badge-info p-1'><i class='fa fa-receipt mr-1'></i>Struk</a>]]>";
            }

            $efisiensiBadge = "-";
            if ($r->KM_Per_Liter !== null && $r->KM_Per_Liter > 0) {
                $val = (float)$r->KM_Per_Liter;
                $color = $val >= 10 ? 'success' : ($val >= 7 ? 'warning' : 'danger');
                $efisiensiBadge = "<![CDATA[<span class='badge badge-$color px-2 py-1'>" . number_format($val, 2) . " KM/L</span>]]>";
            }

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
    }

    /**
     * Endpoint JSON KPI & Analytics untuk Dashboard Cards dan Chart.js.
     */
    public function kpiData(Request $request)
    {
        $this->ensureTablesExist();

        $assetNo = trim($request->input('asset_no', ''));
        $filterMonth = trim($request->input('month', date('Y-m')));

        $logQuery = DB::table('vehicle_logbook');
        $fuelQuery = DB::table('vehicle_fuel_log');

        if ($assetNo !== '' && $assetNo !== 'ALL') {
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

        // Tren 6 bulan terakhir
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
            if ($assetNo !== '' && $assetNo !== 'ALL') {
                $mLogQ->where('Asset_No', $assetNo);
                $mFuelQ->where('Asset_No', $assetNo);
            }

            $chartKM[] = (float)$mLogQ->sum('Total_KM');
            $chartLiters[] = round((float)$mFuelQ->sum('Liters'), 1);
            $chartCost[] = (float)$mFuelQ->sum('Total_Cost');
        }

        // Perbandingan efisiensi antar armada
        $vehicleComparison = [];
        $allVehicles = DB::table('asset_list')
            ->where(function ($q) {
                $q->whereNotNull('Asset_Type')->where('Asset_Type', '!=', '')
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

        return response()->json([
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
    }

    /**
     * Endpoint detail satu kendaraan (KM terakhir & status trip).
     */
    public function vehicleInfo(Request $request)
    {
        $this->ensureTablesExist();

        $assetNo = trim($request->input('asset_no', ''));
        if (!$assetNo) {
            return response()->json(['status' => 'error', 'message' => 'Asset No tidak boleh kosong'], 400);
        }

        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset) {
            return response()->json(['status' => 'error', 'message' => 'Kendaraan tidak ditemukan'], 404);
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

        return response()->json([
            'status' => 'success',
            'data' => [
                'Asset_No' => $asset->Asset_No,
                'Registration_No' => $asset->Registration_No ?: $asset->Asset_No,
                'Unit_Name' => $asset->Unit_Name ?: ($asset->Product_Brand . ' ' . $asset->Model),
                'Brand' => $asset->Product_Brand,
                'Model' => $asset->Model,
                'Asset_Type' => $asset->Asset_Type,
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
    }

    /**
     * Simpan perjalanan baru (Check-Out).
     */
    public function storeLogbook(Request $request)
    {
        $this->ensureTablesExist();

        $assetNo = trim($request->input('Asset_No', ''));
        $driver = trim($request->input('Driver_Name', ''));
        $startDate = $request->input('Start_Date') ?: date('Y-m-d H:i:s');
        $startKM = (float)$request->input('Start_KM', 0);
        $destination = trim($request->input('Destination', ''));
        $purpose = trim($request->input('Purpose', ''));
        $conditionOut = trim($request->input('Vehicle_Condition_Out', 'Baik'));
        $dept = trim($request->input('Department', ''));
        $notes = trim($request->input('Notes', ''));

        if (!$assetNo || !$driver || !$destination) {
            return response()->json(['status' => 'error', 'message' => 'Kendaraan, Driver, dan Tujuan wajib diisi.'], 422);
        }

        // Cek apakah kendaraan sedang dalam perjalanan aktif
        $activeTrip = DB::table('vehicle_logbook')
            ->where('Asset_No', $assetNo)
            ->where('Status', 'ON_GOING')
            ->first();

        if ($activeTrip) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kendaraan ini masih tercatat dalam perjalanan aktif (' . $activeTrip->Log_No . '). Harap selesaikan (Check-In) terlebih dahulu.'
            ], 422);
        }

        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        $regNo = $asset ? ($asset->Registration_No ?: $asset->Asset_No) : $assetNo;

        // Generate No Logbook: LOG-YYYYMMDD-XXXX
        $todayStr = date('Ymd');
        $lastSeq = DB::table('vehicle_logbook')
            ->where('Log_No', 'LIKE', "LOG-{$todayStr}-%")
            ->count();
        $logNo = sprintf("LOG-%s-%04d", $todayStr, $lastSeq + 1);

        // Upload foto odometer awal jika ada
        $photoStart = null;
        if ($request->hasFile('Photo_Odometer_Start')) {
            $file = $request->file('Photo_Odometer_Start');
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension();
                $filename = 'odo_start_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $subDir = "filevendor/logbook/" . date('Y/m');
                $targetDir = public_path($subDir);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $filename);
                $photoStart = $subDir . '/' . $filename;
            }
        }

        $userName = Auth::user()->Name ?: Auth::user()->username;

        DB::table('vehicle_logbook')->insert([
            'Log_No' => $logNo,
            'Asset_No' => $assetNo,
            'Registration_No' => $regNo,
            'Driver_Name' => $driver,
            'Department' => $dept ?: ($asset->Asset_Location_Department ?? ''),
            'Start_Date' => $startDate,
            'Start_KM' => $startKM,
            'Destination' => $destination,
            'Purpose' => $purpose,
            'Vehicle_Condition_Out' => $conditionOut,
            'Status' => 'ON_GOING',
            'Photo_Odometer_Start' => $photoStart,
            'Notes' => $notes,
            'Created_By' => $userName,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Perjalanan berhasil dicatat (Check-Out: ' . $logNo . ').'
        ]);
    }

    /**
     * Selesaikan perjalanan (Check-In).
     */
    public function finishLogbook(Request $request)
    {
        $this->ensureTablesExist();

        $id = $request->input('id');
        $endKM = (float)$request->input('End_KM', 0);
        $endDate = $request->input('End_Date') ?: date('Y-m-d H:i:s');
        $conditionIn = trim($request->input('Vehicle_Condition_In', 'Baik'));
        $notes = trim($request->input('Notes', ''));

        $log = DB::table('vehicle_logbook')->where('id', $id)->first();
        if (!$log) {
            return response()->json(['status' => 'error', 'message' => 'Data logbook tidak ditemukan.'], 404);
        }

        if ($endKM < (float)$log->Start_KM) {
            return response()->json([
                'status' => 'error',
                'message' => 'KM Akhir (' . number_format($endKM, 1) . ') tidak boleh lebih kecil dari KM Awal (' . number_format($log->Start_KM, 1) . ').'
            ], 422);
        }

        $totalKM = round($endKM - (float)$log->Start_KM, 1);

        // Upload foto odometer akhir jika ada
        $photoEnd = null;
        if ($request->hasFile('Photo_Odometer_End')) {
            $file = $request->file('Photo_Odometer_End');
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension();
                $filename = 'odo_end_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $subDir = "filevendor/logbook/" . date('Y/m');
                $targetDir = public_path($subDir);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $filename);
                $photoEnd = $subDir . '/' . $filename;
            }
        }

        $userName = Auth::user()->Name ?: Auth::user()->username;

        $updateData = [
            'End_Date' => $endDate,
            'End_KM' => $endKM,
            'Total_KM' => $totalKM,
            'Vehicle_Condition_In' => $conditionIn,
            'Status' => 'COMPLETED',
            'Updated_By' => $userName,
            'updated_at' => now()
        ];
        if ($photoEnd) {
            $updateData['Photo_Odometer_End'] = $photoEnd;
        }
        if ($notes) {
            $updateData['Notes'] = ($log->Notes ? $log->Notes . " | " : '') . $notes;
        }

        DB::table('vehicle_logbook')->where('id', $id)->update($updateData);

        // Perbarui Odometer terakhir di tabel asset_list
        DB::table('asset_list')
            ->where('Asset_No', $log->Asset_No)
            ->where(function ($q) use ($endKM) {
                $q->whereNull('KM_Jadwal_PM')->orWhere('KM_Jadwal_PM', '<', $endKM);
            })
            ->update(['KM_Jadwal_PM' => $endKM]);

        return response()->json([
            'status' => 'success',
            'message' => 'Perjalanan selesai. Total jarak tempuh: ' . number_format($totalKM, 1) . ' KM.'
        ]);
    }

    /**
     * Hapus / Batalkan data logbook.
     */
    public function deleteLogbook(Request $request)
    {
        $this->ensureTablesExist();

        $id = $request->input('id');
        $log = DB::table('vehicle_logbook')->where('id', $id)->first();
        if (!$log) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        DB::table('vehicle_logbook')->where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Log perjalanan ' . $log->Log_No . ' berhasil dihapus.']);
    }

    /**
     * Catat transaksi pengisian BBM (Fuel Tracking).
     */
    public function storeFuel(Request $request)
    {
        $this->ensureTablesExist();

        $assetNo = trim($request->input('Asset_No', ''));
        $fillDate = $request->input('Fill_Date') ?: date('Y-m-d H:i:s');
        $driver = trim($request->input('Driver_Name', ''));
        $currentKM = (float)$request->input('Current_KM', 0);
        $fuelType = trim($request->input('Fuel_Type', 'Pertalite'));
        $liters = (float)$request->input('Liters', 0);
        $totalCost = (float)$request->input('Total_Cost', 0);
        $gasStation = trim($request->input('Gas_Station', ''));
        $paymentMethod = trim($request->input('Payment_Method', 'MyPertamina'));
        $fullTank = $request->input('Full_Tank', 1) ? 1 : 0;
        $notes = trim($request->input('Notes', ''));

        if (!$assetNo || $liters <= 0 || $totalCost <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Asset Kendaraan, Jumlah Liter, dan Total Biaya wajib diisi.'], 422);
        }

        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        $regNo = $asset ? ($asset->Registration_No ?: $asset->Asset_No) : $assetNo;

        // Cari pengisian terakhir kendaraan ini untuk mendapatkan Previous_KM
        $lastFuel = DB::table('vehicle_fuel_log')
            ->where('Asset_No', $assetNo)
            ->orderBy('Fill_Date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();

        $previousKM = $lastFuel ? (float)$lastFuel->Current_KM : null;
        $distanceKM = ($previousKM !== null && $currentKM >= $previousKM) ? round($currentKM - $previousKM, 1) : null;

        // Hitung kalkulasi otomatis
        $costPerLiter = $liters > 0 ? round($totalCost / $liters, 2) : 0;
        $kmPerLiter = ($distanceKM !== null && $distanceKM > 0 && $liters > 0) ? round($distanceKM / $liters, 2) : null;
        $costPerKM = ($distanceKM !== null && $distanceKM > 0) ? round($totalCost / $distanceKM, 2) : null;

        // Generate No Transaksi BBM: BBM-YYYYMMDD-XXXX
        $todayStr = date('Ymd');
        $lastSeq = DB::table('vehicle_fuel_log')
            ->where('Fuel_No', 'LIKE', "BBM-{$todayStr}-%")
            ->count();
        $fuelNo = sprintf("BBM-%s-%04d", $todayStr, $lastSeq + 1);

        // Upload foto struk dan odometer jika ada
        $receiptPhoto = null;
        if ($request->hasFile('Receipt_Photo')) {
            $file = $request->file('Receipt_Photo');
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension();
                $filename = 'receipt_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $subDir = "filevendor/fuel/" . date('Y/m');
                $targetDir = public_path($subDir);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $filename);
                $receiptPhoto = $subDir . '/' . $filename;
            }
        }

        $odoPhoto = null;
        if ($request->hasFile('Odometer_Photo')) {
            $file = $request->file('Odometer_Photo');
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension();
                $filename = 'odo_fuel_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $subDir = "filevendor/fuel/" . date('Y/m');
                $targetDir = public_path($subDir);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $filename);
                $odoPhoto = $subDir . '/' . $filename;
            }
        }

        $userName = Auth::user()->Name ?: Auth::user()->username;

        DB::table('vehicle_fuel_log')->insert([
            'Fuel_No' => $fuelNo,
            'Asset_No' => $assetNo,
            'Registration_No' => $regNo,
            'Fill_Date' => $fillDate,
            'Driver_Name' => $driver,
            'Current_KM' => $currentKM,
            'Previous_KM' => $previousKM,
            'Distance_KM' => $distanceKM,
            'Fuel_Type' => $fuelType,
            'Liters' => $liters,
            'Cost_Per_Liter' => $costPerLiter,
            'Total_Cost' => $totalCost,
            'KM_Per_Liter' => $kmPerLiter,
            'Cost_Per_KM' => $costPerKM,
            'Gas_Station' => $gasStation,
            'Payment_Method' => $paymentMethod,
            'Receipt_Photo' => $receiptPhoto,
            'Odometer_Photo' => $odoPhoto,
            'Full_Tank' => $fullTank,
            'Notes' => $notes,
            'Created_By' => $userName,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Perbarui Odometer terakhir di master asset_list bila lebih besar
        if ($currentKM > 0) {
            DB::table('asset_list')
                ->where('Asset_No', $assetNo)
                ->where(function ($q) use ($currentKM) {
                    $q->whereNull('KM_Jadwal_PM')->orWhere('KM_Jadwal_PM', '<', $currentKM);
                })
                ->update(['KM_Jadwal_PM' => $currentKM]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pencatatan BBM ' . $fuelNo . ' berhasil disimpan. ' . ($kmPerLiter ? 'Rasio efisiensi: ' . number_format($kmPerLiter, 2) . ' KM/L' : '')
        ]);
    }

    /**
     * Hapus transaksi BBM.
     */
    public function deleteFuel(Request $request)
    {
        $this->ensureTablesExist();

        $id = $request->input('id');
        $fuel = DB::table('vehicle_fuel_log')->where('id', $id)->first();
        if (!$fuel) {
            return response()->json(['status' => 'error', 'message' => 'Data BBM tidak ditemukan.'], 404);
        }

        DB::table('vehicle_fuel_log')->where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Transaksi BBM ' . $fuel->Fuel_No . ' berhasil dihapus.']);
    }

    /**
     * Export Logbook ke file CSV / Excel.
     */
    public function exportLogbook(Request $request)
    {
        $this->ensureTablesExist();

        $assetNo = trim($request->input('asset_no', ''));
        $month = trim($request->input('month', ''));

        $query = DB::table('vehicle_logbook')
            ->leftJoin('asset_list', 'vehicle_logbook.Asset_No', '=', 'asset_list.Asset_No')
            ->select(
                'vehicle_logbook.*',
                'asset_list.Unit_Name',
                'asset_list.Product_Brand',
                'asset_list.Model'
            )
            ->orderBy('vehicle_logbook.id', 'DESC');

        if ($assetNo && $assetNo !== 'ALL') {
            $query->where('vehicle_logbook.Asset_No', $assetNo);
        }
        if ($month && $month !== 'ALL') {
            $query->where('vehicle_logbook.Start_Date', 'LIKE', $month . '%');
        }

        $data = $query->get();

        $filename = "Logbook_Kendaraan_" . date('Ymd_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, ['No', 'No Log', 'Status', 'Plat Nomor', 'Kendaraan', 'Driver', 'Departemen', 'Waktu Berangkat', 'Waktu Kembali', 'KM Awal', 'KM Akhir', 'Total KM', 'Tujuan', 'Keperluan', 'Kondisi']);

        $no = 1;
        foreach ($data as $d) {
            fputcsv($output, [
                $no++,
                $d->Log_No,
                $d->Status,
                $d->Registration_No,
                ($d->Product_Brand ? $d->Product_Brand . ' ' : '') . ($d->Model ?: $d->Unit_Name),
                $d->Driver_Name,
                $d->Department,
                $d->Start_Date,
                $d->End_Date ?: '-',
                $d->Start_KM,
                $d->End_KM !== null ? $d->End_KM : '-',
                $d->Total_KM !== null ? $d->Total_KM : '-',
                $d->Destination,
                $d->Purpose,
                $d->Vehicle_Condition_In ?: $d->Vehicle_Condition_Out
            ]);
        }
        fclose($output);
        exit;
    }

    /**
     * Export Fuel Tracking ke file CSV / Excel.
     */
    public function exportFuel(Request $request)
    {
        $this->ensureTablesExist();

        $assetNo = trim($request->input('asset_no', ''));
        $month = trim($request->input('month', ''));

        $query = DB::table('vehicle_fuel_log')
            ->leftJoin('asset_list', 'vehicle_fuel_log.Asset_No', '=', 'asset_list.Asset_No')
            ->select(
                'vehicle_fuel_log.*',
                'asset_list.Unit_Name',
                'asset_list.Product_Brand',
                'asset_list.Model'
            )
            ->orderBy('vehicle_fuel_log.Fill_Date', 'DESC');

        if ($assetNo && $assetNo !== 'ALL') {
            $query->where('vehicle_fuel_log.Asset_No', $assetNo);
        }
        if ($month && $month !== 'ALL') {
            $query->where('vehicle_fuel_log.Fill_Date', 'LIKE', $month . '%');
        }

        $data = $query->get();

        $filename = "Fuel_Tracking_" . date('Ymd_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, ['No', 'No Transaksi', 'Tanggal', 'Plat Nomor', 'Kendaraan', 'Driver', 'Odometer (KM)', 'Jarak (KM)', 'Jenis BBM', 'Liter', 'Total Biaya (Rp)', 'Rp/Liter', 'Efisiensi (KM/L)', 'Biaya/KM (Rp/KM)', 'SPBU', 'Metode Bayar']);

        $no = 1;
        foreach ($data as $d) {
            fputcsv($output, [
                $no++,
                $d->Fuel_No,
                $d->Fill_Date,
                $d->Registration_No,
                ($d->Product_Brand ? $d->Product_Brand . ' ' : '') . ($d->Model ?: $d->Unit_Name),
                $d->Driver_Name,
                $d->Current_KM,
                $d->Distance_KM !== null ? $d->Distance_KM : '-',
                $d->Fuel_Type,
                $d->Liters,
                $d->Total_Cost,
                $d->Cost_Per_Liter ?: '-',
                $d->KM_Per_Liter ?: '-',
                $d->Cost_Per_KM ?: '-',
                $d->Gas_Station,
                $d->Payment_Method
            ]);
        }
        fclose($output);
        exit;
    }
}
