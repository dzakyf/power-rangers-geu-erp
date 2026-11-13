<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementController extends Controller
{
    public function createDetail(Request $request) {
    $rfqNo = $request->query('rfq');

    // Ambil data header RFQ dan Supplier
    $rfq = DB::table('rfq as r')
            ->leftJoin('supplier as s', 'r.Supplier_ID', '=', 's.Supplier_ID')
            ->where('r.RFQ_No', $rfqNo)
            ->first();

    if (!$rfq) {
        return redirect()->back()->with('error', 'Data RFQ tidak ditemukan.');
    }

    // Ambil rincian item dari tabel purchase_request_dtl berdasarkan PR_No
    $pr_items = DB::table('purchase_request_dtl')
                ->where('PR_No', $rfq->PR_No)
                ->get();

    // TAMBAHKAN FOLDER 'procurement.' DI DEPAN NAMA VIEW
    return view('procurement.qtn-create-detail', compact('rfq', 'pr_items'));
}
}