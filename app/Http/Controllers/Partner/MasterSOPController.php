<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterAuditCategory;
use App\User;
use Illuminate\Http\Request;

class MasterSOPController extends Controller
{
    public function indexSOP()
    {
        return view('partner.master-sop');
    }
}
