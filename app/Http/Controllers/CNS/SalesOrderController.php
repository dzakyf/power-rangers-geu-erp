<?php

namespace App\Http\Controllers\CNS;

use App\CnsMasterKemasan;
use App\Http\Controllers\Controller;
use App\SalesOrderCNS;
use App\SalesOrderCNSDtl;
use App\SludgeMovement;
use App\TankDraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesOrderController extends Controller
{
    public function indexSalesOrder()
    {
        return view('cns.sales.input-order');
    }

    public function indexSalesOrderXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $salesorders = SalesOrderCNS::whereBetween('so_date',[$request->tgl1, $request->tgl2])->get();
        foreach ($salesorders as $key => $order) {
            echo "<row id=\"$order->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($order->so_date) . "</cell>";
            echo "<cell>" . htmlspecialchars($order->so_no) . "</cell>";
            echo "<cell>" . htmlspecialchars($order->site) . "</cell>";
            echo "<cell>" . htmlspecialchars($order->payment_term) . "</cell>";
            echo "<cell>" . htmlspecialchars($order->status ?: 'PENDING') . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    protected function getKemasanOptions()
    {
        return CnsMasterKemasan::where('product_type', 'Kemasan')->orderBy('Name', 'asc')->get(['Kemasan_ID', 'Name', 'Volume', 'Is_Ampas', 'Unit', 'Price']);
    }

    protected function getUsedSludgeQtyMap(?int $excludeSoId = null): array
    {
        try {
            if (!Schema::hasTable('sales_order_cns') || !Schema::hasColumn('sales_order_cns', 'draining_id')) {
                return [];
            }

            $query = DB::table('sales_order_cns as so')
                ->join('sales_order_cns_dtl as dtl', 'dtl.so_no', '=', 'so.so_no')
                ->where('so.so_type', 'SLUDGE')
                ->whereNotNull('so.draining_id')
                ->whereRaw("UPPER(COALESCE(so.status, '')) != 'REJECTED'");

            if (Schema::hasColumn('sales_order_cns', 'sludge_movement_id')) {
                $query->whereNull('so.sludge_movement_id');
            }
            if ($excludeSoId !== null) {
                $query->where('so.id', '!=', $excludeSoId);
            }

            return $query->select('so.draining_id', DB::raw('SUM(dtl.qty) as used_qty'))
                ->groupBy('so.draining_id')
                ->pluck('used_qty', 'so.draining_id')
                ->map(function ($qty) {
                    return (float) $qty;
                })
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getUsedMovementSludgeQtyMap(?int $excludeSoId = null): array
    {
        try {
            if (!Schema::hasTable('sales_order_cns') || !Schema::hasColumn('sales_order_cns', 'sludge_movement_id')) {
                return [];
            }

            $query = DB::table('sales_order_cns as so')
                ->join('sales_order_cns_dtl as dtl', 'dtl.so_no', '=', 'so.so_no')
                ->where('so.so_type', 'SLUDGE')
                ->whereNotNull('so.sludge_movement_id')
                ->whereRaw("UPPER(COALESCE(so.status, '')) != 'REJECTED'");

            if ($excludeSoId !== null) {
                $query->where('so.id', '!=', $excludeSoId);
            }

            return $query->select('so.sludge_movement_id', DB::raw('SUM(dtl.qty) as used_qty'))
                ->groupBy('so.sludge_movement_id')
                ->pluck('used_qty', 'so.sludge_movement_id')
                ->map(function ($qty) {
                    return (float) $qty;
                })
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getMovedOutSludgeQtyMap(): array
    {
        try {
            if (!Schema::hasTable('cns_sludge_movements')) {
                return [];
            }
            return SludgeMovement::select('draining_id', DB::raw('SUM(qty) as moved_qty'))
                ->groupBy('draining_id')
                ->pluck('moved_qty', 'draining_id')
                ->map(function ($qty) {
                    return (float) $qty;
                })
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getAvailableSludgeDrainings(?int $excludeSoId = null, ?int $selectedDrainingId = null, ?int $selectedMovementId = null)
    {
        try {
            $usedMap = $this->getUsedSludgeQtyMap($excludeSoId);
            $movedMap = $this->getMovedOutSludgeQtyMap();
            $drainings = TankDraining::where('qty_sludge', '>', 0)
                ->orderBy('draining_date', 'desc')
                ->orderBy('id', 'desc')
                ->get(['id', 'draining_code', 'draining_date', 'tank_name', 'qty_sludge']);

            $drainingItems = $drainings->map(function ($draining) use ($usedMap, $movedMap) {
                $used = (float) ($usedMap[$draining->id] ?? 0);
                $moved = (float) ($movedMap[$draining->id] ?? 0);
                $draining->available_qty_sludge = max(0.0, round((float) $draining->qty_sludge - $used - $moved, 4));
                $draining->option_value = (string) $draining->id;
                $draining->source_type = 'DRAINING';
                $draining->movement_id = null;
                return $draining;
            })->filter(function ($draining) use ($selectedDrainingId, $selectedMovementId) {
                $isSelectedDirect = $selectedMovementId === null && $selectedDrainingId !== null && (int) $draining->id === $selectedDrainingId;
                return $draining->available_qty_sludge > 0 || $isSelectedDirect;
            });

            $movementItems = collect();
            if (Schema::hasTable('cns_sludge_movements')) {
                $usedMovMap = $this->getUsedMovementSludgeQtyMap($excludeSoId);
                $movements = SludgeMovement::with('draining')
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();

                $movementItems = $movements->map(function ($mov) use ($usedMovMap) {
                    $used = (float) ($usedMovMap[$mov->id] ?? 0);
                    $avail = max(0.0, round((float) $mov->qty - $used, 4));
                    $srcCode = $mov->draining ? $mov->draining->draining_code : '';
                    return (object) [
                        'id'                   => $mov->draining_id,
                        'movement_id'          => $mov->id,
                        'option_value'         => 'M:' . $mov->id,
                        'source_type'          => 'MOVEMENT',
                        'draining_code'        => $mov->movement_code,
                        'tank_name'            => $mov->target_warehouse_name . ($srcCode ? ' / asal ' . $srcCode : ''),
                        'qty_sludge'           => (float) $mov->qty,
                        'available_qty_sludge' => $avail,
                    ];
                })->filter(function ($item) use ($selectedMovementId) {
                    return $item->available_qty_sludge > 0 || ($selectedMovementId !== null && (int) $item->movement_id === $selectedMovementId);
                });
            }

            return $drainingItems->concat($movementItems)->values();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    protected function getAvailableSludgeQty(int $drainingId, ?int $excludeSoId = null): ?float
    {
        $draining = TankDraining::find($drainingId);
        if (!$draining) {
            return null;
        }
        $usedMap = $this->getUsedSludgeQtyMap($excludeSoId);
        $movedMap = $this->getMovedOutSludgeQtyMap();
        $used = (float) ($usedMap[$drainingId] ?? 0);
        $moved = (float) ($movedMap[$drainingId] ?? 0);
        return max(0.0, round((float) $draining->qty_sludge - $used - $moved, 4));
    }

    protected function getAvailableMovementSludgeQty(int $movementId, ?int $excludeSoId = null): ?float
    {
        $movement = SludgeMovement::find($movementId);
        if (!$movement) {
            return null;
        }
        $usedMap = $this->getUsedMovementSludgeQtyMap($excludeSoId);
        $used = (float) ($usedMap[$movementId] ?? 0);
        return max(0.0, round((float) $movement->qty - $used, 4));
    }

    protected function resolveSludgeSource(Request $request): array
    {
        $rawVal = trim((string) $request->input('sludgeDrainingId', ''));
        $movementId = $request->filled('sludgeMovementId') ? (int) $request->input('sludgeMovementId') : null;
        $drainingId = null;

        if (strpos($rawVal, 'M:') === 0) {
            $movementId = (int) substr($rawVal, 2);
        } elseif ($rawVal !== '' && is_numeric($rawVal)) {
            $drainingId = (int) $rawVal;
        }

        $code = trim((string) $request->input('sludgeDrainingCode', ''));
        if ($movementId) {
            $mov = SludgeMovement::find($movementId);
            if ($mov) {
                $drainingId = (int) $mov->draining_id;
                if ($code === '') {
                    $code = (string) $mov->movement_code;
                }
            }
        } elseif ($drainingId && $code === '') {
            $draining = TankDraining::find($drainingId);
            $code = $draining ? (string) $draining->draining_code : '';
        }

        return [
            'draining_id' => $drainingId,
            'movement_id' => $movementId,
            'code'        => $code,
            'product_ref' => $movementId ? ('M:' . $movementId) : ($drainingId ? (string) $drainingId : ''),
        ];
    }

    protected function parseSubmittedItems(Request $request, ?bool $hasUnitCol = null): array
    {
        if (strtoupper((string) $request->input('soType', '')) === 'SLUDGE') {
            if ($hasUnitCol === null) {
                try {
                    $hasUnitCol = Schema::hasColumn('sales_order_cns_dtl', 'unit');
                } catch (\Throwable $e) {
                    $hasUnitCol = true;
                }
            }

            $src = $this->resolveSludgeSource($request);
            $qty = (float) $request->input('sludgeQty', 0);
            $price = (float) $request->input('sludgePrice', 0);
            $lineTotal = $qty * $price;
            $desc = $src['code'] !== '' ? "Sludge ({$src['code']})" : 'Sludge';

            $row = [
                'sort_order'  => 1,
                'description' => $desc,
                'qty'         => $qty,
                'unit_price'  => $price,
                'total'       => $lineTotal,
            ];
            if ($hasUnitCol) {
                $row['unit'] = 'KG';
            }
            if ($src['product_ref'] !== '') {
                $row['product_code'] = $src['product_ref'];
            }

            return [[$row], $lineTotal];
        }
        $raw = [];
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^([a-zA-Z_]+)(\d+)$/', $key, $matches)) {
                $raw[(int) $matches[2]][$matches[1]] = $value;
            }
        }
        ksort($raw);

        if ($hasUnitCol === null) {
            try {
                $hasUnitCol = Schema::hasColumn('sales_order_cns_dtl', 'unit');
            } catch (\Throwable $e) {
                $hasUnitCol = true;
            }
        }
        $items = [];
        $subtotal = 0.0;
        $sortOrder = 1;
        foreach ($raw as $value) {
            $desc = trim((string) ($value['description'] ?? ''));
            if ($desc === '') {
                continue;
            }
            $qty = (float) ($value['qty'] ?? 0);
            $unit = (float) ($value['unit'] ?? 0);
            $lineTotal = $qty * $unit;
            $row = [
                'sort_order'  => $sortOrder++,
                'description' => $desc,
                'qty'         => $qty,
                'unit_price'  => $unit,
                'total'       => $lineTotal,
            ];
            if ($hasUnitCol) {
                $itemUnit = trim((string) ($value['item_unit'] ?? ''));
                $row['unit'] = $itemUnit !== '' ? $itemUnit : null;
            }
            if (!empty($value['product_code'])) {
                $row['product_code'] = trim((string) $value['product_code']);
            }
            $items[] = $row;
            $subtotal += $lineTotal;
        }

        return [$items, $subtotal];
    }

    protected function buildHeaderPayload(Request $request, string $soNo, float $subtotal): array
    {
        $tax = (float) ($request->taxAmount ?? 0);
        $shipping = (float) ($request->shippingAmount ?? 0);
        $other = (float) ($request->otherAmount ?? 0);

        $siteId = !empty($request->site_id) ? (int) $request->site_id : $this->resolveSiteIdByName($request->site);

        $payload = [
            'so_no'          => $soNo,
            'so_date'        => $request->soDate,
            'buyer_pic'      => (string) ($request->buyerPic ?? ''),
            'buyer_city'     => (string) ($request->buyerKota ?? ''),
            'buyer_province' => (string) ($request->buyerProvinsi ?? ''),
            'buyer_phone'    => (string) ($request->buyerPhone ?? ''),
            'site'           => (string) ($request->site ?? ''),
            'site_address'   => (string) ($request->siteAddress ?? ''),
            'payment_term'   => (string) ($request->paymentTerm ?? ''),
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'shipping'       => $shipping,
            'other'          => $other,
            'total'          => $subtotal + $tax + $shipping + $other,
            'remarks'        => $request->remarks,
        ];

        $soType = strtoupper((string) $request->input('soType', 'KEMASAN_AMPAS'));
        if (!in_array($soType, ['SLUDGE', 'KEMASAN_AMPAS'], true)) {
            $soType = 'KEMASAN_AMPAS';
        }
        $sludgeSrc = $soType === 'SLUDGE' ? $this->resolveSludgeSource($request) : ['draining_id' => null, 'movement_id' => null, 'code' => null];
        $drainingId = $sludgeSrc['draining_id'];
        $movementId = $sludgeSrc['movement_id'];
        $drainingCode = $sludgeSrc['code'];

        try {
            if (Schema::hasColumn('sales_order_cns', 'site_id')) {
                $payload['site_id'] = $siteId;
            }
            if (Schema::hasColumn('sales_order_cns', 'status')) {
                $payload['status'] = $request->filled('status') ? (string) $request->status : 'PENDING';
            }
            if (Schema::hasColumn('sales_order_cns', 'so_type')) {
                $payload['so_type'] = $soType;
            }
            if (Schema::hasColumn('sales_order_cns', 'draining_id')) {
                $payload['draining_id'] = $drainingId;
            }
            if (Schema::hasColumn('sales_order_cns', 'draining_code')) {
                $payload['draining_code'] = $drainingCode;
            }
            if (Schema::hasColumn('sales_order_cns', 'sludge_movement_id')) {
                $payload['sludge_movement_id'] = $movementId;
            }
        } catch (\Throwable $e) {
            $payload['site_id'] = $siteId;
            $payload['status'] = $request->filled('status') ? (string) $request->status : 'PENDING';
        }

        return $payload;
    }

    protected function validateSludgeOrder(?int $drainingId, float $qty, ?int $excludeSoId = null, ?int $movementId = null): ?string
    {
        if (!$drainingId && !$movementId) {
            return 'Sumber sludge (kode draining / moving) wajib dipilih untuk Sales Order Sludge.';
        }
        if ($qty <= 0) {
            return 'Qty Sludge harus lebih dari 0.';
        }

        if ($movementId) {
            $availableSludge = $this->getAvailableMovementSludgeQty($movementId, $excludeSoId);
            if ($availableSludge === null) {
                return 'Data moving sludge tidak ditemukan.';
            }
            if ($qty > $availableSludge) {
                return "Qty Sludge ({$qty} KG) melebihi sisa qty moving sludge di gudang ({$availableSludge} KG).";
            }
            return null;
        }

        $availableSludge = $this->getAvailableSludgeQty($drainingId, $excludeSoId);
        if ($availableSludge === null) {
            return 'Data draining tidak ditemukan.';
        }
        if ($qty > $availableSludge) {
            return "Qty Sludge ({$qty} KG) melebihi sisa qty sludge draining yang tersedia ({$availableSludge} KG).";
        }

        return null;
    }

    public function newSalesOrder()
    {
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        try {
            $counter = SalesOrderCNS::where("so_date", 'LIKE', date("Y-m")."%")->count() + 1;
        } catch (\Throwable $e) {
            $counter = 1;
        }
        $socode = sprintf("%03d", $counter) . "/SO/" . $romawi[(int)date('n') - 1] . "/" . date("Y");
        $kemasans = $this->getKemasanOptions();
        $drainings = $this->getAvailableSludgeDrainings();
        return view('cns.sales.input-order-new', compact('socode', 'kemasans', 'drainings'));
    }

    public function newSalesOrderSubmit(Request $request)
    {
        $strtime = strtotime($request->soDate);
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        try {
            $counter = SalesOrderCNS::where("so_date", 'LIKE', date("Y-m", $strtime)."%")->count() + 1;
        } catch (\Throwable $e) {
            $counter = 1;
        }
        $socode = sprintf("%03d", $counter) . "/SO/" . $romawi[(int)date('n', $strtime) - 1] . "/" . date("Y", $strtime);

        [$items, $total] = $this->parseSubmittedItems($request);
        $headerPayload = $this->buildHeaderPayload($request, $socode, $total);
        $headerPayload['created_by'] = auth()->check() ? auth()->user()->username : '';

        $soType = strtoupper((string) ($headerPayload['so_type'] ?? $request->input('soType', 'KEMASAN_AMPAS')));
        if ($soType === 'SLUDGE') {
            $sludgeSrc = $this->resolveSludgeSource($request);
            $drainingId = $headerPayload['draining_id'] ?? $sludgeSrc['draining_id'];
            $movementId = $headerPayload['sludge_movement_id'] ?? $sludgeSrc['movement_id'];
            $sludgeQty = (float) ($items[0]['qty'] ?? 0);
            if ($error = $this->validateSludgeOrder($drainingId ?: null, $sludgeQty, null, $movementId ?: null)) {
                return response($error, 422);
            }
        } else {
            $siteId = $headerPayload['site_id'] ?? $this->resolveSiteIdByName($request->site);
            $siteName = (string) ($request->site ?? '');
            $inventoryService = app(\App\Services\CNS\KemasanInventoryService::class);

            foreach ($items as $item) {
                $qty = (float) ($item['qty'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $desc = $item['description'] ?? '';
                $packagingId = !empty($item['product_code']) ? (int) $item['product_code'] : null;
                if (!$packagingId) {
                    $kemasan = $this->findKemasanByDescription($desc);
                    $packagingId = $kemasan ? (int) $kemasan->Kemasan_ID : null;
                }

                $availableStock = $inventoryService->getAvailableSaleBalance($desc, $packagingId, $siteId);
                if ($qty > $availableStock) {
                    return response("Stok kemasan '{$desc}' tidak mencukupi di site {$siteName}. Tersedia: {$availableStock}, Diminta: {$qty}", 422);
                }
            }
        }

        DB::transaction(function () use ($socode, $items, $headerPayload) {
            foreach ($items as $item) {
                SalesOrderCNSDtl::create(array_merge($item, [
                    'so_no'      => $socode,
                    'created_by' => $headerPayload['created_by'],
                ]));
            }

            SalesOrderCNS::create($headerPayload);
        });

        return response('success');
    }

    public function detailSalesOrder(Request $request)
    {
        $salesOrder = SalesOrderCNS::where('id', $request->task)->firstOrFail();
        $details = SalesOrderCNSDtl::where('so_no', $salesOrder->so_no)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        $kemasans = $this->getKemasanOptions();
        $selectedDrainingId = !empty($salesOrder->draining_id) ? (int) $salesOrder->draining_id : null;
        $selectedMovementId = !empty($salesOrder->sludge_movement_id) ? (int) $salesOrder->sludge_movement_id : null;
        $drainings = $this->getAvailableSludgeDrainings((int) $salesOrder->id, $selectedDrainingId, $selectedMovementId);

        return view('cns.sales.input-order-detail', compact('salesOrder', 'details', 'kemasans', 'drainings'));
    }

    public function detailSalesOrderSubmit(Request $request)
    {
        $salesOrder = $request->filled('soId')
            ? SalesOrderCNS::findOrFail($request->soId)
            : SalesOrderCNS::where('so_no', $request->soCode)->firstOrFail();

        if (strtoupper((string) ($salesOrder->status ?? '')) === 'APPROVED') {
            return response('Sales Order yang sudah di-approve tidak dapat diubah.', 422);
        }

        $oldSoNo = $salesOrder->so_no;
        $newSoNo = trim((string) $request->soCode) !== '' ? trim((string) $request->soCode) : $oldSoNo;
        [$items, $total] = $this->parseSubmittedItems($request);
        $headerPayload = $this->buildHeaderPayload($request, $newSoNo, $total);

        $soType = strtoupper((string) ($headerPayload['so_type'] ?? $request->input('soType', $salesOrder->so_type ?? 'KEMASAN_AMPAS')));
        if ($soType === 'SLUDGE') {
            $sludgeSrc = $this->resolveSludgeSource($request);
            $drainingId = $headerPayload['draining_id'] ?? $sludgeSrc['draining_id'];
            $movementId = $headerPayload['sludge_movement_id'] ?? $sludgeSrc['movement_id'];
            $sludgeQty = (float) ($items[0]['qty'] ?? 0);
            if ($error = $this->validateSludgeOrder($drainingId ?: null, $sludgeQty, (int) $salesOrder->id, $movementId ?: null)) {
                return response($error, 422);
            }
        }

        $createdBy = auth()->check() ? auth()->user()->username : (string) ($salesOrder->created_by ?? '');

        DB::transaction(function () use ($salesOrder, $oldSoNo, $newSoNo, $items, $headerPayload, $createdBy) {
            SalesOrderCNSDtl::where('so_no', $oldSoNo)->delete();
            foreach ($items as $item) {
                SalesOrderCNSDtl::create(array_merge($item, [
                    'so_no'      => $newSoNo,
                    'created_by' => $createdBy,
                ]));
            }

            $salesOrder->update($headerPayload);
        });

        return response('success');
    }

    public function checkStock(Request $request)
    {
        $siteId = $request->query('site_id');
        $siteName = $request->query('site_name');
        $packagingId = $request->query('packaging_id');
        $desc = trim((string) $request->query('description', ''));

        if (!$siteId && $siteName) {
            $siteId = $this->resolveSiteIdByName($siteName);
        }

        if (!$packagingId && $desc !== '') {
            $kemasan = $this->findKemasanByDescription($desc);
            if ($kemasan) {
                $packagingId = $kemasan->Kemasan_ID;
            }
        }

        $service = app(\App\Services\CNS\KemasanInventoryService::class);
        $stock = $service->getAvailableSaleBalance($desc, $packagingId ? (int) $packagingId : null, $siteId ? (int) $siteId : null);

        return response()->json([
            'status' => 'success',
            'stock'  => (float) $stock,
        ]);
    }

    public function approveSalesOrder(Request $request, $id)
    {
        $so = SalesOrderCNS::findOrFail($id);

        if (strtoupper((string) $so->status) === 'APPROVED') {
            return response()->json(['status' => 'error', 'message' => 'Sales Order ini sudah di-approve sebelumnya.'], 400);
        }

        if (strtoupper((string) $so->status) === 'REJECTED') {
            return response()->json(['status' => 'error', 'message' => 'Sales Order yang sudah di-reject tidak dapat di-approve.'], 400);
        }

        $details = SalesOrderCNSDtl::where('so_no', $so->so_no)->get();
        if ($details->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Detail Sales Order kosong.'], 400);
        }

        $isSludgeOrder = strtoupper((string) ($so->so_type ?? '')) === 'SLUDGE';

        if ($isSludgeOrder) {
            $productRef = (string) ($details->first()->product_code ?? '');
            $movementId = !empty($so->sludge_movement_id)
                ? (int) $so->sludge_movement_id
                : (strpos($productRef, 'M:') === 0 ? (int) substr($productRef, 2) : null);
            $drainingId = !empty($so->draining_id) ? (int) $so->draining_id : (is_numeric($productRef) ? (int) $productRef : null);
            $sludgeQty = (float) $details->sum('qty');
            if ($error = $this->validateSludgeOrder($drainingId ?: null, $sludgeQty, (int) $so->id, $movementId ?: null)) {
                return response()->json(['status' => 'error', 'message' => $error], 422);
            }

            $actor = auth()->check() ? auth()->user()->username : 'admin';
            $this->markSalesOrderApproved($so, $actor);

            return response()->json(['status' => 'success', 'message' => 'Sales Order Sludge berhasil di-approve.']);
        }

        $inventoryService = app(\App\Services\CNS\KemasanInventoryService::class);
        $siteId = $so->site_id ? (int) $so->site_id : $this->resolveSiteIdByName($so->site);
        $siteName = (string) ($so->site ?: ($siteId ? ($inventoryService::getSites()[$siteId] ?? '') : ''));

        foreach ($details as $dtl) {
            $qty = (float) $dtl->qty;
            if ($qty <= 0) {
                continue;
            }

            $packagingId = !empty($dtl->product_code) ? (int) $dtl->product_code : null;
            if (!$packagingId) {
                $kemasan = $this->findKemasanByDescription($dtl->description);
                $packagingId = $kemasan ? (int) $kemasan->Kemasan_ID : null;
            }

            $availableStock = $inventoryService->getAvailableSaleBalance($dtl->description, $packagingId, $siteId);
            if ($qty > $availableStock) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Stok kemasan '{$dtl->description}' tidak mencukupi di site {$siteName}. Tersedia: {$availableStock}, Diminta: {$qty}",
                ], 422);
            }
        }

        $actor = auth()->check() ? auth()->user()->username : 'admin';

        DB::transaction(function () use ($so, $details, $inventoryService, $siteId, $siteName, $actor) {
            foreach ($details as $dtl) {
                $qty = (float) $dtl->qty;
                if ($qty <= 0) {
                    continue;
                }

                $packagingId = !empty($dtl->product_code) ? (int) $dtl->product_code : null;
                $kemasan = $packagingId ? CnsMasterKemasan::find($packagingId) : $this->findKemasanByDescription($dtl->description);
                if (!$packagingId && $kemasan) {
                    $packagingId = (int) $kemasan->Kemasan_ID;
                }

                $unit = $dtl->unit ?: ($kemasan ? $kemasan->Unit : 'Pcs');

                $inventoryService->recordStockMutation(
                    $dtl->description,
                    $unit ?: 'Pcs',
                    'OUT',
                    $qty,
                    $so->so_no,
                    'Sales Order Approval',
                    $actor,
                    $packagingId,
                    null,
                    date('Y-m-d'),
                    'PENJUALAN',
                    $siteId,
                    $siteName
                );
            }

            $this->markSalesOrderApproved($so, $actor);
        });

        return response()->json(['status' => 'success', 'message' => 'Sales Order berhasil di-approve dan stok telah dipotong.']);
    }

    protected function markSalesOrderApproved(SalesOrderCNS $so, string $actor): void
    {
        $updateData = [];
        try {
            if (Schema::hasColumn('sales_order_cns', 'status')) {
                $updateData['status'] = 'APPROVED';
            }
            if (Schema::hasColumn('sales_order_cns', 'approved_by')) {
                $updateData['approved_by'] = $actor;
            }
            if (Schema::hasColumn('sales_order_cns', 'approved_date')) {
                $updateData['approved_date'] = now();
            }
        } catch (\Throwable $e) {
            $updateData = [
                'status' => 'APPROVED',
                'approved_by' => $actor,
                'approved_date' => now(),
            ];
        }
        if (!empty($updateData)) {
            $so->update($updateData);
        }
    }

    public function rejectSalesOrder(Request $request, $id)
    {
        $so = SalesOrderCNS::findOrFail($id);

        if (strtoupper((string) $so->status) === 'APPROVED') {
            return response()->json(['status' => 'error', 'message' => 'Sales Order yang sudah di-approve tidak dapat di-reject.'], 400);
        }

        $actor = auth()->check() ? auth()->user()->username : 'admin';
        $reason = $request->input('reason', '');

        $updateData = [];
        try {
            if (Schema::hasColumn('sales_order_cns', 'status')) {
                $updateData['status'] = 'REJECTED';
            }
            if (Schema::hasColumn('sales_order_cns', 'rejected_by')) {
                $updateData['rejected_by'] = $actor;
            }
            if (Schema::hasColumn('sales_order_cns', 'rejected_date')) {
                $updateData['rejected_date'] = now();
            }
            if (Schema::hasColumn('sales_order_cns', 'rejection_reason')) {
                $updateData['rejection_reason'] = $reason;
            }
        } catch (\Throwable $e) {
            $updateData = [
                'status' => 'REJECTED',
                'rejected_by' => $actor,
                'rejected_date' => now(),
                'rejection_reason' => $reason,
            ];
        }

        if (!empty($updateData)) {
            $so->update($updateData);
        }

        return response()->json(['status' => 'success', 'message' => 'Sales Order berhasil di-reject.']);
    }

    protected function resolveSiteIdByName(?string $siteName): ?int
    {
        if (!$siteName) {
            return null;
        }
        $sites = \App\Services\CNS\KemasanInventoryService::getSites();
        foreach ($sites as $id => $name) {
            if (strcasecmp((string) $name, (string) $siteName) === 0) {
                return (int) $id;
            }
        }
        return null;
    }

    protected function findKemasanByDescription(string $description): ?CnsMasterKemasan
    {
        $desc = trim($description);
        if ($desc === '') {
            return null;
        }

        try {
            $item = CnsMasterKemasan::where('Name', $desc)->first();
            if ($item) {
                return $item;
            }

            $parts = explode(' ', $desc);
            if (count($parts) > 1) {
                array_pop($parts);
                $candidateName = implode(' ', $parts);
                $item = CnsMasterKemasan::where('Name', $candidateName)->first();
                if ($item) {
                    return $item;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public function printSalesOrder(Request $request)
    {
        $salesOrder = SalesOrderCNS::where('id', $request->task)->first();
        $details = SalesOrderCNSDtl::where('so_no', $salesOrder->so_no)->get();

        return view('cns.sales.print.input-order', compact('salesOrder', 'details'));
    }
}
