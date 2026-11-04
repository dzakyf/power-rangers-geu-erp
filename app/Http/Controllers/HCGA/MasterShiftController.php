<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\MasterShift;
use App\Models\HCGA\RosterPattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterShiftController extends Controller
{
    protected $Saya;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->Saya = Auth::user()->username ?? '';
            return $next($request);
        });
    }

    /**
     * Data XML untuk grid Master Shift (dhtmlXGrid)
     */
    public function xmlShifts()
    {
        header("Content-type: text/xml");
        echo ('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");
        echo "<rows>";

        $shifts = MasterShift::orderBy('id', 'asc')->get();
        $no = 1;

        foreach ($shifts as $s) {
            echo "<row id=\"{$s->id}\">";
            echo "<cell>{$no}</cell>";
            echo "<cell>" . htmlspecialchars($s->shift_code) . "</cell>";
            echo "<cell>" . htmlspecialchars($s->shift_name) . "</cell>";
            echo "<cell>" . htmlspecialchars(substr($s->start_time, 0, 5)) . "</cell>";
            echo "<cell>" . htmlspecialchars(substr($s->end_time, 0, 5)) . "</cell>";
            echo "<cell>" . (int) $s->break_minutes . "</cell>";
            echo "<cell>" . (int) $s->working_minutes . "</cell>";
            echo "<cell>" . ($s->is_cross_day ? '1' : '0') . "</cell>";
            echo "<cell>" . ($s->is_off ? '1' : '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($s->status) . "</cell>";
            echo "</row>";
            $no++;
        }

        echo "</rows>";
    }

    /**
     * Data XML untuk grid Master Pola Roster
     */
    public function xmlRosterPatterns(Request $request)
    {
        header("Content-type: text/xml");
        echo ('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");
        echo "<rows>";

        $query = RosterPattern::with('details.shift');
        if ($request->filled('calendar_id')) {
            $query->where('calendar_id', $request->input('calendar_id'));
        }
        $patterns = $query->orderBy('id', 'asc')->get();
        $no = 1;

        foreach ($patterns as $p) {
            $seqSummary = $p->details->map(function ($d) {
                return $d->shift ? $d->shift->shift_code : '-';
            })->implode(' -> ');

            echo "<row id=\"{$p->id}\">";
            echo "<cell>{$no}</cell>";
            echo "<cell>" . htmlspecialchars($p->pattern_name) . "</cell>";
            echo "<cell>{$p->cycle_days} Hari</cell>";
            echo "<cell>" . htmlspecialchars($seqSummary ?: 'Belum disetup') . "</cell>";
            echo "<cell>" . htmlspecialchars($p->description ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($p->status) . "</cell>";
            echo "</row>";
            $no++;
        }

        echo "</rows>";
    }

    /**
     * Simpan / Perbarui Master Shift
     */
    public function storeShift(Request $request)
    {
        $request->validate([
            'shift_code'      => 'required|string|max:20',
            'shift_name'      => 'required|string|max:100',
            'start_time'      => 'required|date_format:H:i',
            'end_time'        => 'required|date_format:H:i',
            'break_minutes'   => 'nullable|integer|min:0',
            'is_cross_day'    => 'nullable|boolean',
            'is_off'          => 'nullable|boolean',
        ]);

        $data = [
            'shift_code'      => strtoupper($request->input('shift_code')),
            'shift_name'      => $request->input('shift_name'),
            'start_time'      => $request->input('start_time') . ':00',
            'end_time'        => $request->input('end_time') . ':00',
            'break_minutes'   => (int) ($request->input('break_minutes') ?? 60),
            'is_cross_day'    => (int) ($request->input('is_cross_day') ?? 0),
            'is_off'          => (int) ($request->input('is_off') ?? 0),
            'status'          => $request->input('status', 'ACTIVE'),
            'updated_by'      => $this->Saya,
            'updated_at'      => now(),
        ];

        if ($request->filled('id')) {
            $shift = MasterShift::findOrFail($request->input('id'));
            $shift->update($data);
        } else {
            $data['created_by'] = $this->Saya;
            $data['created_at'] = now();
            $shift = MasterShift::create($data);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Master Shift berhasil disimpan.',
            'data'    => $shift,
        ]);
    }

    /**
     * Simpan / Perbarui Pola Siklus Roster Baru
     */
    public function storeRosterPattern(Request $request)
    {
        $request->validate([
            'pattern_name' => 'required|string|max:100',
            'calendar_id'  => 'required|integer',
            'cycle_days'   => 'required|integer|min:1|max:60',
            'shift_ids'    => 'required|array',
            'shift_ids.*'  => 'required|integer|exists:master_shifts,id',
            'description'  => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $pattern = RosterPattern::create([
                'calendar_id'  => (int) $request->input('calendar_id'),
                'pattern_name' => $request->input('pattern_name'),
                'cycle_days'   => (int) $request->input('cycle_days'),
                'description'  => $request->input('description'),
                'status'       => 'ACTIVE',
                'created_by'   => $this->Saya,
                'created_at'   => now(),
            ]);

            $shiftIds = $request->input('shift_ids');
            for ($seq = 1; $seq <= count($shiftIds); $seq++) {
                DB::table('roster_pattern_details')->insert([
                    'pattern_id'   => $pattern->id,
                    'day_sequence' => $seq,
                    'shift_id'     => $shiftIds[$seq - 1],
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pola Roster baru berhasil disimpan.',
                'data'    => $pattern,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan pola roster: ' . $e->getMessage(),
            ], 500);
        }
    }
}

