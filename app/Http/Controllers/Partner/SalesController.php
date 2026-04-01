<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function tieringSales()
    {
        return view('partner.sales.tiering');
    }
    public function teamPerformanceSales()
    {
        return view('partner.sales.team-performance');
    }
}
