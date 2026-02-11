<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;
use Auth;

class AdminController extends Controller
{
    public function index()
    {
        return view('depan');
    }
    public function __construct()
    {
        $this->middleware('auth');
    }
}
