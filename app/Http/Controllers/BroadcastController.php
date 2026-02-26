<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class BroadcastController extends Controller
{
    public function index()
    {
        return view('broadcast.index');
    }

    public function create()
    {
        return view('broadcast.create');
    }

    public function detail()
    {
        return view('broadcast.detail');
    }

    public function submit()
    {
        return view('broadcast.index');
    }
}
