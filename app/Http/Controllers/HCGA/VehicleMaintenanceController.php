<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleMaintenanceController extends Controller
{
    /**
     * Tampilan utama grid & tombol.
     */
    public function index()
    {
        return view('humanresources.vehicle-maintenance.index');
    }

    /**
     * Endpoint data untuk grid (XML/JSON fallback — mengikuti pola asset_fixing).
     */
    public function data()
    {
        $httpAccept = $_SERVER['HTTP_ACCEPT'] ?? 'text/xml';
        if (!headers_sent() && stristr($httpAccept, 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } elseif (!headers_sent()) {
            header('Content-type: text/xml');
        }
        echo('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");

        $dept = Auth::user()->Department;
        $saya = Auth::user()->Name ?: Auth::user()->username;

        $Query = DB::table('vehicle_maintenance')->orderBy('created_at', 'DESC');

        if ($dept !== 'HCGA' && $dept !== 'DIR' && $dept !== 'OPERATION') {
            $Query->where('Department', $dept);
        }

        $results = $Query->get();
        $nomor = 1;

        echo "<rows>";
        foreach ($results as $Fetch) {
            echo "<row id=\"" . $Fetch->id . "\">";
            echo "<cell>" . $nomor . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->No_Trx) . "</cell>";
            echo "<cell>" . htmlspecialchars(ucfirst($Fetch->Jenis_Perawatan)) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Plat_Nomor) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Product_Description) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $Fetch->Kilometer_Saat_Ini) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($Fetch->Estimasi_Biaya, 0, ',', '.')) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    /**
     * Form create (partial).
     */
    public function create()
    {
        $assetList = DB::table('asset_list')
            ->select('Asset_No', 'Product_Code', 'Product_Description', 'SN', 'Asset_Location_Department', 'Asset_Location', 'WH_ID')
            ->get();

        return view('humanresources.vehicle-maintenance.create', compact('assetList'));
    }

    /**
     * Simpan data perawatan kendaraan.
     */
    public function store(Request $request)
    {
        $Saya = Auth::user()->username;
        $jenis = $request->input('Jenis_Perawatan', 'preventif');

        // Wajib ada kendaraan yang dipilih
        $productCode = $request->input('Product_Code', '');
        if (empty($productCode)) {
            $productCode = $request->input('Asset_No', '');
        }

        if (empty($productCode)) {
            Log::error('VehicleMaintenance store gagal: Product_Code/Asset_No kosong. User=' . $Saya);
            return response()->json([
                'status'  => 'error',
                'message' => 'Harap pilih kendaraan dari daftar terlebih dahulu!',
            ]);
        }

        // Cari di asset_list
        $asset = DB::table('asset_list')
            ->where('Product_Code', $productCode)
            ->select('Asset_No', 'Product_Code', 'Product_Description', 'SN', 'Asset_Location', 'Asset_Location_Department')
            ->first();

        if (!$asset) {
            $asset = DB::table('asset_list')
                ->where('Asset_No', $productCode)
                ->select('Asset_No', 'Product_Code', 'Product_Description', 'SN', 'Asset_Location', 'Asset_Location_Department')
                ->first();
        }

        if (!$asset) {
            Log::error('VehicleMaintenance store gagal: asset_list tidak ditemukan untuk kode=' . $productCode . '. User=' . $Saya);
            return response()->json([
                'status'  => 'error',
                'message' => 'Kendaraan tidak ditemukan di asset_list!',
            ]);
        }

        // Sanitasi estimasi biaya
        $estimasiRaw = $request->input('Estimasi_Biaya', '0');
        $estimasi = (float) str_replace(['Rp', '.', ',', ' '], ['', '', '', ''], $estimasiRaw);

        $status = 'APPROVAL_PIC';

        $data = [
            'No_Trx'               => 'VM/' . date('Ymd') . '/' . $jenis . '/' . date('ymdhis'),
            'Jenis_Perawatan'      => $jenis,
            'Asset_No'             => $asset->Asset_No,
            'Product_Code'         => $asset->Product_Code,
            'Product_Description'  => $request->input('Product_Description', $asset->Product_Description),
            'SN'                   => $asset->SN ?? '',
            'Plat_Nomor'           => $request->input('Plat_Nomor', ''),
            'Kategori_Kendaraan'   => $request->input('Kategori_Kendaraan', ''),
            'Kilometer_Saat_Ini'   => (int) str_replace([' ', '.', ',', 'km', 'KM'], ['', '', '', '', '', ''], $request->input('Kilometer_Saat_Ini', '0')),
            'Tipe_Service_Preventif' => $request->input('Tipe_Service_Preventif', ''),
            'Jadwal_Perawatan_Bulan' => (int) $request->input('Jadwal_Perawatan_Bulan', 0),
            'Kerusakan_Yang_Ditemui' => $request->input('Kerusakan_Yang_Ditemui', ''),
            'Penyebab_Diperkirakan'  => $request->input('Penyebab_Diperkirakan', ''),
            'Komponen_Yang_Diganti'  => $request->input('Komponen_Yang_Diganti', ''),
            'Kondisi_Awal'            => $request->input('Kondisi_Awal', ''),
            'Keterangan_Lain'         => $request->input('Keterangan_Lain', ''),
            'Estimasi_Biaya'          => $estimasi,
            'Vendor_Bengkel'          => $request->input('Vendor_Bengkel', ''),
            'Status'                  => $status,
            'Department'              => Auth::user()->Department,
            'User_NIP'                => Auth::user()->NIP ?? Auth::user()->username,
            'User_Trx'                => $Saya,
            'created_at'              => date('Y-m-d H:i:s'),
            'updated_at'              => date('Y-m-d H:i:s'),
        ];

        Log::info('VehicleMaintenance store: ' . json_encode($data));

        try {
            DB::table('vehicle_maintenance')->insert($data);
        } catch (\Exception $e) {
            Log::error('VehicleMaintenance store DB error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan ke database: ' . $e->getMessage(),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil disimpan',
        ], 200);
    }

    /**
     * Detail satu record.
     */
    public function detail(Request $request)
    {
        $data = DB::table('vehicle_maintenance')->where('id', $request->input('ID'))->first();
        if (!$data) {
            abort(404);
        }

        return view('humanresources.vehicle-maintenance.detail', compact('data'));
    }
}
