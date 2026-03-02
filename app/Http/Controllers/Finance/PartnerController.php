<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    public function printInvoice(Request $r)
    {
        $q = $r->query();
        $type = $q['type'] ?? null;
        $id = $q['id'];

        $inv = DB::table('opportunity_partner')->select('estimated_revenue')->where('id', '=', $id)->first();

        if ($type == 'downpayment') {
            $percentage = 0.1213;
            $amount = $inv->estimated_revenue - ($inv->estimated_revenue * $percentage);

            $res = [
                'description' => "Pembayaran down payment",
                'value' => $inv->estimated_revenue,
                'percentage' => $percentage,
                'amount' => $amount
            ];
        }

        if ($type == 'termin-1') {
            $percentage = 0.6666;
            $amount = $inv->estimated_revenue - ($inv->estimated_revenue * $percentage);

            $res = [
                'description' => "Pembayaran termin 1 (Facility Procurement)",
                'value' => $inv->estimated_revenue,
                'percentage' => $percentage,
                'amount' => $amount
            ];
        }

        if ($type == 'termin-2') {
            $percentage = 0.2122;
            $amount = $inv->estimated_revenue - ($inv->estimated_revenue * $percentage);

            $res = [
                'description' => "Pembayaran termin 2 (Working Capital)",
                'value' => $inv->estimated_revenue,
                'percentage' => $percentage,
                'amount' => $amount
            ];
        }


        return view('finance.partner.prints.invoice', ['data' => $res]);
    }
}
