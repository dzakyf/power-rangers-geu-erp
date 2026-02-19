<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalController extends Controller {
    public function index(){
        return view('approval.index');
    }   

    public function loadXml(){}

    public function detail(Request $request)
    {
        $partner = DB::table('opportunity_partner')->where('id', $request->ID)->first();

        $pic = [];
        if (Schema::hasTable('users')) {
            $pic = DB::table('users')->select('username', DB::raw("COALESCE(name, username) as Name"))->get();
        }

        $documents = DB::table('opportunity_partner_file')->where('opportunity_id', $request->ID)->get();


        foreach ($documents as $key => $value) {
            $documents[$key]->url = "https://dev.hexamatika.com/geu/hcga/public/filevendor/".$value->file_path;
        };

        return view('approval.detail', compact('partner', 'pic', 'documents'));
    }

    public function submit(){
        return "submitted";
    }
}