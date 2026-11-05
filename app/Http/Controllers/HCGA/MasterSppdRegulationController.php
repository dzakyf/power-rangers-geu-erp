<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\MasterSppdJobLevel;
use App\MasterSppdTripType;
use App\MasterSppdAllowance;
use App\MasterSppdComponent;
use Illuminate\Support\Facades\DB;

class MasterSppdRegulationController extends Controller
{
    /**
     * Relasi Komponen Tunjangan (master_sppd_components.code) -> kolom master_sppd_allowances.
     * format: list (JSON array), text, money (Rp), star (bintang hotel).
     * Hanya komponen dengan code di sini yang tampil pada Matriks Plafon dan form edit plafon.
     */
    public static function componentMap()
    {
        return [
            'transport_land'              => ['column' => 'transport_land', 'format' => 'list', 'category' => 'Transportasi', 'type' => 'Teks / List Pilihan', 'name' => 'Transport Darat'],
            'transport_air'               => ['column' => 'transport_air', 'format' => 'text', 'category' => 'Transportasi', 'type' => 'Teks', 'name' => 'Transport Udara'],
            'ticket_max'                  => ['column' => 'ticket_max', 'format' => 'money', 'category' => 'Transportasi', 'type' => 'Nominal (Rp)', 'name' => 'Max Harga Tiket'],
            'reimburse_max_per_night'     => ['column' => 'reimburse_max_per_night', 'format' => 'money', 'category' => 'Akomodasi', 'type' => 'Nominal (Rp)', 'name' => 'Max Hotel / Malam'],
            'hotel_star'                  => ['column' => 'hotel_star', 'format' => 'star', 'category' => 'Akomodasi', 'type' => 'Angka (Star)', 'name' => 'Bintang Hotel'],
            'daily_meal'                  => ['column' => 'daily_meal', 'format' => 'money', 'category' => 'Uang Saku', 'type' => 'Nominal (Rp)', 'name' => 'Uang Makan / Hari'],
            'daily_pocket'                => ['column' => 'daily_pocket', 'format' => 'money', 'category' => 'Uang Saku', 'type' => 'Nominal (Rp)', 'name' => 'Uang Saku / Hari'],
            'kos_per_month'               => ['column' => 'kos_per_month', 'format' => 'money', 'category' => 'Bantuan Khusus', 'type' => 'Nominal (Rp)', 'name' => 'Bantuan Kos / Bulan'],
            'relocation_one_time_payment' => ['column' => 'relocation_one_time_payment', 'format' => 'money', 'category' => 'Bantuan Khusus', 'type' => 'Nominal (Rp)', 'name' => 'Bantuan Relokasi (1x)'],
            'mobilization_cost_per_week'  => ['column' => 'mobilization_cost_per_week', 'format' => 'money', 'category' => 'Bantuan Khusus', 'type' => 'Nominal (Rp)', 'name' => 'Uang Perbantuan / Minggu'],
        ];
    }

    /** Pastikan komponen standar tersedia (tidak menimpa data yang sudah ada). */
    protected function ensureDefaultComponents()
    {
        foreach (self::componentMap() as $code => $def) {
            MasterSppdComponent::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $def['name'],
                    'category' => $def['category'],
                    'type' => $def['type'],
                    'description' => 'Komponen standar matriks regulasi SPPD',
                ]
            );
        }
    }

    public function index()
    {
        $this->ensureDefaultComponents();

        $jobLevels = MasterSppdJobLevel::orderBy('id', 'asc')->get();
        $tripTypes = MasterSppdTripType::orderBy('id', 'asc')->get();
        $allowances = MasterSppdAllowance::orderBy('id', 'asc')->get();
        $components = MasterSppdComponent::orderBy('id', 'asc')->get();
        $componentMap = self::componentMap();

        return view('humanresources.sppd.master', compact('jobLevels', 'tripTypes', 'allowances', 'components', 'componentMap'));
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

    // --- ALLOWANCE COMPONENTS CRUD ---

    public function getAllowanceComponents()
    {
        $components = MasterSppdComponent::orderBy('id', 'asc')->get();
        return response()->json(['status' => 'success', 'data' => $components]);
    }

    public function storeComponent(Request $request)
    {
        $request->validate([
            'code'        => 'required|string|max:100|unique:master_sppd_components,code|in:' . implode(',', array_keys(self::componentMap())),
            'name'        => 'required|string|max:150',
            'category'    => 'required|string|max:100',
            'type'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ], ['code.in' => 'Kode komponen harus salah satu kolom plafon yang tersedia agar terhubung ke matriks.']);

        $item = MasterSppdComponent::create([
            'code'        => strtolower(str_replace(' ', '_', trim($request->code))),
            'name'        => trim($request->name),
            'category'    => trim($request->category),
            'type'        => trim($request->type),
            'description' => $request->description ? trim($request->description) : null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Komponen Tunjangan berhasil ditambahkan', 'data' => $item]);
    }

    public function updateComponent(Request $request, $id)
    {
        $item = MasterSppdComponent::findOrFail($id);

        $request->validate([
            'code'        => 'required|string|max:100|unique:master_sppd_components,code,' . $id,
            'name'        => 'required|string|max:150',
            'category'    => 'required|string|max:100',
            'type'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $item->update([
            'code'        => strtolower(str_replace(' ', '_', trim($request->code))),
            'name'        => trim($request->name),
            'category'    => trim($request->category),
            'type'        => trim($request->type),
            'description' => $request->description ? trim($request->description) : null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Komponen Tunjangan berhasil diperbarui', 'data' => $item]);
    }

    public function deleteComponent($id)
    {
        $item = MasterSppdComponent::findOrFail($id);
        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Komponen Tunjangan berhasil dihapus']);
    }

    // --- ALLOWANCES MATRIX CRUD ---

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

        $existing = MasterSppdAllowance::where('trip_type_key', $request->trip_type_key)
            ->where('job_level_key', $request->job_level_key)->first();

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
                'assistance_note'             => $request->has('assistance_note') ? ($request->assistance_note ?: null) : ($existing ? $existing->assistance_note : null),
                'relocation_one_time_payment' => $request->relocation_one_time_payment !== null && $request->relocation_one_time_payment !== '' ? (float)$request->relocation_one_time_payment : null,
                'relocation_note'             => $request->has('relocation_note') ? ($request->relocation_note ?: null) : ($existing ? $existing->relocation_note : null),
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
            // delete() (bukan truncate) agar tetap dalam transaksi; truncate memicu implicit commit di MySQL.
            MasterSppdAllowance::query()->delete();
            MasterSppdTripType::query()->delete();
            MasterSppdJobLevel::query()->delete();

            $seeder = new \MasterSppdRegulationSeeder();
            $seeder->run();
            // Komponen tunjangan dipertahankan (seeder tidak mengisinya); lengkapi komponen standar yang hilang.
            $this->ensureDefaultComponents();
            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Data regulasi berhasil disinkronkan ulang dari matric.json!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menyinkronkan data: ' . $e->getMessage()], 500);
        }
    }
}

