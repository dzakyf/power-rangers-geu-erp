<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleMaintenanceController extends Controller
{
    /**
     * Sub-category asset yang menandai sebuah asset sebagai kendaraan.
     * Master: master_product_sub_group -> Product_Group=ASSET, Sub_Group=VEHICLE, Initial=VC
     * ("VEHICLE - VC" pada dropdown sub category).
     */
    const SUB_CATEGORY_KENDARAAN = 'VEHICLE';

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
            echo "<cell>" . htmlspecialchars($Fetch->Asset_No) . "</cell>";
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
     *
     * Dropdown hanya berisi kendaraan (asset_list dengan sub category VEHICLE),
     * dan seluruh data kendaraan (plat, kategori, KM, jadwal PM) diambil dari
     * asset_list supaya tidak ada input ganda dengan halaman asset.
     */
    public function create()
    {
        $assetList = DB::table('asset_list')
            ->where('Asset_Sub_Category', self::SUB_CATEGORY_KENDARAAN)
            ->select(
                'Asset_No',
                'Product_Code',
                'Product_Description',
                'SN',
                'Registration_No',
                'Asset_Type',
                'Asset_Location',
                'Asset_Location_Department',
                'KM_Saat_Ini',
                'KM_Jadwal_PM',
                'Interval_PM',
                'Tanggal_Jadwal_PM',
                'Tanggal_PM_Terakhir',
                'Repair_Vendor',
                'WH_ID'
            )
            ->orderBy('Asset_No', 'ASC')
            ->get();

        return view('humanresources.vehicle-maintenance.create', compact('assetList'));
    }

    /**
     * Simpan data perawatan kendaraan.
     *
     * - Data kendaraan (deskripsi, SN, plat, kategori, product code) selalu
     *   diambil dari asset_list, bukan dari input user (hindari dobel input).
     * - Perawatan PREVENTIF: snapshot PM di asset_list diperbarui
     *   (tanggal PM terakhir, tanggal PM berikutnya, KM, vendor, PM_Task_ID).
     * - Perawatan KOREKTIF: hanya odometer (KM_Saat_Ini) yang diperbarui.
     */
    public function store(Request $request)
    {
        $Saya = Auth::user()->username;
        $jenis = $request->input('Jenis_Perawatan', 'preventif');

        // Wajib ada kendaraan yang dipilih
        $assetKey = trim((string) $request->input('Asset_No', ''));
        if ($assetKey === '') {
            $assetKey = trim((string) $request->input('Product_Code', ''));
        }

        if ($assetKey === '') {
            Log::error('VehicleMaintenance store gagal: Asset_No kosong. User=' . $Saya);
            return response()->json([
                'status'  => 'error',
                'message' => 'Harap pilih kendaraan dari daftar terlebih dahulu!',
            ]);
        }

        // Cari di asset_list (Asset_No dulu, lalu Product_Code)
        $selectAsset = 'Asset_No, Product_Code, Product_Description, SN, Registration_No, Jenis_Kendaraan, '
            . 'Asset_Location, Asset_Location_Department, KM_Saat_Ini, KM_Jadwal_PM, Interval_PM, '
            . 'Tanggal_Jadwal_PM, Tanggal_PM_Terakhir, Repair_Vendor';

        $asset = DB::table('asset_list')->where('Asset_No', $assetKey)->select($selectAsset)->first();
        if (!$asset) {
            $asset = DB::table('asset_list')->where('Product_Code', $assetKey)->select($selectAsset)->first();
        }

        if (!$asset) {
            Log::error('VehicleMaintenance store gagal: asset_list tidak ditemukan untuk kode=' . $assetKey . '. User=' . $Saya);
            return response()->json([
                'status'  => 'error',
                'message' => 'Kendaraan tidak ditemukan di asset_list!',
            ]);
        }

        // Sanitasi estimasi biaya
        $estimasiRaw = $request->input('Estimasi_Biaya', '0');
        $estimasi = (float) str_replace(['Rp', '.', ',', ' '], ['', '', '', ''], $estimasiRaw);

        $status = 'APPROVAL_PIC';

        // Interval PM (bulan): dari form, jika kosong ambil dari master asset_list
        $interval = (int) $request->input('Jadwal_Perawatan_Bulan', 0);
        if ($interval <= 0) {
            $interval = (int) ($asset->Interval_PM ?? 0);
        }

        // Tanggal PM: tanggal pengajuan, fallback hari ini
        $tglPengajuan = trim((string) $request->input('Tanggal_Pengajuan', ''));
        if ($tglPengajuan === '') {
            $tglPengajuan = date('Y-m-d');
        }

        // Kilometer saat ini: dari form, fallback ke odometer terakhir di asset_list
        $km = (int) str_replace([' ', '.', ',', 'km', 'KM'], ['', '', '', '', ''], (string) $request->input('Kilometer_Saat_Ini', '0'));
        if ($km <= 0) {
            $km = (int) ($asset->KM_Saat_Ini ?? 0);
        }

        $vendor = trim((string) $request->input('Vendor_Bengkel', ''));
        if ($vendor === '') {
            $vendor = (string) ($asset->Repair_Vendor ?? '');
        }

        $noTrx = 'VM/' . date('Ymd') . '/' . $jenis . '/' . date('ymdhis');

        $data = [
            'No_Trx'               => $noTrx,
            'Jenis_Perawatan'      => $jenis,
            'Asset_No'             => $asset->Asset_No,
            'Product_Code'         => $asset->Product_Code,
            'Product_Description'  => $asset->Product_Description,
            'SN'                   => $asset->SN ?? '',
            // Plat & kategori selalu mengikuti asset_list (tidak ada input ganda)
            'Plat_Nomor'           => $asset->Registration_No ?? '',
            'Kategori_Kendaraan'   => $asset->Asset_Type ?? '',
            'Kilometer_Saat_Ini'   => $km,
            'Tipe_Service_Preventif' => $request->input('Tipe_Service_Preventif', ''),
            'Jadwal_Perawatan_Bulan' => $interval,
            'Kerusakan_Yang_Ditemui' => $request->input('Kerusakan_Yang_Ditemui', ''),
            'Penyebab_Diperkirakan'  => $request->input('Penyebab_Diperkirakan', ''),
            'Komponen_Yang_Diganti'  => $request->input('Komponen_Yang_Diganti', ''),
            'Kondisi_Awal'            => $request->input('Kondisi_Awal', ''),
            'Keterangan_Lain'         => $request->input('Keterangan_Lain', ''),
            'Estimasi_Biaya'          => $estimasi,
            'Vendor_Bengkel'          => $vendor,
            'Status'                  => $status,
            'Department'              => Auth::user()->Department,
            'User_NIP'                => Auth::user()->NIP ?? Auth::user()->username,
            'User_Trx'                => $Saya,
            'Nama_Pengaju'            => $request->input('Nama_Pengaju', Auth::user()->Name ?? ''),
            'Jabatan'                 => $request->input('Jabatan', Auth::user()->Position ?? ''),
            'Tanggal_Pengajuan'       => $tglPengajuan,
            'Nomor_Formulir'          => $request->input('Nomor_Formulir', ''),
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

        // Sinkronkan snapshot PM di asset_list
        try {
            $this->syncAssetPmState($asset, $jenis, $tglPengajuan, $km, $interval, $vendor, $noTrx);
        } catch (\Exception $e) {
            Log::error('VehicleMaintenance sync asset PM error: ' . $e->getMessage());
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil disimpan',
        ], 200);
    }

    /**
     * Perbarui state PM di asset_list sesuai perawatan yang baru dicatat.
     *
     * - Korektif  : hanya odometer (KM_Saat_Ini) yangaju, jadwal PM tidak berubah.
     * - Preventif : tanggal PM terakhir, tanggal PM berikutnya (tgl + interval),
     *   KM, vendor, dan PM_Task_ID (penunjuk ke transaksi ini) diperbarui.
     */
    protected function syncAssetPmState($asset, string $jenis, string $tglPM, int $km, int $interval, string $vendor, string $noTrx)
    {
        $upd = [
            'KM_Saat_Ini' => $km > 0 ? $km : (int) ($asset->KM_Saat_Ini ?? 0),
        ];

        if ($jenis === 'preventif') {
            $upd['Tanggal_PM_Terakhir'] = $tglPM;
            $upd['PM_Task_ID'] = $noTrx;
            // Reminder PM sebelumnya sudah tidak relevan
            $upd['Last_Reminder_Sent'] = null;

            if ($interval > 0) {
                $upd['Tanggal_Jadwal_PM'] = Carbon::parse($tglPM)->addMonths($interval)->format('Y-m-d');
                // Jadwal per bulan ikut master asset
                $upd['Interval_PM'] = $interval;
            }

            if ($vendor !== '') {
                $upd['Repair_Vendor'] = $vendor;
            }

            // Batas KM PM berikutnya:
            // bila bisa dihitung dari siklus sebelumnya (selisih target vs odometer
            // saat PM terakhir), perpetuate. Bila tidak, target lama dipertahankan.
            $targetLama = (int) ($asset->KM_Jadwal_PM ?? 0);
            if ($targetLama > 0 && $km > 0 && $km >= $targetLama) {
                $kmAwal = (int) DB::table('vehicle_maintenance')
                    ->where('Asset_No', $asset->Asset_No)
                    ->where('Jenis_Perawatan', 'preventif')
                    ->max('Kilometer_Saat_Ini');

                if ($kmAwal > 0 && $targetLama > $kmAwal) {
                    $jarakKM = $targetLama - $kmAwal;
                    $upd['KM_Jadwal_PM'] = $km + $jarakKM;
                }
            }
        }

        DB::table('asset_list')->where('Asset_No', $asset->Asset_No)->update($upd);
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