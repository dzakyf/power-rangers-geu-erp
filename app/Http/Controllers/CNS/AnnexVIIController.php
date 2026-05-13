<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnnexVIIController extends Controller
{
    public function __construct()
    {
        // throw new \Exception('Not implemented');
    }

    public function indexAnnex()
    {
        return view('cns.export.annex-index');
    }

    public function getAnnexData(Request $r)
    {
        return;
    }

    public function submitAnnexData() {}

    public function detailAnnexData() {}

    public function deleteAnnexDatra() {}

    public function printAnnex()
    {
        return view('cns.export.print.annex-print');
    }

    public function printAnnexSample()
    {
        return view('cns.export.print.annex-print-sample');
    }
}
