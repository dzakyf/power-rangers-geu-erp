<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Attendance\OvertimeMatrixService;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class OvertimeRequestController extends Controller
{
    protected $overtimeMatrixService;
    protected $attendanceService;

    public function __construct(
        OvertimeMatrixService $overtimeMatrixService,
        AttendanceService $attendanceService
    ) {
        $this->overtimeMatrixService = $overtimeMatrixService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Halaman Master Matriks Lembur (HCGA Admin)
     */
    public function indexMatrix()
    {
        $matrices = DB::table('overtime_rate_matrices')
            ->orderBy('id', 'asc')
            ->get();

        $tiers = DB::table('overtime_matrix_tiers')
            ->orderBy('matrix_id', 'asc')
            ->orderBy('tier_order', 'asc')
            ->get()
            ->groupBy('matrix_id');

        $regions = DB::table('overtime_regions')
            ->orderBy('id', 'asc')
            ->get();

        return view('humanresources.attendant.overtime_matrix_index', compact('matrices', 'tiers', 'regions'));
    }

    /**
     * List Master Wilayah (JSON / API)
     */
    public function indexRegions()
    {
        $regions = DB::table('overtime_regions')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $regions
        ]);
    }

    /**
     * Detail Master Wilayah
     */
    public function getRegionDetail($id)
    {
        $region = DB::table('overtime_regions')->where('id', $id)->first();
        if (!$region) {
            return response()->json(['status' => 'error', 'message' => 'Data wilayah tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $region
        ]);
    }

    /**
     * Simpan Master Wilayah Baru
     */
    public function storeRegion(Request $request)
    {
        $request->validate([
            'region_code'       => 'required|string|max:50|unique:overtime_regions,region_code',
            'region_name'       => 'required|string|max:150',
            'coverage_keywords' => 'nullable|string',
            'description'       => 'nullable|string',
            'status'            => 'required|in:ACTIVE,INACTIVE',
        ]);

        $user = Auth::user();
        $username = $user ? $user->username : 'admin';

        DB::table('overtime_regions')->insert([
            'region_code'       => strtoupper(trim($request->region_code)),
            'region_name'       => trim($request->region_name),
            'coverage_keywords' => trim($request->coverage_keywords),
            'description'       => trim($request->description),
            'status'            => $request->status,
            'created_by'        => $username,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Wilayah baru berhasil ditambahkan!'
        ]);
    }

    /**
     * Update Master Wilayah
     */
    public function updateRegion(Request $request, $id)
    {
        $region = DB::table('overtime_regions')->where('id', $id)->first();
        if (!$region) {
            return response()->json(['status' => 'error', 'message' => 'Data wilayah tidak ditemukan.'], 404);
        }

        $request->validate([
            'region_code'       => 'required|string|max:50|unique:overtime_regions,region_code,' . $id,
            'region_name'       => 'required|string|max:150',
            'coverage_keywords' => 'nullable|string',
            'description'       => 'nullable|string',
            'status'            => 'required|in:ACTIVE,INACTIVE',
        ]);

        $user = Auth::user();
        $username = $user ? $user->username : 'admin';

        DB::table('overtime_regions')->where('id', $id)->update([
            'region_code'       => strtoupper(trim($request->region_code)),
            'region_name'       => trim($request->region_name),
            'coverage_keywords' => trim($request->coverage_keywords),
            'description'       => trim($request->description),
            'status'            => $request->status,
            'updated_by'        => $username,
            'updated_at'        => Carbon::now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data wilayah berhasil diperbarui!'
        ]);
    }

    /**
     * Hapus Master Wilayah
     */
    public function destroyRegion($id)
    {
        $region = DB::table('overtime_regions')->where('id', $id)->first();
        if (!$region) {
            return response()->json(['status' => 'error', 'message' => 'Data wilayah tidak ditemukan.'], 404);
        }

        // Cek apakah ada matriks yang menggunakan region_code ini
        $usedInMatrix = DB::table('overtime_rate_matrices')->where('region_type', $region->region_code)->exists();
        if ($usedInMatrix) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Wilayah ini sedang digunakan pada salah satu Matriks Tarif Lembur dan tidak dapat dihapus.'
            ], 422);
        }

        DB::table('overtime_regions')->where('id', $id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => "Wilayah {$region->region_name} berhasil dihapus."
        ]);
    }

    /**
     * Ambil Detail Matriks & Tiers (JSON) untuk Modal Edit
     */
    public function getMatrixDetail($id)
    {
        $matrix = DB::table('overtime_rate_matrices')->where('id', $id)->first();
        if (!$matrix) {
            return response()->json(['status' => 'error', 'message' => 'Matriks tidak ditemukan'], 404);
        }

        $tiers = DB::table('overtime_matrix_tiers')
            ->where('matrix_id', $id)
            ->orderBy('tier_order', 'asc')
            ->get();

        // Normalisasi format job_titles
        $titles = $matrix->job_titles;
        $decoded = json_decode($titles, true);
        if (is_array($decoded)) {
            $titles = implode(', ', $decoded);
        }

        return response()->json([
            'status' => 'success',
            'matrix' => $matrix,
            'job_titles_str' => $titles,
            'tiers'  => $tiers
        ]);
    }

    /**
     * Simpan Tambah Matriks Baru Beserta Tier-nya
     */
    public function storeMatrix(Request $request)
    {
        $request->validate([
            'matrix_code'  => 'required|string|max:50|unique:overtime_rate_matrices,matrix_code',
            'matrix_name'  => 'required|string|max:150',
            'grade_group'  => 'nullable|string|max:100',
            'job_titles'   => 'nullable|string',
            'region_type'  => 'required|string|max:50',
            'pricing_type' => 'required|in:TIERED_HOURLY,FLAT_TASK,MINUTE_RANGE',
            'max_hours'    => 'required|numeric|min:1|max:24',
            'status'       => 'required|in:ACTIVE,INACTIVE',
            'tiers'        => 'required|array|min:1',
        ]);

        $user = Auth::user();
        $username = $user ? $user->username : 'admin';

        // Konversi job_titles ke format JSON array
        $jobTitles = null;
        if (!empty($request->job_titles)) {
            $items = array_map('trim', explode(',', $request->job_titles));
            $jobTitles = json_encode(array_values(array_filter($items)));
        }

        DB::transaction(function () use ($request, $username, $jobTitles) {
            $matrixId = DB::table('overtime_rate_matrices')->insertGetId([
                'matrix_code'  => strtoupper(trim($request->matrix_code)),
                'matrix_name'  => trim($request->matrix_name),
                'grade_group'  => trim($request->grade_group),
                'job_titles'   => $jobTitles,
                'region_type'  => $request->region_type,
                'pricing_type' => $request->pricing_type,
                'max_hours'    => (float) $request->max_hours,
                'status'       => $request->status,
                'created_by'   => $username,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]);

            $order = 1;
            foreach ($request->tiers as $t) {
                DB::table('overtime_matrix_tiers')->insert([
                    'matrix_id'          => $matrixId,
                    'tier_order'         => $order++,
                    'min_minutes'        => (int) ($t['min_minutes'] ?? 0),
                    'max_minutes'        => isset($t['max_minutes']) && $t['max_minutes'] !== '' ? (int) $t['max_minutes'] : null,
                    'tier_label'         => trim($t['tier_label'] ?? ''),
                    'base_rate'          => (float) str_replace(['.', ','], ['', '.'], $t['base_rate'] ?? 0),
                    'next_per_hour_rate' => (float) str_replace(['.', ','], ['', '.'], $t['next_per_hour_rate'] ?? 0),
                    'created_at'         => Carbon::now(),
                    'updated_at'         => Carbon::now(),
                ]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Matriks tarif lembur berhasil ditambahkan!'
        ]);
    }

    /**
     * Update Perubahan Matriks & Tiers
     */
    public function updateMatrix(Request $request, $id)
    {
        $matrix = DB::table('overtime_rate_matrices')->where('id', $id)->first();
        if (!$matrix) {
            return response()->json(['status' => 'error', 'message' => 'Data matriks tidak ditemukan'], 404);
        }

        $request->validate([
            'matrix_code'  => 'required|string|max:50|unique:overtime_rate_matrices,matrix_code,' . $id,
            'matrix_name'  => 'required|string|max:150',
            'grade_group'  => 'nullable|string|max:100',
            'job_titles'   => 'nullable|string',
            'region_type'  => 'required|string|max:50',
            'pricing_type' => 'required|in:TIERED_HOURLY,FLAT_TASK,MINUTE_RANGE',
            'max_hours'    => 'required|numeric|min:1|max:24',
            'status'       => 'required|in:ACTIVE,INACTIVE',
            'tiers'        => 'required|array|min:1',
        ]);

        $user = Auth::user();
        $username = $user ? $user->username : 'admin';

        $jobTitles = null;
        if (!empty($request->job_titles)) {
            $items = array_map('trim', explode(',', $request->job_titles));
            $jobTitles = json_encode(array_values(array_filter($items)));
        }

        DB::transaction(function () use ($request, $id, $username, $jobTitles) {
            DB::table('overtime_rate_matrices')->where('id', $id)->update([
                'matrix_code'  => strtoupper(trim($request->matrix_code)),
                'matrix_name'  => trim($request->matrix_name),
                'grade_group'  => trim($request->grade_group),
                'job_titles'   => $jobTitles,
                'region_type'  => $request->region_type,
                'pricing_type' => $request->pricing_type,
                'max_hours'    => (float) $request->max_hours,
                'status'       => $request->status,
                'updated_by'   => $username,
                'updated_at'   => Carbon::now(),
            ]);

            // Hapus tier lama dan simpan tier yang baru
            DB::table('overtime_matrix_tiers')->where('matrix_id', $id)->delete();

            $order = 1;
            foreach ($request->tiers as $t) {
                DB::table('overtime_matrix_tiers')->insert([
                    'matrix_id'          => $id,
                    'tier_order'         => $order++,
                    'min_minutes'        => (int) ($t['min_minutes'] ?? 0),
                    'max_minutes'        => isset($t['max_minutes']) && $t['max_minutes'] !== '' ? (int) $t['max_minutes'] : null,
                    'tier_label'         => trim($t['tier_label'] ?? ''),
                    'base_rate'          => (float) str_replace(['.', ','], ['', '.'], $t['base_rate'] ?? 0),
                    'next_per_hour_rate' => (float) str_replace(['.', ','], ['', '.'], $t['next_per_hour_rate'] ?? 0),
                    'created_at'         => Carbon::now(),
                    'updated_at'         => Carbon::now(),
                ]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Matriks tarif lembur berhasil diperbarui!'
        ]);
    }

    /**
     * Hapus Matriks Lembur
     */
    public function destroyMatrix($id)
    {
        $matrix = DB::table('overtime_rate_matrices')->where('id', $id)->first();
        if (!$matrix) {
            return response()->json(['status' => 'error', 'message' => 'Data matriks tidak ditemukan'], 404);
        }

        DB::table('overtime_rate_matrices')->where('id', $id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Matriks ' . $matrix->matrix_name . ' berhasil dihapus.'
        ]);
    }

    /**
     * API Preview / Kalkulasi Lembur Realtime saat input jam pada form
     */
    public function apiCalculatePreview(Request $request)
    {
        $request->validate([
            'start_time'        => 'required|string',
            'end_time'          => 'required|string',
            'compensation_type' => 'nullable|string',
            'location_id'       => 'nullable|integer',
            'zipcode'           => 'nullable|string',
        ]);

        try {
            $user = $request->user() ?: Auth::user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $locationId = $request->input('location_id');
            $zipcode = $request->input('zipcode');

            // Resolusi kompensasi langsung dari backend berdasarkan hierarki posisi/karyawan
            $compensationInfo = $this->overtimeMatrixService->resolveCompensationType($user);
            $compensationType = $compensationInfo['type'];

            $region = $this->overtimeMatrixService->resolveRegion($zipcode, $locationId);
            $matrix = $this->overtimeMatrixService->resolveMatrix($user, $region);

            $result = $this->overtimeMatrixService->calculateOvertime(
                $request->input('start_time'),
                $request->input('end_time'),
                $matrix,
                $compensationType
            );

            return response()->json([
                'status'            => 'success',
                'region'            => $region,
                'compensation_info' => $compensationInfo,
                'data'              => $result,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghitung lembur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit Pengajuan Lembur dari Portal Absensi
     */
    public function storeOvertimeRequest(Request $request)
    {
        $request->validate([
            'overtime_date'     => 'required|date_format:Y-m-d',
            'day_type'          => 'nullable|in:WORKDAY,HOLIDAY',
            'overtime_type'     => 'required|in:BEFORE_SHIFT,AFTER_SHIFT',
            'start_time'        => 'required|string',
            'end_time'          => 'required|string',
            'compensation_type' => 'nullable|in:PAID,TIME_OFF',
            'task_description'  => 'required|string|max:1000',
            'location_id'       => 'nullable|integer',
            'zipcode'           => 'nullable|string',
            'hardcopy_file'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $user = $request->user() ?: Auth::user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Sesi login telah berakhir.'], 401);
            }

            $uploadedHardcopy = $request->hasFile('hardcopy_file') ? $request->file('hardcopy_file') : null;

            $result = $this->attendanceService->submitOvertimeRequest($user, $request->all(), $uploadedHardcopy);

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }
}
