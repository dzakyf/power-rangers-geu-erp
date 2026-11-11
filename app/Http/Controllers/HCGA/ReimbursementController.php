<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReimbursementController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    public static function canChooseEmployee($user = null): bool
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return false;
        }
        $privilegedUsers = ['admin', 'user.hcga', 'admin.hris'];
        if (in_array(strtolower($user->username ?? ''), $privilegedUsers)) {
            return true;
        }
        if (method_exists($user, 'hasRole') && $user->hasRole('ADM')) {
            return true;
        }
        if (($user->Position ?? '') === 'Administrator' || ($user->Department ?? '') === 'HCGA') {
            return true;
        }
        return false;
    }

    public static function getEmployeeBankDetails($nik)
    {
        if (empty($nik)) {
            return null;
        }
        $emp = DB::table('employment')
            ->where('NIK', $nik)
            ->select('NIK', 'Name', 'Department', 'Payment_Reference', 'Payment_Reference2', 'Payment_Reference3', 'Payment_Reference_Branch')
            ->first();
        if (!$emp) {
            return null;
        }

        $bankName   = trim($emp->Payment_Reference ?? '');
        $bankAcc    = trim($emp->Payment_Reference2 ?? '');
        $bankAn     = trim($emp->Payment_Reference3 ?? '');
        $bankBranch = trim($emp->Payment_Reference_Branch ?? '');

        $bankUpper = strtoupper($bankName);
        $isBca = (strpos($bankUpper, 'BCA') !== false || strpos($bankUpper, 'CENTRAL ASIA') !== false);

        return [
            'bank_name'   => $bankName ?: '-',
            'bank_acc'    => $bankAcc ?: '-',
            'bank_an'     => $bankAn ?: ($emp->Name ?: '-'),
            'bank_branch' => $bankBranch ?: '-',
            'is_bca'      => $isBca,
            'display'     => $bankName ? ($bankName . ' - ' . $bankAcc . ($bankAn ? ' a/n ' . $bankAn : '')) : '-',
        ];
    }

    public function index()
    {
        return view('humanresources.reimbursement.index');
    }

    public function data()
    {
        if (!headers_sent()) {
            if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
                header("Content-type: application/xhtml+xml");
            } else {
                header("Content-type: text/xml");
            }
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $user = Auth::user();
        $nik = $user->Emp_ID;
        $nomor = 1;


        $getDepartment = DB::table('master_department')->get()->pluck('Department', 'Initial');

        $canSeeAll = self::canChooseEmployee($user);

        $query = DB::table('reimbursement')->where('Location', $user->Location)->orderBy('ID', 'DESC');
        if (!$canSeeAll) {
            $query->where(function ($q) use ($nik, $user) {
                if ($nik) {
                    $q->where('User_Responsibility_ID', $nik);
                }
                $q->orWhere('Created_By', $user->username);
            });
        }

        $Query = $query->get();

        echo "<rows>";
        foreach ($Query as $Fetch) {
            $bankInfo = $Fetch->Payment_Reference_No ? ($Fetch->Payment_Reference_No . ($Fetch->Payment_Acc_No ? ' - ' . $Fetch->Payment_Acc_No : '')) : '-';
            if ($bankInfo === '-' && $Fetch->User_Responsibility_ID) {
                $bDetail = self::getEmployeeBankDetails($Fetch->User_Responsibility_ID);
                if ($bDetail && $bDetail['bank_name'] !== '-') {
                    $bankInfo = $bDetail['bank_name'] . ($bDetail['bank_acc'] !== '-' ? ' - ' . $bDetail['bank_acc'] : '');
                }
            }

            echo "<row id=\"" . $Fetch->ID . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Reimbursement_No) . "</cell>";
            echo "<cell>" . ($Fetch->Reimbursement_Date ? date('d-m-Y', strtotime($Fetch->Reimbursement_Date)) : '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($getDepartment[$Fetch->Department] ?? $Fetch->Department ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->User_Responsibility_Name ?: $Fetch->User_Responsibility_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars($bankInfo) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Description) . "</cell>";
            echo "<cell>" . number_format((float) $Fetch->Total, 0, ',', '.') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
            echo "<cell>" . ($Fetch->submitted_date ? date('d-m-Y H:i', strtotime($Fetch->submitted_date)) : '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Created_By) . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public function create()
    {
        $user = Auth::user();
        $departments = DB::table('master_department')->orderBy('Department', 'ASC')->get();
        $Reimbursement_No = 'RMB/' . ($user->Location ?: 'GEU-HO') . '/' . date('ymd') . '-' . date('His');

        $canChooseEmployee = self::canChooseEmployee($user);
        $employees = [];
        if ($canChooseEmployee) {
            $employees = DB::table('employment')
                ->whereIn('Status', ['ACTIVE', 'Active'])
                ->select(
                    'NIK',
                    'Name',
                    'Department',
                    'Position',
                    'Payment_Reference',
                    'Payment_Reference2',
                    'Payment_Reference3',
                    'Payment_Reference_Branch'
                )
                ->orderBy('Name', 'ASC')
                ->get();
        }

        $defaultBank = self::getEmployeeBankDetails($user->Emp_ID) ?: [
            'bank_name'   => '',
            'bank_acc'    => '',
            'bank_an'     => $user->Name ?: $user->username,
            'bank_branch' => '',
            'is_bca'      => false,
            'display'     => 'Belum diisi',
        ];

        return view('humanresources.reimbursement.new', compact('user', 'departments', 'Reimbursement_No', 'canChooseEmployee', 'employees', 'defaultBank'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (trim($request->input('Department', '')) === '') {
            return response()->json(['status' => 'error', 'message' => 'Field Department wajib diisi'], 422);
        }
        if (trim($request->input('User_Responsibility_Name', '')) === '') {
            return response()->json(['status' => 'error', 'message' => 'Field User_Responsibility_Name wajib diisi'], 422);
        }
        if (trim($request->input('Reimbursement_Date', '')) === '') {
            return response()->json(['status' => 'error', 'message' => 'Field Reimbursement_Date wajib diisi'], 422);
        }
        if (trim($request->input('Description', '')) === '') {
            return response()->json(['status' => 'error', 'message' => 'Field Description wajib diisi'], 422);
        }

        // Tentukan NIK Pemohon
        $nik = trim($request->input('User_Responsibility_ID', ''));
        if (empty($nik)) {
            $nik = $user->Emp_ID ?: $user->username;
        }

        $dtls = [];
        $total = 0;
        foreach ($request->all() as $key => $val) {
            if (preg_match('/^Amount_(\d+)$/', $key, $m)) {
                $idx = $m[1];
                $remark = trim($request->input('Remark_' . $idx, ''));
                if (trim($val) === '' && $remark === '') continue;
                $nominal = (float) str_replace([',', '.'], ['', '.'], trim($val));
                if ($nominal > 0) {
                    $dtls[] = [
                        'Reference' => trim($request->input('Ref_' . $idx, '')),
                        'Remark'    => $remark,
                        'Amount'    => $nominal,
                    ];
                    $total += $nominal;
                }
            }
        }
        if (count($dtls) == 0 || $total <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Rincian biaya wajib diisi minimal satu baris'], 422);
        }

        // Dapatkan data bank dari input atau dari data master employment
        $bankName = trim($request->input('Bank_Name', ''));
        $bankAcc  = trim($request->input('Bank_Account_No', ''));
        if (empty($bankName) || empty($bankAcc)) {
            $bInfo = self::getEmployeeBankDetails($nik);
            if ($bInfo && $bInfo['bank_name'] !== '-') {
                $bankName = $bankName ?: $bInfo['bank_name'];
                $bankAcc  = $bankAcc ?: $bInfo['bank_acc'];
            }
        }

        // Cek struktur organisasi pemohon untuk penentuan approver legacy / fallback
        $getlevelsx = DB::table('organization_structure')->where('NIK', $nik)->first();
        if (!$getlevelsx && $user->Emp_ID) {
            $getlevelsx = DB::table('organization_structure')->where('NIK', $user->Emp_ID)->first();
        }

        $nama = trim($request->input('User_Responsibility_Name', '')) ?: ($user->Name ?: $user->username);
        $LSP = $user->Location ?: 'GEU-HO';

        $status = 'APPROVAL_VP';
        $link = 'approvalreimbursement2';
        $getupper = (object)[
            'Email' => "hcga@pt-geu.com",
            'Name'  => "HCGA Approver"
        ];

        if ($getlevelsx) {
            $userLevel = (int) ($getlevelsx->Level ?? 4);
            if ($userLevel >= 4) {
                $findUpper = DB::table('organization_structure')
                    ->where('Level', 3)
                    ->where('Department', trim($request->Department))
                    ->first();
                if ($findUpper) {
                    $getupper = $findUpper;
                }
                $status = 'APPROVAL_UPPER';
                $link = 'approvalreimbursement';
            } else {
                $findUpper = DB::table('organization_structure')
                    ->whereIn('Level', [1, 2])
                    ->first();
                if ($findUpper) {
                    $getupper = $findUpper;
                }
                $status = 'APPROVAL_VP';
                $link = 'approvalreimbursement2';
            }
        }

        $Reimbursement_No = 'RMB/' . $LSP . '/' . date('ymd') . '-' . date('His');
        $linkkey = md5(date('Ym') . '789213123122122');

        $attachmentId = null;
        if ($request->hasFile('Attachment_File')) {
            $file = $request->file('Attachment_File');
            $attachmentId = $this->uploadService->storeFile($file, 'file', 'reimbursement', $user->id, 'Bukti Nota / Kwitansi Reimbursement');
        }

        DB::beginTransaction();
        try {
            $rmbId = DB::table('reimbursement')->insertGetId([
                'Location'                  => $LSP,
                'Reimbursement_No'          => $Reimbursement_No,
                'Reference_No'              => trim($request->input('Reference_No', '')),
                'Attachment_File'           => $attachmentId,
                'User_Responsibility_ID'    => $nik,
                'User_Responsibility_Name'  => trim($request->User_Responsibility_Name),
                'User_Reference'            => $user->username,
                'Currency'                  => 'Rp',
                'Reimbursement_Date'        => $request->Reimbursement_Date,
                'Department'                => trim($request->Department),
                'Description'               => trim($request->Description),
                'Status'                    => $status,
                'Total'                     => $total,
                'Created_By'                => $user->username,
                'Created_Date'              => now(),
                'submitted_date'            => now(),
                'Payment_Type'              => 'TRANSFER',
                'Payment_Reference_No'      => $bankName ?: null,
                'Payment_Acc_No'            => $bankAcc ?: null,
            ]);

            foreach ($dtls as $d) {
                DB::table('reimbursement_dtl')->insert([
                    'Reimbursement_No'    => $Reimbursement_No,
                    'Reimbursement_Date'  => $request->Reimbursement_Date,
                    'Reference'           => $d['Reference'],
                    'Remark'              => $d['Remark'],
                    'Amount'              => $d['Amount'],
                    'Department'          => trim($request->Department),
                    'Created_By'          => $user->username,
                ]);
            }

            DB::table('reimbursement_log')->insert([
                'Location'      => $LSP,
                'Cash_No'       => $Reimbursement_No,
                'Transaction'   => 'REIMBURSEMENT',
                'Description'   => 'Request reimbursement diajukan',
                'Reference'     => trim($request->input('Reference_No', '')),
                'Status'        => $status,
                'Created_Date'  => now(),
                'Created_By'    => $user->username,
            ]);

            // Daftarkan ke Dynamic Approval Engine (ApprovalWorkflowService)
            try {
                $rmbRow = DB::table('reimbursement')->where('ID', $rmbId)->first();
                $rmbApprovalService = app(\App\Services\Approval\ReimbursementApprovalService::class);
                $rmbApprovalService->submit($rmbRow, $user);
            } catch (\Exception $apprEx) {
                Log::warning('ApprovalWorkflowService submission fallback for Reimbursement #' . $rmbId . ': ' . $apprEx->getMessage());
            }

            $subjek = "IMPORTANT : APPROVAL PERMINTAAN REIMBURSEMENT $Reimbursement_No # , Request By : $nama";
            $approvalUrl = url($link) . "?ID=$Reimbursement_No&linkkey=$linkkey&saya=$user->username";
            $badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>" . ($getupper->Name ?? '') . "</b><Br><br>
                    Mohon dilakukan approval untuk Permintaan Reimbursement dengan rincian sebagai berikut:<br><br>
                    No Permintaan : $Reimbursement_No<br><br>
                    User : " . trim($request->User_Responsibility_Name) . " / $nik<br><br>
                    Reference : " . trim($request->input('Reference_No', '')) . "<br><br>
                    Nilai : " . number_format($total, 0, ',', '.') . "<br><br>
                    Note : " . trim($request->Description) . "<br>
                    <br><br>
                    Anda dapat melakukan approval melalui link berikut ini :<a href='$approvalUrl'>KLIK DISINI</a><br><br>
                    Terima kasih<br>
                    GREEN ENERGI UTAMA
            </div>";

            DB::table('mail_logs')->insert([
                'Email_To'      => $getupper->Email,
                'Subject'       => $subjek,
                'Contents'      => $badan,
                'Status'        => 'UNSENT',
                'Inserted_Date' => now(),
                'Send_Date'     => now(),
            ]);

            DB::commit();

            Log::info('Reimbursement created: ' . $Reimbursement_No . ' by ' . $user->username . ' status=' . $status);

            return response()->json([
                'status'           => 'success',
                'message'          => 'Request reimbursement submitted',
                'Reimbursement_No' => $Reimbursement_No,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reimbursement create failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function detail(Request $request)
    {
        $user = Auth::user();
        $reimbursement = DB::table('reimbursement')->where('ID', $request->ID)->first();
        if (!$reimbursement) {
            abort(404);
        }

        $canSeeAll = self::canChooseEmployee($user);
        $isOwner = $reimbursement->User_Responsibility_ID == $user->Emp_ID
            || $reimbursement->Created_By == $user->username;
        if (!$isOwner && !$canSeeAll && !SettlementController::isHcgaVerifier() && !ReimbursementApprovalController::isApprover($user)) {
            abort(403, 'Anda tidak berhak mengakses data ini');
        }

        $dtls = DB::table('reimbursement_dtl')
            ->where('Reimbursement_No', $reimbursement->Reimbursement_No)
            ->orderBy('ID', 'ASC')
            ->get();
        $logs = DB::table('reimbursement_log')
            ->where('Cash_No', $reimbursement->Reimbursement_No)
            ->orderBy('ID', 'ASC')
            ->get();
        $department = DB::table('master_department')->where("Initial", $reimbursement->Department)->first();
        $attachment = $reimbursement->Attachment_File ? DB::table('meta_files')->where('ID', $reimbursement->Attachment_File)->first() : null;

        $bankDetails = self::getEmployeeBankDetails($reimbursement->User_Responsibility_ID);
        if (!$bankDetails || $bankDetails['bank_name'] === '-') {
            if ($reimbursement->Payment_Reference_No || $reimbursement->Payment_Acc_No) {
                $bName = $reimbursement->Payment_Reference_No ?: '-';
                $bAcc  = $reimbursement->Payment_Acc_No ?: '-';
                $bUpper = strtoupper($bName);
                $bankDetails = [
                    'bank_name'   => $bName,
                    'bank_acc'    => $bAcc,
                    'bank_an'     => $reimbursement->User_Responsibility_Name ?: '-',
                    'bank_branch' => '-',
                    'is_bca'      => (strpos($bUpper, 'BCA') !== false || strpos($bUpper, 'CENTRAL ASIA') !== false),
                    'display'     => $bName . ' - ' . $bAcc,
                ];
            }
        }

        return view('humanresources.reimbursement.detail', compact('reimbursement', 'dtls', 'logs', 'attachment', 'bankDetails', 'department'));
    }

    public function downloadAttachment(Request $request)
    {
        $reimbursement = DB::table('reimbursement')->where('ID', $request->ID)->first();
        if (!$reimbursement || !$reimbursement->Attachment_File) {
            abort(404, 'Berkas lampiran tidak ditemukan');
        }

        $user = Auth::user();
        $canSeeAll = self::canChooseEmployee($user);
        $isOwner = $reimbursement->User_Responsibility_ID == $user->Emp_ID || $reimbursement->Created_By == $user->username;
        if (!$isOwner && !$canSeeAll && !SettlementController::isHcgaVerifier() && !ReimbursementApprovalController::isApprover($user)) {
            abort(403, 'Anda tidak berhak mengakses berkas ini');
        }

        $meta = DB::table('meta_files')->where('ID', $reimbursement->Attachment_File)->first();
        if (!$meta) {
            abort(404, 'Data berkas tidak ditemukan di sistem');
        }

        $path = public_path('filevendor/' . $meta->Path);
        if (!file_exists($path)) {
            abort(404, 'File fisik tidak ditemukan pada storage server');
        }

        return response()->download($path, $meta->Filename);
    }
}
