<?php

namespace App\Http\Controllers\CNS;

use App\CnsMasterKemasan;
use App\Http\Controllers\Controller;
use App\SalesOrderCNS;
use App\SalesOrderCNSDtl;
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

    protected function parseSubmittedItems(Request $request, ?bool $hasUnitCol = null): array
    {
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

        try {
            if (Schema::hasColumn('sales_order_cns', 'site_id')) {
                $payload['site_id'] = $siteId;
            }
            if (Schema::hasColumn('sales_order_cns', 'status')) {
                $payload['status'] = $request->filled('status') ? (string) $request->status : 'PENDING';
            }
        } catch (\Throwable $e) {
            $payload['site_id'] = $siteId;
            $payload['status'] = $request->filled('status') ? (string) $request->status : 'PENDING';
        }

        return $payload;
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
        return view('cns.sales.input-order-new', compact('socode', 'kemasans'));
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

        return view('cns.sales.input-order-detail', compact('salesOrder', 'details', 'kemasans'));
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
        });

        return response()->json(['status' => 'success', 'message' => 'Sales Order berhasil di-approve dan stok telah dipotong.']);
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
