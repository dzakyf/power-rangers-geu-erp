<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterCompanyController extends Controller
{
    public function indexMasterCompany()
    {
        return view('cns.master.company');
    }
}
