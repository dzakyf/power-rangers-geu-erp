<?php

namespace App\Http\Controllers\CNS;

use App\CNSInboundAdjustment;
use App\Http\Controllers\Controller;
use App\MasterTank;
use App\Services\ExternalApi\ApipiApiClient;
use App\UCOStoragePelindo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InboundAdjustmentController extends Controller
{
    public function index()
    {
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        return view('cns.inventory.inbound-adjustment', compact('tanks'));
    }

    public function newAdjustment()
    {
        $adjCode = $this->generateNextCode();
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();

        // Get movement options from APIPI (only logistics_status === 'done' and not yet used in storage)
        $usedKodes = [];
        foreach (UCOStoragePelindo::whereNotNull('Reference_Number')->where('Reference_Number', '!=', '')->pluck('Reference_Number') as $ref) {
            $usedKodes[strtoupper(trim((string) $ref))] = true;
        }

        $movements = [];
        try {
            $cacheKey = 'cns.inbound_adjustment.movements';
            $rawMovements = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if (!is_array($rawMovements)) {
                $rawMovements = app(ApipiApiClient::class)->pelindoInboundMovements();
                \Illuminate\Support\Facades\Cache::put($cacheKey, $rawMovements, now()->addMinutes(2));
            }
            foreach ($rawMovements as $m) {
                $kode = trim((string) ($m['kode'] ?? ''));
                if ($kode === '' || isset($usedKodes[strtoupper($kode)])) {
                    continue;
                }
                if (strtolower(trim((string) ($m['logistics_status'] ?? ''))) !== 'done') {
                    continue;
                }
                $movements[] = [
                    'id'             => $m['id'] ?? null,
                    'kode'           => $kode,
                    'tgl'            => !empty($m['tgl']) ? Carbon::parse($m['tgl'])->toDateString() : '',
                    'dari_gudang'    => trim((string) ($m['dari_gudang'] ?? '')),
                    'total_qty'      => (float) ($m['total_qty'] ?? 0),
                    'tujuan_storage' => trim((string) ($m['tujuan_storage'] ?? '')),
                ];
            }
        } catch (\Throwable $e) {
            // Log or ignore if APIPI unreachable
        }

        return view('cns.inventory.inbound-adjustment-new', compact('adjCode', 'tanks', 'movements'));
    }

    public function detailAdjustment(Request $request)
    {
        $id = $request->query('ID') ?: $request->query('id');
        $adjustment = CNSInboundAdjustment::findOrFail($id);
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();

        $currentStock = $this->calculateTankBalance($adjustment->tank_name);

        return view('cns.inventory.inbound-adjustment-detail', compact('adjustment', 'tanks', 'currentStock'));
    }

    public function xmlData(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $tankId    = $request->query('tank_id');
        $status    = $request->query('approval_status');
        $search    = $request->query('search');

        $query = CNSInboundAdjustment::query()->orderBy('adjustment_date', 'desc')->orderBy('id', 'desc');

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
                  ->orWhere('movement_kode', 'like', "%{$search}%")
                  ->orWhere('asal_warehouse', 'like', "%{$search}%")
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
                $docUrl = route('cns-inbound-adjustment.document', ['id' => $row->id], false);
                $docCell = htmlspecialchars($docUrl) . '^Lihat Dokumen^_blank';
            }

            $badgeStatus = htmlspecialchars($row->approval_status);

            echo "<row id=\"{$row->id}\">\n";
            echo "<cell>{$no}</cell>\n";
            echo "<cell>" . htmlspecialchars($row->adjustment_code) . "</cell>\n";
            echo "<cell>" . ($row->adjustment_date ? $row->adjustment_date->format('d/m/Y') : '-') . "</cell>\n";
            echo "<cell>" . htmlspecialchars($row->movement_kode) . "</cell>\n";
            echo "<cell>" . htmlspecialchars($row->asal_warehouse ?? '-') . "</cell>\n";
            echo "<cell>" . htmlspecialchars($row->tank_name) . "</cell>\n";
            echo "<cell>" . number_format((float) $row->original_qty, 2, '.', ',') . " KG</cell>\n";
            echo "<cell>" . number_format((float) $row->adjusted_qty, 2, '.', ',') . " KG</cell>\n";
            echo "<cell>" . number_format((float) $row->selisih_qty, 2, '.', ',') . " KG</cell>\n";
            echo "<cell>{$badgeStatus}</cell>\n";
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

    public function getActiveAdjustmentByMovement($kode)
    {
        $kode = trim((string) $kode);
        if ($kode === '') {
            return response()->json(['success' => false, 'has_adjustment' => false]);
        }

        $adj = CNSInboundAdjustment::approved()
            ->where('movement_kode', $kode)
            ->orderBy('id', 'desc')
            ->first();

        if (!$adj) {
            return response()->json([
                'success'        => true,
                'has_adjustment' => false,
            ]);
        }

        return response()->json([
            'success'         => true,
            'has_adjustment'  => true,
            'adjustment'      => [
                'id'              => $adj->id,
                'adjustment_code' => $adj->adjustment_code,
                'movement_kode'   => $adj->movement_kode,
                'tank_name'       => $adj->tank_name,
                'original_qty'    => (float) $adj->original_qty,
                'adjusted_qty'    => (float) $adj->adjusted_qty,
                'selisih_qty'     => (float) $adj->selisih_qty,
                'notes'           => $adj->notes,
                'approved_by'     => $adj->approved_by,
                'approved_at'     => $adj->approved_at ? $adj->approved_at->format('d/m/Y H:i') : null,
            ],
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

        $sourceTank = MasterTank::findOrFail($request->tank_id);
        $currentStock = $this->calculateTankBalance($sourceTank->name);
        $tankCapacity = (float) $sourceTank->capacity;
        $tankRemaining = max(0, $tankCapacity - $currentStock);

        $origQty = round((float) $request->original_qty, 2);
        $adjQty = round((float) $request->adjusted_qty, 2);

        if ($adjQty > $tankRemaining && $tankCapacity > 0) {
            return response()->json([
                'success' => false,
                'message' => "Qty adjustment ({$adjQty} KG) melebihi sisa kapasitas tangki {$sourceTank->name} ({$tankRemaining} KG).",
            ], 422);
        }

        $selisihQty = round(max(0, $origQty - $adjQty), 2);
        $username = auth()->check() ? (auth()->user()->username ?? auth()->user()->name ?? 'System') : 'System';

        $docMeta = ['path' => null, 'name' => null];
        if ($request->hasFile('document')) {
            $docMeta = $this->storeAdjustmentDocument($request->file('document'), $request->adjustment_code);
        }

        DB::beginTransaction();
        try {
            $adjustment = CNSInboundAdjustment::create([
                'adjustment_code'    => $request->adjustment_code,
                'adjustment_date'    => $request->adjustment_date,
                'movement_id'        => $request->movement_id,
                'movement_kode'      => trim((string) $request->movement_kode),
                'asal_warehouse'     => $request->asal_warehouse,
                'tank_id'            => $sourceTank->id,
                'tank_name'          => $sourceTank->name,
                'original_qty'       => $origQty,
                'tank_capacity'      => $tankCapacity,
                'tank_current_stock' => $currentStock,
                'tank_remaining'     => $tankRemaining,
                'adjusted_qty'       => $adjQty,
                'selisih_qty'        => $selisihQty,
                'notes'              => $request->notes,
                'document_path'      => $docMeta['path'],
                'document_name'      => $docMeta['name'],
                'approval_status'    => 'PENDING',
                'created_by'         => $username,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data Inbound Adjustment berhasil disimpan dengan status PENDING.',
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
        $adjustment = CNSInboundAdjustment::findOrFail($id);

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

        $sourceTank = MasterTank::findOrFail($request->tank_id);
        $currentStock = $this->calculateTankBalance($sourceTank->name);
        $tankCapacity = (float) $sourceTank->capacity;
        $tankRemaining = max(0, $tankCapacity - $currentStock);

        $origQty = round((float) $request->original_qty, 2);
        $adjQty = round((float) $request->adjusted_qty, 2);

        if ($adjQty > $tankRemaining && $tankCapacity > 0) {
            return response()->json([
                'success' => false,
                'message' => "Qty adjustment ({$adjQty} KG) melebihi sisa kapasitas tangki {$sourceTank->name} ({$tankRemaining} KG).",
            ], 422);
        }

        $selisihQty = round(max(0, $origQty - $adjQty), 2);
        $username = auth()->check() ? (auth()->user()->username ?? auth()->user()->name ?? 'System') : 'System';

        $oldDocPath = $adjustment->document_path;
        $newDocMeta = $request->hasFile('document')
            ? $this->storeAdjustmentDocument($request->file('document'), $request->adjustment_code)
            : null;

        DB::beginTransaction();
        try {
            $updatePayload = [
                'adjustment_code'    => $request->adjustment_code,
                'adjustment_date'    => $request->adjustment_date,
                'movement_id'        => $request->movement_id,
                'movement_kode'      => trim((string) $request->movement_kode),
                'asal_warehouse'     => $request->asal_warehouse,
                'tank_id'            => $sourceTank->id,
                'tank_name'          => $sourceTank->name,
                'original_qty'       => $origQty,
                'tank_capacity'      => $tankCapacity,
                'tank_current_stock' => $currentStock,
                'tank_remaining'     => $tankRemaining,
                'adjusted_qty'       => $adjQty,
                'selisih_qty'        => $selisihQty,
                'notes'              => $request->notes,
                'updated_by'         => $username,
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
                'message' => 'Data Inbound Adjustment berhasil diperbarui.',
                'data'    => $adjustment,
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
        $adjustment = CNSInboundAdjustment::findOrFail($id);

        if ($adjustment->approval_status === 'APPROVED') {
            return response()->json([
                'success' => false,
                'message' => 'Data Inbound Adjustment yang sudah APPROVED tidak dapat dihapus.',
            ], 422);
        }

        $adjustment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Inbound Adjustment berhasil dihapus.',
        ]);
    }

    public function approve(Request $request, $id)
    {
        $adjustment = CNSInboundAdjustment::findOrFail($id);

        if ($adjustment->approval_status === 'APPROVED') {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah berstatus APPROVED.',
            ], 422);
        }

        $actor = auth()->check() ? (auth()->user()->username ?? auth()->user()->name ?? 'admin') : 'admin';

        $adjustment->update([
            'approval_status'  => 'APPROVED',
            'approved_by'      => $actor,
            'approved_at'      => now(),
            'rejection_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inbound Adjustment berhasil di-approve.',
        ]);
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

        $adjustment = CNSInboundAdjustment::findOrFail($id);

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
            'message' => 'Inbound Adjustment berhasil ditolak.',
        ]);
    }

    public function document($id)
    {
        $adjustment = CNSInboundAdjustment::findOrFail($id);
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
        $prefix = "IADJ-{$dateStr}-";

        $latest = CNSInboundAdjustment::withTrashed()
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

    protected function validateAdjustmentRequest(Request $request, ?CNSInboundAdjustment $existing = null)
    {
        $codeRule = 'required|string|max:50|unique:cns_inbound_adjustments,adjustment_code' . ($existing ? ",{$existing->id}" : '');

        return Validator::make($request->all(), [
            'adjustment_code' => $codeRule,
            'adjustment_date' => 'required|date',
            'movement_kode'   => 'required|string|max:100',
            'tank_id'         => 'required|exists:cns_master_tanks,id',
            'original_qty'    => 'required|numeric|min:0.01',
            'adjusted_qty'    => 'required|numeric|min:0.01',
            'notes'           => 'nullable|string',
            'document'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'adjustment_code.unique' => 'Kode adjustment sudah pernah digunakan.',
            'movement_kode.required' => 'Nomor movement wajib dipilih/diisi.',
            'tank_id.required'       => 'Tangki tujuan wajib dipilih.',
            'original_qty.required'  => 'Qty original movement wajib terisi.',
            'adjusted_qty.required'  => 'Qty adjustment wajib diisi.',
            'document.mimes'         => 'Dokumen pendukung harus berformat PDF, JPG, JPEG, atau PNG.',
            'document.max'           => 'Ukuran dokumen pendukung maksimal 10 MB.',
        ]);
    }

    protected function storeAdjustmentDocument($file, string $adjCode): array
    {
        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $adjCode);
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'INADJ_' . $safeCode . '_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
        $path = $file->storeAs('cns/inbound-adjustment-docs', $filename, 'local');

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
