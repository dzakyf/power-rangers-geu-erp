<?php

namespace App\Http\Controllers\CNS;

use App\CNSDrainAdjustment;
use App\Http\Controllers\Controller;
use App\MasterTank;
use App\UCOStoragePelindo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DrainAdjustmentController extends Controller
{
    public function index()
    {
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        return view('cns.inventory.drain-adjustment', compact('tanks'));
    }

    public function newAdjustment()
    {
        $adjCode = $this->generateNextCode();
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();

        return view('cns.inventory.drain-adjustment-new', compact('adjCode', 'tanks'));
    }

    public function detailAdjustment(Request $request)
    {
        $id = $request->query('ID') ?: $request->query('id');
        $adjustment = CNSDrainAdjustment::findOrFail($id);
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        $currentStock = $this->calculateTankBalance($adjustment->tank_name);

        return view('cns.inventory.drain-adjustment-detail', compact('adjustment', 'tanks', 'currentStock'));
    }

    public function xmlData(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $tankId    = $request->query('tank_id');
        $status    = $request->query('approval_status');
        $search    = $request->query('search');

        $query = CNSDrainAdjustment::query()->orderBy('adjustment_date', 'desc')->orderBy('id', 'desc');

        if ($startDate) {
            $query->whereDate('adjustment_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('adjustment_date', '<=', $endDate);
        }
        if ($tankId) {
            $query->where('tank_id', $tankId);
        }
        if ($status) {
            $query->where('approval_status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_code', 'like', "%{$search}%")
                  ->orWhere('tank_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $records = $query->get();

        header("Content-type: text/xml");
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<rows>\n";

        $no = 1;
        foreach ($records as $row) {
            $docCell = '';
            if (!empty($row->document_path)) {
                $docUrl = route('cns-drain-adjustment.document', ['id' => $row->id], false);
                $docCell = htmlspecialchars($docUrl) . '^Lihat Dokumen^_blank';
            }

            $selisihPrefix = $row->selisih_qty > 0 ? '+' : '';

            echo "<row id=\"{$row->id}\">\n";
            echo "<cell>{$no}</cell>\n";
            echo "<cell>" . htmlspecialchars($row->adjustment_code) . "</cell>\n";
            echo "<cell>" . ($row->adjustment_date ? $row->adjustment_date->format('d/m/Y') : '-') . "</cell>\n";
            echo "<cell>" . htmlspecialchars($row->tank_name) . "</cell>\n";
            echo "<cell>" . number_format((float) $row->qty_sistem, 2, '.', ',') . " KG</cell>\n";
            echo "<cell>" . number_format((float) $row->qty_timbang, 2, '.', ',') . " KG</cell>\n";
            echo "<cell>" . $selisihPrefix . number_format((float) $row->selisih_qty, 2, '.', ',') . " KG</cell>\n";
            echo "<cell>" . htmlspecialchars($row->approval_status) . "</cell>\n";
            echo "<cell>" . htmlspecialchars($row->notes ?? '-') . "</cell>\n";
            echo "<cell>{$docCell}</cell>\n";
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

    public function store(Request $request)
    {
        $validator = $this->validateAdjustmentRequest($request);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $tank = MasterTank::findOrFail($request->tank_id);
        $qtySistem = round($this->calculateTankBalance($tank->name), 2);
        $qtyTimbang = round((float) $request->qty_timbang, 2);
        $tankCapacity = (float) $tank->capacity;

        if (abs($qtyTimbang - $qtySistem) < 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Qty Timbang sama dengan Qty Sistem. Tidak diperlukan adjustment.',
            ], 422);
        }

        if ($tankCapacity > 0 && $qtyTimbang > $tankCapacity) {
            return response()->json([
                'success' => false,
                'message' => "Qty Timbang ({$qtyTimbang} KG) melebihi kapasitas tangki {$tank->name} ({$tankCapacity} KG).",
            ], 422);
        }

        $selisihQty = round($qtyTimbang - $qtySistem, 2);
        $username = auth()->check() ? (auth()->user()->username ?? auth()->user()->name ?? 'System') : 'System';

        $docMeta = ['path' => null, 'name' => null];
        if ($request->hasFile('document')) {
            $docMeta = $this->storeAdjustmentDocument($request->file('document'), $request->adjustment_code);
        }

        DB::beginTransaction();
        try {
            $adjustment = CNSDrainAdjustment::create([
                'adjustment_code' => $request->adjustment_code,
                'adjustment_date' => $request->adjustment_date,
                'tank_id'         => $tank->id,
                'tank_name'       => $tank->name,
                'tank_capacity'   => $tankCapacity,
                'qty_sistem'      => $qtySistem,
                'qty_timbang'     => $qtyTimbang,
                'selisih_qty'     => $selisihQty,
                'notes'           => $request->notes,
                'document_path'   => $docMeta['path'],
                'document_name'   => $docMeta['name'],
                'approval_status' => 'PENDING',
                'created_by'      => $username,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data Drain Adjustment berhasil disimpan dengan status PENDING.',
                'data'    => $adjustment,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            if ($docMeta['path']) {
                Storage::disk('local')->delete($docMeta['path']);
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $adjustment = CNSDrainAdjustment::findOrFail($id);

        if ($adjustment->approval_status === 'APPROVED') {
            return response()->json([
                'success' => false,
                'message' => 'Data yang sudah APPROVED tidak dapat diubah.',
            ], 422);
        }

        $validator = $this->validateAdjustmentRequest($request, $adjustment);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $tank = MasterTank::findOrFail($request->tank_id);
        $qtySistem = round($this->calculateTankBalance($tank->name), 2);
        $qtyTimbang = round((float) $request->qty_timbang, 2);
        $tankCapacity = (float) $tank->capacity;

        if (abs($qtyTimbang - $qtySistem) < 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Qty Timbang sama dengan Qty Sistem. Tidak diperlukan adjustment.',
            ], 422);
        }

        if ($tankCapacity > 0 && $qtyTimbang > $tankCapacity) {
            return response()->json([
                'success' => false,
                'message' => "Qty Timbang ({$qtyTimbang} KG) melebihi kapasitas tangki {$tank->name} ({$tankCapacity} KG).",
            ], 422);
        }

        $selisihQty = round($qtyTimbang - $qtySistem, 2);
        $username = auth()->check() ? (auth()->user()->username ?? auth()->user()->name ?? 'System') : 'System';

        $oldDocPath = $adjustment->document_path;
        $newDocMeta = $request->hasFile('document')
            ? $this->storeAdjustmentDocument($request->file('document'), $request->adjustment_code)
            : null;

        DB::beginTransaction();
        try {
            $updatePayload = [
                'adjustment_code' => $request->adjustment_code,
                'adjustment_date' => $request->adjustment_date,
                'tank_id'         => $tank->id,
                'tank_name'       => $tank->name,
                'tank_capacity'   => $tankCapacity,
                'qty_sistem'      => $qtySistem,
                'qty_timbang'     => $qtyTimbang,
                'selisih_qty'     => $selisihQty,
                'notes'           => $request->notes,
                'updated_by'      => $username,
            ];

            if ($adjustment->approval_status === 'REJECTED') {
                $updatePayload['approval_status'] = 'PENDING';
                $updatePayload['rejection_reason'] = null;
            }

            if ($newDocMeta) {
                $updatePayload['document_path'] = $newDocMeta['path'];
                $updatePayload['document_name'] = $newDocMeta['name'];
            }

            $adjustment->update($updatePayload);

            DB::commit();

            if ($newDocMeta && $oldDocPath && $oldDocPath !== $newDocMeta['path']) {
                Storage::disk('local')->delete($oldDocPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Drain Adjustment berhasil diperbarui.',
                'data'    => $adjustment->fresh(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            if ($newDocMeta) {
                Storage::disk('local')->delete($newDocMeta['path']);
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $adjustment = CNSDrainAdjustment::findOrFail($id);

        if ($adjustment->approval_status === 'APPROVED') {
            return response()->json([
                'success' => false,
                'message' => 'Data Drain Adjustment yang sudah APPROVED tidak dapat dihapus.',
            ], 422);
        }

        $adjustment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Drain Adjustment berhasil dihapus.',
        ]);
    }

    public function approve(Request $request, $id)
    {
        $actor = auth()->check() ? (auth()->user()->username ?? auth()->user()->name ?? 'admin') : 'admin';

        DB::beginTransaction();
        try {
            $adjustment = CNSDrainAdjustment::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($adjustment->approval_status === 'APPROVED') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah berstatus APPROVED.',
                ], 422);
            }

            $selisih = round((float) $adjustment->selisih_qty, 2);
            if (abs($selisih) < 0.01) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Selisih qty bernilai 0, tidak ada mutasi yang dapat dicatat.',
                ], 422);
            }

            $currentStock = $this->calculateTankBalance($adjustment->tank_name);
            if ($selisih < 0 && ($currentStock + $selisih) < -0.001) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Saldo tangki {$adjustment->tank_name} saat ini (" . number_format($currentStock, 2) . " KG) tidak mencukupi untuk pengurangan sebesar " . number_format(abs($selisih), 2) . " KG.",
                ], 422);
            }

            $adjustment->update([
                'approval_status'  => 'APPROVED',
                'approved_by'      => $actor,
                'approved_at'      => now(),
                'rejection_reason' => null,
            ]);

            UCOStoragePelindo::create([
                'Type'             => $selisih > 0 ? 'inbound' : 'outbound',
                'Quantity'         => abs($selisih),
                'Asal_Warehouse'   => 'PELINDO',
                'Nama_Tangki'      => $adjustment->tank_name,
                'Created_By'       => $actor,
                'Transaction_Date' => $adjustment->adjustment_date,
                'Reference_Number' => $adjustment->adjustment_code,
                'Approval_Status'  => 'APPROVED',
                'Approved_By'      => $actor,
                'Approved_At'      => now(),
                'Notes'            => "Drain Adjustment [{$adjustment->adjustment_code}] (Sistem: " . number_format((float) $adjustment->qty_sistem, 2) . " KG, Timbang: " . number_format((float) $adjustment->qty_timbang, 2) . " KG)",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Drain Adjustment berhasil di-approve dan qty sistem telah disesuaikan.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve Drain Adjustment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $adjustment = CNSDrainAdjustment::findOrFail($id);

        if ($adjustment->approval_status === 'APPROVED') {
            return response()->json([
                'success' => false,
                'message' => 'Data yang sudah APPROVED tidak dapat di-reject.',
            ], 422);
        }

        $actor = auth()->check() ? (auth()->user()->username ?? auth()->user()->name ?? 'admin') : 'admin';

        $adjustment->update([
            'approval_status'  => 'REJECTED',
            'approved_by'      => $actor,
            'approved_at'      => now(),
            'rejection_reason' => trim((string) $request->input('reason')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Drain Adjustment berhasil ditolak.',
        ]);
    }

    public function document($id)
    {
        $adjustment = CNSDrainAdjustment::findOrFail($id);
        if (empty($adjustment->document_path) || !Storage::disk('local')->exists($adjustment->document_path)) {
            abort(404, 'Dokumen pendukung tidak ditemukan.');
        }

        $path = Storage::disk('local')->path($adjustment->document_path);
        $name = $adjustment->document_name ?: basename($adjustment->document_path);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . addslashes($name) . '"',
        ]);
    }

    protected function generateNextCode(): string
    {
        $dateStr = Carbon::now()->format('Ymd');
        $prefix = "DADJ-{$dateStr}-";

        $latest = CNSDrainAdjustment::withTrashed()
            ->where('adjustment_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('adjustment_code');

        if ($latest) {
            $num = (int) substr($latest, strlen($prefix));
            $next = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return "{$prefix}{$next}";
    }

    protected function validateAdjustmentRequest(Request $request, ?CNSDrainAdjustment $existing = null)
    {
        $codeRule = 'required|string|max:50|unique:cns_drain_adjustments,adjustment_code' . ($existing ? ",{$existing->id}" : '');

        return Validator::make($request->all(), [
            'adjustment_code' => $codeRule,
            'adjustment_date' => 'required|date',
            'tank_id'         => 'required|exists:cns_master_tanks,id',
            'qty_timbang'     => 'required|numeric|min:0',
            'notes'           => 'nullable|string',
            'document'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'adjustment_code.unique' => 'Kode adjustment sudah pernah digunakan.',
            'tank_id.required'       => 'Tangki wajib dipilih.',
            'qty_timbang.required'   => 'Qty timbang manual wajib diisi.',
            'qty_timbang.min'        => 'Qty timbang minimal 0 KG.',
            'document.mimes'         => 'Dokumen pendukung harus berformat PDF, JPG, JPEG, atau PNG.',
            'document.max'           => 'Ukuran dokumen pendukung maksimal 10 MB.',
        ]);
    }

    protected function storeAdjustmentDocument($file, string $adjCode): array
    {
        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $adjCode);
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'DADJ_' . $safeCode . '_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
        $path = $file->storeAs('cns/drain-adjustment-docs', $filename, 'local');

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
