<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function printInvoice()
    {
        // $inv = DB::table('')->where('','')->first();
        return view('finance.partner.prints.invoice');
    }
}
