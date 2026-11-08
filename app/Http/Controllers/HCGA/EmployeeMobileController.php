<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\EmployeeDataUpdateApprovalService;
use App\Services\Employee\EmployeeMobileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeMobileController extends Controller
{
    protected $employeeService;
    protected $approvalService;

    public function __construct(
        EmployeeMobileService $employeeService,
        EmployeeDataUpdateApprovalService $approvalService
    ) {
        $this->employeeService = $employeeService;
        $this->approvalService = $approvalService;
    }

    /**
     * Halaman Data Diri Saya atau Detail Karyawan
     */
    public function myProfile(Request $request)
    {
        $user = Auth::user();
        $isHcga = $this->employeeService->isHcgaOrAdmin($user);
        
        $taskId = $request->get('task_id');
        $myEmp = $this->employeeService->getMyEmployeeData($user);

        if ($taskId) {
            $employee = $this->employeeService->getEmployeeByTaskId($taskId);
            $isOwnProfile = ($myEmp && $myEmp->Task_ID == $taskId);
        } else {
            $employee = $myEmp;
            $isOwnProfile = true;
        }

        if (!$employee) {
            return view('humanresources.employment.mobile.not-found', [
                'isHcga' => $isHcga,
                'message' => 'Data profil karyawan tidak ditemukan atau belum terhubung dengan akun Anda.'
            ]);
        }

        return view('humanresources.employment.mobile.detail', [
            'employee'     => $employee,
            'isHcga'       => $isHcga,
            'isOwnProfile' => $isOwnProfile,
            'user'         => $user,
        ]);
    }

    /**
     * Directory / Phonebook Karyawan (Dapat diakses seluruh karyawan)
     */
    public function directory(Request $request)
    {
        $user = Auth::user();
        $isHcga = $this->employeeService->isHcgaOrAdmin($user);

        $params = [
            'search'     => $request->get('search'),
            'department' => $request->get('department'),
            'status'     => $request->get('status'),
            'location'   => $request->get('location'),
        ];

        $employees = $this->employeeService->getEmployeeDirectory($params, 15);
        $departments = DB::table('employment')->whereNotNull('Department')->distinct()->pluck('Department');
        $locations = DB::table('employment')->whereNotNull('Location')->distinct()->pluck('Location');

        return view('humanresources.employment.mobile.index', [
            'employees'   => $employees,
            'departments' => $departments,
            'locations'   => $locations,
            'filters'     => $params,
            'user'        => $user,
            'isHcga'      => $isHcga,
        ]);
    }

    /**
     * Endpoint Lazy Loading Component Tab (Partial HTML)
     */
    public function loadTabContent(Request $request)
    {
        $taskId = $request->get('task_id');
        $tab = $request->get('tab', 'profile');
        $user = Auth::user();
        $isHcga = $this->employeeService->isHcgaOrAdmin($user);

        $myEmp = $this->employeeService->getMyEmployeeData($user);
        $isOwn = ($myEmp && $myEmp->Task_ID == $taskId);

        // ATURAN HAK AKSES BARU:
        // Jika user lain (bukan akun sendiri & bukan HCGA/Admin):
        // HANYA boleh mengakses tab 'profile' (yang di dalamnya hanya tampil Jabatan & Kontak Komunikasi)
        if (!$isHcga && !$isOwn && $tab !== 'profile') {
            return response('<div class="alert alert-warning m-3 small"><i class="fa fa-lock me-1"></i> Informasi ini bersifat privat dan hanya dapat dilihat oleh karyawan bersangkutan atau tim HCGA.</div>', 403);
        }

        $tabData = $this->employeeService->getTabData($taskId, $tab, $isHcga);

        $viewName = "humanresources.employment.mobile.components.tab-{$tab}";
        if (!view()->exists($viewName)) {
            return response('<div class="alert alert-warning m-3">Komponen tab belum tersedia.</div>', 404);
        }

        return view($viewName, array_merge($tabData, [
            'taskId'       => $taskId,
            'isHcga'       => $isHcga,
            'isOwnProfile' => $isOwn,
        ]));
    }

    /**
     * Pengajuan / Direct Perubahan Data
     * - Karyawan sendiri: Mengajukan permohonan via workflow approval
     * - Admin / HCGA: Bisa langsung mengubah data karyawan manapun secara instan
     */
    public function submitUpdate(Request $request)
    {
        $this->validate($request, [
            'task_id'        => 'required',
            'field_category' => 'required',
            'field_name'     => 'required',
            'new_value'      => 'required',
        ]);

        $user = Auth::user();
        $isHcga = $this->employeeService->isHcgaOrAdmin($user);
        $myEmp = $this->employeeService->getMyEmployeeData($user);
        $isOwn = ($myEmp && $myEmp->Task_ID == $request->task_id);

        if (!$isHcga && !$isOwn) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk mengubah data karyawan ini.'], 403);
        }

        try {
            $data = $request->only(['field_category', 'field_name', 'old_value', 'new_value', 'reason']);
            
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = 'emp_upd_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/employee/requests'), $filename);
                $data['attachment'] = 'uploads/employee/requests/' . $filename;
            }

            // JIKA ADMIN / HCGA: Update LANGSUNG diterapkan ke database
            if ($isHcga) {
                $column = $request->field_name;
                $allowedEmploymentCols = [
                    'Name', 'Position', 'Department', 'Location', 'Handphone', 'Phone', 'Email',
                    'Email_Individual', 'Address', 'City', 'Province', 'District', 'Sub_District',
                    'ZipCode', 'Address_Living', 'City_Living', 'Province_Living', 'District_Living',
                    'Sub_District_Living', 'ZipCode_Living', 'Blood', 'Blood_Rhesus', 'Marital_Status',
                    'PTKP_Status', 'Payment_Reference', 'Payment_Reference2', 'Payment_Reference3',
                    'Payment_Method', 'NPWP', 'KTP', 'Religion', 'Status', 'Employment_Status'
                ];

                if (in_array($column, $allowedEmploymentCols)) {
                    DB::table('employment')->where('Task_ID', $request->task_id)->update([
                        $column => $request->new_value,
                    ]);
                }

                // Catat di log permohonan dengan status APPROVED langsung
                $req = $this->employeeService->submitUpdateRequest($request->task_id, $data, $user);
                DB::table('employee_data_update_requests')->where('id', $req->id)->update([
                    'status'        => 'APPROVED',
                    'approved_by'   => $user->username ?? $user->name,
                    'approved_at'   => \Carbon\Carbon::now(),
                    'approval_note' => 'Diperbarui langsung oleh Admin/HCGA: ' . ($request->reason ?? '-'),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data karyawan berhasil diperbarui secara langsung oleh HCGA.',
                ]);
            }

            // JIKA KARYAWAN SENDIRI: Alur approval berjenjang
            $req = $this->employeeService->submitUpdateRequest($request->task_id, $data, $user);
            $this->approvalService->submit($req, $user);

            return response()->json([
                'success' => true,
                'message' => 'Permohonan perubahan data berhasil diajukan dan sedang menunggu verifikasi tim HCGA.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Action Approval / Rejection oleh HCGA
     */
    public function approveRequest(Request $request, $id)
    {
        $user = Auth::user();
        if (!$this->employeeService->isHcgaOrAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $action = $request->input('action', 'approve');
        $note = $request->input('note', '');

        try {
            if ($action === 'approve') {
                $this->approvalService->approve($id, $user, $note);
                $msg = 'Permohonan perubahan data berhasil disetujui & data karyawan telah diperbarui.';
            } else {
                $this->approvalService->reject($id, $user, $note);
                $msg = 'Permohonan perubahan data berhasil ditolak.';
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
