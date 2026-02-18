<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApprovalController extends Controller {
    public function index(){
        return view('approval.index');
    }   

    public function loadXml(){}

    public function loadDetailXml(){}

    public function submit(){
        return "submitted";
    }
}