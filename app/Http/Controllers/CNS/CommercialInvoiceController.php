<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommercialInvoiceController extends Controller
{
    public function indexCommercialInvoice()
    {
        return view('cns.export.ci');
    }

    public function indexCommercialInvoiceXml()
    {
        //
    }

    public function newCommercialInvoice()
    {
        return view('cns.export.ci-new');
    }
}
