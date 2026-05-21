<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\SalesOrderCNS;
use App\SalesOrderCNSDtl;
use Illuminate\Http\Request;

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

    public function newSalesOrder()
    {
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $counter = SalesOrderCNS::where("so_date", 'LIKE', date("Y-m")."%")->count() + 1;
        $socode = sprintf("%03d", $counter) . "/SO/" . $romawi[(int)date('n') - 1] . "/" . date("Y");
        return view('cns.sales.input-order-new', compact('socode'));
    }

    public function newSalesOrderSubmit(Request $request)
    {
        $strtime = strtotime($request->soDate);
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $counter = SalesOrderCNS::where("so_date", 'LIKE', date("Y-m", $strtime)."%")->count() + 1;
        $socode = sprintf("%03d", $counter) . "/SO/" . $romawi[(int)date('n', $strtime) - 1] . "/" . date("Y", $strtime);
        
        $items = [];
        $total = 0;
        $reqAll = $request->all();
        foreach($reqAll as $key => $value)
        {
            // Cek apakah field item memiliki angka di belakang
            if (preg_match('/^([a-zA-Z_]+)(\d+)$/', $key, $matches)) {

                $field = $matches[1]; // description, qty, unit
                $index = $matches[2]; // 1,2,3,...

                $items[$index][$field] = $value;

            } else {

                // Selain item masuk ke header
                $header[$key] = $value;
            }
        }
        
        foreach ($items as $key => $value) {
            if ($value['description'] != '') {
                SalesOrderCNSDtl::create([
                    'so_no' => $socode,
                    'sort_order' => $key,
                    'description' => $value['description'],
                    'qty' => $value['qty'],
                    'unit_price' => $value['unit'],
                    'total' => $value['qty'] * $value['unit'],
                ]);
    
                $total += $value['qty'] * $value['unit'];
            }
        }


        SalesOrderCNS::create([
            'so_no' => $socode,
            'so_date' => $request->soDate,
            'buyer_pic' => $request->buyerPic,
            'buyer_city' => $request->buyerKota,
            'buyer_province' => $request->buyerProvinsi,
            'buyer_phone' => $request->buyerPhone,
            'site' => $request->site,
            'site_address' => $request->siteAddress,
            'payment_term' => $request->paymentTerm,
            'subtotal' => $total,
            'tax' => $request->taxAmount,
            'shipping' => $request->shippingAmount,
            'other' => $request->otherAmount,
            'total' => $request->totalAmount,
            'remarks' => $request->remarks,
            'created_by' => auth()->user()->username,
        ]);


        echo "success";
    }

    public function detailSalesOrder(Request $request)
    {
        $salesOrder = SalesOrderCNS::where('id', $request->task)->first();
        $details = SalesOrderCNSDtl::where('so_no', $salesOrder->so_no)->get();

        return view('cns.sales.input-order-detail', compact('salesOrder', 'details'));
    }

    public function detailSalesOrderSubmit(Request $request)
    {
        //
    }

    public function printSalesOrder(Request $request)
    {
        $salesOrder = SalesOrderCNS::where('id', $request->task)->first();
        $details = SalesOrderCNSDtl::where('so_no', $salesOrder->so_no)->get();

        return view('cns.sales.print.input-order', compact('salesOrder', 'details'));
    }
}
