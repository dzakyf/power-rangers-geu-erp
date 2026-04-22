<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PackingListController extends Controller
{
    public function indexPackingList()
    {
        return view('cns.export.pl');
    }

    public function indexPackingListXml()
    {
        //
    }

    public function newPackingList()
    {
        return view('cns.export.pl-new');
    }
}
