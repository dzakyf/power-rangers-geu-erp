<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\EmployeeThrSchemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeThrSchemeController extends Controller
{
    protected $schemeService;

    public function __construct(EmployeeThrSchemeService $schemeService)
    {
        $this->schemeService = $schemeService;
    }

    public function modal(Request $request)
    {
        $rawIds = (string) $request->input('ids', '');
        $ids = array_values(array_unique(array_filter(array_map('trim', explode(',', $rawIds)), function ($v) {
            return $v !== '';
        })));

        $employees = [];
        if (!empty($ids)) {
            $employees = DB::table('employment')
                ->where(function ($q) use ($ids) {
                    $q->whereIn('Task_ID', $ids)->orWhereIn('ID', $ids);
                })
                ->select('ID', 'Task_ID', 'NIK', 'Name', 'Department', 'Employment_Status', 'thr_scheme', 'thr_contract_note')
                ->orderBy('Name')
                ->get();
        }

        $schemes = EmployeeThrSchemeService::schemeOptions();

        return view('humanresources.employment.modals.thr-scheme-modal', compact('employees', 'schemes', 'ids'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'thr_scheme' => 'nullable|string|max:40',
            'thr_contract_note' => 'nullable|string|max:255',
        ]);

        $operator = Auth::user()->username ?? 'system';
        $affected = $this->schemeService->updateSchemes(
            $request->input('employee_ids', []),
            $request->input('thr_scheme'),
            $request->input('thr_contract_note'),
            $operator
        );

        return response()->json([
            'status' => 'success',
            'message' => "Skema THR berhasil diperbarui untuk {$affected} karyawan.",
            'affected' => $affected,
        ]);
    }
}
