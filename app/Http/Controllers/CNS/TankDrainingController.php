<?php

namespace App\Http\Controllers\CNS;

use App\CNSDrainAdjustment;
use App\Http\Controllers\Controller;
use App\MasterTank;
use App\SalesOrderCNS;
use App\TankDraining;
use App\UCOStoragePelindo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TankDrainingController extends Controller
{
    public function index()
    {
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        return view('cns.inventory.tank-draining', compact('tanks'));
    }

    public function newDraining()
    {
        $drainingCode = $this->generateNextCode();
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        $salesOrders = SalesOrderCNS::orderBy('id', 'desc')->take(100)->get();

        return view('cns.inventory.tank-draining-new', compact('drainingCode', 'tanks', 'salesOrders'));
    }

    public function detailDraining(Request $request)
    {
        $id = $request->query('ID') ?: $request->query('id');
        $draining = TankDraining::findOrFail($id);
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        $salesOrders = SalesOrderCNS::orderBy('id', 'desc')->take(100)->get();

        $currentStock = $this->calculateTankBalance($draining->tank_name);
        $pendingDrainAdjustment = CNSDrainAdjustment::pending()
            ->where('tank_id', $draining->tank_id)
            ->orderBy('id', 'desc')
            ->first();

        return view('cns.inventory.tank-draining-detail', compact('draining', 'tanks', 'salesOrders', 'currentStock', 'pendingDrainAdjustment'));
    }

    public function xmlData(Request $request)
    {
        $startDate  = $request->query('start_date');
        $endDate    = $request->query('end_date');
        $tankId     = $request->query('tank_id');
        $targetType = $request->query('target_type');
        $search     = $request->query('search');

        $query = TankDraining::query()->orderBy('draining_date', 'desc')->orderBy('id', 'desc');

        if ($startDate) {
            $query->whereDate('draining_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('draining_date', '<=', $endDate);
        }
        if ($tankId) {
            $query->where('tank_id', $tankId);
        }
        if ($targetType) {
            $query->where('target_type', $targetType);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('draining_code', 'like', "%{$search}%")
                  ->orWhere('tank_name', 'like', "%{$search}%")
                  ->orWhere('moving_target_tank_name', 'like', "%{$search}%")
                  ->orWhere('moving_target_external', 'like', "%{$search}%")
                  ->orWhere('sales_order_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $drainings = $query->get();

        header("Content-type: text/xml");
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<rows>\n";

        $no = 1;
        foreach ($drainings as $row) {
            $tujuanDetail = '-';
            if ($row->target_type === 'MOVING') {
                $tujuanDetail = $row->moving_target_type === 'TANK'
                    ? 'Tangki: ' . ($row->moving_target_tank_name ?? '-')
                    : 'Eksternal: ' . ($row->moving_target_external ?? '-');
            } elseif ($row->target_type === 'PENJUALAN') {
                $tujuanDetail = $row->sales_order_no ? 'SO: ' . $row->sales_order_no : 'Penjualan Umum';
            }

            $bastCell = '';
            if (!empty($row->bast_document_path)) {
                $bastUrl = route('cns-tank-draining.bast', ['id' => $row->id], false);
                $bastCell = htmlspecialchars($bastUrl) . '^Lihat BAST^_blank';
            }

            echo "<row id=\"{$row->id}\">\n";
            echo "<cell>{$no}</cell>\n";
            echo "<cell>" . htmlspecialchars($row->draining_code) . "</cell>\n";
            echo "<cell>" . ($row->draining_date ? $row->draining_date->format('d/m/Y') : '-') . "</cell>\n";
            echo "<cell>" . htmlspecialchars($row->tank_name) . "</cell>\n";
            echo "<cell>" . number_format((float) $row->qty, 2, '.', ',') . " KG</cell>\n";
            echo "<cell>" . htmlspecialchars($row->target_type) . "</cell>\n";
            echo "<cell>" . htmlspecialchars($tujuanDetail) . "</cell>\n";
            echo "<cell>" . htmlspecialchars($row->notes ?? '-') . "</cell>\n";
            echo "<cell>{$bastCell}</cell>\n";
            echo "<cell>" . htmlspecialchars($row->created_by ?? '-') . "</cell>\n";
            echo "<cell>" . ($row->created_at ? $row->created_at->format('d/m/Y H:i') : '-') . "</cell>\n";
            echo "</row>\n";
            $no++;
        }

        echo "</rows>";
    }

    public function generateCode()
    {
        return response()->json([
            'success' => true,
            'code'    => $this->generateNextCode(),
        ]);
    }

    public function getTankBalance($id)
    {
        $tank = MasterTank::findOrFail($id);
        $balance = $this->calculateTankBalance($tank->name);
        $pending = CNSDrainAdjustment::pending()
            ->where('tank_id', $tank->id)
            ->orderBy('id', 'desc')
            ->first();

        return response()->json([
            'success'                  => true,
            'tank_id'                  => $tank->id,
            'name'                     => $tank->name,
            'capacity'                 => (float) $tank->capacity,
            'balance'                  => $balance,
            'pending_drain_adjustment' => $pending ? [
                'id'              => $pending->id,
                'adjustment_code' => $pending->adjustment_code,
                'qty_sistem'      => (float) $pending->qty_sistem,
                'qty_timbang'     => (float) $pending->qty_timbang,
                'selisih_qty'     => (float) $pending->selisih_qty,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateDrainingRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $sourceTank = MasterTank::findOrFail($request->tank_id);

        if ($pendingAdj = CNSDrainAdjustment::pending()->where('tank_id', $sourceTank->id)->orderBy('id', 'desc')->first()) {
            return response()->json([
                'success' => false,
                'message' => "Tangki {$sourceTank->name} masih memiliki Drain Adjustment ({$pendingAdj->adjustment_code}) berstatus PENDING. Harap selesaikan approval terlebih dahulu sebelum melakukan draining.",
            ], 422);
        }

        $currentStock = $this->calculateTankBalance($sourceTank->name);

        if ((float) $request->qty > $currentStock) {
            return response()->json([
                'success' => false,
                'message' => "Qty Draining (" . number_format($request->qty, 2) . " KG) melebihi stok tangki {$sourceTank->name} yang tersedia (" . number_format($currentStock, 2) . " KG).",
            ], 422);
        }

        $username = Auth::user()->username ?? 'SYSTEM';

        $movingTargetTankName = null;
        if ($request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' && $request->moving_target_tank_id) {
            $destTank = MasterTank::find($request->moving_target_tank_id);
            $movingTargetTankName = $destTank ? $destTank->name : null;
        }

        $salesOrderNo = null;
        if ($request->target_type === 'PENJUALAN' && $request->sales_order_id) {
            $so = SalesOrderCNS::find($request->sales_order_id);
            $salesOrderNo = $so ? $so->so_no : null;
        }

        $bastFileMeta = $this->storeBastDocument($request->file('bast_document'), $request->draining_code);

        DB::beginTransaction();
        try {
            $draining = TankDraining::create([
                'draining_code'          => $request->draining_code,
                'draining_date'          => $request->draining_date,
                'tank_id'                => $sourceTank->id,
                'tank_name'              => $sourceTank->name,
                'qty'                    => $request->qty,
                'target_type'            => $request->target_type,
                'moving_target_type'     => $request->target_type === 'MOVING' ? $request->moving_target_type : null,
                'moving_target_tank_id'  => $request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' ? $request->moving_target_tank_id : null,
                'moving_target_tank_name'=> $movingTargetTankName,
                'moving_target_external' => $request->target_type === 'MOVING' && $request->moving_target_type === 'EXTERNAL' ? $request->moving_target_external : null,
                'sales_order_id'         => $request->target_type === 'PENJUALAN' ? $request->sales_order_id : null,
                'sales_order_no'         => $salesOrderNo,
                'notes'                  => $request->notes,
                'bast_document_path'     => $bastFileMeta['path'],
                'bast_document_name'     => $bastFileMeta['name'],
                'created_by'             => $username,
            ]);

            // Catat Outbound di UCOStoragePelindo untuk tangki asal
            $outboundNotes = "Tank Draining [{$request->draining_code}] Tujuan {$request->target_type}";
            if ($request->target_type === 'MOVING') {
                $outboundNotes .= $request->moving_target_type === 'TANK' ? " ke {$movingTargetTankName}" : " ke {$request->moving_target_external}";
            } elseif ($salesOrderNo) {
                $outboundNotes .= " (SO: {$salesOrderNo})";
            }

            UCOStoragePelindo::create([
                'Type'             => 'outbound',
                'Quantity'         => $request->qty,
                'Asal_Warehouse'   => 'PELINDO',
                'Nama_Tangki'      => $sourceTank->name,
                'Created_By'       => $username,
                'Transaction_Date' => $request->draining_date,
                'Reference_Number' => $request->draining_code,
                'Notes'            => $outboundNotes,
            ]);

            // Jika Moving ke tangki lain di master tangki, catat Inbound di UCOStoragePelindo untuk tangki tujuan
            if ($request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' && $movingTargetTankName) {
                UCOStoragePelindo::create([
                    'Type'             => 'inbound',
                    'Quantity'         => $request->qty,
                    'Asal_Warehouse'   => 'PELINDO',
                    'Nama_Tangki'      => $movingTargetTankName,
                    'Created_By'       => $username,
                    'Transaction_Date' => $request->draining_date,
                    'Reference_Number' => $request->draining_code,
                    'Notes'            => "Tank Draining [{$request->draining_code}] Moving dari {$sourceTank->name}",
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data Tank Draining berhasil disimpan.',
                'data'    => $draining,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Storage::disk('local')->delete($bastFileMeta['path']);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $draining = TankDraining::findOrFail($id);

        $validator = $this->validateDrainingRequest($request, $draining);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $sourceTank = MasterTank::findOrFail($request->tank_id);

        if ($pendingAdj = CNSDrainAdjustment::pending()->where('tank_id', $sourceTank->id)->orderBy('id', 'desc')->first()) {
            return response()->json([
                'success' => false,
                'message' => "Tangki {$sourceTank->name} masih memiliki Drain Adjustment ({$pendingAdj->adjustment_code}) berstatus PENDING. Harap selesaikan approval terlebih dahulu sebelum melakukan draining.",
            ], 422);
        }

        // Hitung ketersediaan stok: saldo saat ini ditambah qty draining sebelumnya jika tangki sama
        $currentStock = $this->calculateTankBalance($sourceTank->name);
        $availableStock = ($sourceTank->id == $draining->tank_id) ? ($currentStock + (float) $draining->qty) : $currentStock;

        if ((float) $request->qty > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Qty Draining (" . number_format($request->qty, 2) . " KG) melebihi kapasitas stok tangki {$sourceTank->name} yang tersedia (" . number_format($availableStock, 2) . " KG).",
            ], 422);
        }

        $username = Auth::user()->username ?? 'SYSTEM';

        $movingTargetTankName = null;
        if ($request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' && $request->moving_target_tank_id) {
            $destTank = MasterTank::find($request->moving_target_tank_id);
            $movingTargetTankName = $destTank ? $destTank->name : null;
        }

        $salesOrderNo = null;
        if ($request->target_type === 'PENJUALAN' && $request->sales_order_id) {
            $so = SalesOrderCNS::find($request->sales_order_id);
            $salesOrderNo = $so ? $so->so_no : null;
        }

        $oldBastPath = $draining->bast_document_path;
        $newBastMeta = $request->hasFile('bast_document')
            ? $this->storeBastDocument($request->file('bast_document'), $request->draining_code)
            : null;

        DB::beginTransaction();
        try {
            // Hapus mutasi lama di UCOStoragePelindo
            UCOStoragePelindo::where('Reference_Number', $draining->draining_code)
                ->where('Notes', 'like', '%Tank Draining%')
                ->delete();

            $updatePayload = [
                'draining_code'          => $request->draining_code,
                'draining_date'          => $request->draining_date,
                'tank_id'                => $sourceTank->id,
                'tank_name'              => $sourceTank->name,
                'qty'                    => $request->qty,
                'target_type'            => $request->target_type,
                'moving_target_type'     => $request->target_type === 'MOVING' ? $request->moving_target_type : null,
                'moving_target_tank_id'  => $request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' ? $request->moving_target_tank_id : null,
                'moving_target_tank_name'=> $movingTargetTankName,
                'moving_target_external' => $request->target_type === 'MOVING' && $request->moving_target_type === 'EXTERNAL' ? $request->moving_target_external : null,
                'sales_order_id'         => $request->target_type === 'PENJUALAN' ? $request->sales_order_id : null,
                'sales_order_no'         => $salesOrderNo,
                'notes'                  => $request->notes,
                'updated_by'             => $username,
            ];

            if ($newBastMeta) {
                $updatePayload['bast_document_path'] = $newBastMeta['path'];
                $updatePayload['bast_document_name'] = $newBastMeta['name'];
            }

            $draining->update($updatePayload);

            // Re-insert Outbound untuk tangki asal
            $outboundNotes = "Tank Draining [{$request->draining_code}] Tujuan {$request->target_type}";
            if ($request->target_type === 'MOVING') {
                $outboundNotes .= $request->moving_target_type === 'TANK' ? " ke {$movingTargetTankName}" : " ke {$request->moving_target_external}";
            } elseif ($salesOrderNo) {
                $outboundNotes .= " (SO: {$salesOrderNo})";
            }

            UCOStoragePelindo::create([
                'Type'             => 'outbound',
                'Quantity'         => $request->qty,
                'Asal_Warehouse'   => 'PELINDO',
                'Nama_Tangki'      => $sourceTank->name,
                'Created_By'       => $username,
                'Transaction_Date' => $request->draining_date,
                'Reference_Number' => $request->draining_code,
                'Notes'            => $outboundNotes,
            ]);

            // Re-insert Inbound jika Moving ke tangki lain
            if ($request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' && $movingTargetTankName) {
                UCOStoragePelindo::create([
                    'Type'             => 'inbound',
                    'Quantity'         => $request->qty,
                    'Asal_Warehouse'   => 'PELINDO',
                    'Nama_Tangki'      => $movingTargetTankName,
                    'Created_By'       => $username,
                    'Transaction_Date' => $request->draining_date,
                    'Reference_Number' => $request->draining_code,
                    'Notes'            => "Tank Draining [{$request->draining_code}] Moving dari {$sourceTank->name}",
                ]);
            }

            DB::commit();

            if ($newBastMeta && $oldBastPath && $oldBastPath !== $newBastMeta['path']) {
                Storage::disk('local')->delete($oldBastPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Tank Draining berhasil diperbarui.',
                'data'    => $draining,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            if ($newBastMeta) {
                Storage::disk('local')->delete($newBastMeta['path']);
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bastDocument($id)
    {
        $draining = TankDraining::findOrFail($id);

        abort_if(
            empty($draining->bast_document_path)
            || !Storage::disk('local')->exists($draining->bast_document_path),
            404,
            'Dokumen BAST tidak ditemukan.'
        );

        $path = Storage::disk('local')->path($draining->bast_document_path);
        $filename = $draining->bast_document_name ?: basename($path);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '', $filename) . '"',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $draining = TankDraining::findOrFail($id);

        DB::beginTransaction();
        try {
            // Revert mutasi stok di UCOStoragePelindo
            UCOStoragePelindo::where('Reference_Number', $draining->draining_code)
                ->where('Notes', 'like', '%Tank Draining%')
                ->delete();

            $draining->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data Tank Draining berhasil dihapus dan mutasi stok telah dikembalikan.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function generateNextCode(): string
    {
        $dateStr = Carbon::now()->format('Ymd');
        $prefix = "DRN-{$dateStr}-";

        $latest = TankDraining::withTrashed()
            ->where('draining_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('draining_code');

        if ($latest) {
            $num = (int) substr($latest, strlen($prefix));
            $next = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return "{$prefix}{$next}";
    }

    protected function validateDrainingRequest(Request $request, ?TankDraining $existing = null)
    {
        $id = $existing ? $existing->id : 'NULL';
        $bastRequired = (!$existing || empty($existing->bast_document_path)) ? 'required' : 'nullable';

        return Validator::make($request->all(), [
            'draining_code'          => "required|string|max:100|unique:cns_tank_drainings,draining_code,{$id},id,deleted_at,NULL",
            'draining_date'          => 'required|date',
            'tank_id'                => 'required|exists:cns_master_tanks,id',
            'qty'                    => 'required|numeric|min:0.01',
            'target_type'            => 'required|in:MOVING,PENJUALAN',
            'moving_target_type'     => 'required_if:target_type,MOVING|nullable|in:TANK,EXTERNAL',
            'moving_target_tank_id'  => 'required_if:moving_target_type,TANK|nullable|exists:cns_master_tanks,id|different:tank_id',
            'moving_target_external' => 'required_if:moving_target_type,EXTERNAL|nullable|string|max:255',
            'sales_order_id'         => 'nullable|exists:sales_order_cns,id',
            'notes'                  => 'nullable|string',
            'bast_document'          => "{$bastRequired}|file|mimes:pdf,jpg,jpeg,png|max:10240",
        ], [
            'moving_target_tank_id.different' => 'Tangki tujuan tidak boleh sama dengan tangki asal.',
            'draining_code.unique'            => 'Kode draining sudah pernah digunakan.',
            'bast_document.required'          => 'Dokumen BAST draining wajib diupload.',
            'bast_document.mimes'             => 'Dokumen BAST harus berformat PDF, JPG, JPEG, atau PNG.',
            'bast_document.max'               => 'Ukuran dokumen BAST maksimal 10 MB.',
        ]);
    }

    protected function storeBastDocument($file, string $drainingCode): array
    {
        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $drainingCode);
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'BAST_' . $safeCode . '_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
        $path = $file->storeAs('cns/tank-draining-bast', $filename, 'local');

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
        ];
    }

    protected function calculateTankBalance(string $tankName): float
    {
        return (float) UCOStoragePelindo::approved()->where('Nama_Tangki', $tankName)
            ->selectRaw("SUM(CASE WHEN Type = 'inbound' THEN Quantity ELSE -Quantity END) as balance")
            ->value('balance') ?: 0;
    }
}
