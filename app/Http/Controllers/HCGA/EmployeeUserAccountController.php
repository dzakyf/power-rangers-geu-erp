<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Role;
use App\Services\HCGA\EmployeeUserAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeUserAccountController extends Controller
{
    /** @var EmployeeUserAccountService */
    private $accountService;

    public function __construct(EmployeeUserAccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Render modal Buat / Hubungkan Akun User Karyawan (Single atau Batch).
     */
    public function modal(Request $request)
    {
        abort_unless($this->accountService->canManageEmployeeAccounts(Auth::user()), 403, 'Anda tidak memiliki akses untuk mengelola akun user karyawan.');

        $rawIds = (string) $request->input('ids', '');
        $ids = array_values(array_unique(array_filter(array_map('trim', explode(',', $rawIds)), function ($v) {
            return $v !== '';
        })));

        $items = $this->accountService->inspectEmployeesForProvisioning($ids);
        $roles = Role::orderBy('name')->orderBy('department')->get();
        $privOptions = DB::table('privilege_access_mappings')
            ->where('status', 'ACTIVE')
            ->distinct()
            ->pluck('priv_id')
            ->merge(['STAFF', 'HEAD', 'MANAGER', 'VP', 'HRD', 'HCGA', 'ADM'])
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('humanresources.employment.modals.user-account-modal', [
            'items' => $items,
            'roles' => $roles,
            'privOptions' => $privOptions,
            'isSingle' => count($items) === 1,
        ]);
    }

    /**
     * Endpoint pencarian AJAX akun kandidat (Position = CANDIDATE) untuk pemilihan manual di modal.
     */
    public function searchCandidates(Request $request)
    {
        abort_unless($this->accountService->canManageEmployeeAccounts(Auth::user()), 403, 'Akses ditolak.');

        $keyword = (string) $request->input('q', '');
        $results = $this->accountService->searchCandidateAccounts($keyword, 25);

        return response()->json([
            'results' => $results,
        ]);
    }

    /**
     * Proses pembuatan akun karyawan baru atau konversi akun kandidat menjadi karyawan.
     */
    public function save(Request $request)
    {
        abort_unless($this->accountService->canManageEmployeeAccounts(Auth::user()), 403, 'Anda tidak memiliki akses untuk mengelola akun user karyawan.');

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.employment_id' => 'nullable|integer',
            'items.*.employee_key' => 'nullable|string|max:100',
            'items.*.username_mode' => 'nullable|in:keep,company_email,nik,custom',
            'items.*.username' => 'nullable|string|max:150',
            'items.*.password' => 'nullable|string|min:6|max:100',
            'items.*.role_id' => 'nullable|integer',
            'items.*.priv_id' => 'nullable|string|max:50',
            'items.*.candidate_user_id' => 'nullable|integer',
        ]);

        $operator = Auth::user()->username ?? 'system';
        $result = $this->accountService->processAccounts($request->input('items', []), $operator);

        $statusCode = ($result['status'] === 'error') ? 422 : 200;

        return response()->json($result, $statusCode);
    }
}
