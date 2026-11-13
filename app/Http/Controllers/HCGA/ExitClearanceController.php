<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\ExitClearanceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExitClearanceController extends Controller
{
    protected $exitClearanceService;

    public function __construct(ExitClearanceService $exitClearanceService)
    {
        $this->exitClearanceService = $exitClearanceService;
    }

    /**
     * Pengecekan Hak Akses Administrator sesuai standar SPPD
     */
    public static function isAdministrator($user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return false;
        }

        return $user->hasRole('ADM')
            || $user->hasRole('Super Admin')
            || ($user->Position ?? '') === 'Administrator';
    }

    /**
     * Pengecekan apakah user adalah Verifikator HCGA
     */
    public static function isHcgaVerifier($user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return false;
        }

        return ($user->Department ?? '') === 'HCGA'
            && in_array(strtoupper(trim((string) ($user->Priv_ID ?? ''))), ['MANAGER', 'HEAD', 'VP', 'HCGA', 'STAFF'], true);
    }

    /**
     * Halaman Utama Index Daftar Pengajuan Resign & Exit Clearance
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $isHcga = self::isHcgaVerifier($user);

        // Cek apakah user adalah atasan bawahan atau manager dept
        $managedDepts = $this->exitClearanceService->getManagedDepartments($user);
        $subordinateIds = $this->exitClearanceService->getSubordinateUserIds($user);

        // Cek apakah user memiliki tugas approval aktif atau terdaftar sebagai approver di approval workflow
        $hasApprovalTasks = false;
        if ($user) {
            $hasApprovalTasks = DB::table('approval_request_task as t')
                ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
                ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                ->whereIn('r.Request_Type', ['EMPLOYEE_RESIGNATION', 'EXIT_CLEARANCE'])
                ->where(function ($sub) use ($user) {
                    $sub->where('t.Claimed_By', $user->id)
                        ->orWhere('t.Acted_By', $user->id)
                        ->orWhere('c.User_Id', $user->id);
                })
                ->exists();
        }

        $isAtasan = !empty($managedDepts) || !empty($subordinateIds) || $hasApprovalTasks;
        $isApprover = $isAdmin || $isHcga || $isAtasan;

        // Auto-sinkronkan status karyawan yang tanggal pengunduran dirinya telah terpenuhi
        try {
            $this->exitClearanceService->syncEmploymentResignationStatus();
        } catch (\Exception $e) {
            \Log::warning('Auto sync resignation status error on index: ' . $e->getMessage());
        }

        $filters = [
            'search'   => $request->input('search'),
            'status'   => $request->input('status'),
            'is_admin' => $isAdmin,
            'is_hcga'  => $isHcga,
        ];

        $resignations = $this->exitClearanceService->getResignations($filters, $user);

        foreach ($resignations as $item) {
            $item->can_manager_approve = false;
            if (!empty($item->exit_clearance_id) && $item->exit_clearance_status === 'PENDING_MANAGER_APPROVAL') {
                if ($isAdmin) {
                    $item->can_manager_approve = ($item->User_ID != $user->id);
                } else {
                    $item->can_manager_approve = $this->exitClearanceService->isManagerOfClearance($user, (object) [
                        'User_ID'             => $item->User_ID,
                        'Departemen'          => $item->Departemen,
                        'Resignation_ID'      => $item->id,
                        'Approval_Request_ID' => $item->exit_clearance_approval_request_id ?? null,
                    ], $item);
                }
            }
        }

        return view('humanresources.exit-clearance.index', compact('resignations', 'isAdmin', 'isApprover', 'isHcga', 'isAtasan'));
    }

    /**
     * Form Pengajuan Resign Baru (oleh Karyawan / Admin / Atasan)
     */
    public function createResignation()
    {
        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $managedDepts = $this->exitClearanceService->getManagedDepartments($user);
        $subordinateIds = $this->exitClearanceService->getSubordinateUserIds($user);

        $hasApprovalTasks = false;
        if ($user) {
            $hasApprovalTasks = DB::table('approval_request_task as t')
                ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
                ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                ->whereIn('r.Request_Type', ['EMPLOYEE_RESIGNATION', 'EXIT_CLEARANCE'])
                ->where(function ($sub) use ($user) {
                    $sub->where('t.Claimed_By', $user->id)
                        ->orWhere('t.Acted_By', $user->id)
                        ->orWhere('c.User_Id', $user->id);
                })
                ->exists();
        }

        $isAtasan = !empty($managedDepts) || !empty($subordinateIds) || $hasApprovalTasks;

        // Hanya admin dan atasan yang memiliki bawahan yang dapat memilih nama karyawan lain
        $canSelectEmployee = $isAdmin || $isAtasan;

        $employees = collect([]);
        if ($canSelectEmployee) {
            $query = DB::table('users')
                ->where('Status', 'ACTIVE')
                ->select('id', 'Name', 'username', 'Emp_ID', 'Position', 'Department', 'Area');

            if ($isAdmin) {
                // Administrator dapat memilih seluruh karyawan aktif
                $employees = $query->orderBy('Name', 'asc')->get();
            } else {
                // Atasan dapat memilih dirinya sendiri atau bawahan langsung di departemennya
                $employees = $query->where(function ($q) use ($user, $subordinateIds, $managedDepts) {
                    $q->where('id', $user->id);
                    if (!empty($subordinateIds)) {
                        $q->orWhereIn('id', $subordinateIds);
                    }
                    if (!empty($managedDepts)) {
                        $q->orWhereIn('Department', $managedDepts);
                    }
                })->orderBy('Name', 'asc')->get();
            }

            // Ambil Join Date dari tabel employment secara batch untuk seluruh pilihan karyawan
            $empNiks = $employees->pluck('Emp_ID')->filter()->unique()->values()->all();
            if (!empty($empNiks)) {
                $employmentData = DB::table('employment')
                    ->whereIn('NIK', $empNiks)
                    ->select('NIK', 'Date_in', 'Start_Date')
                    ->orderBy('ID', 'desc')
                    ->get()
                    ->keyBy('NIK');

                foreach ($employees as $emp) {
                    $empRec = $employmentData->get($emp->Emp_ID);
                    $joinDate = null;
                    if ($empRec) {
                        $rawDate = (!empty($empRec->Date_in) && $empRec->Date_in !== '0000-00-00')
                            ? $empRec->Date_in
                            : ((!empty($empRec->Start_Date) && $empRec->Start_Date !== '0000-00-00') ? $empRec->Start_Date : null);
                        if ($rawDate) {
                            $joinDate = date('Y-m-d', strtotime($rawDate));
                        }
                    }
                    $emp->join_date = $joinDate;
                }
            }
        }

        // Ambil Join Date untuk user login saat ini dari tabel employment
        if (!empty($user->Emp_ID)) {
            $userEmp = DB::table('employment')
                ->where('NIK', $user->Emp_ID)
                ->select('Date_in', 'Start_Date')
                ->orderBy('ID', 'desc')
                ->first();
            if ($userEmp) {
                $rawUserDate = (!empty($userEmp->Date_in) && $userEmp->Date_in !== '0000-00-00')
                    ? $userEmp->Date_in
                    : ((!empty($userEmp->Start_Date) && $userEmp->Start_Date !== '0000-00-00') ? $userEmp->Start_Date : null);
                if ($rawUserDate) {
                    $user->join_date = date('Y-m-d', strtotime($rawUserDate));
                }
            }
        }

        return view('humanresources.exit-clearance.resignation-create', compact('user', 'employees', 'canSelectEmployee', 'isAdmin', 'isAtasan'));
    }

    /**
     * Simpan Pengajuan Resign Baru
     */
    public function storeResignation(Request $request)
    {
        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $managedDepts = $this->exitClearanceService->getManagedDepartments($user);
        $subordinateIds = $this->exitClearanceService->getSubordinateUserIds($user);
        $isAtasan = !empty($managedDepts) || !empty($subordinateIds);
        $canSelectEmployee = $isAdmin || $isAtasan;

        $request->validate([
            'User_ID' => 'nullable|integer',
            'Resign_Date' => 'required|date',
            'Last_Working_Date' => 'required|date|after_or_equal:Resign_Date',
            'Reason' => 'required|string',
            'Resignation_Letter_File' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Jika bukan admin dan tidak memiliki bawahan, paksakan target user adalah dirinya sendiri
        if (!$canSelectEmployee) {
            $targetUserId = (int) $user->id;
        } else {
            $targetUserId = (int) ($request->input('User_ID') ?: $user->id);
        }

        $data = $request->only(['Nama', 'Jabatan', 'Divisi', 'Departemen', 'Join_Date', 'Resign_Date', 'Last_Working_Date', 'Reason']);

        // Fallback jika Join_Date tidak terisi dari input, ambil dari master employment
        if (empty($data['Join_Date'])) {
            $targetUser = DB::table('users')->where('id', $targetUserId)->first();
            if ($targetUser && !empty($targetUser->Emp_ID)) {
                $targetEmp = DB::table('employment')
                    ->where('NIK', $targetUser->Emp_ID)
                    ->select('Date_in', 'Start_Date')
                    ->orderBy('ID', 'desc')
                    ->first();
                if ($targetEmp) {
                    $rawDate = (!empty($targetEmp->Date_in) && $targetEmp->Date_in !== '0000-00-00')
                        ? $targetEmp->Date_in
                        : ((!empty($targetEmp->Start_Date) && $targetEmp->Start_Date !== '0000-00-00') ? $targetEmp->Start_Date : null);
                    if ($rawDate) {
                        $data['Join_Date'] = date('Y-m-d', strtotime($rawDate));
                    }
                }
            }
        }

        // Upload berkas jika ada
        if ($request->hasFile('Resignation_Letter_File')) {
            $file = $request->file('Resignation_Letter_File');
            $fileName = 'resign_' . time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/exit_clearance'), $fileName);
            $data['Resignation_Letter_File'] = 'uploads/exit_clearance/' . $fileName;
        }

        try {
            $resignationId = $this->exitClearanceService->submitResignation($data, $targetUserId, auth()->id());
            return redirect()->route('hrd-exit-clearance-index')->with('success', 'Pengajuan pengunduran diri berhasil diajukan ke Atasan Langsung.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengajukan pengunduran diri: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Proses Approval Resign oleh Atasan
     * Proses Approval Resign oleh Atasan (via Approval Workflow Engine)
     */
    public function approveResignation(Request $request, $id)
    {
        try {
            $resignation = DB::table('employee_resignations')->where('id', $id)->first();
            if (!$resignation) {
                return redirect()->back()->with('error', 'Pengajuan resign tidak ditemukan.');
            }

            // Jika terhubung ke Approval Workflow Engine, proses task aktifnya
            if (!empty($resignation->Approval_Request_ID)) {
                $task = DB::table('approval_request_task')
                    ->where('Request_ID', $resignation->Approval_Request_ID)
                    ->whereIn('Status', ['PENDING', 'CLAIMED'])
                    ->first();

                if ($task) {
                    app(\App\Services\Approval\ExitClearanceApprovalService::class)
                        ->actOnTask((int) $task->ID, (int) auth()->id(), 'APPROVE', (string) $request->input('notes', ''));
                    return redirect()->route('hrd-exit-clearance-index')->with('success', 'Pengajuan pengunduran diri disetujui via Approval Workflow. Formulir Exit Clearance telah dibuka.');
                }
            }

            // Fallback eksekusi langsung
            $this->exitClearanceService->approveResignation((int) $id, (int) auth()->id(), (string) ($request->input('notes') ?? ''));
            return redirect()->route('hrd-exit-clearance-index')->with('success', 'Pengajuan pengunduran diri disetujui. Formulir Exit Clearance telah dibuat untuk diisi oleh karyawan.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses approval: ' . $e->getMessage());
        }
    }

    /**
     * Proses Reject Resign oleh Atasan
     * Proses Reject Resign oleh Atasan (via Approval Workflow Engine)
     */
    public function rejectResignation(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        try {
            $resignation = DB::table('employee_resignations')->where('id', $id)->first();
            if (!$resignation) {
                return redirect()->back()->with('error', 'Pengajuan resign tidak ditemukan.');
            }

            // Jika terhubung ke Approval Workflow Engine, proses task aktifnya
            if (!empty($resignation->Approval_Request_ID)) {
                $task = DB::table('approval_request_task')
                    ->where('Request_ID', $resignation->Approval_Request_ID)
                    ->whereIn('Status', ['PENDING', 'CLAIMED'])
                    ->first();

                if ($task) {
                    app(\App\Services\Approval\ExitClearanceApprovalService::class)
                        ->actOnTask((int) $task->ID, (int) auth()->id(), 'REJECT', (string) $request->input('rejection_reason'));
                    return redirect()->route('hrd-exit-clearance-index')->with('success', 'Pengajuan pengunduran diri telah ditolak via Approval Workflow.');
                }
            }

            // Fallback eksekusi langsung
            $this->exitClearanceService->rejectResignation((int) $id, (int) auth()->id(), (string) ($request->input('rejection_reason') ?? ''));
            return redirect()->route('hrd-exit-clearance-index')->with('success', 'Pengajuan pengunduran diri telah ditolak.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal menolak pengajuan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan Formulir Exit Clearance (Karyawan mengisi Kuesioner A-D & Checklist E)
     */
    public function formClearance($clearanceId)
    {
        $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
        if (!$clearance) {
            abort(404, 'Form Exit Clearance tidak ditemukan.');
        }

        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);

        // Hanya karyawan yang bersangkutan atau administrator yang berhak mengisi form
        if ($clearance->User_ID != $user->id && !$isAdmin) {
            return redirect()->route('hrd-exit-clearance-index')
                ->with('error', 'Akses ditolak. Pengisian formulir Exit Clearance hanya dapat dilakukan oleh karyawan yang bersangkutan.');
        }

        // Jika form sudah disubmit / bukan DRAFT, arahkan langsung ke halaman detail
        if ($clearance->Status !== 'DRAFT') {
            return redirect()->route('hrd-exit-clearance-view', $clearanceId)
                ->with('info', 'Formulir Exit Clearance sudah dikirimkan sebelumnya.');
        }

        $resignation = DB::table('employee_resignations')->where('id', $clearance->Resignation_ID)->first();
        $checklist = DB::table('exit_clearance_checklist_values')->where('Exit_Clearance_ID', $clearanceId)->get();

        return view('humanresources.exit-clearance.clearance-form', compact('clearance', 'resignation', 'checklist'));
    }

    /**
     * Submit Pengisian Form Exit Clearance oleh Karyawan
     */
    public function submitClearance(Request $request, $clearanceId)
    {
        $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
        if (!$clearance) {
            return redirect()->route('hrd-exit-clearance-index')->with('error', 'Form Exit Clearance tidak ditemukan.');
        }

        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);

        // Hanya karyawan yang bersangkutan atau administrator yang berhak submit form
        if ($clearance->User_ID != $user->id && !$isAdmin) {
            return redirect()->route('hrd-exit-clearance-index')
                ->with('error', 'Akses ditolak. Anda tidak berhak mengirimkan formulir Exit Clearance karyawan lain.');
        }

        if ($clearance->Status !== 'DRAFT') {
            return redirect()->route('hrd-exit-clearance-view', $clearanceId)
                ->with('info', 'Formulir Exit Clearance sudah pernah disubmit sebelumnya.');
        }

        $payload = [
            'reasons_ratings' => $request->input('reasons_ratings', []),
            'other_uncovered_reasons' => $request->input('other_uncovered_reasons'),
            'new_company_offers' => $request->input('new_company_offers', []),
            'company_ratings' => $request->input('company_ratings', []),
            'division_feedbacks' => $request->input('division_feedbacks', []),
            'company_suggestions' => $request->input('company_suggestions'),
            'employee_declaration' => $request->has('employee_declaration'),
            'checklist' => $request->input('checklist', []),
        ];

        try {
            $this->exitClearanceService->submitExitClearance((int) $clearanceId, $payload, auth()->id());
            return redirect()->route('hrd-exit-clearance-index')->with('success', 'Formulir Exit Clearance berhasil dikirimkan ke HCGA untuk sesi Exit Interview.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan formulir: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Halaman Detail / Exit Interview oleh HCGA, Approval Atasan, & Verifikasi HCGA
     */
    public function viewClearance($clearanceId)
    {
        $this->exitClearanceService->ensureFlowSchema();

        $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
        if (!$clearance) {
            abort(404, 'Exit Clearance tidak ditemukan.');
        }

        // Jika status clearance sudah COMPLETED, verifikasi apakah tanggal efektifnya sudah terpenuhi untuk sinkronisasi status
        if ($clearance->Status === 'COMPLETED') {
            try {
                $this->exitClearanceService->syncEmploymentResignationStatus((int) $clearanceId);
            } catch (\Exception $e) {
                \Log::warning('Auto sync resignation status error on viewClearance: ' . $e->getMessage());
            }
        }

        $resignation = DB::table('employee_resignations')->where('id', $clearance->Resignation_ID)->first();
        $checklist = DB::table('exit_clearance_checklist_values')->where('Exit_Clearance_ID', $clearanceId)->get();
        $documents = DB::table('exit_clearance_documents')->where('Exit_Clearance_ID', $clearanceId)->orderBy('id', 'desc')->get();

        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $isHcga = self::isHcgaVerifier($user);

        // Cek wewenang atasan
        $managedDepts = $this->exitClearanceService->getManagedDepartments($user);
        $subordinateIds = $this->exitClearanceService->getSubordinateUserIds($user);

        $hasApprovalTasks = false;
        if ($user) {
            $hasApprovalTasks = DB::table('approval_request_task as t')
                ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
                ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                ->whereIn('r.Request_Type', ['EMPLOYEE_RESIGNATION', 'EXIT_CLEARANCE'])
                ->where(function ($sub) use ($user) {
                    $sub->where('t.Claimed_By', $user->id)
                        ->orWhere('t.Acted_By', $user->id)
                        ->orWhere('c.User_Id', $user->id);
                })
                ->exists();
        }

        $isAtasan = !empty($managedDepts)
            || !empty($subordinateIds)
            || $hasApprovalTasks
            || ($resignation && (int) $resignation->Approved_By === (int) $user->id);

        // Tahap 1: Yang berhak mengisi Exit Interview adalah HCGA atau Admin (dan bukan pemohon sendiri kecuali Admin)
        $isInterviewAuthor = $isAdmin || ($isHcga && $clearance->User_ID != $user->id);

        // Tahap 2: Yang berhak melakukan Approval Departemen HANYA Atasan Langsung / Manager Departemen terkait atau Admin
        $isManagerApprover = $isAdmin
            ? ($clearance->User_ID != $user->id)
            : $this->exitClearanceService->isManagerOfClearance($user, $clearance, $resignation);

        $hcgaInterviewerId = $clearance->Hcga_Interview_By ?? null;
        $interviewer = !empty($hcgaInterviewerId)
            ? DB::table('users')->where('id', $hcgaInterviewerId)->first()
            : null;

        $managerApprover = !empty($clearance->Manager_Interview_By)
            ? DB::table('users')->where('id', $clearance->Manager_Interview_By)->first()
            : null;

        $hcgaVerifier = !empty($clearance->Hcga_Verified_By)
            ? DB::table('users')->where('id', $clearance->Hcga_Verified_By)->first()
            : null;

        $auditLogs = $this->exitClearanceService->getLogs((int) $clearanceId, (int) ($resignation->id ?? 0));

        return view('humanresources.exit-clearance.clearance-detail', compact(
            'clearance', 'resignation', 'checklist', 'documents',
            'isAdmin', 'isHcga', 'isAtasan', 'isInterviewAuthor', 'isManagerApprover',
            'interviewer', 'managerApprover', 'hcgaVerifier',
            'auditLogs'
        ));
    }

    /**
     * Simpan Hasil Exit Interview oleh Tim HCGA (Tahap 1 -> Diajukan ke Atasan Departemen)
     */
    public function recordInterview(Request $request, $clearanceId)
    {
        $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
        if (!$clearance) {
            return redirect()->back()->with('error', 'Exit Clearance tidak ditemukan.');
        }

        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $isHcga = self::isHcgaVerifier($user);

        if (!$isAdmin && !$isHcga) {
            return redirect()->back()->with('error', 'Akses ditolak. Input hasil Exit Interview hanya dapat dilakukan oleh tim HCGA atau Administrator.');
        }

        // Karyawan tidak boleh meng-interview diri sendiri
        if ($clearance->User_ID == $user->id && !$isAdmin) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak dapat melakukan wawancara Exit Interview terhadap diri sendiri.');
        }

        if ($clearance->Status !== 'PENDING_INTERVIEW') {
            return redirect()->route('hrd-exit-clearance-view', $clearanceId)->with('info', 'Sesi Exit Interview telah dilakukan sebelumnya.');
        }

        $request->validate([
            'exit_interview_notes' => 'required|string',
        ]);

        try {
            $this->exitClearanceService->recordInterviewByHcga(
                (int) $clearanceId,
                $request->only(['exit_interview_notes']),
                auth()->id()
            );

            $this->advanceApprovalStageTask($clearance, (int) auth()->id(), (string) $request->input('exit_interview_notes', ''));

            return redirect()->route('hrd-exit-clearance-view', $clearanceId)->with('success', 'Hasil wawancara Exit Interview oleh HCGA berhasil dicatat dan diajukan ke Atasan Departemen terkait.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat wawancara: ' . $e->getMessage());
        }
    }

    /**
     * Approval Exit Clearance oleh Atasan Departemen Terkait (Tahap 2 -> Diteruskan ke Verifikasi HCGA)
     */
    public function approveByManager(Request $request, $clearanceId)
    {
        $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
        if (!$clearance) {
            return redirect()->back()->with('error', 'Exit Clearance tidak ditemukan.');
        }

        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $resignation = DB::table('employee_resignations')->where('id', $clearance->Resignation_ID)->first();

        $canApprove = $isAdmin || $this->exitClearanceService->isManagerOfClearance($user, $clearance, $resignation);

        if (!$canApprove) {
            return redirect()->back()->with('error', 'Akses ditolak. Approval ini hanya dapat dilakukan oleh Atasan Langsung / Manager Departemen terkait atau Administrator.');
        }

        if ($clearance->User_ID == $user->id && !$isAdmin) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak dapat menyetujui Exit Clearance milik sendiri.');
        }

        if ($clearance->Status !== 'PENDING_MANAGER_APPROVAL') {
            return redirect()->route('hrd-exit-clearance-view', $clearanceId)->with('info', 'Approval Atasan Departemen sudah dilakukan sebelumnya.');
        }

        $request->validate([
            'manager_notes' => 'nullable|string',
        ]);

        try {
            $this->exitClearanceService->approveByManager(
                (int) $clearanceId,
                $request->only(['manager_notes']),
                auth()->id()
            );

            $this->advanceApprovalStageTask($clearance, (int) auth()->id(), (string) $request->input('manager_notes', ''));

            return redirect()->route('hrd-exit-clearance-view', $clearanceId)->with('success', 'Exit Clearance telah disetujui oleh Atasan Departemen dan diteruskan ke HCGA untuk verifikasi final.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses approval atasan: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi & Final Approval oleh HCGA
     */
    public function verifyHcga(Request $request, $clearanceId)
    {
        $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
        if (!$clearance) {
            return redirect()->back()->with('error', 'Exit Clearance tidak ditemukan.');
        }

        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $isHcga = self::isHcgaVerifier($user);

        // HANYA user HCGA atau Administrator yang berhak memverifikasi
        if (!$isAdmin && !$isHcga) {
            return redirect()->back()->with('error', 'Akses ditolak. Verifikasi akhir Exit Clearance hanya dapat dilakukan oleh tim HCGA atau Administrator.');
        }

        if ($clearance->Status !== 'PENDING_HCGA_APPROVAL') {
            return redirect()->route('hrd-exit-clearance-view', $clearanceId)->with('info', 'Verifikasi HCGA sudah diselesaikan sebelumnya.');
        }

        $request->validate([
            'hcga_notes' => 'nullable|string',
        ]);

        try {
            $this->exitClearanceService->verifyAndApproveByHcga(
                (int) $clearanceId,
                $request->only(['hcga_notes']),
                auth()->id()
            );

            $this->advanceApprovalStageTask($clearance, (int) auth()->id(), (string) $request->input('hcga_notes', ''));

            return redirect()->route('hrd-exit-clearance-view', $clearanceId)->with('success', 'Exit Clearance telah selesai diverifikasi oleh HCGA.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal verifikasi HCGA: ' . $e->getMessage());
        }
    }

    /**
     * Selaraskan task aktif di Approval Workflow Engine tanpa menggagalkan proses utama
     */
    protected function advanceApprovalStageTask($clearance, int $userId, string $notes = ''): void
    {
        if (empty($clearance->Approval_Request_ID)) {
            return;
        }

        try {
            $task = DB::table('approval_request_task')
                ->where('Request_ID', $clearance->Approval_Request_ID)
                ->whereIn('Status', ['PENDING', 'CLAIMED'])
                ->orderBy('Stage_Order', 'asc')
                ->first();

            if ($task) {
                app(\App\Services\Approval\ExitClearanceApprovalService::class)
                    ->actOnTask((int) $task->ID, $userId, 'APPROVE', $notes);
            }
        } catch (Exception $e) {
            \Log::warning('Advance approval stage task skipped for Exit Clearance #' . ($clearance->id ?? '') . ': ' . $e->getMessage());
        }
    }

    /**
     * Unduh / Cetak Dokumen PDF Resmi (FR/HCGA/07-00)
     */
    public function downloadPdf($clearanceId)
    {
        $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
        if (!$clearance) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $resignation = DB::table('employee_resignations')->where('id', $clearance->Resignation_ID)->first();
        $checklist = DB::table('exit_clearance_checklist_values')->where('Exit_Clearance_ID', $clearanceId)->get();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $html = view('humanresources.exit-clearance.print-pdf', compact('clearance', 'resignation', 'checklist'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $sanitizedNo = str_replace('/', '_', $clearance->No_Trx);
        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, "Exit_Clearance_{$sanitizedNo}.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }
}

