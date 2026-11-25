<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\MasterWarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterWarehouseController extends Controller
{
    /** @var MasterWarehouseService */
    protected $service;

    public function __construct(MasterWarehouseService $service)
    {
        $this->service = $service;
    }

    /**
     * Tampilkan halaman Master Warehouse / Site (gudang_list).
     */
    public function index(Request $request)
    {
        $search = $request->query('q');
        $statusFilter = strtoupper(trim((string) $request->query('status', 'ALL')));
        if (!in_array($statusFilter, ['ALL', 'ACTIVE', 'INACTIVE'], true)) {
            $statusFilter = 'ALL';
        }

        $warehouses = $this->service->listWarehouses($search, $statusFilter);
        $summary = $this->service->getSummaryStats();
        $refOptions = $this->service->getReferenceOptions();

        return view('humanresources.master.warehouse', compact(
            'warehouses',
            'summary',
            'refOptions',
            'search',
            'statusFilter'
        ));
    }

    /**
     * Simpan warehouse / site baru.
     */
    public function store(Request $request)
    {
        $validated = $this->validateWarehouse($request, false);

        try {
            $result = $this->service->createWarehouse($validated, Auth::user());

            return redirect()->route('hrd.master-warehouse.index')
                ->with('success', 'Master warehouse berhasil ditambahkan (' . $result['gudang_id'] . ').');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Gudang_ID' => $e->getMessage()]);
        }
    }

    /**
     * Perbarui data warehouse / site.
     */
    public function update(Request $request, $id)
    {
        $validated = $this->validateWarehouse($request, true);

        try {
            $updated = $this->service->updateWarehouse((int) $id, $validated, Auth::user());

            if (!$updated) {
                return redirect()->route('hrd.master-warehouse.index')
                    ->withErrors(['not_found' => 'Data warehouse tidak ditemukan.']);
            }

            return redirect()->route('hrd.master-warehouse.index')
                ->with('success', 'Data master warehouse berhasil diperbarui.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['Gudang_ID' => $e->getMessage()]);
        }
    }

    /**
     * Aktifkan / nonaktifkan warehouse (soft-deactivate).
     */
    public function toggleStatus(Request $request, $id)
    {
        $targetStatus = $request->input('status');
        $newStatus = $this->service->toggleStatus((int) $id, $targetStatus, Auth::user());

        if ($newStatus === null) {
            return redirect()->route('hrd.master-warehouse.index')
                ->withErrors(['not_found' => 'Data warehouse tidak ditemukan.']);
        }

        $label = $newStatus === 'ACTIVE' ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->route('hrd.master-warehouse.index')
            ->with('success', "Master warehouse berhasil {$label}.");
    }

    /**
     * Validasi input sesuai batas kolom tabel gudang_list.
     *
     * @param Request $request
     * @param bool $isUpdate
     * @return array
     */
    protected function validateWarehouse(Request $request, bool $isUpdate): array
    {
        $rules = [
            'Gudang_ID'   => ($isUpdate ? 'required' : 'nullable') . '|string|max:50',
            'Description' => 'required|string|max:500',
            'PIC'         => 'nullable|string|max:40',
            'Department'  => 'nullable|string|max:150',
            'Location'    => 'nullable|string|max:30',
            'Address'     => 'nullable|string|max:255',
            'Street'      => 'nullable|string|max:255',
            'City'        => 'nullable|string|max:255',
        ];

        if ($isUpdate) {
            $rules['Status'] = 'nullable|in:ACTIVE,INACTIVE';
        }

        return $request->validate($rules, [
            'Gudang_ID.required'   => 'Kode Warehouse wajib diisi saat memperbarui data.',
            'Description.required' => 'Nama / Deskripsi Warehouse wajib diisi.',
        ]);
    }
}
