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

        $siMeta = $this->getShipmentMetadataMap($allocations->pluck('si_no')->filter()->all());

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
            'siMeta'      => $siMeta,
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
        $allocatedSiIds = CnsShipmentCostAllocation::query()
            ->whereNotNull('si_id')
            ->pluck('si_id')
            ->all();

        $shipments = $this->getAvailableShipments($allocatedSiIds);

        return view('cns.export.cost-allocation-form', [
            'allocation' => null,
            'itemsByCat' => $this->emptyItemsByCategory(),
            'shipments'  => $shipments,
            'siMeta'     => $this->getShipmentMetadataMap(array_column($shipments, 'si_no')),
            'categories' => CnsShipmentCostAllocation::CATEGORIES,
            'readOnly'   => false,
        ]);
    }

    public function edit($id)
    {
        $allocation = CnsShipmentCostAllocation::with('items')->findOrFail($id);
        $shipments = $this->getAvailableShipments([], $allocation->si_id);

        $itemsByCat = $this->emptyItemsByCategory();
        foreach ($allocation->items as $item) {
            $cat = strtolower((string) $item->category);
            if (array_key_exists($cat, $itemsByCat)) {
                $itemsByCat[$cat][] = $item;
            }
        }

        return view('cns.export.cost-allocation-form', [
            'allocation' => $allocation,
            'itemsByCat' => $itemsByCat,
            'shipments'  => $shipments,
            'siMeta'     => $this->getShipmentMetadataMap([$allocation->si_no]),
            'categories' => CnsShipmentCostAllocation::CATEGORIES,
            'readOnly'   => $allocation->isFinal() || (function_exists('cns_can') && !cns_can('cost_allocation_shipment', 'edit')),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $si = ShippingInstruction::findOrFail($data['si_id']);
        $existing = CnsShipmentCostAllocation::where('si_id', $si->id)->first();
        if ($existing) {
            return redirect()
                ->route('cns-export-cost-allocation.edit', $existing->id)
                ->with('error', 'Cost Allocation untuk SI ini sudah ada.');
        }

        $meta = $this->getShipmentMetadataMap([$si->SI_No])[$si->SI_No] ?? [];
        $username = $this->currentUsername();

        $allocation = DB::transaction(function () use ($data, $si, $meta, $username) {
            $allocation = CnsShipmentCostAllocation::create([
                'allocation_no'         => CnsShipmentCostAllocation::generateNextAllocationNo(),
                'si_id'                 => $si->id,
                'si_no'                 => $si->SI_No,
                'final_si_no'           => $si->Final_SI_No,
                'order_no'              => $si->Order_No,
                'default_exchange_rate' => (float) ($data['default_exchange_rate'] ?? 16000),
                'volume_mt'             => (float) ($meta['volume_mt'] ?? $this->parseNumeric($si->Qty)),
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
        $meta = $this->getShipmentMetadataMap([$allocation->si_no])[$allocation->si_no] ?? [];

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
        $siMeta = $this->getShipmentMetadataMap($allocations->pluck('si_no')->filter()->all());

        $filename = 'cost_allocation_per_shipment_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($allocations, $siMeta) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Allocation No',
                'SI No',
                'Final SI No',
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
                $meta = $siMeta[$row->si_no] ?? [];
                fputcsv($handle, [
                    $row->allocation_no,
                    $row->si_no,
                    $row->final_si_no ?: '-',
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
                    ->orWhere('si_no', 'like', $term)
                    ->orWhere('final_si_no', 'like', $term)
                    ->orWhere('order_no', 'like', $term);
            });
        }

        return $query;
    }

    private function validatePayload(Request $request, bool $requireSi = true): array
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

        if ($requireSi) {
            $rules['si_id'] = 'required|integer|exists:shipping_instructions,id';
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

    private function getAvailableShipments(array $excludeIds = [], ?int $includeId = null): array
    {
        $query = ShippingInstruction::query()
            ->whereIn('Status', ['DRAFT', 'CREATED', 'APPROVED', 'FINAL', 'COMPLETED'])
            ->orderByDesc('id');

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }
        if ($includeId !== null) {
            $query->orWhere('id', $includeId);
        }

        $sis = $query->get(['id', 'SI_No', 'Final_SI_No', 'Order_No', 'Consignee', 'POD', 'Qty', 'Status']);
        $metaMap = $this->getShipmentMetadataMap($sis->pluck('SI_No')->all());

        $result = [];
        foreach ($sis as $si) {
            $meta = $metaMap[$si->SI_No] ?? [];
            $result[] = [
                'id'          => $si->id,
                'si_no'       => $si->SI_No,
                'final_si_no' => $si->Final_SI_No ?: '-',
                'order_no'    => $si->Order_No ?: '-',
                'consignee'   => $meta['consignee'] ?? ($si->Consignee ?: '-'),
                'vessel_name' => $meta['vessel_name'] ?? '-',
                'pod'         => $meta['pod'] ?? ($si->POD ?: '-'),
                'volume_mt'   => $meta['volume_mt'] ?? $this->parseNumeric($si->Qty),
                'containers'  => $meta['containers'] ?? 0,
            ];
        }

        return $result;
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
