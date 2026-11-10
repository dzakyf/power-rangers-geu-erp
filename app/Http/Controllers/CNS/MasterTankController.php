<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\MasterTank;
use App\MasterTankLog;
use App\UCOStoragePelindo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class MasterTankController extends Controller
{
    public function index()
    {
        return view('cns.master.tank');
    }

    public function newTank()
    {
        return view('cns.master.tank-new');
    }

    public function detailTank(Request $request)
    {
        $id = $request->query('ID') ?: $request->query('id');
        $tank = MasterTank::with('logs')->findOrFail($id);

        return view('cns.master.tank-detail', compact('tank'));
    }

    public function xmlData(Request $request)
    {
        $status    = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $name      = $request->query('name');

        $query = MasterTank::query()->orderBy('name', 'asc');
        if ($status) {
            $query->where('status', $status);
        }
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }

        $tanks = $query->get();

        header("Content-type: text/xml");
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<rows>\n";

        $no = 1;
        foreach ($tanks as $tank) {
            $currentStock = $this->calculateTankBalance($tank->name);
            $utilization = $tank->capacity > 0 ? round(($currentStock / $tank->capacity) * 100, 1) : 0;

            echo "<row id=\"{$tank->id}\">\n";
            echo "<cell>{$no}</cell>\n";
            echo "<cell>" . htmlspecialchars($tank->name) . "</cell>\n";
            echo "<cell>" . number_format($tank->capacity, 2, ',', '.') . " KG</cell>\n";
            echo "<cell>" . number_format($currentStock, 2, ',', '.') . " KG</cell>\n";
            echo "<cell>{$utilization}%</cell>\n";
            echo "<cell>" . htmlspecialchars($tank->status) . "</cell>\n";
            echo "<cell>" . htmlspecialchars($tank->notes ?? '-') . "</cell>\n";
            echo "<cell>" . htmlspecialchars($tank->created_by ?? '-') . "</cell>\n";
            echo "<cell>" . ($tank->created_at ? $tank->created_at->format('d/m/Y H:i') : '-') . "</cell>\n";
            echo "</row>\n";
            $no++;
        }

        echo "</rows>";
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100|unique:cns_master_tanks,name,NULL,id,deleted_at,NULL',
            'capacity' => 'required|numeric|min:0',
            'notes'    => 'nullable|string',
            'status'   => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $userName = Auth::check() ? (Auth::user()->name ?? Auth::user()->username) : 'System';

            $tank = MasterTank::create([
                'name'       => trim($request->input('name')),
                'capacity'   => (float) $request->input('capacity'),
                'notes'      => $request->input('notes'),
                'status'     => $request->input('status', 'ACTIVE') ?: 'ACTIVE',
                'created_by' => $userName,
            ]);

            MasterTankLog::create([
                'tank_id'      => $tank->id,
                'tank_name'    => $tank->name,
                'action'       => 'CREATE',
                'details'      => "Menambahkan tangki {$tank->name} dengan kapasitas " . number_format($tank->capacity, 0, ',', '.') . " KG",
                'old_values'   => null,
                'new_values'   => $tank->toArray(),
                'performed_by' => $userName,
                'created_at'   => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tangki berhasil ditambahkan.',
                'data'    => $tank,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan tangki: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $tank = MasterTank::find($id);
        if (!$tank) {
            return response()->json([
                'success' => false,
                'message' => 'Data tangki tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $tank,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tank = MasterTank::find($id);
        if (!$tank) {
            return response()->json([
                'success' => false,
                'message' => 'Data tangki tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100|unique:cns_master_tanks,name,' . $id . ',id,deleted_at,NULL',
            'capacity' => 'required|numeric|min:0',
            'notes'    => 'nullable|string',
            'status'   => 'required|in:ACTIVE,INACTIVE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $userName = Auth::check() ? (Auth::user()->name ?? Auth::user()->username) : 'System';
            $oldValues = $tank->only(['name', 'capacity', 'notes', 'status']);

            $tank->update([
                'name'     => trim($request->input('name')),
                'capacity' => (float) $request->input('capacity'),
                'notes'    => $request->input('notes'),
                'status'   => $request->input('status'),
            ]);

            $newValues = $tank->only(['name', 'capacity', 'notes', 'status']);

            MasterTankLog::create([
                'tank_id'      => $tank->id,
                'tank_name'    => $tank->name,
                'action'       => 'UPDATE',
                'details'      => "Memperbarui data tangki {$tank->name}",
                'old_values'   => $oldValues,
                'new_values'   => $newValues,
                'performed_by' => $userName,
                'created_at'   => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tangki berhasil diperbarui.',
                'data'    => $tank,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui tangki: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $tank = MasterTank::find($id);
        if (!$tank) {
            return response()->json([
                'success' => false,
                'message' => 'Data tangki tidak ditemukan.',
            ], 404);
        }

        try {
            $userName = Auth::check() ? (Auth::user()->name ?? Auth::user()->username) : 'System';
            $oldValues = $tank->toArray();

            MasterTankLog::create([
                'tank_id'      => $tank->id,
                'tank_name'    => $tank->name,
                'action'       => 'DELETE',
                'details'      => "Menghapus tangki {$tank->name} (soft delete)",
                'old_values'   => $oldValues,
                'new_values'   => null,
                'performed_by' => $userName,
                'created_at'   => Carbon::now(),
            ]);

            $tank->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tangki berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tangki: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logs($id)
    {
        $tank = MasterTank::withTrashed()->find($id);
        if (!$tank) {
            return response()->json([
                'success' => false,
                'message' => 'Data tangki tidak ditemukan.',
            ], 404);
        }

        $logs = MasterTankLog::where('tank_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success'   => true,
            'tank_name' => $tank->name,
            'logs'      => $logs,
        ]);
    }

    public function options()
    {
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get(['id', 'name', 'capacity', 'notes']);

        return response()->json([
            'success' => true,
            'tanks'   => $tanks,
        ]);
    }

    public function tankBalances()
    {
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();

        $data = $tanks->map(function ($tank) {
            $currentStock = $this->calculateTankBalance($tank->name);
            $utilization = $tank->capacity > 0 ? round(($currentStock / $tank->capacity) * 100, 1) : 0;

            return [
                'id'              => $tank->id,
                'name'            => $tank->name,
                'capacity'        => (float) $tank->capacity,
                'current_stock'   => (float) $currentStock,
                'remaining'       => (float) max(0, $tank->capacity - $currentStock),
                'utilization_pct' => $utilization,
                'status'          => $tank->status,
                'notes'           => $tank->notes,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    protected function calculateTankBalance(string $tankName): float
    {
        return (float) UCOStoragePelindo::where('Nama_Tangki', $tankName)
            ->selectRaw("SUM(CASE WHEN Type = 'inbound' THEN Quantity ELSE -Quantity END) as balance")
            ->value('balance') ?: 0;
    }
}
