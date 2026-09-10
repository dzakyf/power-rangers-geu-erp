<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
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

        $isApprover = self::isApprover();
        $nomor = 1;

        $Query = DB::table('sppd')->orderBy('ID', 'DESC');
        if (!$isApprover) {
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
        $counter = DB::table('sppd')->where('Created_Date', 'LIKE', date('Y-m') . '%')->count() + 1;
        $SPPD_No = "SPPD/GA/" . ($user->Location ?: 'GEU-HO') . "/" . date('ymdhis') . "-" . $counter;
        $fleetList = \App\Http\Controllers\HCGA\CarRentController::fleetOptions();

        return view('humanresources.sppd.new', compact('user', 'departments', 'SPPD_No', 'fleetList'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $required = ['Department', 'Destination', 'Route_Start', 'Route_Finish', 'Schedule_Date', 'Schedule_Time', 'Program_Name', 'Description'];
        foreach ($required as $f) {
            if (trim($request->input($f, '')) === '') {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Field $f wajib diisi",
                ], 422);
            }
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

        $approver = self::getApprover();
        $approverId   = $approver ? $approver->id : null;
        $approverName = $approver ? ($approver->Name ?: $approver->username) : '';

        $nama = $user->Name ?: $user->username;
        $nik  = $user->Emp_ID ?: '';

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

        $counter = DB::table('sppd')->where('Created_Date', 'LIKE', date('Y-m') . '%')->count() + 1;
        $SPPD_No = "SPPD/GA/" . ($user->Location ?: 'GEU-HO') . "/" . date('ymdhis') . "-" . $counter;

        DB::beginTransaction();
        try {
            $sppdId = DB::table('sppd')->insertGetId([
                'SPPD_No'        => $SPPD_No,
                'Requester_ID'   => $user->id,
                'Requester_Name' => $nama,
                'Requester_NIK'  => $nik,
                'Department'     => trim($request->Department),
                'Destination'    => trim($request->Destination),
                'Route_Start'    => trim($request->Route_Start),
                'Route_Finish'   => trim($request->Route_Finish),
                'Description'    => trim($request->Description),
                'Schedule_Date'  => $request->Schedule_Date,
                'Schedule_Time'  => trim($request->Schedule_Time),
                'Program_Name'   => trim($request->Program_Name),
                'Estimated_Cost' => $total,
                'Status'         => 'SUBMITTED',
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
                'Note'       => 'Request SPPD diajukan',
                'Created_At' => now(),
            ]);

            if ($approverId && (int) $approverId === (int) $user->id) {
                DB::table('sppd')->where('ID', $sppdId)->update([
                    'Status'        => 'ACCEPTED',
                    'Approved_At'   => now(),
                    'Approval_Note' => 'Approve otomatis (pengaju adalah HCGA Manager)',
                ]);
                DB::table('sppd_log')->insert([
                    'SPPD_ID'    => $sppdId,
                    'Action'     => 'APPROVED',
                    'Actor_ID'   => $user->id,
                    'Actor_Name' => $nama,
                    'Note'       => 'Approve otomatis (pengaju adalah HCGA Manager)',
                    'Created_At' => now(),
                ]);
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

            Log::info('SPPD created: ' . $SPPD_No . ' by ' . $user->username);

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
        if (!$isRequester && !$isApprover) {
            abort(403, 'Anda tidak berhak mengakses data ini');
        }

        $costs = DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->get();
        $logs  = DB::table('sppd_log')->where('SPPD_ID', $sppd->ID)->orderBy('ID', 'ASC')->get();
        $canClose = $isRequester && $sppd->Status == 'ACCEPTED';
        $carRents = DB::table('car_rent')->where('SPPD_ID', $sppd->ID)->orderBy('ID', 'DESC')->get();

        return view('humanresources.sppd.detail', compact('sppd', 'costs', 'logs', 'canClose', 'carRents'));
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
        if ($sppd->Status !== 'ACCEPTED') {
            return response()->json(['status' => 'error', 'message' => 'Hanya SPPD ACCEPTED yang bisa dibuatkan booking mobil'], 422);
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
        if ($sppd->Status !== 'ACCEPTED') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Request hanya dapat ditutup setelah disetujui (ACCEPTED)',
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

        $path = public_path('template-pengajuan-sppd.docx');
        if (!file_exists($path)) {
            abort(404, 'Template not found');
        }

        $doc = new TemplateProcessor($path);
        $doc->setValues([
            'effectiveDate'    => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'docNo'            => $sppd->SPPD_No,
            'department'       => $sppd->Department,
            'requester'        => $sppd->Requester_Name,
            'destination'      => $sppd->Destination,
            'routeDestination' => $sppd->Route_Start . ' - ' . $sppd->Route_Finish,
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

    public function printPersetujuan(Request $request)
    {
        $sppd = DB::table('sppd')->where('ID', $request->ID)->first();
        if (!$sppd) {
            abort(404);
        }
        if ($sppd->Status !== 'ACCEPTED') {
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

    protected function downloadDoc(TemplateProcessor $doc, $filename)
    {
        $filename = str_replace(['/', '\\'], '_', $filename);
        $outputPath = storage_path('app/' . uniqid('sppd_', true) . '.docx');
        $doc->saveAs($outputPath);

        return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
    }

    protected function authorizeViewer($sppd)
    {
        $user = Auth::user();
        if ((int) $sppd->Requester_ID === (int) $user->id || (int) $sppd->Approver_ID === (int) $user->id) {
            return;
        }
        abort(403, 'Anda tidak berhak mengakses dokumen ini');
    }
}
