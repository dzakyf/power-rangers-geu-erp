<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\EmployeeGridService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeListsController extends Controller
{
    /** @var EmployeeGridService */
    private $gridService;

    public function __construct(EmployeeGridService $gridService)
    {
        $this->gridService = $gridService;
    }

    /**
     * Halaman utama Data Internal Employees (/employee-lists).
     */
    public function index()
    {
        $user = Auth::user();
        abort_unless($this->gridService->canViewEmployeeLists($user), 403, 'Anda tidak memiliki akses ke menu Employee Lists.');

        $data = $this->gridService->getIndexViewData((string) ($user->username ?? ''), $user);

        return view('humanresources.employment.employee-lists', $data);
    }

    /**
     * Endpoint XML dhtmlXGrid daftar karyawan aktif per lokasi (/xml-employee-lists).
     */
    public function xmlData(Request $request)
    {
        $user = Auth::user();
        abort_unless($this->gridService->canViewEmployeeLists($user), 403, 'Akses ditolak.');

        $colsParam = (string) $request->query('cols', '');
        $requestedCols = $colsParam !== '' ? explode(',', $colsParam) : [];
        $xml = $this->gridService->buildEmployeeGridXml((string) ($user->Location ?? ''), $requestedCols);

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }

    /**
     * Endpoint JSON untuk Dynamic Pivot Analytics (/xml-employee-pivot).
     */
    public function pivotData(Request $request)
    {
        $user = Auth::user();
        abort_unless($this->gridService->canViewEmployeeLists($user), 403, 'Akses ditolak.');

        $colsParam = (string) $request->query('cols', '');
        $requestedCols = $colsParam !== '' ? explode(',', $colsParam) : [];
        $data = $this->gridService->buildPivotData(
            (string) ($user->Location ?? ''),
            (string) ($user->username ?? ''),
            $requestedCols
        );

        return response()->json($data);
    }

    /**
     * Simpan preferensi kolom grid karyawan (/employee-lists-columns-save).
     */
    public function saveColumns(Request $request)
    {
        $user = Auth::user();
        abort_unless($this->gridService->canViewEmployeeLists($user), 403, 'Akses ditolak.');

        $result = $this->gridService->saveColumnPreference(
            (string) ($user->username ?? ''),
            (string) $request->input('cols', '')
        );

        return response()->json($result);
    }
}
