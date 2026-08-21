<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetFixingController extends Controller
{
    public function index()
    {
        return view('humanresources.asset-fixing.index');
    }

    /**
     * Data grid endpoint
     */
    public function data()
    {
        if (stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } else {
            header('Content-type: text/xml');
        }
        echo('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");

        $username = Auth::user()->username;
        $dept     = Auth::user()->Department;
        $saya     = Auth::user()->Name ?: $username;
        $nomor    = 1;

        $Query = DB::table('asset_fixing')->orderBy('created_at', 'DESC');

        // User biasa hanya lihat data milik departemennya sendiri
        if ($dept !== 'HCGA' && $dept !== 'DIR' && $dept !== 'OPERATION') {
            $Query->where('Department', $dept);
        }

        $results = $Query->get();

        echo "<rows>";
        foreach ($results as $Fetch) {
            echo "<row id=\"" . $Fetch->id . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->No_Trx) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Jenis_Perbaikan) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Nama_Pengaju) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Tanggal_Pengajuan) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Nama_Aset) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Product_Code) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($Fetch->Estimasi_Biaya, 0, ',', '.')) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public function create()
    {
        return view('humanresources.asset-fixing.create');
    }

    /**
     * Endpoint pencarian aset dari asset_list berdasarkan Product_Code
     * Dipanggil via AJAX saat user memilih aset.
     * Request: POST dengan data: {'product_code': 'ABC123'}
     * Response: JSON berisi { asset_no, product_code, product_description, sn, asset_location, wh_id }
     */
    public function getAsetFromProductCode(Request $request)
    {
        $productCode = $request->input('product_code', '');

        if (empty($productCode)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Product Code tidak boleh kosong',
            ]);
        }

        $asset = DB::table('asset_list')
            ->where('Product_Code', $productCode)
            ->select('Asset_No', 'Product_Code', 'Product_Description', 'SN', 'Asset_Location', 'Asset_Location_Department', 'WH_ID')
            ->first();

        if (!$asset) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Aset tidak ditemukan dengan Product Code ' . $productCode,
            ]);
        }

        return response()->json([
            'status'            => 'success',
            'asset_no'          => $asset->Asset_No,
            'product_code'      => $asset->Product_Code,
            'product_description' => $asset->Product_Description,
            'sn'                => $asset->SN,
            'asset_location'    => $asset->Asset_Location ?? $asset->Asset_Location_Department ?? '',
            'wh_id'             => $asset->WH_ID,
        ]);
    }

    /**
     * Store data perbaikan aset.
     * Mengambil data aset dari asset_list berdasarkan Asset_No yang dipilih,
     * kemudian menyimpanya ke tabel asset_fixing.
     */
    public function store(Request $request)
    {
        $Saya       = Auth::user()->username;
        $jenis      = $request->input('Jenis_Perbaikan', 'office');
        $productCode = $request->input('Asset_No', $request->input('Product_Code', ''));
        $snRaw      = $request->input('Nomor_Seri', '');

        // Jika tidak ada Product_Code yang dipilih, kirim error
        if (empty($productCode)) {
            Log::error('AssetFixing store gagal: Product_Code kosong. User=' . $Saya);
            return response()->json([
                'status'  => 'error',
                'message' => 'Harap pilih aset dari daftar terlebih dahulu!',
            ]);
        }

        // Ambil data dari asset_list berdasarkan Product_Code
        $asset = DB::table('asset_list')
            ->where('Product_Code', $productCode)
            ->select('Asset_No', 'Product_Code', 'Product_Description', 'SN', 'Asset_Location', 'Asset_Location_Department', 'WH_ID')
            ->first();

        if (!$asset) {
            Log::error('AssetFixing store gagal: asset_list tidak ditemukan untuk Product_Code=' . $productCode . '. User=' . $Saya);
            return response()->json([
                'status'  => 'error',
                'message' => 'Aset tidak ditemukan di asset_list!',
            ]);
        }

        // Nomor_Seri = SN dari asset_list
        $nomorSeri = $asset->SN ?? '';

        // Estimasi biaya parsing
        $estimasiRaw  = $request->input('Estimasi_Biaya', '0');
        $estimasi     = (float) str_replace(['Rp', '.', ',', ' '], ['', '', '', ''], $estimasiRaw);

        // Status awal: menunggu approval PIC
        $status = 'APPROVAL_PIC';

        $data = [
            'No_Trx'           => 'AF/' . date('Ymd') . '/' . $jenis . '/' . date('ymdhis'),
            'Jenis_Perbaikan'  => $jenis,
            'Product_Code'     => $asset->Product_Code,
            'Asset_No'         => $asset->Asset_No,
            'Nama_Pengaju'     => Auth::user()->Name ?: $Saya,
            'Jabatan'          => Auth::user()->Position,
            'Tanggal_Pengajuan' => now()->format('Y-m-d'),
            'Nomor_Formulir'   => $request->input('Nomor_Formulir', ''),
            'Nama_Aset'        => $request->input('Nama_Aset', $asset->Product_Description),
            'Nomor_Seri'       => $nomorSeri,
            'Deskripsi_Aset'   => $request->input('Deskripsi_Aset', $asset->Product_Description),
            'Kondisi_Aset'     => $request->input('Kondisi_Aset', ''),
            'Lokasi_Saat_Ini'  => $request->input('Lokasi_Saat_Ini', $asset->Asset_Location ?? $asset->Asset_Location_Department ?? ''),
            'Alasan_Perkiraan' => $request->input('Alasan_Perkiraan', ''),
            'Estimasi_Biaya'   => $estimasi,
            'Vendor_Yang_Digunakan' => $request->input('Vendor_Yang_Digunakan', ''),
            'Status'           => $status,
            'Department'       => Auth::user()->Department,
            'User_NIP'         => Auth::user()->NIP ?? Auth::user()->username,
            'User_Trx'         => $Saya,
        ];

        Log::info('AssetFixing store: ' . json_encode($data));

        try {
            DB::table('asset_fixing')->insert($data);
        } catch (\Exception $e) {
            Log::error('AssetFixing store DB error: ' . $e->getMessage());
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

    public function detail(Request $request)
    {
        $data = DB::table('asset_fixing')->where('id', $request->input('ID'))->first();
        if (!$data) {
            abort(404);
        }
        return view('humanresources.asset-fixing.detail', compact('data'));
    }

    public function log(Request $request)
    {
        return view('humanresources.asset-fixing.log');
    }
}
