<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarRentController extends Controller
{
    const TRIP_NON_SPPD = 'NON_SPPD';
    const TRIP_SPPD = 'SPPD';

    public function index()
    {
        return view('humanresources.car-rent.index');
    }

    public function data(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $priv = Auth::user()->Priv_ID;
        $nik = Auth::user()->Emp_ID;

        $getCarRent = DB::table('car_rent');

        if ($priv != 'HCGA') {
            $getCarRent->where('User_NIK', $nik);
        }

        // Filter index: Trip_Type / SPPD_No / Jenis_Mobil / rentang tanggal.
        if ($request->filled('Trip_Type')) {
            $getCarRent->where('Trip_Type', $request->input('Trip_Type'));
        }
        if ($request->filled('SPPD_No')) {
            $getCarRent->where('SPPD_No', 'LIKE', '%' . $request->input('SPPD_No') . '%');
        }
        if ($request->filled('Jenis_Mobil')) {
            $getCarRent->where('Jenis_Mobil', $request->input('Jenis_Mobil'));
        }
        if ($request->filled('Tanggal_From')) {
            $getCarRent->where('Tanggal_Peminjaman', '>=', $request->input('Tanggal_From'));
        }
        if ($request->filled('Tanggal_To')) {
            $getCarRent->where('Tanggal_Peminjaman', '<=', $request->input('Tanggal_To'));
        }
        $getCarRent->orderBy('ID', 'DESC');

        $getCarRent = $getCarRent->get();

        $nomor = 1;
        echo "<rows>";
        foreach ($getCarRent as $key => $value) {
            $tripType = isset($value->Trip_Type) && $value->Trip_Type ? $value->Trip_Type : self::TRIP_NON_SPPD;
            $sppdNo = isset($value->SPPD_No) && $value->SPPD_No ? $value->SPPD_No : '-';
            echo "<row id='{$value->ID}'>";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($value->Car_Rent_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->User_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->User_NIK) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->User_Jabatan) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->User_Department) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Site_Office) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Sim_Link) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Keperluan) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Tujuan_Penggunaan) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Tanggal_Peminjaman) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Jam_Peminjaman) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Tanggal_Pengembalian) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Jam_Pengembalian) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Jenis_Mobil) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Kerusakan_Denda) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Bukti_Pembayaran) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($tripType) . "</cell>";
            echo "<cell>" . htmlspecialchars($sppdNo) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function create(Request $request)
    {
        $LSP = Auth::user()->Location;
        $NIK = Auth::user()->Emp_ID;
        $Saya = Auth::user()->username;
        $counter = DB::table('car_rent')->where("Created_By", "LIKE", date("Y-m") . "%")->count() + 1;
        $Car_Rent_No = "CR/GA/" . $LSP . "/" . date("ymdhis") . "-" . $counter;

        $sppdList = $this->eligibleSppd();
        $selectedSppd = $request->input('SPPD_ID');
        $fleetList = self::fleetOptions();

        return view('humanresources.car-rent.create', compact('Car_Rent_No', 'NIK', 'Saya', 'sppdList', 'selectedSppd', 'fleetList'));
    }

    public function store(Request $request)
    {
        list($err, $clean) = $this->validateRent($request->all());
        if ($err) {
            return response()->json(['status' => 'error', 'message' => $err], 422);
        }

        $clean['Created_By'] = Auth::user()->username;
        $clean['Created_Date'] = now();
        $clean['Status'] = "CREATED";

        DB::beginTransaction();
        try {
            $conflict = $this->checkOverlap($clean);
            if ($conflict) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $conflict], 422);
            }
            DB::table('car_rent')->insert($clean);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CarRent create failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menginput data peminjaman mobil",
        ]);
    }

    public function detail(Request $request)
    {
        $cr = DB::table('car_rent')->where('Car_Rent_No', $request->Car_Rent_No)->first();
        $sppd = null;
        if ($cr && !empty($cr->SPPD_ID)) {
            $sppd = DB::table('sppd')->where('ID', $cr->SPPD_ID)->first();
        }
        return view('humanresources.car-rent.detail', compact('cr', 'sppd'));
    }

    public function edit(Request $request)
    {
        $cr = DB::table('car_rent')->where('Car_Rent_No', $request->Car_Rent_No)->first();
        $sppdList = $this->eligibleSppd();
        $fleetList = self::fleetOptions();
        return view('humanresources.car-rent.edit', compact('cr', 'sppdList', 'fleetList'));
    }

    public function update(Request $request)
    {
        list($err, $clean) = $this->validateRent($request->all(), $request->ID);
        if ($err) {
            return response()->json(['status' => 'error', 'message' => $err], 422);
        }

        $clean['Updated_By'] = Auth::user()->username;
        $clean['Updated_Date'] = now();

        DB::beginTransaction();
        try {
            $conflict = $this->checkOverlap($clean, $request->ID);
            if ($conflict) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $conflict], 422);
            }
            DB::table('car_rent')->where('ID', $request->ID)->update($clean);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CarRent update failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil update data",
        ]);
    }

    // SPPD yang boleh dilink: sudah ACCEPTED, milik pengaju (kecuali HCGA lihat semua).
    private function eligibleSppd()
    {
        $q = DB::table('sppd')->where('Status', 'ACCEPTED')->orderBy('ID', 'DESC');
        if (Auth::user()->Priv_ID != 'HCGA') {
            $q->where('Requester_ID', Auth::user()->id);
        }
        return $q->get();
    }

    // Validasi cabang SPPD vs non-SPPD. Return [error|null, cleaned|null].
    private function validateRent(array $in, $excludeId = null)
    {
        $allow = ['Car_Rent_No', 'User_NIK', 'User_Name', 'User_Jabatan', 'User_Department',
            'Site_Office', 'Phone_Number', 'Email', 'Sim_Link', 'Keperluan', 'Tujuan_Penggunaan',
            'Tanggal_Peminjaman', 'Jam_Peminjaman', 'Tanggal_Pengembalian', 'Jam_Pengembalian',
            'Jenis_Mobil', 'Kerusakan_Denda', 'Bukti_Pembayaran', 'Trip_Type', 'SPPD_ID'];
        $clean = [];
        foreach ($allow as $f) {
            if (array_key_exists($f, $in)) {
                $clean[$f] = is_string($in[$f]) ? trim($in[$f]) : $in[$f];
            }
        }
        if (empty($clean['Trip_Type']) || !in_array($clean['Trip_Type'], [self::TRIP_SPPD, self::TRIP_NON_SPPD])) {
            $clean['Trip_Type'] = self::TRIP_NON_SPPD;
        }
        if (empty($clean['Keperluan'])) {
            $clean['Keperluan'] = 'Peminjaman Mobil Operasional';
        }
        foreach (['Tanggal_Peminjaman', 'Tanggal_Pengembalian', 'Jenis_Mobil'] as $f) {
            if (empty($clean[$f])) {
                return ["Field $f wajib diisi", null];
            }
        }
        if ($clean['Tanggal_Pengembalian'] < $clean['Tanggal_Peminjaman']) {
            return ['Tanggal Pengembalian tidak boleh sebelum Tanggal Peminjaman', null];
        }

        if ($clean['Trip_Type'] === self::TRIP_SPPD) {
            if (empty($clean['SPPD_ID'])) {
                return ['SPPD wajib dipilih untuk tipe SPPD', null];
            }
            $sppd = DB::table('sppd')->where('ID', $clean['SPPD_ID'])->first();
            if (!$sppd) {
                return ['Data SPPD tidak ditemukan', null];
            }
            if ($sppd->Status !== 'ACCEPTED') {
                return ['SPPD ' . $sppd->SPPD_No . ' belum disetujui (status: ' . $sppd->Status . ')', null];
            }
            if (Auth::user()->Priv_ID != 'HCGA' && (int) $sppd->Requester_ID !== (int) Auth::user()->id) {
                return ['Anda bukan pengaju SPPD ' . $sppd->SPPD_No, null];
            }
            if (!empty($sppd->Schedule_Date) && $clean['Tanggal_Peminjaman'] < $sppd->Schedule_Date) {
                return ['Tanggal Peminjaman tidak boleh sebelum jadwal SPPD (' . $sppd->Schedule_Date . ')', null];
            }
            $clean['SPPD_No'] = $sppd->SPPD_No;
            if (empty($clean['Tujuan_Penggunaan'])) {
                $clean['Tujuan_Penggunaan'] = $sppd->SPPD_No . ' - ' . $sppd->Destination;
            }
        } else {
            $clean['SPPD_ID'] = null;
            $clean['SPPD_No'] = null;
        }

        if (empty($clean['Tujuan_Penggunaan'])) {
            return ['Field Tujuan_Penggunaan wajib diisi', null];
        }

        return [null, $clean];
    }

    // Tolak booking ganda: mobil sama + rentang tanggal irisan + status bukan REJECTED.
    // public agar bisa dipakai ulang dari createFromSppd (isi query tidak diubah).
    public function checkOverlap(array $clean, $excludeId = null)
    {
        $q = DB::table('car_rent')
            ->where('Jenis_Mobil', $clean['Jenis_Mobil'])
            ->where('Status', '!=', 'REJECTED')
            ->where('Tanggal_Peminjaman', '<=', $clean['Tanggal_Pengembalian'])
            ->where('Tanggal_Pengembalian', '>=', $clean['Tanggal_Peminjaman']);
        if ($excludeId) {
            $q->where('ID', '!=', $excludeId);
        }
        if ($q->exists()) {
            return 'Mobil ' . $clean['Jenis_Mobil'] . ' sudah dibooking pada rentang tanggal tersebut';
        }
        return null;
    }

    // Opsi dropdown mobil: master_fleet ACTIVE jadi acuan tunggal, fallback hardcode bila kosong.
    // Return array string siap dipakai blade car-rent + sppd. ponytail: tambah kolom tipe/kapasitas bila armada bertambah.
    public static function fleetOptions()
    {
        try {
            $rows = DB::table('master_fleet')
                ->where('Status', 'ACTIVE')
                ->orderBy('Fleet_Brand', 'ASC')
                ->get();
            $out = [];
            foreach ($rows as $r) {
                $label = trim(($r->Fleet_Brand ?? '') . ($r->Police_Number ? ' (' . $r->Police_Number . ')' : ''));
                if ($label !== '') {
                    $out[] = $label;
                }
            }
            if (!empty($out)) {
                return array_values(array_unique($out));
            }
        } catch (\Exception $e) {
            // tabel belum ada / kolom beda — pakai fallback di bawah
        }
        return ['Rush', 'Avanza', 'Innova', 'Pajero', 'Alphard', 'Hiace', 'Other'];
    }

    // Auto-create booking mobil dari SPPD yang sudah ACCEPTED + Need_Car=1.
    // Dipakai SppdController (auto-approve) dan SppdApprovalController (approve manual).
    // Idempoten: SPPD_ID yang sudah punya car_rent aktif tidak dibuatkan lagi.
    // Tidak kelola transaksi sendiri — ikut transaksi caller bila ada.
    // Return [error|null, carRentNo|null, info('created'|'exists'|'skipped')].
    public static function createFromSppd($sppd, $actorUsername)
    {
        if (empty($sppd->Need_Car)) {
            return [null, null, 'skipped'];
        }
        $exists = DB::table('car_rent')
            ->where('SPPD_ID', $sppd->ID)
            ->where('Status', '!=', 'REJECTED')
            ->orderBy('ID', 'DESC')
            ->first();
        if ($exists) {
            return [null, $exists->Car_Rent_No, 'exists'];
        }
        if (empty($sppd->Car_Jenis_Mobil) || empty($sppd->Car_Tgl_Pinjam) || empty($sppd->Car_Tgl_Kembali)) {
            return ['Data kebutuhan mobil di SPPD belum lengkap (jenis mobil / tanggal)', null, null];
        }
        if ($sppd->Car_Tgl_Kembali < $sppd->Car_Tgl_Pinjam) {
            return ['Tanggal kembali mobil tidak boleh sebelum tanggal pinjam', null, null];
        }
        $reqUser = DB::table('users')->where('id', $sppd->Requester_ID)->first();
        $loc = ($reqUser && !empty($reqUser->Location)) ? $reqUser->Location : 'GEU-HO';
        $counter = DB::table('car_rent')->where('Created_By', 'LIKE', date('Y-m') . '%')->count() + 1;
        $clean = [
            'Car_Rent_No' => 'CR/GA/' . $loc . '/' . date('ymdhis') . '-' . $counter,
            'Trip_Type' => self::TRIP_SPPD,
            'SPPD_ID' => $sppd->ID,
            'SPPD_No' => $sppd->SPPD_No,
            'User_NIK' => $sppd->Requester_NIK ?: ($reqUser->Emp_ID ?? ''),
            'User_Name' => $sppd->Requester_Name,
            'User_Jabatan' => $reqUser->Position ?? '',
            'User_Department' => $sppd->Department,
            'Site_Office' => $reqUser->Location ?? '',
            'Phone_Number' => $reqUser->Contact ?? null,
            'Email' => $reqUser->email ?? null,
            'Sim_Link' => null,
            'Keperluan' => 'Peminjaman Mobil Operasional (SPPD)',
            'Tujuan_Penggunaan' => $sppd->SPPD_No . ' - ' . $sppd->Destination,
            'Tanggal_Peminjaman' => $sppd->Car_Tgl_Pinjam,
            'Jam_Peminjaman' => $sppd->Car_Jam_Pinjam ?: '08:00',
            'Tanggal_Pengembalian' => $sppd->Car_Tgl_Kembali,
            'Jam_Pengembalian' => $sppd->Car_Jam_Kembali ?: '17:00',
            'Jenis_Mobil' => $sppd->Car_Jenis_Mobil,
            'Kerusakan_Denda' => null,
            'Bukti_Pembayaran' => null,
            'Status' => 'CREATED',
            'Created_By' => $actorUsername,
            'Created_Date' => now(),
        ];
        $conflict = (new self)->checkOverlap($clean);
        if ($conflict) {
            return [$conflict, null, null];
        }
        DB::table('car_rent')->insert($clean);
        Log::info('CarRent auto-created from SPPD ' . $sppd->SPPD_No . ' -> ' . $clean['Car_Rent_No']);
        return [null, $clean['Car_Rent_No'], 'created'];
    }
}
