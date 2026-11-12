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
            echo "</row>";
        }

        echo "</rows>";
    }

    protected function getKemasanOptions()
    {
        return CnsMasterKemasan::orderBy('Name', 'asc')->get(['Kemasan_ID', 'Name', 'Volume', 'Is_Ampas', 'Unit', 'Price']);
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

        $hasUnitCol = $hasUnitCol ?? Schema::hasColumn('sales_order_cns_dtl', 'unit');
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

        return [
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
    }

    public function newSalesOrder()
    {
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $counter = SalesOrderCNS::where("so_date", 'LIKE', date("Y-m")."%")->count() + 1;
        $socode = sprintf("%03d", $counter) . "/SO/" . $romawi[(int)date('n') - 1] . "/" . date("Y");
        $kemasans = $this->getKemasanOptions();
        return view('cns.sales.input-order-new', compact('socode', 'kemasans'));
    }

    public function newSalesOrderSubmit(Request $request)
    {
        $strtime = strtotime($request->soDate);
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $counter = SalesOrderCNS::where("so_date", 'LIKE', date("Y-m", $strtime)."%")->count() + 1;
        $socode = sprintf("%03d", $counter) . "/SO/" . $romawi[(int)date('n', $strtime) - 1] . "/" . date("Y", $strtime);

        [$items, $total] = $this->parseSubmittedItems($request);
        $headerPayload = $this->buildHeaderPayload($request, $socode, $total);
        $headerPayload['created_by'] = auth()->check() ? auth()->user()->username : '';

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

    public function printSalesOrder(Request $request)
    {
        $salesOrder = SalesOrderCNS::where('id', $request->task)->first();
        $details = SalesOrderCNSDtl::where('so_no', $salesOrder->so_no)->get();

        return view('cns.sales.print.input-order', compact('salesOrder', 'details'));
    }
}
