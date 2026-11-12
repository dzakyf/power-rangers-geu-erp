<?php

namespace App\Http\Controllers\HCGA;

use App\Exceptions\ApprovalWorkflowException;
use App\Http\Controllers\Controller;
use App\Services\Approval\RequisitionApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class RequisitionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $location = $user->Location;
        $priv = $user->Priv_ID;
        $username = $user->username;

        $baseQuery = DB::table('requisition')->where('Location', $location);
        if ($priv != 'HCGA' && $priv != 'ADM') {
            $baseQuery->where('Created_By', $username);
        }

        $allActive = (clone $baseQuery)
            ->whereNotIn('Status', ['CLOSED', 'CANCELED'])
            ->get();

        $totalActive = $allActive->count();
        $totalOnProgress = $allActive->whereIn('Status', ['CREATED', 'ON_PROGRESS', 'WAITING_APPROVAL', 'PENDING'])->count();
        $totalApproved = $allActive->where('Status', 'APPROVED')->count();
        $totalRejected = $allActive->where('Status', 'REJECTED')->count();
        $totalHeadcount = $allActive->sum('Qty');
        $urgentCount = $allActive->where('Priority', 'URGENT')->count();

        // Ambil daftar policy dan step approval REQUISITION dari tabel approval_policy & approval_policy_step
        $requisitionPolicies = DB::table('approval_policy')
            ->where('Request_Type', 'REQUISITION')
            ->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")
            ->orderBy('ID', 'asc')
            ->get();

        foreach ($requisitionPolicies as $pol) {
            $pol->steps = DB::table('approval_policy_step')
                ->where('Policy_ID', $pol->ID)
                ->orderBy('Stage_Order', 'asc')
                ->orderBy('Level_Number', 'asc')
                ->orderBy('ID', 'asc')
                ->get();
        }

        return view('humanresources.recruitment.requisition', compact(
            'location',
            'username',
            'totalActive',
            'totalOnProgress',
            'totalApproved',
            'totalRejected',
            'totalHeadcount',
            'urgentCount',
            'requisitionPolicies'
        ));
    }

    public function indexXml()
    {
        //
    }

    /**
     * Tampilkan form pengajuan PTK baru (partial view)
     */
    public function create()
    {
        $user = Auth::user();
        $location = $user->Location;
        $username = $user->username;

        $positions = DB::table('master_position')->orderBy('Position', 'asc')->get();
        $workCalendar = DB::table('work_calendars')->get();
        $reqCount = DB::table('requisition')->where('Created_Date', 'LIKE', date('Y') . '%')->count() + 1;
        $taskId = 'REQ/' . $location . '/' . date('Ymdhis') . '-' . sprintf('%02d', $reqCount);
        $locations = DB::table('location')->get();

        $policies = DB::table('approval_policy')
            ->where('Request_Type', 'REQUISITION')
            ->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")
            ->orderBy('ID', 'asc')
            ->get();

        foreach ($policies as $pol) {
            $pol->steps = DB::table('approval_policy_step')
                ->where('Policy_ID', $pol->ID)
                ->orderBy('Stage_Order', 'asc')
                ->orderBy('Level_Number', 'asc')
                ->orderBy('ID', 'asc')
                ->get();
        }

        return view('humanresources.recruitment.requisition-new', compact(
            'location',
            'username',
            'positions',
            'workCalendar',
            'taskId',
            'locations',
            'policies'
        ));
    }

    /**
     * Tampilkan detail PTK (partial view modal/container)
     */
    public function detail(Request $request)
    {
        $id = $request->get('ID') ?? $request->get('id');
        $taskId = $request->get('Task_ID') ?? $request->get('Task');

        $query = DB::table('requisition');
        if (!empty($id)) {
            $query->where('ID', $id);
        } elseif (!empty($taskId)) {
            $query->where('Task_ID', $taskId);
        } else {
            abort(404, 'Data PTK tidak ditemukan');
        }

        $ptk = $query->first();
        if (!$ptk) {
            return response('<div class="alert alert-danger m-3"><i class="fa fa-exclamation-triangle me-2"></i> Data PTK tidak ditemukan.</div>', 404);
        }

        // Master Position
        $position = DB::table('master_position')
            ->where('Position', $ptk->Job_Position)
            ->where('Department', $ptk->Department)
            ->first();

        // Work Calendar
        $workCalendar = DB::table('work_calendars')->where('ID', $ptk->Work_Schedule)->first();

        // Approval timeline/tasks
        $approvalTasks = [];
        $approvalRequest = null;
        $policySteps = [];
        $matchedPolicy = null;

        if (!empty($ptk->Approval_Request_ID)) {
            $approvalRequest = DB::table('approval_request')->where('ID', $ptk->Approval_Request_ID)->first();
            $approvalTasks = DB::table('approval_request_task as t')
                ->leftJoin('users as u_act', 'u_act.id', '=', 't.Acted_By')
                ->where('t.Request_ID', $ptk->Approval_Request_ID)
                ->select('t.*', 'u_act.username as Acted_By_Username', 'u_act.Name as Acted_By_Name')
                ->orderBy('t.Stage_Order', 'asc')
                ->orderBy('t.ID', 'asc')
                ->get();

            foreach ($approvalTasks as $task) {
                $candidates = DB::table('approval_task_candidate as c')
                    ->join('users as u', 'u.id', '=', 'c.User_Id')
                    ->where('c.Task_ID', $task->ID)
                    ->where('c.Status', 'ACTIVE')
                    ->pluck('u.username')
                    ->toArray();
                $task->Candidate_Usernames = !empty($candidates) ? implode(', ', $candidates) : '-';
            }

            if ($approvalRequest && !empty($approvalRequest->Policy_ID)) {
                $matchedPolicy = DB::table('approval_policy')->where('ID', $approvalRequest->Policy_ID)->first();
                $policySteps = DB::table('approval_policy_step')
                    ->where('Policy_ID', $approvalRequest->Policy_ID)
                    ->orderBy('Stage_Order', 'asc')
                    ->orderBy('Level_Number', 'asc')
                    ->get();
            }
        }

        // Jika belum punya Policy_ID dari request, cari policy REQUISITION dari tabel approval_policy
        if (empty($policySteps)) {
            $posLevel = $position ? (int) $position->Level : 4;
            $dept = $ptk->Department ?? '';
            $policies = DB::table('approval_policy')
                ->where('Request_Type', 'REQUISITION')
                ->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")
                ->get();

            foreach ($policies as $pol) {
                if (!empty($pol->Department) && strcasecmp($pol->Department, $dept) !== 0) {
                    continue;
                }
                $conds = !empty($pol->Conditions) ? json_decode($pol->Conditions, true) : [];
                $isMatch = true;
                if (is_array($conds)) {
                    foreach ($conds as $c) {
                        if (($c['field'] ?? '') === 'Position_Level') {
                            $vals = (array) ($c['value'] ?? []);
                            $op = strtolower($c['operator'] ?? 'in');
                            if ($op === 'in' && !in_array($posLevel, $vals)) $isMatch = false;
                            if ($op === 'not_in' && in_array($posLevel, $vals)) $isMatch = false;
                        }
                    }
                }
                if ($isMatch) {
                    $matchedPolicy = $pol;
                    $policySteps = DB::table('approval_policy_step')
                        ->where('Policy_ID', $pol->ID)
                        ->orderBy('Stage_Order', 'asc')
                        ->orderBy('Level_Number', 'asc')
                        ->get();
                    break;
                }
            }
        }

        // Legacy logs jika ada
        $legacyLogs = DB::table('requisition_approval')
            ->where('Task_ID', $ptk->Task_ID)
            ->orderBy('ID', 'asc')
            ->get();

        return view('humanresources.recruitment.requisition-detail', compact(
            'ptk',
            'position',
            'workCalendar',
            'approvalRequest',
            'approvalTasks',
            'legacyLogs',
            'matchedPolicy',
            'policySteps'
        ));
    }

    public function print(Request $request)
    {
        $taskParam = $request->get('Task') ?? $request->get('Task_ID');
        $idParam = $request->get('ID') ?? $request->get('id');

        $query = DB::table('requisition');
        if (!empty($taskParam)) {
            $query->where('Task_ID', $taskParam);
        } elseif (!empty($idParam)) {
            $query->where('ID', $idParam);
        } else {
            abort(404, 'Parameter Task atau ID PTK wajib diisi.');
        }

        $ptk = $query->first();
        if (!$ptk) {
            abort(404, 'Data PTK tidak ditemukan.');
        }

        // 1. Master Position & Hierarki Jabatan
        $position = DB::table('master_position')
            ->where('Position', $ptk->Job_Position)
            ->where('Department', $ptk->Department)
            ->first();
        if (!$position) {
            $position = DB::table('master_position')->where('Position', $ptk->Job_Position)->first();
        }

        $posLevel = $position ? (int) $position->Level : 4;
        $isStaff = $posLevel <= 4;
        $isNonStaff = $posLevel > 4;

        // Cari atasan langsung (Bertanggung jawab kepada) & bawahan (Membawahi)
        $bertanggungJawabKepada = '';
        $membawahi = '';
        if ($position) {
            if (!empty($position->Parent_Position_ID)) {
                $parentPos = DB::table('master_position')->where('ID', $position->Parent_Position_ID)->first();
                if ($parentPos) {
                    $bertanggungJawabKepada = $parentPos->Position;
                }
            }
            if ($bertanggungJawabKepada === '') {
                $parentUpper = DB::table('master_position')
                    ->where('Department', $ptk->Department)
                    ->where('Level', '<', $posLevel)
                    ->orderBy('Level', 'desc')
                    ->first();
                if ($parentUpper) {
                    $bertanggungJawabKepada = $parentUpper->Position;
                }
            }

            $subordinates = DB::table('master_position')
                ->where('Parent_Position_ID', $position->ID)
                ->pluck('Position')
                ->toArray();
            if (!empty($subordinates)) {
                $membawahi = implode(', ', $subordinates);
            }
        }

        // 2. Site / Penempatan
        $siteCode = $ptk->Placement ?: ($ptk->Location ?: '');
        $locRow = $siteCode ? DB::table('location')->where('Location_ID', $siteCode)->first() : null;
        $site = $locRow ? ($locRow->Name . ' (' . $locRow->Location_ID . ')') : $siteCode;

        // 3. Field PTK dasar
        $noPtk = $ptk->Task_ID ?? '';
        $jumlahPermintaan = !empty($ptk->Qty) ? ($ptk->Qty . ' Orang') : '';
        $mulaiTanggal = !empty($ptk->Start_Date) ? date('d M Y', strtotime($ptk->Start_Date)) : '';

        $allocation = strtoupper((string) ($ptk->Allocation ?? ''));
        $isKaryawanBaru = ($allocation === 'NEW');
        $isPenggantiKaryawan = ($allocation === 'REPLACEMENT');

        $reqType = (string) ($ptk->Request_Type ?? '');
        $reqDesc = strtolower((string) ($ptk->Request_Desc ?? ''));
        $isPeningkatanVolume = ($reqType === '1' || strpos($reqDesc, 'volume') !== false);
        $isPenggantiKeluar = ($reqType === '2' || strpos($reqDesc, 'pengganti') !== false || strpos($reqDesc, 'keluar') !== false);

        $namaJabatan = $ptk->Job_Position ?? '';

        // 4. Uraian Tugas (5 baris minimal)
        $rawDesc = trim((string) ($ptk->Description ?? ''));
        $lines = $rawDesc !== '' ? preg_split('/\r\n|\r|\n/', $rawDesc) : [];
        $lines = array_values(array_filter(array_map('trim', $lines), function ($v) { return $v !== ''; }));
        $tugasList = array_pad($lines, 5, '');

        // 5. Persyaratan Khusus (parsing pintar atau fallback)
        $pendidikan = '';
        $pengalaman = '';
        $syaratKhusus = '';
        $rawReq = trim((string) ($ptk->Requirement ?? ''));
        if ($rawReq !== '') {
            $reqLines = preg_split('/\r\n|\r|\n/', $rawReq);
            $otherReqs = [];
            foreach ($reqLines as $rLine) {
                $rTrim = trim($rLine);
                if ($rTrim === '') continue;
                if (preg_match('/^(?:[-*\d.]+\s*)?pendidikan\s*[:\-]\s*(.+)$/i', $rTrim, $m)) {
                    $pendidikan = trim($m[1]);
                } elseif (preg_match('/^(?:[-*\d.]+\s*)?pengalaman\s*[:\-]\s*(.+)$/i', $rTrim, $m)) {
                    $pengalaman = trim($m[1]);
                } elseif (preg_match('/^(?:[-*\d.]+\s*)?(?:syarat\s*khusus|kualifikasi|keahlian)\s*[:\-]\s*(.+)$/i', $rTrim, $m)) {
                    $otherReqs[] = trim($m[1]);
                } else {
                    $otherReqs[] = $rTrim;
                }
            }
            $syaratKhusus = implode('; ', $otherReqs);
        }

        // 6. Resolusi Approval Policy & Step untuk Blok Tanda Tangan Dinamis
        $creatorUser = !empty($ptk->Created_By)
            ? DB::table('users')->where('username', $ptk->Created_By)->first()
            : null;
        $creatorDisplayName = $creatorUser ? ($creatorUser->Name ?: $creatorUser->username) : ($ptk->Created_By ?? '');
        $creatorEmp = ($creatorUser && !empty($creatorUser->Emp_ID))
            ? DB::table('employment')->where('NIK', $creatorUser->Emp_ID)->first()
            : null;
        $creatorPositionLabel = $creatorEmp && !empty($creatorEmp->Position)
            ? $creatorEmp->Position
            : ('Pemohon (' . ($ptk->Department ?? 'Dept') . ')');

        $preparedDate = !empty($ptk->Created_Date) ? date('d/m/Y', strtotime($ptk->Created_Date)) : '';
        $preparedQrUri = !empty($creatorDisplayName)
            ? $this->qrDataUri("PREPARED BY\nName: {$creatorDisplayName}\nPTK: {$noPtk}\nDate: {$preparedDate}")
            : null;

        $preparedBy = [
            'role_header'    => 'Disiapkan oleh',
            'step_label'     => $creatorPositionLabel,
            'name'           => $creatorDisplayName,
            'date'           => $preparedDate,
            'status'         => 'SUBMITTED',
            'position_code'  => '',
            'qr_uri'         => $preparedQrUri,
        ];

        $approvalTasks = collect();
        $approvalRequest = null;
        $policySteps = collect();
        $matchedPolicy = null;

        if (!empty($ptk->Approval_Request_ID)) {
            $approvalRequest = DB::table('approval_request')->where('ID', $ptk->Approval_Request_ID)->first();
            $approvalTasks = DB::table('approval_request_task as t')
                ->leftJoin('users as u_act', 'u_act.id', '=', 't.Acted_By')
                ->where('t.Request_ID', $ptk->Approval_Request_ID)
                ->select('t.*', 'u_act.username as Acted_By_Username', 'u_act.Name as Acted_By_Name')
                ->orderBy('t.Stage_Order', 'asc')
                ->orderBy('t.ID', 'asc')
                ->get();

            foreach ($approvalTasks as $task) {
                $cands = DB::table('approval_task_candidate as c')
                    ->join('users as u', 'u.id', '=', 'c.User_Id')
                    ->where('c.Task_ID', $task->ID)
                    ->where('c.Status', 'ACTIVE')
                    ->select('u.Name', 'u.username')
                    ->orderBy('c.ID', 'asc')
                    ->get();
                $task->Candidate_Names = $cands->map(function ($u) {
                    return $u->Name ?: $u->username;
                })->toArray();
            }

            if ($approvalRequest && !empty($approvalRequest->Policy_ID)) {
                $matchedPolicy = DB::table('approval_policy')->where('ID', $approvalRequest->Policy_ID)->first();
                $policySteps = DB::table('approval_policy_step')
                    ->where('Policy_ID', $approvalRequest->Policy_ID)
                    ->orderBy('Stage_Order', 'asc')
                    ->orderBy('Level_Number', 'asc')
                    ->get();
            }
        }

        // Jika belum punya Policy_ID dari request, cari policy REQUISITION yang cocok dengan posisi & departemen
        if ($policySteps->isEmpty()) {
            $dept = $ptk->Department ?? '';
            $policies = DB::table('approval_policy')
                ->where('Request_Type', 'REQUISITION')
                ->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")
                ->orderBy('ID', 'asc')
                ->get();

            foreach ($policies as $pol) {
                if (!empty($pol->Department) && strcasecmp($pol->Department, $dept) !== 0) {
                    continue;
                }
                $conds = !empty($pol->Conditions) ? json_decode($pol->Conditions, true) : [];
                $isMatch = true;
                if (is_array($conds)) {
                    foreach ($conds as $c) {
                        if (($c['field'] ?? '') === 'Position_Level') {
                            $vals = (array) ($c['value'] ?? []);
                            $op = strtolower($c['operator'] ?? 'in');
                            if ($op === 'in' && !in_array($posLevel, $vals)) $isMatch = false;
                            if ($op === 'not_in' && in_array($posLevel, $vals)) $isMatch = false;
                        }
                    }
                }
                if ($isMatch) {
                    $matchedPolicy = $pol;
                    $policySteps = DB::table('approval_policy_step')
                        ->where('Policy_ID', $pol->ID)
                        ->orderBy('Stage_Order', 'asc')
                        ->orderBy('Level_Number', 'asc')
                        ->get();
                    break;
                }
            }
        }

        // Legacy fallback dari requisition_approval jika ada
        $legacyByLevel = [];
        $legacyRows = DB::table('requisition_approval')
            ->where('Task_ID', $ptk->Task_ID)
            ->orderBy('ID', 'asc')
            ->get();
        foreach ($legacyRows as $lr) {
            $lvl = (int) ($lr->Level ?? 0);
            $legacyByLevel[$lvl] = $lr;
        }

        $taskByStage = [];
        foreach ($approvalTasks as $t) {
            $taskByStage[(int) $t->Stage_Order] = $t;
        }

        // Susun list step approval dari policySteps (atau langsung dari approvalTasks jika policySteps kosong)
        $stepSigners = [];
        $stepsSource = $policySteps->isNotEmpty() ? $policySteps : $approvalTasks;
        foreach ($stepsSource as $idx => $stepObj) {
            $stg = (int) ($stepObj->Stage_Order ?? ($idx + 1));
            $tRow = $taskByStage[$stg] ?? null;
            $legRow = $legacyByLevel[$stg] ?? null;

            $label = $stepObj->Label
                ?? ($tRow->Position_Name ?? ($stepObj->Position_Code ?? ('Tahap ' . $stg)));
            $posCode = $stepObj->Position_Code ?? ($tRow->Position_Code ?? '');

            $signerName = '';
            $signedDate = '';
            $stepStatus = 'PENDING';

            if ($tRow) {
                $stepStatus = strtoupper((string) ($tRow->Status ?? 'PENDING'));
                if ($stepStatus === 'APPROVED' || $stepStatus === 'AUTO_APPROVED') {
                    $signerName = $tRow->Acted_By_Name ?: ($tRow->Acted_By_Username ?: '');
                    if ($signerName === '' && !empty($tRow->Candidate_Names)) {
                        $signerName = $tRow->Candidate_Names[0];
                    }
                    $signedDate = !empty($tRow->Acted_At) ? date('d/m/Y', strtotime($tRow->Acted_At)) : '';
                } elseif ($stepStatus === 'REJECTED') {
                    $signerName = $tRow->Acted_By_Name ?: ($tRow->Acted_By_Username ?: '');
                    $signedDate = !empty($tRow->Acted_At) ? date('d/m/Y', strtotime($tRow->Acted_At)) : '';
                }
            } elseif ($legRow && (int) ($legRow->Approved_Status ?? 0) === 1) {
                $stepStatus = 'APPROVED';
                $legUser = !empty($legRow->Approved_By)
                    ? DB::table('users')->where('username', $legRow->Approved_By)->first()
                    : null;
                $signerName = $legUser ? ($legUser->Name ?: $legUser->username) : ($legRow->Approved_By ?? '');
                $signedDate = !empty($legRow->Approved_Date) ? date('d/m/Y', strtotime($legRow->Approved_Date)) : '';
            }

            $qrUri = null;
            if (in_array($stepStatus, ['APPROVED', 'AUTO_APPROVED'], true) && $signerName !== '') {
                $qrUri = $this->qrDataUri("APPROVED BY\nStage: {$stg} ({$label})\nName: {$signerName}\nPTK: {$noPtk}\nDate: {$signedDate}");
            }

            $stepSigners[] = [
                'stage'         => $stg,
                'step_label'    => $label,
                'position_code' => $posCode,
                'name'          => $signerName,
                'date'          => $signedDate,
                'status'        => $stepStatus,
                'qr_uri'        => $qrUri,
            ];
        }

        // Kelompokkan ke dalam 3 grup kolom cetak:
        // 1. Disiapkan oleh (Pemohon PTK)
        // 2. Diketahui oleh (Step intermediate: Stage 1 .. N-1 jika N >= 2)
        // 3. Disetujui oleh (Step final: Stage N, atau kosong jika hanya 0 step)
        // Jika policy memiliki >= 2 step, step sebelum terakhir masuk ke "Diketahui oleh" dan step terakhir ke "Disetujui oleh".
        // Jika policy hanya memiliki 1 step (misal PTK-HCGA-STAFF: Manager HCGA saja), step tersebut menjadi "Disetujui oleh" dan "Diketahui oleh" tetap ditampilkan sebagai "-" / opsional.
        $acknowledgedBy = [];
        $approvedBy = [];
        $totalSteps = count($stepSigners);

        if ($totalSteps === 1) {
            $approvedBy[] = $stepSigners[0];
        } elseif ($totalSteps >= 2) {
            // Untuk policy 4 step (mis. Manager Dept, VP Dept, Manager HCGA, VP Director):
            // Jika step terakhir adalah VPD / Director dan step sebelumnya adalah MHCGA, maka:
            // Diketahui oleh = Stage 1 .. N-1 (atau Manager Dept, VP Dept, Manager HCGA)
            // Disetujui oleh = Stage terakhir (VP Director / Manager HCGA)
            $acknowledgedBy = array_slice($stepSigners, 0, $totalSteps - 1);
            $approvedBy[] = $stepSigners[$totalSteps - 1];
        }

        $signatureColumns = [
            'prepared'     => $preparedBy,
            'acknowledged' => $acknowledgedBy,
            'approved'     => $approvedBy,
            'policy_code'  => $matchedPolicy->Policy_Code ?? '',
            'policy_name'  => $matchedPolicy->Policy_Name ?? '',
            'all_steps'    => $stepSigners,
        ];

        // 7. Logo Base64 (diratakan ke latar putih agar transparansi tidak hitam di mPDF)
        $logoSrc = $this->logoDataUri(public_path('assets/images/large-logo.png')) ?? '';

        $docId = 'FR/HCGA/19-15';
        $revision = '#00';
        // $effectiveDate = !empty($ptk->Created_Date) ? date('d/m/Y', strtotime($ptk->Created_Date)) : date('d/m/Y');
        $effectiveDate = "04-02-2025";

        $viewData = compact(
            'ptk',
            'position',
            'noPtk',
            'jumlahPermintaan',
            'mulaiTanggal',
            'site',
            'isStaff',
            'isNonStaff',
            'isKaryawanBaru',
            'isPenggantiKaryawan',
            'isPeningkatanVolume',
            'isPenggantiKeluar',
            'namaJabatan',
            'bertanggungJawabKepada',
            'membawahi',
            'tugasList',
            'pendidikan',
            'pengalaman',
            'syaratKhusus',
            'docId',
            'revision',
            'effectiveDate',
            'logoSrc',
            'signatureColumns',
            'matchedPolicy'
        );

        // Preview HTML murni (?view=html): sisipkan header langsung di body
        if ($request->get('view') === 'html') {
            return view('humanresources.recruitment.prints.requisition', $viewData + ['inlineHeader' => true]);
        }

        // Body + header dirender terpisah
        $html       = view('humanresources.recruitment.prints.requisition', $viewData)->render();
        $headerHtml = view('humanresources.recruitment.prints.requisition-header', $viewData)->render();

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'tempDir'       => $tempDir,
            'margin_top'    => 7,
            'margin_bottom' => 7,
            'margin_left'   => 7,
            'margin_right'  => 7,
            'margin_header' => 7,
            'margin_footer' => 0,
        ]);

        $mpdf->splitTableBorderWidth = 1;
        $mpdf->setAutoTopMargin   = 'stretch'; // margin atas otomatis mengikuti tinggi header
        $mpdf->autoMarginPadding  = 0;         // tanpa celah antara header dan isi
        $mpdf->SetHTMLHeader($headerHtml);     // wajib dipanggil SEBELUM WriteHTML

        $safeTaskId = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $ptk->Task_ID);
        $filename = 'PTK_' . $safeTaskId;
        $mpdf->SetTitle($filename);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename . '.pdf', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '.pdf"',
        ]);
    }

    /**
     * Ubah file logo menjadi PNG opaque (latar putih) dalam bentuk data URI
     * agar transparansi tidak berubah hitam di mPDF.
     */
    private function logoDataUri(string $path, int $scale = 2): ?string
    {
        if (!is_file($path)) {
            return null;
        }
        if (function_exists('imagecreatefromstring')) {
            $src = @imagecreatefromstring(file_get_contents($path));
            if ($src) {
                $w = imagesx($src);
                $h = imagesy($src);
                $dst = imagecreatetruecolor($w * $scale, $h * $scale);
                imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $w * $scale, $h * $scale, $w, $h);
                ob_start();
                imagepng($dst);
                $png = ob_get_clean();
                imagedestroy($src);
                imagedestroy($dst);
                return 'data:image/png;base64,' . base64_encode($png);
            }
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: 'png';
        return 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($path));
    }

    /**
     * Hasilkan QR Code Data URI (SVG base64 bersih dari deklarasi XML) agar aman dirender di mPDF.
     */
    private function qrDataUri(string $text): ?string
    {
        try {
            $svg = (string) \SimpleSoftwareIO\QrCode\Facade::format('svg')->size(110)->margin(1)->generate($text);
            $svg = preg_replace('/<\?xml[^\?]*\?>\s*/i', '', $svg);
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected $approval;

    public function __construct(RequisitionApprovalService $approval)
    {
        $this->approval = $approval;
    }

    public function store(Request $request)
    {
        $request->validate([
            'Job_Position'  => 'required|integer|exists:master_position,ID',
            'Placement'     => 'required|string|max:255',
            'Description'   => 'required|string',
            'Requirement'   => 'required|string',
            'Priority'      => 'required|in:NORMAL,HIGH,URGENT',
            'Allocation'    => 'required|in:NEW,REPLACEMENT',
            'Request_Type'  => 'required|string',
            'Request_Desc'  => 'nullable|required_if:Request_Type,OTHER|string|max:255',
            'Work_Schedule' => 'required|integer|exists:work_calendars,ID',
            'Qty'           => 'required|integer|min:1',
            'Start_Date'    => 'required|date',
            'Task_ID'       => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $LSP = $user->Location;
        $Saya = $user->username;

        $jobPosition = DB::table('master_position')->where('ID', $request->input('Job_Position'))->first();

        $startDate = date('Y-m-d', strtotime($request->input('Start_Date')));
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

        $arr = [
            'Task_ID'          => $request->input('Task_ID'),
            'Job_Position'     => $jobPosition->Position,
            'Department'       => $jobPosition->Department,
            'Subject'          => $request->input('Subject') ?? '',
            'Placement'        => $request->input('Placement'),
            'Description'      => $request->input('Description'),
            'Requirement'      => $request->input('Requirement'),
            'Priority'         => $request->input('Priority'),
            'Allocation'       => $request->input('Allocation'),
            'Request_Type'     => $request->input('Request_Type'),
            'Request_Desc'     => $request->input('Request_Desc'),
            'Work_Schedule'    => $request->input('Work_Schedule'),
            'Qty'              => $request->input('Qty'),
            'Start_Date'       => $startDate,
            'End_Date'         => $endDate,
            'Created_By'       => $Saya,
            'Location'         => $LSP,
            'Created_Date'     => now(),
            'Status'           => 'CREATED',
            'Requisition_Type' => 'NON_PO',
        ];

        // Cegah Task_ID ganda (double submit)
        $dupTask = DB::table('requisition')->where('Task_ID', $request->input('Task_ID'))->first();
        if ($dupTask) {
            return 'exist';
        }

        // Cegah PTK terbuka ganda untuk posisi + departemen yang sama
        $openReq = DB::table('requisition')
            ->where('Job_Position', $jobPosition->Position)
            ->where('Department', $jobPosition->Department)
            ->where('Allocation', $request->input('Allocation'))
            ->whereNotIn('Status', ['CANCELED', 'CLOSED', 'REJECTED'])
            ->orderBy('ID', 'DESC')
            ->first();
        if ($openReq && strtotime($openReq->End_Date) >= strtotime(date('Y-m-d'))) {
            return 'exist';
        }

        // requisition dan requisition_log adalah MyISAM, jadi transaksi tidak
        // bisa rollback baris tersebut. Validasi policy dilakukan SEBELUM
        // insert agar error POLICY_NOT_FOUND tidak meninggalkan data PTK.
        $draftRequisition = (object) [
            'ID' => 0,
            'Task_ID' => $request->input('Task_ID'),
            'Location' => $LSP,
            'Department' => $jobPosition->Department,
        ];

        $reqId = null;

        try {
            $this->approval->assertCanSubmit($draftRequisition, $jobPosition, $user);

            DB::transaction(function () use ($arr, $jobPosition, $user, &$reqId) {
                $reqId = DB::table('requisition')->insertGetId($arr);

                $logArr = $arr;
                $logArr['Action_By'] = $user->username;
                $logArr['Action_Date'] = now();
                $logArr['Action_Status'] = 'CREATE FROM REQUISITION';
                $logArr['Last_ID'] = $reqId;

                DB::table('requisition_log')->insert($logArr);

                // Buat approval_request + task dari policy yang aktif.
                $requisition = DB::table('requisition')->where('ID', $reqId)->first();
                $this->approval->submit($requisition, $jobPosition, $user);
            });
        } catch (ApprovalWorkflowException $e) {
            if ($reqId !== null) {
                $this->approval->cleanupFailedSubmission($request->input('Task_ID'), (int) $reqId);
            }

            Log::warning('Requisition approval policy gagal: ' . $e->getMessage(), [
                'code' => $e->getErrorCode(),
                'Task_ID' => $request->input('Task_ID'),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'code' => $e->getErrorCode(),
            ], $e->getStatusCode());
        } catch (\Throwable $e) {
            if ($reqId !== null) {
                $this->approval->cleanupFailedSubmission($request->input('Task_ID'), (int) $reqId);
            }

            Log::error('Requisition submit gagal: ' . $e->getMessage(), [
                'Task_ID' => $request->input('Task_ID'),
            ]);

            return response()->json([
                'message' => 'Requisition gagal diajukan: ' . $e->getMessage(),
            ], 500);
        }

        return 'success';
    }

    /**
     * Tampilkan halaman Dashboard PTK (Requisition Pivot Dashboard)
     * GET /hrd-recruitment-dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $lsp = $user ? $user->Location : null;

        $query = DB::table('requisition')
            ->select(
                'Task_ID',
                'Subject',
                'Placement',
                'Job_Position',
                'Department',
                'Priority',
                'Allocation',
                'Qty',
                'Status',
                'Requisition_Type',
                'PO_No',
                'PIC_Production',
                'Work_Area',
                'Work_Schedule',
                'Created_By'
            );

        if (!empty($lsp)) {
            $query->where('Location', $lsp);
        }

        $getRecruit = $query->get();

        return view('humanresources.recruitment.recruitment-dashboard', compact('getRecruit'));
    }
}
