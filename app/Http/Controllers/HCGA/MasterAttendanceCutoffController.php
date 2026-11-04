<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceCutoffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterAttendanceCutoffController extends Controller
{
    protected $cutoffService;

    public function __construct(AttendanceCutoffService $cutoffService)
    {
        $this->cutoffService = $cutoffService;
    }

    /**
     * Tampilan Halaman Master Attendance Cutoff (Adminor V3 Responsive)
     */
    public function index()
    {
        $cutoffs = DB::table('master_attendance_cutoff')
            ->orderBy('is_default', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $departments = DB::table('users')
            ->whereNotNull('Department')
            ->where('Department', '!=', '')
            ->distinct()
            ->orderBy('Department', 'asc')
            ->pluck('Department');

        return view('humanresources.master.attendance-cutoff', compact('cutoffs', 'departments'));
    }

    /**
     * Data XML untuk kompatibilitas dhtmlXGrid legacy jika dibutuhkan
     */
    public function dataXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";
        echo "<rows>";

        $cutoffs = DB::table('master_attendance_cutoff')->orderBy('is_default', 'desc')->orderBy('id', 'asc')->get();
        $no = 1;
        foreach ($cutoffs as $row) {
            echo "<row id=\"{$row->id}\">";
            echo "<cell>{$no}</cell>";
            echo "<cell>" . htmlspecialchars($row->cutoff_code) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->name) . "</cell>";
            echo "<cell>" . (int)$row->start_day . "</cell>";
            echo "<cell>" . (int)$row->end_day . "</cell>";
            echo "<cell>" . htmlspecialchars($row->target_type) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->target_value ?: 'Semua') . "</cell>";
            echo "<cell>" . ($row->is_default ? 'Ya' : 'Tidak') . "</cell>";
            echo "<cell>" . htmlspecialchars($row->status) . "</cell>";
            echo "</row>";
            $no++;
        }
        echo "</rows>";
    }

    /**
     * Simpan Konfigurasi Cutoff Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cutoff_code'   => 'required|string|max:50|unique:master_attendance_cutoff,cutoff_code',
            'name'          => 'required|string|max:150',
            'start_day'     => 'required|integer|min:1|max:31',
            'end_day'       => 'required|integer|min:1|max:31',
            'cutoff_cycle'  => 'required|in:PREV_TO_CURRENT,CURRENT_TO_NEXT,SAME_MONTH',
            'target_type'   => 'required|in:GLOBAL,DEPARTMENT,EMPLOYEE_TYPE,EMPLOYEE',
            'target_value'  => 'nullable|string|max:100',
            'is_default'    => 'nullable|boolean',
            'notes'         => 'nullable|string',
        ]);

        $user = Auth::user();
        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use ($validated, $isDefault, $user) {
            if ($isDefault) {
                DB::table('master_attendance_cutoff')->update(['is_default' => 0]);
            }

            DB::table('master_attendance_cutoff')->insert([
                'cutoff_code'   => strtoupper(trim($validated['cutoff_code'])),
                'name'          => trim($validated['name']),
                'start_day'     => (int) $validated['start_day'],
                'end_day'       => (int) $validated['end_day'],
                'cutoff_cycle'  => $validated['cutoff_cycle'],
                'target_type'   => $validated['target_type'],
                'target_value'  => $validated['target_value'] ?: null,
                'is_default'    => $isDefault ? 1 : 0,
                'status'        => 'ACTIVE',
                'notes'         => $validated['notes'] ?? null,
                'created_by'    => $user->username ?? 'SYSTEM',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Konfigurasi cutoff absensi berhasil ditambahkan.',
            ]);
        }

        return redirect()->back()->with('success', 'Konfigurasi cutoff absensi berhasil ditambahkan.');
    }

    /**
     * Update Konfigurasi Cutoff
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'start_day'     => 'required|integer|min:1|max:31',
            'end_day'       => 'required|integer|min:1|max:31',
            'cutoff_cycle'  => 'required|in:PREV_TO_CURRENT,CURRENT_TO_NEXT,SAME_MONTH',
            'target_type'   => 'required|in:GLOBAL,DEPARTMENT,EMPLOYEE_TYPE,EMPLOYEE',
            'target_value'  => 'nullable|string|max:100',
            'is_default'    => 'nullable|boolean',
            'status'        => 'required|in:ACTIVE,INACTIVE',
            'notes'         => 'nullable|string',
        ]);

        $user = Auth::user();
        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use ($id, $validated, $isDefault, $user) {
            if ($isDefault) {
                DB::table('master_attendance_cutoff')->where('id', '!=', $id)->update(['is_default' => 0]);
            }

            DB::table('master_attendance_cutoff')->where('id', $id)->update([
                'name'          => trim($validated['name']),
                'start_day'     => (int) $validated['start_day'],
                'end_day'       => (int) $validated['end_day'],
                'cutoff_cycle'  => $validated['cutoff_cycle'],
                'target_type'   => $validated['target_type'],
                'target_value'  => $validated['target_value'] ?: null,
                'is_default'    => $isDefault ? 1 : 0,
                'status'        => $validated['status'],
                'notes'         => $validated['notes'] ?? null,
                'updated_by'    => $user->username ?? 'SYSTEM',
                'updated_at'    => now(),
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Konfigurasi cutoff absensi berhasil diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Konfigurasi cutoff absensi berhasil diperbarui.');
    }

    /**
     * Hapus Konfigurasi Cutoff
     */
    public function destroy($id)
    {
        $cutoff = DB::table('master_attendance_cutoff')->where('id', $id)->first();
        if (!$cutoff) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        if ($cutoff->is_default) {
            return response()->json(['status' => 'error', 'message' => 'Cutoff default tidak boleh dihapus.'], 422);
        }

        DB::table('master_attendance_cutoff')->where('id', $id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Konfigurasi cutoff berhasil dihapus.']);
    }

    /**
     * Endpoint API Preview Rentang Tanggal Cutoff berdasarkan Periode Bulan
     */
    public function previewRange(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $cutoffId = $request->input('cutoff_id');

        $range = $this->cutoffService->getCutoffPeriodRange($periode, $cutoffId);

        return response()->json([
            'status' => 'success',
            'range'  => $range,
        ]);
    }
}

