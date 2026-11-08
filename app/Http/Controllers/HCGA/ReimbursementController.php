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
    public function index()
    {
        return view('humanresources.reimbursement.index');
    }

    public function data()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $nik = Auth::user()->Emp_ID;
        $nomor = 1;

        $Query = DB::table('reimbursement')
            ->where('User_Responsibility_ID', $nik)
            ->orderBy('ID', 'DESC')
            ->get();

        echo "<rows>";
        foreach ($Query as $Fetch) {
            echo "<row id=\"" . $Fetch->ID . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Reimbursement_No) . "</cell>";
            echo "<cell>" . ($Fetch->Reimbursement_Date ? date('d-m-Y', strtotime($Fetch->Reimbursement_Date)) : '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
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

        return view('humanresources.reimbursement.new', compact('user', 'departments', 'Reimbursement_No'));
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

        $nik = $user->Emp_ID;
        $getlevelsx = DB::table('organization_structure')->where('NIK', $nik)->first();
        if (!$getlevelsx) {
            return response()->json(['status' => 'error', 'message' => 'Profil organisasi tidak ditemukan, hubungi HCGA'], 422);
        }

        $lepel = 0;
        foreach (range(1, 8) as $lv) {
            $col = 'Level' . $lv;
            if ((int) $getlevelsx->{$col} == 1) {
                $lepel = $lv;
                break;
            }
        }
        $upper = $lepel - 1;

        $nama = $user->Name ?: $user->username;
        $LSP = $user->Location ?: 'GEU-HO';

        if ($upper == 3) {
            $getupper = DB::table('organization_structure')
                ->where('Level3', 1)
                ->where('Department', trim($request->Department))
                ->where('Alerted', 1)
                ->first();
            $status = 'APPROVAL_UPPER';
            $link = 'approvalreimbursement';
        } else {
            $getupper = DB::table('organization_structure')
                ->where('Level2', 1)
                ->where('Department', $getlevelsx->divisi)
                ->where('Alerted', 1)
                ->first();
            $status = 'APPROVAL_VP';
            $link = 'approvalreimbursement2';
        }

        // if (!$getupper || !$getupper->Email) {
        //     return response()->json(['status' => 'error', 'message' => 'Approver (superior) tidak ditemukan, hubungi HCGA'], 422);
        // }
        $getupper = (object)[
            'Email' => "test@mail.com",
            'Name' => "testing approver"
        ];

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

        $isOwner = $reimbursement->User_Responsibility_ID == $user->Emp_ID
            || $reimbursement->Created_By == $user->username;
        if (!$isOwner && !SettlementController::isHcgaVerifier()) {
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
        $attachment = $reimbursement->Attachment_File ? DB::table('meta_files')->where('ID', $reimbursement->Attachment_File)->first() : null;

        return view('humanresources.reimbursement.detail', compact('reimbursement', 'dtls', 'logs', 'attachment'));
    }

    public function downloadAttachment(Request $request)
    {
        $reimbursement = DB::table('reimbursement')->where('ID', $request->ID)->first();
        if (!$reimbursement || !$reimbursement->Attachment_File) {
            abort(404, 'Berkas lampiran tidak ditemukan');
        }

        $user = Auth::user();
        $isOwner = $reimbursement->User_Responsibility_ID == $user->Emp_ID || $reimbursement->Created_By == $user->username;
        if (!$isOwner && !SettlementController::isHcgaVerifier() && !ReimbursementApprovalController::isApprover($user)) {
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
