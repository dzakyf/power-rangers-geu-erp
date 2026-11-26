<?php

namespace App\Http\Controllers\CNS;

use App\CnsShipmentCostAllocation;
use App\CnsShipmentCostAllocationItem;
use App\Http\Controllers\Controller;
use App\ShippingInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShipmentCostAllocationController extends Controller
{
    public function index(Request $request)
    {
        $allocations = $this->filteredAllocationsQuery($request)
            ->orderByDesc('id')
            ->get();

        $metaMap = $this->getAllocationMetaMap($allocations);

        $summary = [
            'count'            => $allocations->count(),
            'final_count'      => $allocations->where('status', CnsShipmentCostAllocation::STATUS_FINAL)->count(),
            'total_cost_usd'   => round($allocations->sum('total_cost_usd'), 2),
            'total_cost_idr'   => round($allocations->sum('total_cost_idr'), 2),
            'total_volume_mt'  => round($allocations->sum('volume_mt'), 3),
            'total_containers' => (int) $allocations->sum('container_count'),
            'freight_usd'      => round($allocations->sum('total_freight_usd'), 2),
            'trucking_usd'     => round($allocations->sum('total_trucking_usd'), 2),
            'surveyor_usd'     => round($allocations->sum('total_surveyor_usd'), 2),
            'port_usd'         => round($allocations->sum('total_port_usd'), 2),
        ];
        $summary['avg_cost_per_mt_usd'] = $summary['total_volume_mt'] > 0
            ? round($summary['total_cost_usd'] / $summary['total_volume_mt'], 2)
            : 0.0;

        return view('cns.export.cost-allocation', [
            'allocations' => $allocations,
            'metaMap'     => $metaMap,
            'summary'     => $summary,
            'filters'     => [
                'tgl1'   => $request->input('tgl1', ''),
                'tgl2'   => $request->input('tgl2', ''),
                'status' => $request->input('status', ''),
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function create()
    {
        $allocatedBcNos = CnsShipmentCostAllocation::query()
            ->whereNotNull('bc_no')
            ->where('bc_no', '!=', '')
            ->pluck('bc_no')
            ->all();

        $bcs = $this->getAvailableBookingConfirmations($allocatedBcNos);

        return view('cns.export.cost-allocation-form', [
            'allocation'  => null,
            'itemsByCat'  => $this->emptyItemsByCategory(),
            'bcs'         => $bcs,
            'currentMeta' => [],
            'categories'  => CnsShipmentCostAllocation::CATEGORIES,
            'readOnly'    => false,
        ]);
    }

    public function edit($id)
    {
        $allocation = CnsShipmentCostAllocation::with('items')->findOrFail($id);

        $itemsByCat = $this->emptyItemsByCategory();
        foreach ($allocation->items as $item) {
            $cat = strtolower((string) $item->category);
            if (array_key_exists($cat, $itemsByCat)) {
                $itemsByCat[$cat][] = $item;
            }
        }

        $currentMeta = $this->getAllocationMetaMap(collect([$allocation]))[$allocation->id] ?? [];

        return view('cns.export.cost-allocation-form', [
            'allocation'  => $allocation,
            'itemsByCat'  => $itemsByCat,
            'bcs'         => [],
            'currentMeta' => $currentMeta,
            'categories'  => CnsShipmentCostAllocation::CATEGORIES,
            'readOnly'    => $allocation->isFinal() || (function_exists('cns_can') && !cns_can('cost_allocation_shipment', 'edit')),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $bcNo = trim((string) $data['bc_no']);

        $meta = $this->getBcMetadataMap([$bcNo])[$bcNo] ?? null;
        if (!$meta) {
            return redirect()
                ->back()
                ->withErrors(['bc_no' => 'Booking Confirmation (BC No) tidak ditemukan pada Vessel Schedule.'])
                ->withInput();
        }

        $existing = CnsShipmentCostAllocation::where('bc_no', $bcNo)->first();
        if ($existing) {
            return redirect()
                ->route('cns-export-cost-allocation.edit', $existing->id)
                ->with('error', 'Cost Allocation untuk BC ini sudah ada.');
        }

        $username = $this->currentUsername();

        $allocation = DB::transaction(function () use ($data, $bcNo, $meta, $username) {
            $allocation = CnsShipmentCostAllocation::create([
                'allocation_no'         => CnsShipmentCostAllocation::generateNextAllocationNo(),
                'bc_no'                 => $bcNo,
                'si_id'                 => $meta['si_id'] ?? null,
                'si_no'                 => $meta['si_no'] !== '-' ? $meta['si_no'] : null,
                'final_si_no'           => $meta['final_si_no'] !== '-' ? $meta['final_si_no'] : null,
                'order_no'              => $meta['order_no'] !== '-' ? $meta['order_no'] : null,
                'default_exchange_rate' => (float) ($data['default_exchange_rate'] ?? 16000),
                'volume_mt'             => (float) ($meta['volume_mt'] ?? 0),
                'container_count'       => (int) ($meta['containers'] ?? 0),
                'status'                => CnsShipmentCostAllocation::STATUS_DRAFT,
                'notes'                 => $data['notes'] ?? null,
                'created_by'            => $username,
                'updated_by'            => $username,
            ]);

            $this->syncItems($allocation, $data['items'] ?? []);
            $allocation->recalculateTotals();
            $allocation->save();

            return $allocation;
        });

        return redirect()
            ->route('cns-export-cost-allocation.edit', $allocation->id)
            ->with('success', 'Cost Allocation berhasil dibuat (' . $allocation->allocation_no . ').');
    }

    public function update(Request $request, $id)
    {
        $allocation = CnsShipmentCostAllocation::findOrFail($id);
        if ($allocation->isFinal()) {
            return redirect()
                ->route('cns-export-cost-allocation.edit', $allocation->id)
                ->with('error', 'Cost Allocation sudah berstatus FINAL. Reopen terlebih dahulu untuk mengubah.');
        }

        $data = $this->validatePayload($request, false);
        $meta = $this->getAllocationMetaMap(collect([$allocation]))[$allocation->id] ?? [];

        DB::transaction(function () use ($allocation, $data, $meta) {
            $allocation->default_exchange_rate = (float) ($data['default_exchange_rate'] ?? $allocation->default_exchange_rate);
            if (!empty($meta)) {
                $allocation->volume_mt = (float) ($meta['volume_mt'] ?? $allocation->volume_mt);
                $allocation->container_count = (int) ($meta['containers'] ?? $allocation->container_count);
            }
            $allocation->notes = $data['notes'] ?? null;
            $allocation->updated_by = $this->currentUsername();
            $allocation->save();

            $this->syncItems($allocation, $data['items'] ?? []);
            $allocation->recalculateTotals();
            $allocation->save();
        });

        return redirect()
            ->route('cns-export-cost-allocation.edit', $allocation->id)
            ->with('success', 'Cost Allocation berhasil diperbarui.');
    }

    public function finalize($id)
    {
        $allocation = CnsShipmentCostAllocation::with('items')->findOrFail($id);
        if ($allocation->isFinal()) {
            return redirect()
                ->route('cns-export-cost-allocation.edit', $allocation->id)
                ->with('error', 'Cost Allocation sudah berstatus FINAL.');
        }

        $allocation->recalculateTotals();
        $allocation->status = CnsShipmentCostAllocation::STATUS_FINAL;
        $allocation->finalized_by = $this->currentUsername();
        $allocation->finalized_at = now();
        $allocation->updated_by = $this->currentUsername();
        $allocation->save();

        return redirect()
            ->route('cns-export-cost-allocation.edit', $allocation->id)
            ->with('success', 'Cost Allocation berhasil difinalisasi.');
    }

    public function reopen($id)
    {
        $allocation = CnsShipmentCostAllocation::findOrFail($id);
        if (!$allocation->isFinal()) {
            return redirect()
                ->route('cns-export-cost-allocation.edit', $allocation->id)
                ->with('error', 'Cost Allocation sudah berstatus DRAFT.');
        }

        $allocation->status = CnsShipmentCostAllocation::STATUS_DRAFT;
        $allocation->finalized_by = null;
        $allocation->finalized_at = null;
        $allocation->updated_by = $this->currentUsername();
        $allocation->save();

        return redirect()
            ->route('cns-export-cost-allocation.edit', $allocation->id)
            ->with('success', 'Cost Allocation berhasil dibuka kembali (DRAFT).');
    }

    public function destroy($id)
    {
        $allocation = CnsShipmentCostAllocation::findOrFail($id);
        if ($allocation->isFinal()) {
            return response()->json([
                'success' => false,
                'message' => 'Cost Allocation berstatus FINAL tidak dapat dihapus.',
            ], 422);
        }

        $allocation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cost Allocation berhasil dihapus.',
        ]);
    }

    public function exportCsv(Request $request)
    {
        $allocations = $this->filteredAllocationsQuery($request)
            ->orderByDesc('id')
            ->get();
        $metaMap = $this->getAllocationMetaMap($allocations);

        $filename = 'cost_allocation_per_shipment_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($allocations, $metaMap) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Allocation No',
                'BC No',
                'SI No',
                'Order No',
                'Consignee',
                'Vessel',
                'POD',
                'Volume (MT)',
                'Containers',
                'Freight (USD)',
                'Trucking (USD)',
                'Surveyor (USD)',
                'Port (USD)',
                'Total Cost (USD)',
                'Total Cost (IDR)',
                'Cost / MT (USD)',
                'Cost / Container (USD)',
                'Status',
            ]);

            foreach ($allocations as $row) {
                $meta = $metaMap[$row->id] ?? [];
                fputcsv($handle, [
                    $row->allocation_no,
                    $row->bc_no ?: '-',
                    $row->si_no ?: ($meta['si_no'] ?? '-'),
                    $row->order_no ?: ($meta['order_no'] ?? '-'),
                    $meta['consignee'] ?? '-',
                    $meta['vessel_name'] ?? '-',
                    $meta['pod'] ?? '-',
                    number_format((float) $row->volume_mt, 3, '.', ''),
                    (int) $row->container_count,
                    number_format((float) $row->total_freight_usd, 2, '.', ''),
                    number_format((float) $row->total_trucking_usd, 2, '.', ''),
                    number_format((float) $row->total_surveyor_usd, 2, '.', ''),
                    number_format((float) $row->total_port_usd, 2, '.', ''),
                    number_format((float) $row->total_cost_usd, 2, '.', ''),
                    number_format((float) $row->total_cost_idr, 0, '.', ''),
                    number_format((float) $row->cost_per_mt_usd, 2, '.', ''),
                    number_format((float) $row->cost_per_container_usd, 2, '.', ''),
                    $row->status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredAllocationsQuery(Request $request)
    {
        $query = CnsShipmentCostAllocation::query();

        if ($request->filled('tgl1') && $request->filled('tgl2')) {
            $query->whereBetween('created_at', [
                $request->input('tgl1') . ' 00:00:00',
                $request->input('tgl2') . ' 23:59:59',
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', strtoupper(trim($request->input('status'))));
        }

        if ($request->filled('search')) {
            $term = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('allocation_no', 'like', $term)
                    ->orWhere('bc_no', 'like', $term)
                    ->orWhere('si_no', 'like', $term)
                    ->orWhere('final_si_no', 'like', $term)
                    ->orWhere('order_no', 'like', $term);
            });
        }

        return $query;
    }

    private function validatePayload(Request $request, bool $requireBc = true): array
    {
        $rules = [
            'default_exchange_rate' => 'required|numeric|min:1',
            'notes'                 => 'nullable|string|max:2000',
            'items'                 => 'nullable|array',
            'items.*.category'      => 'required|in:freight,trucking,surveyor,port',
            'items.*.description'   => 'required|string|max:255',
            'items.*.vendor_name'   => 'nullable|string|max:150',
            'items.*.reference_no'  => 'nullable|string|max:100',
            'items.*.currency'      => 'required|in:USD,IDR',
            'items.*.amount'        => 'required|numeric|min:0',
            'items.*.exchange_rate' => 'required|numeric|min:1',
            'items.*.notes'         => 'nullable|string|max:255',
        ];

        if ($requireBc) {
            $rules['bc_no'] = 'required|string|max:150';
        }

        return $request->validate($rules);
    }

    private function syncItems(CnsShipmentCostAllocation $allocation, array $items): void
    {
        $allocation->items()->delete();
        $allocation->unsetRelation('items');

        foreach ($items as $row) {
            $desc = trim((string) ($row['description'] ?? ''));
            if ($desc === '') {
                continue;
            }

            $converted = CnsShipmentCostAllocationItem::convertAmounts(
                (string) ($row['currency'] ?? 'USD'),
                (float) ($row['amount'] ?? 0),
                (float) ($row['exchange_rate'] ?? $allocation->default_exchange_rate)
            );

            $allocation->items()->create([
                'category'      => strtolower((string) $row['category']),
                'description'   => $desc,
                'vendor_name'   => $row['vendor_name'] ?? null,
                'reference_no'  => $row['reference_no'] ?? null,
                'currency'      => $converted['currency'],
                'amount'        => (float) ($row['amount'] ?? 0),
                'exchange_rate' => $converted['exchange_rate'],
                'amount_usd'    => $converted['amount_usd'],
                'amount_idr'    => $converted['amount_idr'],
                'notes'         => $row['notes'] ?? null,
            ]);
        }
    }

    private function emptyItemsByCategory(): array
    {
        return [
            'freight'  => [],
            'trucking' => [],
            'surveyor' => [],
            'port'     => [],
        ];
    }

    private function getAllocationMetaMap($allocations): array
    {
        $bcNos = [];
        $legacySiNos = [];

        foreach ($allocations as $row) {
            if (!empty($row->bc_no)) {
                $bcNos[] = $row->bc_no;
            } elseif (!empty($row->si_no)) {
                $legacySiNos[] = $row->si_no;
            }
        }

        $bcMeta = $this->getBcMetadataMap($bcNos);
        $siMeta = $this->getShipmentMetadataMap($legacySiNos);

        $result = [];
        foreach ($allocations as $row) {
            if (!empty($row->bc_no) && isset($bcMeta[$row->bc_no])) {
                $result[$row->id] = $bcMeta[$row->bc_no];
            } elseif (!empty($row->si_no) && isset($siMeta[$row->si_no])) {
                $result[$row->id] = $siMeta[$row->si_no];
            } else {
                $result[$row->id] = [];
            }
        }

        return $result;
    }

    private function getAvailableBookingConfirmations(array $excludeBcNos = []): array
    {
        if (!Schema::hasTable('vessel_schedules') || !Schema::hasColumn('vessel_schedules', 'BC_No')) {
            return [];
        }

        $query = DB::table('vessel_schedules')
            ->whereNotNull('BC_No')
            ->where('BC_No', '!=', '');

        if (!empty($excludeBcNos)) {
            $query->whereNotIn('BC_No', $excludeBcNos);
        }

        $bcNos = $query
            ->groupBy('BC_No')
            ->orderByDesc(DB::raw('MAX(id)'))
            ->pluck('BC_No')
            ->all();

        $metaMap = $this->getBcMetadataMap($bcNos);
        $result = [];
        foreach ($bcNos as $bcNo) {
            $meta = $metaMap[$bcNo] ?? [];
            $result[] = [
                'bc_no'       => $bcNo,
                'si_no'       => $meta['si_no'] ?? '-',
                'order_no'    => $meta['order_no'] ?? '-',
                'consignee'   => $meta['consignee'] ?? '-',
                'vessel_name' => $meta['vessel_name'] ?? '-',
                'pod'         => $meta['pod'] ?? '-',
                'volume_mt'   => $meta['volume_mt'] ?? 0.0,
                'containers'  => $meta['containers'] ?? 0,
            ];
        }

        return $result;
    }

    private function getBcMetadataMap(array $bcNos): array
    {
        $bcNos = array_values(array_unique(array_filter($bcNos)));
        if (empty($bcNos) || !Schema::hasTable('vessel_schedules')) {
            return [];
        }

        $schedules = DB::table('vessel_schedules')
            ->whereIn('BC_No', $bcNos)
            ->orderBy('id')
            ->get(['id', 'Task_ID', 'Order_No', 'BC_No', 'Vessel', 'POL', 'POD', 'ETD', 'ATD', 'Qty']);

        if ($schedules->isEmpty()) {
            return [];
        }

        $byBc = [];
        $taskToBc = [];
        $orderToBc = [];
        $allTaskIds = [];
        $allOrderNos = [];

        foreach ($schedules as $vs) {
            $bc = (string) $vs->BC_No;
            $byBc[$bc][] = $vs;
            if (!empty($vs->Task_ID)) {
                $taskToBc[$vs->Task_ID] = $bc;
                $allTaskIds[] = $vs->Task_ID;
            }
            if (!empty($vs->Order_No)) {
                $orderToBc[$vs->Order_No] = $bc;
                $allOrderNos[] = $vs->Order_No;
            }
        }

        $allTaskIds = array_values(array_unique($allTaskIds));
        $allOrderNos = array_values(array_unique($allOrderNos));

        $vsdTotals = [];
        if (Schema::hasTable('vessel_schedule_dtls') && (!empty($allTaskIds) || !empty($allOrderNos))) {
            $vsdRows = DB::table('vessel_schedule_dtls')
                ->where('Status', '!=', 'CANCELED')
                ->where(function ($q) use ($allTaskIds, $allOrderNos) {
                    if (!empty($allTaskIds)) {
                        $q->whereIn('Task_ID', $allTaskIds);
                    }
                    if (!empty($allOrderNos)) {
                        !empty($allTaskIds) ? $q->orWhereIn('Order_No', $allOrderNos) : $q->whereIn('Order_No', $allOrderNos);
                    }
                })
                ->get(['Task_ID', 'Order_No', 'Container', 'Netto_MT']);

            foreach ($vsdRows as $row) {
                $bc = $taskToBc[$row->Task_ID ?? ''] ?? ($orderToBc[$row->Order_No ?? ''] ?? null);
                if ($bc === null) {
                    continue;
                }
                if (!isset($vsdTotals[$bc])) {
                    $vsdTotals[$bc] = ['volume_mt' => 0.0, 'containers' => []];
                }
                $vsdTotals[$bc]['volume_mt'] += (float) ($row->Netto_MT ?? 0);
                if (!empty($row->Container)) {
                    $vsdTotals[$bc]['containers'][(string) $row->Container] = true;
                }
            }
        }

        $siByOrder = [];
        $siTable = Schema::hasTable('shipping_instructions') ? 'shipping_instructions' : 'shipment_instructions';
        if (Schema::hasTable($siTable) && !empty($allOrderNos)) {
            $sis = DB::table($siTable)
                ->whereIn('Order_No', $allOrderNos)
                ->orderBy('id')
                ->get(['id', 'SI_No', 'Final_SI_No', 'Order_No', 'Consignee', 'POD', 'POL', 'ETD']);

            foreach ($sis as $si) {
                if (!isset($siByOrder[$si->Order_No])) {
                    $siByOrder[$si->Order_No] = $si;
                }
            }
        }

        $consigneeMap = [];
        if (Schema::hasTable('supplier')) {
            $suppliers = DB::table('supplier')->select('Supplier_ID', 'Company_Name', 'ID')->get();
            foreach ($suppliers as $s) {
                if ($s->Supplier_ID) {
                    $consigneeMap[$s->Supplier_ID] = $s->Company_Name;
                }
                $consigneeMap[(string) $s->ID] = $s->Company_Name;
            }
        }

        $map = [];
        foreach ($byBc as $bcNo => $rows) {
            $first = $rows[0];
            $si = null;
            $qtyFallback = 0.0;
            $orderNos = [];

            foreach ($rows as $r) {
                $qtyFallback += $this->parseNumeric($r->Qty);
                if (!empty($r->Order_No)) {
                    $orderNos[] = $r->Order_No;
                    if (!$si && isset($siByOrder[$r->Order_No])) {
                        $si = $siByOrder[$r->Order_No];
                    }
                }
            }

            $vsd = $vsdTotals[$bcNo] ?? ['volume_mt' => 0.0, 'containers' => []];
            $volumeMt = $vsd['volume_mt'] > 0 ? $vsd['volume_mt'] : $qtyFallback;
            $consigneeRaw = $si->Consignee ?? '';
            $consigneeName = $consigneeMap[$consigneeRaw] ?? ($consigneeRaw ?: '-');

            $map[$bcNo] = [
                'si_id'       => $si->id ?? null,
                'si_no'       => $si->SI_No ?? '-',
                'final_si_no' => $si->Final_SI_No ?? '-',
                'order_no'    => !empty($orderNos) ? implode(', ', array_unique($orderNos)) : '-',
                'consignee'   => $consigneeName,
                'vessel_name' => $first->Vessel ?: '-',
                'pol'         => $first->POL ?: ($si->POL ?? '-'),
                'pod'         => $first->POD ?: ($si->POD ?? '-'),
                'etd'         => $first->ATD ?: ($first->ETD ?: ($si->ETD ?? '-')),
                'volume_mt'   => round($volumeMt, 4),
                'containers'  => count($vsd['containers']),
            ];
        }

        return $map;
    }

    private function getShipmentMetadataMap(array $siNos): array
    {
        $siNos = array_values(array_unique(array_filter($siNos)));
        if (empty($siNos)) {
            return [];
        }

        $vsdData = [];
        if (Schema::hasTable('vessel_schedule_dtls')) {
            $vsds = DB::table('vessel_schedule_dtls')
                ->whereIn('SI_No', $siNos)
                ->where('Status', '!=', 'CANCELED')
                ->groupBy('SI_No')
                ->select(
                    'SI_No',
                    DB::raw('COALESCE(SUM(Netto_MT), 0) as total_netto_mt'),
                    DB::raw('COUNT(DISTINCT Container) as total_containers')
                )
                ->get();

            foreach ($vsds as $v) {
                $vsdData[$v->SI_No] = [
                    'volume_mt'  => (float) $v->total_netto_mt,
                    'containers' => (int) $v->total_containers,
                ];
            }
        }

        $consigneeMap = [];
        if (Schema::hasTable('supplier')) {
            $suppliers = DB::table('supplier')->select('Supplier_ID', 'Company_Name', 'ID')->get();
            foreach ($suppliers as $s) {
                if ($s->Supplier_ID) {
                    $consigneeMap[$s->Supplier_ID] = $s->Company_Name;
                }
                $consigneeMap[(string) $s->ID] = $s->Company_Name;
            }
        }

        $siTable = Schema::hasTable('shipping_instructions') ? 'shipping_instructions' : 'shipment_instructions';
        $rows = DB::table("{$siTable} as si")
            ->whereIn('si.SI_No', $siNos)
            ->leftJoin('vessel_schedules as vs', 'vs.Order_No', '=', 'si.Order_No')
            ->select(
                'si.SI_No',
                'si.Final_SI_No',
                'si.Consignee',
                'si.POD as si_pod',
                'si.POL as si_pol',
                'si.Qty',
                'si.ETD as si_etd',
                'vs.Vessel as vessel_name',
                'vs.POD as vs_pod',
                'vs.POL as vs_pol',
                'vs.ETD as vs_etd',
                'vs.ATD as vs_atd'
            )
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $vsd = $vsdData[$r->SI_No] ?? ['volume_mt' => 0.0, 'containers' => 0];
            $volumeMt = $vsd['volume_mt'] > 0 ? $vsd['volume_mt'] : $this->parseNumeric($r->Qty);
            $consigneeName = $consigneeMap[$r->Consignee] ?? ($r->Consignee ?: '-');

            $map[$r->SI_No] = [
                'final_si_no' => $r->Final_SI_No ?: '-',
                'consignee'   => $consigneeName,
                'vessel_name' => $r->vessel_name ?: '-',
                'pol'         => $r->vs_pol ?: ($r->si_pol ?: '-'),
                'pod'         => $r->vs_pod ?: ($r->si_pod ?: '-'),
                'etd'         => $r->vs_atd ?: ($r->si_etd ?: ($r->vs_etd ?: '-')),
                'volume_mt'   => round($volumeMt, 4),
                'containers'  => (int) $vsd['containers'],
            ];
        }

        return $map;
    }

    private function parseNumeric($val): float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }
        $cleaned = preg_replace('/[^0-9.\-]/', '', (string) $val);
        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }

    private function currentUsername(): string
    {
        $user = Auth::user();
        if (!$user) {
            return 'system';
        }
        return (string) ($user->username ?? $user->name ?? 'system');
    }
}
