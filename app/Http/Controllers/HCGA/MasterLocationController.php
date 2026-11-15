<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\MasterLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterLocationController extends Controller
{
    const MENU_ID = 'SI01005';

    /** @var MasterLocationService */
    protected $service;

    public function __construct(MasterLocationService $service)
    {
        $this->service = $service;
    }

    /**
     * Tampilkan halaman Master Location untuk HCGA.
     */
    public function index(Request $request)
    {
        $this->authorizeAction('view');

        $search = $request->query('q');
        $locations = $this->service->listLocations($search);

        return view('humanresources.master.location', compact('locations', 'search'));
    }

    /**
     * Simpan lokasi baru.
     */
    public function store(Request $request)
    {
        $this->authorizeAction('create');

        $validated = $this->validateLocation($request);
        $this->service->createLocation($validated, Auth::user());

        return redirect()->route('hrd.master-location.index')
            ->with('success', 'Master lokasi berhasil ditambahkan.');
    }

    /**
     * Perbarui data lokasi.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAction('edit');

        $validated = $this->validateLocation($request);
        $updated = $this->service->updateLocation($id, $validated, Auth::user());

        if (!$updated) {
            return redirect()->route('hrd.master-location.index')
                ->withErrors(['not_found' => 'Data lokasi tidak ditemukan.']);
        }

        return redirect()->route('hrd.master-location.index')
            ->with('success', 'Data master lokasi berhasil diperbarui.');
    }

    /**
     * Hapus data lokasi.
     */
    public function destroy($id)
    {
        $this->authorizeAction('delete');

        $deleted = $this->service->deleteLocation($id);

        if (!$deleted) {
            return redirect()->route('hrd.master-location.index')
                ->withErrors(['not_found' => 'Data lokasi tidak ditemukan atau gagal dihapus.']);
        }

        return redirect()->route('hrd.master-location.index')
            ->with('success', 'Data master lokasi berhasil dihapus.');
    }

    /**
     * Validasi input sesuai batas kolom tabel `location`.
     *
     * @param Request $request
     * @return array
     */
    protected function validateLocation(Request $request)
    {
        return $request->validate([
            'Company_ID'    => 'nullable|string|max:11',
            'Location_Type' => 'required|in:Head Office,Branch Office',
            'Location_ID'   => 'nullable|string|max:11',
            'Location_Code' => 'nullable|string|max:15',
            'Name'          => 'required|string|max:30',
            'Address'       => 'nullable|string',
            'Street'        => 'nullable|string',
            'City'          => 'nullable|string|max:20',
            'IDCity'        => 'nullable|string|max:11',
            'MainCity'      => 'nullable|string|max:11',
            'IDCountry'     => 'nullable|string|max:11',
            'ZipCode'       => 'nullable|string|max:15',
            'Phone'         => 'nullable|string|max:40',
            'Phone_Ext'     => 'nullable|string|max:40',
            'Fax'           => 'nullable|string|max:40',
            'PIC'           => 'nullable|string|max:30',
            'Latitude'      => 'nullable|string|max:20',
            'Longitude'     => 'nullable|string|max:20',
        ]);
    }

    /**
     * Cek otorisasi menu SI01005 (atau role ADM).
     *
     * @param string $action
     * @return void
     */
    protected function authorizeAction($action)
    {
        $user = Auth::user();
        if (!$user || (!can_menu(self::MENU_ID, $action) && !$user->hasRole('ADM'))) {
            abort(403, 'Unauthorized');
        }
    }
}
