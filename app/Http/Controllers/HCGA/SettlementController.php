<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class SettlementController extends Controller
{
    protected $generatorService;
    protected $uploaderService;

    public function __construct(GeneratorService $g, UploadService $u)
    {
        $this->generatorService = $g;
        $this->uploaderService  = $u;
    }

    public function index()
    {
        return view('humanresources.settlement.index');
    }

    public function data()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $canSeeAll = self::isHcgaVerifier() || \App\Http\Controllers\HCGA\SppdController::isAdministrator()
            || \App\Http\Controllers\HCGA\SppdController::canChooseEmployee(Auth::user());
        $nomor = 1;

        $Query = DB::table('settlement_sppd')->orderBy('ID', 'DESC');
        if (!$canSeeAll) {
            $Query->where('Requester_ID', Auth::user()->id);
        }

        echo "<rows>";
        foreach ($Query->get() as $Fetch) {
            $expenses = DB::table('settlement_sppd_expense')->where('Settlement_Sppd_ID', $Fetch->ID)->get();
            $total = 0;
            foreach ($expenses as $e) {
                $total += (float) $e->Cost;
            }
            if (isset($Fetch->Matrix_Amount) && (float) $Fetch->Matrix_Amount > 0) {
                $total += (float) $Fetch->Matrix_Amount;
            }

            $linkInvoice = $Fetch->Invoice_File
                ? "<a href='hrd-settlement-invoice?ID=$Fetch->ID' style='color:blue;' target='_blank'>Download</a>"
                : '-';
            $linkDoc = "<a href='hrd-settlement-print?ID=$Fetch->ID' style='color:blue;' target='_blank'>Download</a>";

            echo "<row id=\"" . $Fetch->ID . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->STTL_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Requester_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->SPPD_No ?: '-') . "</cell>";
            echo "<cell>" . count($expenses) . "</cell>";
            echo "<cell>" . number_format($total, 0, ',', '.') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
            echo "<cell>$linkInvoice</cell>";
            echo "<cell>$linkDoc</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public static function isMatrixAllowanceItem($desc)
    {
        $desc = trim((string) $desc);
        if ($desc === '') {
            return false;
        }

        // Cek pola deskripsi allowance matriks regulasi master SPPD
        $patterns = [
            '/^Uang\s+Makan/i',
            '/^Uang\s+Saku/i',
            '/^Penginapan(\s+★\d+)?\s*(\(Rp|\s*$)/i',
            '/^Tiket\s+PP\s*(\(maks\.|\s*$)/i',
            '/^Uang\s+Perbantuan\s*\/\s*Kos/i',
            '/^Bantuan\s+Kos/i',
            '/^Bantuan\s+Relokasi/i',
            '/^Uang\s+Perbantuan\s*\(Rp/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $desc)) {
                return true;
            }
        }

        // Cek kecocokan nama komponen dari master_sppd_components
        try {
            $components = DB::table('master_sppd_components')->pluck('name');
            foreach ($components as $name) {
                if (strcasecmp($desc, trim($name)) === 0) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return false;
    }

    public function create()
    {
        $user = Auth::user();
        $departments = DB::table('master_department')->orderBy('Department', 'ASC')->get();
        $counter = DB::table('settlement_sppd')->where('Created_Date', 'LIKE', date('Y-m') . '%')->count() + 1;
        $STTL_No = "STTL/GA/" . ($user->Location ?: 'GEU-HO') . "/" . date('ymdhis') . "-" . $counter;

        // Selectable SPPD references: requester's own (excluding REJECTED); administrator / HCGA sees all.
        $canSeeAll = \App\Http\Controllers\HCGA\SppdController::isAdministrator()
            || \App\Http\Controllers\HCGA\SppdController::canChooseEmployee($user);
        $sppdQuery = DB::table('sppd')
            ->leftJoin('users', 'users.id', '=', 'sppd.Requester_ID')
            ->orderBy('sppd.ID', 'DESC')
            ->where('sppd.Status', '!=', 'REJECTED');
        if (!$canSeeAll) {
            $sppdQuery->where('sppd.Requester_ID', $user->id);
        }
        $sppdList = $sppdQuery->get([
            'sppd.ID',
            'sppd.SPPD_No',
            'sppd.Destination',
            'sppd.Sppd_Type',
            'sppd.Estimated_Cost',
            'sppd.Department',
            'sppd.Route_Start',
            'sppd.Route_Finish',
            'sppd.Requester_Name',
            'sppd.Requester_NIK',
            'sppd.Requester_ID',
            'users.Bank_Name as Requester_Bank_Name',
            'users.Bank_Account_No as Requester_Bank_Account_No',
            'users.Bank_Account_Name as Requester_Bank_Account_Name',
        ]);

        // Load detail rincian biaya masing-masing SPPD
        // HANYA memunculkan biaya yang diinputkan manual di luar matriks regulasi master SPPD
        $sppdIds = $sppdList->pluck('ID')->all();
        $sppdCosts = [];
        $sppdMatrixCosts = [];
        if (!empty($sppdIds)) {
            $costs = DB::table('sppd_cost')->whereIn('SPPD_ID', $sppdIds)->get();
            foreach ($costs as $c) {
                if (self::isMatrixAllowanceItem($c->Description)) {
                    $sppdMatrixCosts[$c->SPPD_ID] = ($sppdMatrixCosts[$c->SPPD_ID] ?? 0) + (float) $c->Cost;
                    continue;
                }
                $sppdCosts[$c->SPPD_ID][] = [
                    'Expense'     => $c->Description,
                    'Cost'        => (float) $c->Cost,
                    'Description' => 'Biaya sesuai pengajuan SPPD (' . $c->Description . ')'
                ];
            }
        }

        // Rekening kantor aktif dari tabel bank_acc untuk pengembalian Lebih Bayar
        $companyBanks = DB::table('bank_acc')
            ->where('Status', 'ACTIVE')
            ->where('Currency', 'RP')
            ->whereNotIn('Acc_No', ['CASH', 'DUMMY', ''])
            ->orderBy('ID', 'ASC')
            ->get(['ID', 'Bank_Name', 'Acc_No', 'Acc_Hold', 'Bank_Branch', 'Remark']);

        // Peta rekening karyawan berdasarkan Emp_ID
        $userBanksMap = DB::table('users')
            ->whereNotNull('Emp_ID')
            ->where('Emp_ID', '!=', '')
            ->get(['id', 'Emp_ID', 'Name', 'username', 'Bank_Name', 'Bank_Account_No', 'Bank_Account_Name'])
            ->keyBy('Emp_ID');

        return view('humanresources.settlement.new', compact('user', 'departments', 'STTL_No', 'sppdList', 'sppdCosts', 'sppdMatrixCosts', 'companyBanks', 'userBanksMap'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (trim($request->input('Department', '')) === '') {
            return response()->json(['status' => 'error', 'message' => 'Field Department wajib diisi'], 422);
        }
        if (trim($request->input('Requester_Name', '')) === '') {
            return response()->json(['status' => 'error', 'message' => 'Field Requester_Name wajib diisi'], 422);
        }

        $expenses = [];
        $total = 0;
        foreach ($request->all() as $key => $val) {
            if (preg_match('/^Expense_(\d+)$/', $key, $m)) {
                $idx = $m[1];
                $costRaw = trim($request->input('Cost_' . $idx, ''));
                if (trim($val) === '' && $costRaw === '') continue;
                $nominal = (float) str_replace([',', '.'], ['', '.'], $costRaw);
                if ($nominal > 0) {
                    $expenses[] = [
                        'Expense'     => trim($val),
                        'Cost'        => $nominal,
                        'Description' => trim($request->input('Desc_' . $idx, '')),
                    ];
                    $total += $nominal;
                }
            }
        }
        if (count($expenses) == 0 || $total <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Rincian penggunaan biaya wajib diisi minimal satu baris'], 422);
        }

        if (!$request->hasFile('Invoice_File')) {
            return response()->json(['status' => 'error', 'message' => 'Berkas Invoice wajib diupload'], 422);
        }
        $file = $request->file('Invoice_File');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
            return response()->json(['status' => 'error', 'message' => 'Berkas Invoice harus berupa PDF/JPG/PNG'], 422);
        }
        if ($file->getSize() > 5242880) {
            return response()->json(['status' => 'error', 'message' => 'Ukuran berkas Invoice maksimal 5MB'], 422);
        }

        // Optional SPPD reference
        $sppdRefId = null;
        $sppdRefNo = null;
        $sppdRef = trim($request->input('SPPD_ID', ''));
        if ($sppdRef !== '') {
            $sppdRow = DB::table('sppd')->where('ID', $sppdRef)->first();
            if (!$sppdRow) {
                return response()->json(['status' => 'error', 'message' => 'Referensi SPPD tidak ditemukan'], 422);
            }
            if ((int) $sppdRow->Requester_ID !== (int) $user->id && !\App\Http\Controllers\HCGA\SppdController::isAdministrator()) {
                return response()->json(['status' => 'error', 'message' => 'Referensi SPPD bukan milik Anda'], 422);
            }
            $sppdRefId = (int) $sppdRow->ID;
            $sppdRefNo = $sppdRow->SPPD_No;
        }

        $validator = self::getDeptHead();
        $validatorId   = $validator ? $validator->id : null;
        $validatorName = $validator ? ($validator->Name ?: $validator->username) : '';

        $nama = $user->Name ?: $user->username;
        $nik  = $user->Emp_ID ?: '';

        $counter = DB::table('settlement_sppd')->where('Created_Date', 'LIKE', date('Y-m') . '%')->count() + 1;
        $STTL_No = "STTL/GA/" . ($user->Location ?: 'GEU-HO') . "/" . date('ymdhis') . "-" . $counter;

        $advanceRaw = trim($request->input('Advance_Nominal', ''));
        $advance = $advanceRaw !== '' ? (float) str_replace([',', '.'], ['', '.'], $advanceRaw) : null;

        // Hitung total tunjangan matriks SPPD otomatis jika ada referensi SPPD
        $matrixAmount = 0;
        if ($sppdRefId) {
            $sppdCosts = DB::table('sppd_cost')->where('SPPD_ID', $sppdRefId)->get();
            foreach ($sppdCosts as $sc) {
                if (self::isMatrixAllowanceItem($sc->Description)) {
                    $matrixAmount += (float) $sc->Cost;
                }
            }
        }

        DB::beginTransaction();
        try {
            $metaId = $this->uploaderService->storeFile($file, 'file', 'settlement_sppd', $user->id, 'Invoice Settlement SPPD');

            $settlementId = DB::table('settlement_sppd')->insertGetId([
                'STTL_No'         => $STTL_No,
                'Requester_ID'    => $user->id,
                'Requester_Name'  => $nama,
                'Requester_NIK'   => $nik,
                'Department'      => trim($request->Department),
                'SPPD_ID'         => $sppdRefId,
                'SPPD_No'         => $sppdRefNo,
                'Invoice_File'    => $metaId,
                'Advance_Nominal' => $advance,
                'Matrix_Amount'   => $matrixAmount,
                'Bank'            => trim($request->input('Bank', '')),
                'Status'          => 'SUBMITTED',
                'Validator_ID'    => $validatorId,
                'Validator_Name'  => $validatorName,
                'Created_By'      => $user->username,
                'Created_Date'    => now(),
            ]);

            foreach ($expenses as $e) {
                DB::table('settlement_sppd_expense')->insert([
                    'Settlement_Sppd_ID' => $settlementId,
                    'Expense'            => $e['Expense'],
                    'Cost'               => $e['Cost'],
                    'Description'        => $e['Description'],
                ]);
            }

            DB::table('settlement_sppd_log')->insert([
                'Settlement_Sppd_ID' => $settlementId,
                'Action'             => 'SUBMITTED',
                'Actor_ID'           => $user->id,
                'Actor_Name'         => $nama,
                'Note'               => 'Request settlement diajukan',
                'Created_At'         => now(),
            ]);

            // Daftarkan ke Dynamic Approval Engine (ApprovalWorkflowService) via SettlementApprovalService
            try {
                $approvalService = app(\App\Services\Approval\SettlementApprovalService::class);
                $sttlRow = DB::table('settlement_sppd')->where('ID', $settlementId)->first();
                $approvalService->submit($sttlRow, $user);
            } catch (\Exception $apprEx) {
                Log::warning('ApprovalWorkflowService submission fallback for Settlement #' . $settlementId . ': ' . $apprEx->getMessage());
            }

            DB::commit();

            Log::info('Settlement created: ' . $STTL_No . ' by ' . $user->username);

            return response()->json([
                'status'  => 'success',
                'message' => 'Request settlement submitted',
                'STTL_No' => $STTL_No,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Settlement create failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function detail(Request $request)
    {
        $settlement = DB::table('settlement_sppd')->where('ID', $request->ID)->first();
        if (!$settlement) {
            abort(404);
        }

        $user = Auth::user();
        $isRequester = (int) $settlement->Requester_ID === (int) $user->id;
        if (!$isRequester && !self::isHcgaVerifier() && !\App\Http\Controllers\HCGA\SppdController::isAdministrator()) {
            abort(403, 'Anda tidak berhak mengakses data ini');
        }

        $expenses = DB::table('settlement_sppd_expense')->where('Settlement_Sppd_ID', $settlement->ID)->get();
        $logs = DB::table('settlement_sppd_log')->where('Settlement_Sppd_ID', $settlement->ID)->orderBy('ID', 'ASC')->get();
        $invoice = $settlement->Invoice_File ? DB::table('meta_files')->where('ID', $settlement->Invoice_File)->first() : null;
        $canClose = $isRequester && $settlement->Status == 'APPROVED';

        return view('humanresources.settlement.detail', compact('settlement', 'expenses', 'logs', 'invoice', 'canClose'));
    }

    public function close(Request $request)
    {
        $user = Auth::user();
        $settlement = DB::table('settlement_sppd')->where('ID', $request->ID)->first();

        if (!$settlement) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }
        if ((int) $settlement->Requester_ID !== (int) $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Hanya pengaju yang dapat menutup request ini'], 403);
        }
        if ($settlement->Status !== 'APPROVED') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Request hanya dapat ditutup setelah diverifikasi (APPROVED)',
            ], 422);
        }

        DB::beginTransaction();
        try {
            DB::table('settlement_sppd')->where('ID', $settlement->ID)->update([
                'Status'       => 'CLOSED',
                'Closed_At'    => now(),
                'Updated_By'   => $user->username,
                'Updated_Date' => now(),
            ]);

            DB::table('settlement_sppd_log')->insert([
                'Settlement_Sppd_ID' => $settlement->ID,
                'Action'             => 'CLOSED',
                'Actor_ID'           => $user->id,
                'Actor_Name'         => $user->Name ?: $user->username,
                'Note'               => 'Request settlement ditutup oleh pengaju',
                'Created_At'         => now(),
            ]);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Request settlement closed'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Settlement close failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getInvoice(Request $request)
    {
        $settlement = DB::table('settlement_sppd')->where('ID', $request->ID)->first();
        if (!$settlement || !$settlement->Invoice_File) {
            abort(404);
        }

        $user = Auth::user();
        if ((int) $settlement->Requester_ID !== (int) $user->id && !self::isHcgaVerifier() && !\App\Http\Controllers\HCGA\SppdController::isAdministrator()) {
            abort(403, 'Anda tidak berhak mengakses berkas ini');
        }

        $meta = DB::table('meta_files')->where('ID', $settlement->Invoice_File)->first();
        if (!$meta) {
            abort(404);
        }

        $path = public_path('filevendor/' . $meta->Path);
        if (!file_exists($path)) {
            abort(404, 'Berkas tidak ditemukan');
        }

        return response()->download($path, $meta->Filename);
    }

    public function printSettlement(Request $request)
    {
        $settlement = DB::table('settlement_sppd')->where('ID', $request->ID)->first();
        if (!$settlement) {
            abort(404);
        }

        $user = Auth::user();
        if ((int) $settlement->Requester_ID !== (int) $user->id && !self::isHcgaVerifier() && !\App\Http\Controllers\HCGA\SppdController::isAdministrator()) {
            abort(403, 'Anda tidak berhak mengakses dokumen ini');
        }

        $path = public_path('template-hr-settlement-sppd.docx');
        if (!file_exists($path)) {
            abort(404, 'Template not found');
        }

        $requesterUser = DB::table('users')->where('id', $settlement->Requester_ID)->first();
        $position = $requesterUser ? ($requesterUser->Position ?: $requesterUser->Priv_ID) : '';

        $expenses = DB::table('settlement_sppd_expense')->where('Settlement_Sppd_ID', $settlement->ID)->get();
        $total = 0;
        foreach ($expenses as $e) {
            $total += (float) $e->Cost;
        }

        // Akumulasikan tunjangan matriks SPPD yang di-carry over dari regulasi matriks
        $matrixAmount = isset($settlement->Matrix_Amount) ? (float) $settlement->Matrix_Amount : 0;
        $total += $matrixAmount;

        $money = function ($val) {
            return $this->generatorService->rupiahFormatter((float) $val);
        };

        $advance = $settlement->Advance_Nominal !== null ? (float) $settlement->Advance_Nominal : null;

        $doc = new TemplateProcessor($path);
        $doc->setValues([
            'effectiveDate'         => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'checkedBy'             => $settlement->Validator_Name ?: '',
            'requester'             => $settlement->Requester_Name,
            'nik'                   => $settlement->Requester_NIK ?: '',
            'position'              => $position,
            'department'            => $settlement->Department,
            'nominal'               => $advance !== null ? $money($advance) : '',
            'spelledNominal'        => $advance !== null ? ucfirst($this->generatorService->numberToWords((int) $advance)) . ' rupiah' : '',
            'totalCost'             => $money($total),
            'differenceCost'        => $advance !== null ? $money($total - $advance) : '',
            'returnedCost'          => $advance !== null
                ? ($advance > $total
                    ? $money($advance - $total) . ' (Pengembalian ke perusahaan)'
                    : $money($total - $advance) . ' (Kekurangan ke karyawan)')
                : '',
            'bank'                  => $settlement->Bank ?: '',
            'approverDeptHeadName'  => $settlement->Validator_Name ?: '',
            'approverHcgaName'      => $settlement->Validator_Name ?: '',
            'resquesterPosition'    => $position,
        ]);

        if ($expenses->count() > 0) {
            $rows = $expenses->map(function ($e) use ($money) {
                return [
                    'expense'     => $e->Expense ?: '',
                    'expenseCost' => $money($e->Cost),
                    'description' => $e->Description ?: '',
                ];
            })->values()->toArray();

            $doc->cloneRowAndSetValues('expense', $rows);
        }

        $filename = 'Form-Settlement-' . $settlement->STTL_No . '.docx';
        $filename = str_replace(['/', '\\'], '_', $filename);
        $outputPath = storage_path('app/' . uniqid('sttl_', true) . '.docx');
        $doc->saveAs($outputPath);

        return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
    }

    public static function getDeptHead()
    {
        return DB::table('users')
            ->where('Priv_ID', 'HEAD')
            ->where('Department', 'HCGA')
            ->where('Status', 'ACTIVE')
            ->orderBy('id', 'ASC')
            ->first();
    }

    public static function isDeptHead()
    {
        if (!Auth::check()) {
            return false;
        }
        $exists = DB::table('users')
            ->where('id', Auth::user()->id)
            ->where('Priv_ID', 'HEAD')
            ->where('Department', 'HCGA')
            ->where('Status', 'ACTIVE')
            ->count();
        return $exists > 0;
    }

    public static function isHcgaVerifier()
    {
        return Auth::check()
            && (Auth::user()->Department === 'HCGA' || Auth::user()->username === 'admin')
            && in_array(Auth::user()->Priv_ID, ['MANAGER', 'HCGA', 'HEAD', 'ADMIN'], true);
    }
}
