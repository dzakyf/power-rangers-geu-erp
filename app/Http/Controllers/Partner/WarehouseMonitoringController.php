<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WarehouseMonitoringController extends Controller
{
    public function indexMonitoringWarehouse()
    {
        return view('partner.warehouse.monitoring');
    }

    public function indexMonitoringWarehouseXml(Request $request)
    {
        //
    }

    public function detailMonitoringWarehouse(Request $request)
    {
        //
    }

    public function detailMonitoringWarehouseXml(Request $request)
    {
        //
    }
}
