<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\MasterSppdAllowance;
use App\MasterSppdJobLevel;
use App\MasterSppdTripType;
use App\Services\GeneratorService;
use App\Services\OSRMService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class SppdController extends Controller
{
    protected $generatorService;

    public function __construct(GeneratorService $g)
    {
        $this->generatorService = $g;
    }

    public function index()
    {
        return view('humanresources.sppd.index');
    }

    public function data()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $canSeeAll = self::isApprover() || self::isHcgaVerifier() || self::isAdministrator();
        $nomor = 1;

        $Query = DB::table('sppd')->orderBy('ID', 'DESC');
        if (!$canSeeAll) {
            $Query->where('Requester_ID', Auth::user()->id);
        }

        echo "<rows>";
        foreach ($Query->get() as $Fetch) {
            $linkPengajuan = "<a href='hrd-sppd-print?ID=$Fetch->ID' style='color:blue;' target='_blank'>Download</a>";
            $linkPersetujuan = $Fetch->Status == 'ACCEPTED'
                ? "<a href='hrd-sppd-print-approved?ID=$Fetch->ID' style='color:green;' target='_blank'>Download</a>"
                : '-';

            $route = $Fetch->Route_Start . ' - ' . $Fetch->Route_Finish;
            $dest  = $Fetch->Destination . ' (' . ($Fetch->Program_Name ?: '-') . ')';
            $tgl   = $Fetch->Schedule_Date ? date('d-m-Y', strtotime($Fetch->Schedule_Date)) : '-';

            echo "<row id=\"" . $Fetch->ID . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->SPPD_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Requester_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Sppd_Type ?? 'NORMAL') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Destination) . "</cell>";
            echo "<cell>" . htmlspecialchars($route) . "</cell>";
            echo "<cell>" . htmlspecialchars($dest) . "</cell>";
            echo "<cell>$tgl</cell>";
            echo "<cell>" . number_format((float) $Fetch->Estimated_Cost, 0, ',', '.') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
            echo "<cell>$linkPengajuan</cell>";
            echo "<cell>$linkPersetujuan</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public function create()
    {
        $user = Auth::user();
        $departments = DB::table('master_department')->orderBy('Department', 'ASC')->get();
        $typeCounts = [
            'NORMAL'     => DB::table('sppd')->where('Sppd_Type', 'NORMAL')->count(),
            'PERBANTUAN' => DB::table('sppd')->where('Sppd_Type', 'PERBANTUAN')->count(),
            'RELOKASI'   => DB::table('sppd')->where('Sppd_Type', 'RELOKASI')->count(),
        ];
        $ymd = date('ymd');
        $SPPD_No = self::nextSppdNumber('NORMAL');
        $fleetList = \App\Http\Controllers\HCGA\CarRentController::fleetOptions();
        $locations = DB::table('location')->orderBy('Name', 'ASC')->get();

        // Get job level from employment
        $jobLevel = 'pelaksana'; // default
        $empData = DB::table('employment')->where('NIK', $user->Emp_ID)->first();
        if ($empData && !empty($empData->Position)) {
            $pos = DB::table('master_position')->where("Initial", $empData->Position)->first();
            $grade = $pos->Level ?? 0;
            if ($grade >= 1 && $grade <= 3) {
                $jobLevel = 'management';
            } elseif ($grade >= 4 && $grade <= 6) {
                $jobLevel = 'senior_middle';
            } elseif ($grade >= 7 && $grade <= 8) {
                $jobLevel = 'junior_middle';
            } else {
                $jobLevel = 'pelaksana';
            }
        }

        return view('humanresources.sppd.new', compact('user', 'departments', 'SPPD_No', 'fleetList', 'jobLevel', 'locations', 'typeCounts', 'ymd'));
    }

    public function getMatrixAllowance()
    {
        try {
            $jobLevels = MasterSppdJobLevel::all();
            $tripTypes = MasterSppdTripType::all();
            $allowances = MasterSppdAllowance::all();

            if ($jobLevels->count() > 0 && $tripTypes->count() > 0) {
                $matrixData = [
                    'title' => 'Tabel Regulasi Perjalanan Dinas',
                    'job_levels' => $jobLevels->map(function ($jl) {
                        return [
                            'level' => $jl->level,
                            'grade' => $jl->grade,
                            'label' => $jl->label,
                        ];
                    })->values()->toArray(),
                    'trip_types' => [],
                ];

                foreach ($tripTypes as $tt) {
                    $typeAllowances = [];
                    $filtered = $allowances->where('trip_type_key', $tt->type_key);
                    foreach ($filtered as $allw) {
                        $land = is_string($allw->transport_land) ? json_decode($allw->transport_land, true) : $allw->transport_land;
                        $typeAllowances[$allw->job_level_key] = [
                            'transport' => [
                                'land' => $land,
                                'air' => $allw->transport_air,
                                'ticket_max' => $allw->ticket_max,
                                'reimburse_max_per_night' => $allw->reimburse_max_per_night,
                            ],
                            'accommodation' => [
                                'hotel_star' => $allw->hotel_star,
                            ],
                            'daily' => [
                                'meal' => $allw->daily_meal,
                                'pocket' => $allw->daily_pocket,
                            ],
                            'assistance' => $allw->kos_per_month !== null || $allw->assistance_note !== null ? [
                                'kos_per_month' => $allw->kos_per_month,
                                'note' => $allw->assistance_note,
                            ] : null,
                            'relocation' => $allw->relocation_one_time_payment !== null || $allw->relocation_note !== null ? [
                                'one_time_payment' => $allw->relocation_one_time_payment,
                                'note' => $allw->relocation_note,
                            ] : null,
                            'mobilization' => $allw->mobilization_cost_per_week !== null || $allw->mobilization_note !== null ? [
                                'cost_per_week' => $allw->mobilization_cost_per_week,
                                'note' => $allw->mobilization_note,
                            ] : null,
                        ];
                    }

                    $matrixData['trip_types'][$tt->type_key] = [
                        'name' => $tt->name,
                        'duration' => $tt->duration,
                        'note' => $tt->note,
                        'allowances' => $typeAllowances,
                    ];
                }

                return response()->json(['status' => 'success', 'data' => $matrixData]);
            }
        } catch (\Exception $e) {
            // Fallback to JSON file if database error occurs
            Log::error($e);
        }

        $matrixPath = base_path('app/Helpers/matric.json');
        if (!file_exists($matrixPath)) {
            return response()->json(['status' => 'error', 'message' => 'Matrix file not found'], 404);
        }
        $matrix = json_decode(file_get_contents($matrixPath), true);
        return response()->json(['status' => 'success', 'data' => $matrix]);
    }

    public function routeInfo(Request $request)
    {
        try {
            $distanceKm = self::routeDistanceKm(
                trim($request->input('Route_Start', '')),
                trim($request->input('Route_Finish', '')),
                $request->input('Dest_Lat', ''),
                $request->input('Dest_Lng', '')
            );
            $durationMin = self::tripDurationMinutes($request->input('Departure_Time', ''), $request->input('Return_Time', ''));

            if ($distanceKm === null) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Koordinat asal/tujuan belum tersedia, rute tidak dapat dihitung',
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'distance_km'  => $distanceKm,
                    'duration_min' => $durationMin,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghitung rute: ' . $e->getMessage(),
            ], 500);
        }
    }

    public static function resolveLocationCoords($locationId)
    {
        if (!$locationId) {
            return null;
        }
        $loc = DB::table('location')->where('Location_ID', $locationId)->first();
        if (!$loc) {
            return null;
        }
        $lat = trim((string) $loc->Latitude);
        $lng = trim((string) $loc->Longitude);
        if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            return null;
        }
        return [(float) $lat, (float) $lng];
    }

    public static function tripDurationMinutes($departure, $return)
    {
        $parse = function ($t) {
            $t = trim((string) $t);
            if ($t === '') {
                return null;
            }
            $parts = explode(':', $t);
            if (count($parts) < 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
                return null;
            }
            return ((int) $parts[0]) * 60 + ((int) $parts[1]);
        };
        $d = $parse($departure);
        $r = $parse($return);
        if ($d === null || $r === null) {
            return null;
        }
        if ($r <= $d) {
            $r += 1440; // overnight trip
        }
        return $r - $d;
    }

    public static function routeDistanceKm($routeStart, $routeFinish, $destLat, $destLng)
    {
        $origin = self::resolveLocationCoords($routeStart);
        if (!$origin) {
            return null;
        }

        $lat = trim((string) $destLat);
        $lng = trim((string) $destLng);
        if ($lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng)) {
            $dest = [(float) $lat, (float) $lng];
        } else {
            $dest = self::resolveLocationCoords($routeFinish);
        }
        if (!$dest) {
            return null;
        }

        $route = app(OSRMService::class)->getRoute([
            [$origin[1], $origin[0]],
            [$dest[1], $dest[0]],
        ]);

        return $route['distance_km'];
    }

    public static function computeRouteScope($sppdType, $routeStart, $routeFinish, $destLat, $destLng, $departure, $return)
    {
        $durationMin = self::tripDurationMinutes($departure, $return);

        $distanceKm = null;
        try {
            $distanceKm = self::routeDistanceKm($routeStart, $routeFinish, $destLat, $destLng);
        } catch (\Throwable $e) {
            $distanceKm = null;
        }

        if ($sppdType !== 'NORMAL') {
            return [$distanceKm, $durationMin, 'FULL'];
        }

        if ($distanceKm === null || $durationMin === null) {
            return [null, $durationMin, null];
        }

        $scope = ($distanceKm < 120 && $durationMin < 480) ? 'MEAL_ONLY' : 'FULL';
        return [$distanceKm, $durationMin, $scope];
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $sppdType = trim($request->input('Sppd_Type', 'NORMAL')) ?: 'NORMAL';
        if ($sppdType === 'RELOKASI') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Relokasi/Mutasi belum tersedia, silakan pilih jenis lain',
            ], 422);
        }

        $dateStart  = trim($request->input('Date_Start', ''));
        $dateFinish = trim($request->input('Date_Finish', ''));
        if ($dateStart === '' || $dateFinish === '') {
            return response()->json(['status' => 'error', 'message' => 'Tanggal mulai dan selesai wajib diisi'], 422);
        }
        if ($dateFinish < $dateStart) {
            return response()->json(['status' => 'error', 'message' => 'Tanggal selesai tidak boleh sebelum tanggal mulai'], 422);
        }
        $spanDays = (int) \Carbon\Carbon::parse($dateStart)->diffInDays(\Carbon\Carbon::parse($dateFinish)) + 1;

        $required = ['Department', 'Route_Start', 'Schedule_Date', 'Departure_Time', 'Return_Time', 'Program_Name', 'Description'];
        foreach ($required as $f) {
            if (trim($request->input($f, '')) === '') {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Field $f wajib diisi",
                ], 422);
            }
        }

        // Rute (Tujuan): Kantor (office) OR destination point (map)
        $routeFinish = trim($request->input('Route_Finish', ''));
        $destLat = trim($request->input('Dest_Lat', ''));
        $destLng = trim($request->input('Dest_Lng', ''));
        $hasDestPoint = ($destLat !== '' && $destLng !== '');
        $destination = trim($request->input('Destination', ''));

        if ($routeFinish === '' && !$hasDestPoint) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Rute (Tujuan) wajib diisi: pilih lokasi kantor atau titik tujuan pada peta',
            ], 422);
        }

        if ($hasDestPoint) {
            // Point mode: the destination name is mandatory (auto-filled by the map / editable)
            if ($destination === '') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tujuan Perjalanan Dinas wajib diisi untuk Pilih Titik Tujuan',
                ], 422);
            }
        } else {
            // Office mode: destination = the selected office
            $office = DB::table('location')->where('Location_ID', $routeFinish)->first();
            $destination = $office ? trim($office->Name . ' (' . $office->Location_ID . ')') : $routeFinish;
        }

        // Distance / duration based entitlement scope (cross-referenced with master regulation)
        list($distanceKm, $durationMin, $scope) = self::computeRouteScope(
            $sppdType,
            trim($request->input('Route_Start', '')),
            $routeFinish,
            $destLat,
            $destLng,
            trim($request->input('Departure_Time', '')),
            trim($request->input('Return_Time', ''))
        );
        if ($sppdType === 'NORMAL' && $scope === null) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Jarak & durasi tidak dapat dihitung. Pastikan lokasi asal/tujuan memiliki koordinat, atau pilih titik tujuan pada peta.',
            ], 422);
        }

        $costs = [];
        $total = 0;
        foreach ($request->all() as $key => $val) {
            if (preg_match('/^Cost_(\d+)$/', $key, $m)) {
                $desc = trim($request->input('Desc_' . $m[1], ''));
                if (trim($val) === '') continue;
                $nominal = (float) str_replace([',', '.'], ['', '.'], trim($val));
                if ($nominal > 0) {
                    $costs[] = ['Cost' => $nominal, 'Description' => $desc];
                    $total += $nominal;
                }
            }
        }
        if (count($costs) == 0 || $total <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Rincian biaya wajib diisi minimal satu baris',
            ], 422);
        }

        $nama = $user->Name ?: $user->username;
        $nik  = $user->Emp_ID ?: '';

        $originSite  = trim($request->input('Origin_Site', ''));
        $targetSite  = trim($request->input('Target_Site', ''));
        $reason      = trim($request->input('Reason', ''));

        if ($sppdType === 'NORMAL') {
            if ($spanDays > 14) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'SPPD Normal maksimal 14 hari',
                ], 422);
            }
            $approver = self::getApprover();
            $approverId   = $approver ? $approver->id : null;
            $approverName = $approver ? ($approver->Name ?: $approver->username) : '';
            $status = 'SUBMITTED';
        } else {
            // PERBANTUAN SEMENTARA
            if ($spanDays <= 14) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Perbantuan Sementara harus lebih dari 14 hari',
                ], 422);
            }
            if ($originSite === '' || $targetSite === '' || $reason === '') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Asal Site, Lokasi Perbantuan, dan Alasan wajib diisi',
                ], 422);
            }

            $org = self::getRequesterOrg($nik);
            if (!$org) {
                return response()->json(['status' => 'error', 'message' => 'Profil organisasi tidak ditemukan, hubungi HCGA'], 422);
            }
            $level = self::getRequesterLevel($org);
            if ($level === null) {
                return response()->json(['status' => 'error', 'message' => 'Level organisasi tidak ditemukan, hubungi HCGA'], 422);
            }

            $placed = self::pbPlaceFirstStage($org, $level);
            if (!$placed['ok']) {
                return response()->json(['status' => 'error', 'message' => $placed['message']], 422);
            }
            $status = $placed['status'];
            $approverId   = $placed['approver_id'];
            $approverName = $placed['approver_name'];
        }

        // Kebutuhan mobil opsional (dipakai auto-create car_rent saat SPPD ACCEPTED).
        $needCar = (int) $request->input('Need_Car', 0) === 1 ? 1 : 0;
        $carJenis = trim($request->input('Car_Jenis_Mobil', ''));
        $carTglPinjam = trim($request->input('Car_Tgl_Pinjam', ''));
        $carTglKembali = trim($request->input('Car_Tgl_Kembali', ''));
        $carJamPinjam = trim($request->input('Car_Jam_Pinjam', ''));
        $carJamKembali = trim($request->input('Car_Jam_Kembali', ''));
        if ($needCar) {
            if ($carJenis === '' || $carTglPinjam === '' || $carTglKembali === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jenis mobil + tanggal pinjam/kembali wajib diisi bila butuh mobil',
                ], 422);
            }
            if ($carTglKembali < $carTglPinjam) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tanggal kembali mobil tidak boleh sebelum tanggal pinjam',
                ], 422);
            }
        }

        $SPPD_No = self::nextSppdNumber($sppdType);

        DB::beginTransaction();
        try {
            $sppdId = DB::table('sppd')->insertGetId([
                'SPPD_No'        => $SPPD_No,
                'Requester_ID'   => $user->id,
                'Requester_Name' => $nama,
                'Requester_NIK'  => $nik,
                'Department'     => trim($request->Department),
                'Destination'    => $destination,
                'Route_Start'    => trim($request->Route_Start),
                'Route_Finish'   => trim($request->Route_Finish),
                'Description'    => trim($request->Description),
                'Schedule_Date'  => $request->Schedule_Date,
                'Schedule_Time'  => trim($request->Departure_Time) . ' - ' . trim($request->Return_Time),
                'Departure_Time' => trim($request->Departure_Time),
                'Return_Time'    => trim($request->Return_Time),
                'Dest_Lat'       => $destLat ?: null,
                'Dest_Lng'       => $destLng ?: null,
                'Distance_Km'    => $distanceKm,
                'Duration_Min'   => $durationMin,
                'Entitlement_Scope' => $scope,
                'Program_Name'   => trim($request->Program_Name),
                'Estimated_Cost' => $total,
                'Sppd_Type'      => $sppdType,
                'Date_Start'     => $dateStart,
                'Date_Finish'    => $dateFinish,
                'Origin_Site'    => $originSite ?: null,
                'Target_Site'    => $targetSite ?: null,
                'Reason'         => $reason ?: null,
                'Status'         => $status,
                'Approver_ID'    => $approverId,
                'Approver_Name'  => $approverName,
                'Created_By'     => $user->username,
                'Created_Date'   => now(),
                'Need_Car'       => $needCar,
                'Car_Jenis_Mobil' => $carJenis ?: null,
                'Car_Tgl_Pinjam' => $carTglPinjam ?: null,
                'Car_Tgl_Kembali' => $carTglKembali ?: null,
                'Car_Jam_Pinjam' => $carJamPinjam ?: null,
                'Car_Jam_Kembali' => $carJamKembali ?: null,
            ]);

            foreach ($costs as $c) {
                DB::table('sppd_cost')->insert([
                    'SPPD_ID'     => $sppdId,
                    'Cost'        => $c['Cost'],
                    'Description' => $c['Description'],
                ]);
            }

            DB::table('sppd_log')->insert([
                'SPPD_ID'    => $sppdId,
                'Action'     => 'SUBMITTED',
                'Actor_ID'   => $user->id,
                'Actor_Name' => $nama,
                'Note'       => 'Request ' . $sppdType . ' diajukan',
                'Created_At' => now(),
            ]);

            $wasApproved = ($sppdType === 'PERBANTUAN' && $status === 'APPROVED');
            $selfApproved = ($sppdType === 'NORMAL' && $approverId && (int) $approverId === (int) $user->id);

            if ($wasApproved || $selfApproved) {
                $finalStatus = $wasApproved ? 'APPROVED' : 'ACCEPTED';
                $note = $wasApproved
                    ? 'Approve otomatis (pengaju adalah satu-satunya approver pada rantai organisasi)'
                    : 'Approve otomatis (pengaju adalah HCGA Manager)';
                DB::table('sppd')->where('ID', $sppdId)->update([
                    'Status'        => $finalStatus,
                    'Approved_At'   => now(),
                    'Approval_Note' => $note,
                ]);
                DB::table('sppd_log')->insert([
                    'SPPD_ID'    => $sppdId,
                    'Action'     => $finalStatus,
                    'Actor_ID'   => $user->id,
                    'Actor_Name' => $nama,
                    'Note'       => $note,
                    'Created_At' => now(),
                ]);
                $status = $finalStatus;
                // Auto-create car_rent bila pengaju butuh mobil (idempoten, ikut transaksi ini).
                $sppdRow = DB::table('sppd')->where('ID', $sppdId)->first();
                list($carErr, $carNo, $carInfo) = CarRentController::createFromSppd($sppdRow, $user->username);
                if ($carErr) {
                    DB::rollBack();
                    Log::error('SPPD auto-create car_rent failed: ' . $carErr);
                    return response()->json(['status' => 'error', 'message' => 'SPPD tersimpan tapi booking mobil gagal: ' . $carErr], 422);
                }
            }

            DB::commit();

            Log::info('SPPD created: ' . $SPPD_No . ' type=' . $sppdType . ' by ' . $user->username . ' status=' . $status);

            return response()->json([
                'status'  => 'success',
                'message' => 'Request SPPD submitted',
                'SPPD_No' => $SPPD_No,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SPPD create failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function detail(Request $request)
    {
        $sppd = DB::table('sppd')->where('ID', $request->ID)->first();
        if (!$sppd) {
            abort(404);
        }

        $user = Auth::user();
        $isRequester = (int) $sppd->Requester_ID === (int) $user->id;
        $isApprover  = (int) $sppd->Approver_ID === (int) $user->id;
        if (!$isRequester && !$isApprover && !$this->isPerbantuanParticipant($sppd, $user)) {
            abort(403, 'Anda tidak berhak mengakses data ini');
        }

        $costs = DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->get();
        $logs  = DB::table('sppd_log')->where('SPPD_ID', $sppd->ID)->orderBy('ID', 'ASC')->get();
        $canClose = $isRequester && in_array($sppd->Status, ['ACCEPTED', 'APPROVED']);
        $canPrint = $sppd->Sppd_Type !== 'RELOKASI';
        $carRents = DB::table('car_rent')->where('SPPD_ID', $sppd->ID)->orderBy('ID', 'DESC')->get();
        $settlements = DB::table('settlement_sppd')->where('SPPD_ID', $sppd->ID)->orderBy('ID', 'DESC')->get();

        return view('humanresources.sppd.detail', compact('sppd', 'costs', 'logs', 'canClose', 'canPrint', 'carRents', 'settlements'));
    }

    // Retry manual auto-create car_rent bila hook approval gagal (misal overlap saat itu).
    public function createCarRent(Request $request)
    {
        $user = Auth::user();
        $sppd = DB::table('sppd')->where('ID', $request->ID)->first();
        if (!$sppd) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }
        $isRequester = (int) $sppd->Requester_ID === (int) $user->id;
        $isApprover = (int) $sppd->Approver_ID === (int) $user->id;
        if (!$isRequester && !$isApprover) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak berhak atas SPPD ini'], 403);
        }
        if (!in_array($sppd->Status, ['ACCEPTED', 'APPROVED'])) {
            return response()->json(['status' => 'error', 'message' => 'Hanya SPPD yang sudah disetujui yang bisa dibuatkan booking mobil'], 422);
        }
        if (empty($sppd->Need_Car)) {
            return response()->json(['status' => 'error', 'message' => 'SPPD ini tidak ditandai butuh mobil'], 422);
        }
        DB::beginTransaction();
        try {
            list($carErr, $carNo, $carInfo) = CarRentController::createFromSppd($sppd, $user->username);
            if ($carErr) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $carErr], 422);
            }
            DB::commit();
            if ($carInfo === 'exists') {
                return response()->json(['status' => 'success', 'message' => 'Booking mobil sudah ada: ' . $carNo, 'Car_Rent_No' => $carNo], 200);
            }
            return response()->json(['status' => 'success', 'message' => 'Booking mobil dibuat: ' . $carNo, 'Car_Rent_No' => $carNo], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SPPD retry create car_rent failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function close(Request $request)
    {
        $user = Auth::user();
        $sppd = DB::table('sppd')->where('ID', $request->ID)->first();

        if (!$sppd) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }
        if ((int) $sppd->Requester_ID !== (int) $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Hanya pengaju yang dapat menutup request ini'], 403);
        }
        if (!in_array($sppd->Status, ['ACCEPTED', 'APPROVED'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Request hanya dapat ditutup setelah disetujui',
            ], 422);
        }

        DB::beginTransaction();
        try {
            DB::table('sppd')->where('ID', $sppd->ID)->update([
                'Status'       => 'CLOSED',
                'Closed_At'    => now(),
                'Updated_By'   => $user->username,
                'Updated_Date' => now(),
            ]);

            DB::table('sppd_log')->insert([
                'SPPD_ID'    => $sppd->ID,
                'Action'     => 'CLOSED',
                'Actor_ID'   => $user->id,
                'Actor_Name' => $user->Name ?: $user->username,
                'Note'       => 'Request SPPD ditutup oleh pengaju',
                'Created_At' => now(),
            ]);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Request SPPD closed'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SPPD close failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function printPengajuan(Request $request)
    {
        $sppd = DB::table('sppd')->where('ID', $request->ID)->first();
        if (!$sppd) {
            abort(404);
        }
        $this->authorizeViewer($sppd);

        if (($sppd->Sppd_Type ?? 'NORMAL') === 'PERBANTUAN') {
            return $this->printPerbantuan($sppd);
        }
        if (($sppd->Sppd_Type ?? 'NORMAL') === 'RELOKASI') {
            abort(403, 'Dokumen relokasi/mutasi belum tersedia');
        }

        $path = public_path('template-pengajuan-sppd.docx');
        if (!file_exists($path)) {
            abort(404, 'Template not found');
        }

        $scopeParts = [];
        if ($sppd->Distance_Km !== null) {
            $scopeParts[] = 'Jarak: ' . rtrim(rtrim(number_format((float) $sppd->Distance_Km, 2, ',', '.'), '0'), ',') . ' km';
        }
        if ($sppd->Duration_Min !== null) {
            $dur = floor($sppd->Duration_Min / 60) . ' jam' . (($sppd->Duration_Min % 60) ? ' ' . ($sppd->Duration_Min % 60) . ' mnt' : '');
            $scopeParts[] = 'Durasi: ' . $dur;
        }
        if ($sppd->Entitlement_Scope) {
            $scopeParts[] = 'Skema: ' . ($sppd->Entitlement_Scope === 'MEAL_ONLY' ? 'Hanya Uang Makan' : 'Hak Penuh');
        }
        $scopeInfo = $scopeParts ? ' (' . implode(', ', $scopeParts) . ')' : '';

        $doc = new TemplateProcessor($path);
        $doc->setValues([
            'effectiveDate'    => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'docNo'            => $sppd->SPPD_No,
            'department'       => $sppd->Department,
            'requester'        => $sppd->Requester_Name,
            'destination'      => $sppd->Destination,
            'routeDestination' => $sppd->Route_Start . ' - ' . ($sppd->Route_Finish ?: $sppd->Destination) . $scopeInfo,
            'shceduleDate'     => $sppd->Schedule_Date ? Carbon::parse($sppd->Schedule_Date)->format('d-m-Y') : '',
            'scheduleTime'     => $sppd->Schedule_Time ?: '',
            'programName'      => $sppd->Program_Name ?: '',
            'estimatedCost'    => $this->generatorService->rupiahFormatter((float) $sppd->Estimated_Cost),
            'description'      => $sppd->Description ?: '',
            'knownBy'          => $sppd->Approver_Name ?: '',
        ]);

        $filename = 'Pengajuan-SPPD-' . $sppd->SPPD_No . '.docx';
        return $this->downloadDoc($doc, $filename);
    }

    protected function printPerbantuan($sppd)
    {
        $org = self::getRequesterOrg($sppd->Requester_NIK);
        $level = $org ? self::getRequesterLevel($org) : null;

        // Template by requester level: >=7 staff (junior), 2..6 supervisor/manager (middle), <=1 VP/CEO (dept-head).
        if ($level !== null && $level >= 7) {
            $templateName = 'template-perbantuan-sementara-junior-middle.docx';
        } elseif ($level !== null && $level >= 2) {
            $templateName = 'template-perbantuan-sementara-middle-senior.docx';
        } else {
            $templateName = 'template-perbantuan-sementara-dept-head.docx';
        }

        $path = public_path($templateName);
        if (!file_exists($path)) {
            abort(404, 'Template not found');
        }

        $requesterUser = DB::table('users')->where('id', $sppd->Requester_ID)->first();
        $position = $requesterUser ? ($requesterUser->Position ?: $requesterUser->Priv_ID) : '';

        // Resolve signature names from the HCGA org chain (by level).
        $nameAtLevel = function ($lv) use ($org) {
            if (!$org) {
                return '';
            }
            $q = DB::table('organization_structure')->where('Alerted', 1);
            if ((int) $lv === 0) {
                $q->where('Level0', 1);
            } else {
                $q->where('Level' . $lv, 1)->where('Department', $org->Department);
            }
            $row = $q->orderBy('ID', 'ASC')->first();
            return $row ? $row->Name : '';
        };

        $directSuperior = '';
        $deptHead = '';
        if ($level !== null) {
            if ($level >= 7) {
                $directSuperior = $nameAtLevel(6); // supervisor
                $deptHead = $nameAtLevel(3);       // manager
            } elseif ($level == 3) {
                $deptHead = $nameAtLevel(1);       // VP
            } elseif ($level >= 2) {
                $deptHead = $nameAtLevel(3);       // manager
            }
        }

        $costs = DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->get();

        $start = \Carbon\Carbon::parse($sppd->Date_Start);
        $finish = \Carbon\Carbon::parse($sppd->Date_Finish);
        $duration = ((int) $start->diffInDays($finish) + 1) . ' hari';

        $money = function ($val) {
            return $this->generatorService->rupiahFormatter((float) $val);
        };

        $doc = new TemplateProcessor($path);
        $doc->setValues([
            'revision_no'    => '00',
            'effective_date' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'reference_no'   => $sppd->SPPD_No,
            'referece_no'    => $sppd->SPPD_No,
            'name'           => $sppd->Requester_Name,
            'position'       => $position,
            'origin_site'    => $sppd->Origin_Site ?: '',
            'target_site'    => $sppd->Target_Site ?: '',
            'duration'       => $duration,
            'reason'         => $sppd->Reason ?: '',
            'total_amount'   => $money($sppd->Estimated_Cost),
            'amount_written' => $sppd->Estimated_Cost > 0
                ? ucfirst($this->generatorService->numberToWords((int) $sppd->Estimated_Cost)) . ' rupiah'
                : '',
            'requested_by'   => $sppd->Requester_Name,
            'direct_superior'=> $directSuperior,
            'dept_head'      => $deptHead,
        ]);

        if ($costs->count() > 0) {
            $rows = $costs->map(function ($c) use ($money) {
                return [
                    'description' => $c->Description ?: '',
                    'price'       => $money($c->Cost),
                    'notes'       => '',
                ];
            })->values()->toArray();
            $doc->cloneRowAndSetValues('description', $rows);
        }

        $filename = 'Form-Perbantuan-Sementara-' . $sppd->SPPD_No . '.docx';
        return $this->downloadDoc($doc, $filename);
    }

    public function printPersetujuan(Request $request)
    {
        $sppd = DB::table('sppd')->where('ID', $request->ID)->first();
        if (!$sppd) {
            abort(404);
        }
        $approvedStatuses = ($sppd->Sppd_Type ?? 'NORMAL') === 'PERBANTUAN' ? ['APPROVED'] : ['ACCEPTED'];
        if (!in_array($sppd->Status, $approvedStatuses)) {
            abort(403, 'Approval document hanya tersedia setelah disetujui');
        }
        $this->authorizeViewer($sppd);

        $path = public_path('template-approved-sppd.docx');
        if (!file_exists($path)) {
            abort(404, 'Template not found');
        }

        $costs = DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->get();

        $doc = new TemplateProcessor($path);
        $doc->setValues([
            'effectiveDate' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'docNo'         => $sppd->SPPD_No,
            'department'    => $sppd->Department,
            'requester'     => $sppd->Requester_Name,
            'approvedCost'  => $this->generatorService->rupiahFormatter((float) $sppd->Estimated_Cost),
        ]);

        if ($costs->count() > 0) {
            $rows = $costs->map(function ($c) {
                return [
                    'cost'        => $this->generatorService->rupiahFormatter((float) $c->Cost),
                    'description' => $c->Description ?: '',
                ];
            })->values()->toArray();

            $doc->cloneRowAndSetValues('cost', $rows);
        }

        $filename = 'Persetujuan-SPPD-' . $sppd->SPPD_No . '.docx';
        return $this->downloadDoc($doc, $filename);
    }

    // ============ Perbantuan Sementara (org-chain) helpers ============

    public static function getRequesterOrg($empId)
    {
        return DB::table('organization_structure')->where('NIK', $empId)->first();
    }

    public static function getRequesterLevel($org)
    {
        foreach (range(0, 8) as $lv) {
            $col = 'Level' . $lv;
            if (isset($org->$col) && (int) $org->$col == 1) {
                return $lv;
            }
        }
        return null;
    }

    // Ordered approval stages (most junior first). Value = the org level flag number.
    public static function pbStageLevels()
    {
        return [
            'APPROVAL_SUPERVISOR' => 6,
            'APPROVAL_MANAGER'    => 3,
            'APPROVAL_VP'         => 1,
            'APPROVAL_CEO'        => 0,
        ];
    }

    public static function isPbStage($status)
    {
        return array_key_exists($status, self::pbStageLevels());
    }

    // Approver for a stage: L6/L3/L1 scoped by requester Department; CEO (L0) is global.
    public static function pbApproverForStage($stage, $org)
    {
        $levels = self::pbStageLevels();
        if (!array_key_exists($stage, $levels)) {
            return null;
        }
        $lv = $levels[$stage];
        $q = DB::table('organization_structure')->where('Alerted', 1);
        if ((int) $lv === 0) {
            $q->where('Level0', 1);
        } else {
            $q->where('Level' . $lv, 1)->where('Department', $org->Department);
        }
        foreach ($q->orderBy('ID', 'ASC')->get() as $cand) {
            $user = DB::table('users')->where('Emp_ID', $cand->NIK)->first();
            if ($user) {
                return $user;
            }
        }
        return null;
    }

    // Stages more senior than the given requester level.
    public static function pbStagesAbove($level)
    {
        $stages = [];
        foreach (self::pbStageLevels() as $stage => $lv) {
            if ($level !== null && $lv < $level) {
                $stages[] = $stage;
            }
        }
        return $stages;
    }

    // Place the FIRST stage at submit time; skips absent levels and self.
    public static function pbPlaceFirstStage($org, $level)
    {
        $requesterId = Auth::user()->id;
        foreach (self::pbStagesAbove($level) as $stage) {
            $approver = self::pbApproverForStage($stage, $org);
            if (!$approver) {
                continue; // skip absent level
            }
            if ((int) $approver->id === (int) $requesterId) {
                continue; // skip self
            }
            return [
                'ok' => true,
                'status' => $stage,
                'approver_id' => $approver->id,
                'approver_name' => $approver->Name ?: $approver->username,
            ];
        }
        return ['ok' => true, 'status' => 'APPROVED', 'approver_id' => null, 'approver_name' => null];
    }

    // Advance to the next more-senior stage (skipping absent levels and self).
    public static function pbAdvanceStage($org, $currentStage, $actorId)
    {
        $order = array_keys(self::pbStageLevels());
        $idx = array_search($currentStage, $order);
        if ($idx === false) {
            return ['status' => 'APPROVED', 'approver_id' => null, 'approver_name' => null];
        }
        foreach (array_slice($order, $idx + 1) as $stage) {
            $approver = self::pbApproverForStage($stage, $org);
            if (!$approver) {
                continue;
            }
            if ((int) $approver->id === (int) $actorId) {
                continue;
            }
            return ['ok' => true, 'status' => $stage, 'approver_id' => $approver->id, 'approver_name' => $approver->Name ?: $approver->username];
        }
        return ['status' => 'APPROVED', 'approver_id' => null, 'approver_name' => null];
    }

    // Is the current user the resolved approver for this perbantuan stage?
    public static function isPbStageApprover($sppd)
    {
        if (!Auth::check() || !$sppd || ($sppd->Sppd_Type ?? 'NORMAL') !== 'PERBANTUAN') {
            return false;
        }
        if (!self::isPbStage($sppd->Status)) {
            return false;
        }
        $org = self::getRequesterOrg($sppd->Requester_NIK);
        if (!$org) {
            return false;
        }
        $approver = self::pbApproverForStage($sppd->Status, $org);
        return $approver && (int) $approver->id === (int) Auth::user()->id;
    }

    public static function nextSppdNumber($type)
    {
        $code = ['NORMAL' => 'RS', 'PERBANTUAN' => 'PS', 'RELOKASI' => 'RL'][$type] ?? 'RS';
        $seq  = DB::table('sppd')->where('Sppd_Type', $type)->count() + 1;
        return "SPPD/{$code}/GEU/" . date('ymd') . "-{$seq}";
    }

    public static function getApprover()
    {
        return DB::table('users')
            ->where('Priv_ID', 'MANAGER')
            ->where('Department', 'HCGA')
            ->where('Status', 'ACTIVE')
            ->orderBy('id', 'ASC')
            ->first();
    }

    public static function isApprover()
    {
        $approver = self::getApprover();
        return $approver && Auth::check() && (int) $approver->id === (int) Auth::user()->id;
    }

    public static function isAdministrator()
    {
        return Auth::check()
            && (Auth::user()->hasRole('ADM') || Auth::user()->Position === 'Administrator');
    }

    public static function isHcgaVerifier()
    {
        return Auth::check()
            && Auth::user()->Department === 'HCGA'
            && in_array(Auth::user()->Priv_ID, ['MANAGER', 'HEAD', 'VP', 'HCGA'], true);
    }

    protected function downloadDoc(TemplateProcessor $doc, $filename)
    {
        $filename = str_replace(['/', '\\'], '_', $filename);
        $outputPath = storage_path('app/' . uniqid('sppd_', true) . '.docx');
        $doc->saveAs($outputPath);

        return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
    }

    protected function isPerbantuanParticipant($sppd, $user)
    {
        if (($sppd->Sppd_Type ?? 'NORMAL') !== 'PERBANTUAN') {
            return false;
        }
        if ($user->Department === 'HCGA') {
            return true;
        }
        return DB::table('sppd_log')
            ->where('SPPD_ID', $sppd->ID)
            ->where('Actor_ID', $user->id)
            ->exists();
    }

    protected function authorizeViewer($sppd)
    {
        $user = Auth::user();
        if ((int) $sppd->Requester_ID === (int) $user->id || (int) $sppd->Approver_ID === (int) $user->id) {
            return;
        }
        if ($this->isPerbantuanParticipant($sppd, $user)) {
            return;
        }
        abort(403, 'Anda tidak berhak mengakses dokumen ini');
    }
}
