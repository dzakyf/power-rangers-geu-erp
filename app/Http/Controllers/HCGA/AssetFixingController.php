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

        if ($dept !== 'HCGA' && $dept !== 'DIR' && $dept !== 'OPERATION') {
            $Query->where('Department', $dept);
        }

        $results = $Query->get();

        echo "<rows>";
        foreach ($results as $Fetch) {
            echo "<row id=\"{$Fetch->id}\">";
            echo "<cell>{$nomor}</cell>";
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
        $assetList = DB::table('asset_list')
            ->select('Asset_No', 'Product_Code', 'Product_Description', 'SN', 'Asset_Location_Department', 'Asset_Location', 'WH_ID')
            ->get();

        return view('humanresources.asset-fixing.create', compact('assetList'));
    }

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

    public function store(Request $request)
    {
        $Saya = Auth::user()->username;
        $jenis = $request->input('Jenis_Perbaikan', 'office');

        // Product_Code adalah kunci utama pencarian ke asset_list.
        // Form mengirimkan Product_Code (readonly, diisi JS saat pilih aset)
        // dan Asset_No (dropdown). Jika Product_Code kosong, fallback ke Asset_No.
        $productCode = $request->input('Product_Code', '');
        if (empty($productCode)) {
            $productCode = $request->input('Asset_No', '');
        }

        if (empty($productCode)) {
            Log::error('AssetFixing store gagal: Product_Code/Asset_No kosong. User=' . $Saya);
            return response()->json([
                'status'  => 'error',
                'message' => 'Harap pilih aset dari daftar terlebih dahulu!',
            ]);
        }

        // Coba cari di asset_list menggunakan Product_Code (utama) atau Asset_No (fallback).
        $asset = DB::table('asset_list')
            ->where('Product_Code', $productCode)
            ->select('Asset_No', 'Product_Code', 'Product_Description', 'SN', 'Asset_Location', 'Asset_Location_Department', 'WH_ID')
            ->first();

        // Jika tidak ditemukan via Product_Code, coba via Asset_No.
        if (!$asset) {
            $asset = DB::table('asset_list')
                ->where('Asset_No', $productCode)
                ->select('Asset_No', 'Product_Code', 'Product_Description', 'SN', 'Asset_Location', 'Asset_Location_Department', 'WH_ID')
                ->first();
        }

        if (!$asset) {
            Log::error('AssetFixing store gagal: asset_list tidak ditemukan untuk kode=' . $productCode . '. User=' . $Saya);
            return response()->json([
                'status'  => 'error',
                'message' => 'Aset tidak ditemukan di asset_list!',
            ]);
        }

        $nomorSeri = $asset->SN ?? '';

        $estimasiRaw  = $request->input('Estimasi_Biaya', '0');
        $estimasi     = (float) str_replace(['Rp', '.', ',', ' '], ['', '', '', ''], $estimasiRaw);

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
        $ID = $request->input('ID') ?? $request->input('No_Trx') ?? '';
        return view('humanresources.asset-fixing.log', compact('ID'));
    }
}
