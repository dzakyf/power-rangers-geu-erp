<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function certificateTraining(Request $request)
    {
        return view('partner.training.certificate');
    }
}
