<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UCOReportController extends Controller
{
    public function indexInboundSource()
    {
        return view('uco.report-inbound-source');
    }
}
