<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\MasterSppdJobLevel;
use App\MasterSppdTripType;
use App\MasterSppdAllowance;
use Illuminate\Support\Facades\DB;

class MasterSppdRegulationController extends Controller
{
    public function index()
    {
        $jobLevels = MasterSppdJobLevel::orderBy('id', 'asc')->get();
        $tripTypes = MasterSppdTripType::orderBy('id', 'asc')->get();
        $allowances = MasterSppdAllowance::orderBy('id', 'asc')->get();

        return view('humanresources.sppd.master', compact('jobLevels', 'tripTypes', 'allowances'));
    }

    // --- JOB LEVELS CRUD ---

    public function getJobLevels()
    {
        $jobLevels = MasterSppdJobLevel::orderBy('id', 'asc')->get();
        return response()->json(['status' => 'success', 'data' => $jobLevels]);
    }

    public function storeJobLevel(Request $request)
    {
        $request->validate([
            'level' => 'required|string|max:100|unique:master_sppd_job_levels,level',
            'grade' => 'required|string|max:50',
            'label' => 'required|string|max:150',
        ]);

        $item = MasterSppdJobLevel::create([
            'level' => strtolower(str_replace(' ', '_', trim($request->level))),
            'grade' => trim($request->grade),
            'label' => trim($request->label),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Level Pekerjaan berhasil ditambahkan', 'data' => $item]);
    }

    public function updateJobLevel(Request $request, $id)
    {
        $item = MasterSppdJobLevel::findOrFail($id);

        $request->validate([
            'level' => 'required|string|max:100|unique:master_sppd_job_levels,level,' . $id,
            'grade' => 'required|string|max:50',
            'label' => 'required|string|max:150',
        ]);

        $item->update([
            'level' => strtolower(str_replace(' ', '_', trim($request->level))),
            'grade' => trim($request->grade),
            'label' => trim($request->label),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Level Pekerjaan berhasil diperbarui', 'data' => $item]);
    }

    public function deleteJobLevel($id)
    {
        $item = MasterSppdJobLevel::findOrFail($id);
        MasterSppdAllowance::where('job_level_key', $item->level)->delete();
        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Level Pekerjaan berhasil dihapus']);
    }

    // --- TRIP TYPES CRUD ---

    public function getTripTypes()
    {
        $tripTypes = MasterSppdTripType::orderBy('id', 'asc')->get();
        return response()->json(['status' => 'success', 'data' => $tripTypes]);
    }

    public function storeTripType(Request $request)
    {
        $request->validate([
            'type_key' => 'required|string|max:100|unique:master_sppd_trip_types,type_key',
            'name'     => 'required|string|max:150',
            'duration' => 'nullable|string|max:100',
            'note'     => 'nullable|string',
        ]);

        $item = MasterSppdTripType::create([
            'type_key' => strtolower(str_replace(' ', '_', trim($request->type_key))),
            'name'     => trim($request->name),
            'duration' => $request->duration ? trim($request->duration) : null,
            'note'     => $request->note ? trim($request->note) : null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Jenis Perjalanan berhasil ditambahkan', 'data' => $item]);
    }

    public function updateTripType(Request $request, $id)
    {
        $item = MasterSppdTripType::findOrFail($id);

        $request->validate([
            'type_key' => 'required|string|max:100|unique:master_sppd_trip_types,type_key,' . $id,
            'name'     => 'required|string|max:150',
            'duration' => 'nullable|string|max:100',
            'note'     => 'nullable|string',
        ]);

        $item->update([
            'type_key' => strtolower(str_replace(' ', '_', trim($request->type_key))),
            'name'     => trim($request->name),
            'duration' => $request->duration ? trim($request->duration) : null,
            'note'     => $request->note ? trim($request->note) : null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Jenis Perjalanan berhasil diperbarui', 'data' => $item]);
    }

    public function deleteTripType($id)
    {
        $item = MasterSppdTripType::findOrFail($id);
        MasterSppdAllowance::where('trip_type_key', $item->type_key)->delete();
        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Jenis Perjalanan berhasil dihapus']);
    }

    // --- ALLOWANCES MATRIX CRUD ---

    public function getAllowanceComponents()
    {
        $components = [
            ['key' => 'transport_land', 'name' => 'Transport Darat', 'category' => 'Transportasi', 'type' => 'array/string', 'desc' => 'Moda transportasi darat yang diizinkan (misal: kereta ekonomi, bus ekonomi)'],
            ['key' => 'transport_air', 'name' => 'Transport Udara', 'category' => 'Transportasi', 'type' => 'string', 'desc' => 'Kelas penerbangan udara yang diizinkan (misal: pesawat ekonomi)'],
            ['key' => 'ticket_max', 'name' => 'Batas Maksimal Tiket', 'category' => 'Transportasi', 'type' => 'decimal', 'desc' => 'Batas atas klaim tiket transportasi'],
            ['key' => 'reimburse_max_per_night', 'name' => 'Reimburse Maksimal per Malam', 'category' => 'Akomodasi', 'type' => 'decimal', 'desc' => 'Batas maksimum klaim reimbursement hotel per malam'],
            ['key' => 'hotel_star', 'name' => 'Bintang Hotel', 'category' => 'Akomodasi', 'type' => 'integer', 'desc' => 'Kelas standar bintang penginapan/hotel'],
            ['key' => 'daily_meal', 'name' => 'Uang Makan / Hari', 'category' => 'Uang Harian', 'type' => 'decimal', 'desc' => 'Alokasi tunjangan makan per hari'],
            ['key' => 'daily_pocket', 'name' => 'Uang Saku / Hari', 'category' => 'Uang Harian', 'type' => 'decimal', 'desc' => 'Alokasi tunjangan uang saku harian'],
            ['key' => 'kos_per_month', 'name' => 'Bantuan Kos / Bulan', 'category' => 'Bantuan Khusus', 'type' => 'decimal', 'desc' => 'Bantuan sewa tempat tinggal per bulan'],
            ['key' => 'relocation_one_time_payment', 'name' => 'Bantuan Relokasi', 'category' => 'Bantuan Khusus', 'type' => 'decimal', 'desc' => 'Uang bantuan relokasi (one-time payment)'],
            ['key' => 'mobilization_cost_per_week', 'name' => 'Mobilisasi / Minggu', 'category' => 'Bantuan Khusus', 'type' => 'decimal', 'desc' => 'Biaya mobilisasi rutin per minggu'],
        ];

        return response()->json(['status' => 'success', 'data' => $components]);
    }

    public function getAllowances()
    {
        $allowances = MasterSppdAllowance::orderBy('id', 'asc')->get();
        return response()->json(['status' => 'success', 'data' => $allowances]);
    }

    public function saveAllowance(Request $request)
    {
        $request->validate([
            'trip_type_key' => 'required|string',
            'job_level_key' => 'required|string',
        ]);

        $transportLand = $request->transport_land;
        if (is_array($transportLand)) {
            $transportLand = json_encode($transportLand);
        } elseif (is_string($transportLand) && trim($transportLand) !== '') {
            $landArr = array_map('trim', explode(',', $transportLand));
            $transportLand = json_encode($landArr);
        } else {
            $transportLand = null;
        }

        $allowance = MasterSppdAllowance::updateOrCreate(
            [
                'trip_type_key' => $request->trip_type_key,
                'job_level_key' => $request->job_level_key,
            ],
            [
                'transport_land'              => $transportLand,
                'transport_air'               => $request->transport_air ?: null,
                'ticket_max'                  => $request->ticket_max !== null && $request->ticket_max !== '' ? (float)$request->ticket_max : null,
                'reimburse_max_per_night'     => $request->reimburse_max_per_night !== null && $request->reimburse_max_per_night !== '' ? (float)$request->reimburse_max_per_night : null,
                'hotel_star'                  => $request->hotel_star !== null && $request->hotel_star !== '' ? (int)$request->hotel_star : null,
                'daily_meal'                  => $request->daily_meal !== null && $request->daily_meal !== '' ? (float)$request->daily_meal : null,
                'daily_pocket'                => $request->daily_pocket !== null && $request->daily_pocket !== '' ? (float)$request->daily_pocket : null,
                'kos_per_month'               => $request->kos_per_month !== null && $request->kos_per_month !== '' ? (float)$request->kos_per_month : null,
                'assistance_note'             => $request->assistance_note ?: null,
                'relocation_one_time_payment' => $request->relocation_one_time_payment !== null && $request->relocation_one_time_payment !== '' ? (float)$request->relocation_one_time_payment : null,
                'relocation_note'             => $request->relocation_note ?: null,
                'mobilization_cost_per_week'  => $request->mobilization_cost_per_week !== null && $request->mobilization_cost_per_week !== '' ? (float)$request->mobilization_cost_per_week : null,
                'mobilization_note'           => $request->mobilization_note ?: null,
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Matriks Tunjangan berhasil diperbarui', 'data' => $allowance]);
    }

    // --- RESET / SYNC FROM JSON ---

    public function syncFromJson()
    {
        try {
            DB::beginTransaction();
            MasterSppdAllowance::truncate();
            MasterSppdTripType::truncate();
            MasterSppdJobLevel::truncate();

            $seeder = new \MasterSppdRegulationSeeder();
            $seeder->run();
            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Data regulasi berhasil disinkronkan ulang dari matric.json!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menyinkronkan data: ' . $e->getMessage()], 500);
        }
    }
}
