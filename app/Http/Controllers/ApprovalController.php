<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalController extends Controller {
    protected $generatorService;
    protected $userService;

    public function __construct(GeneratorService $g, UserService $s)
    {
        $this->generatorService = $g;
        $this->userService = $s;
    }

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

    public function generateMOU(){
        $path = public_path('ksodoc.docx');
        
        if (!file_exists($path)) {
            abort(404, 'Template not found');
        }
        
        return response()->download($path, 'ksodoc.docx');
    }

    public function generateInvoice(){}

    public function submit(Request $r){
        $body = $r->all();

        $id = DB::table('approval_request')->insertGetId([
            'Status' => $body['type'],
            'Requester_Id' => $body['id'],
            'Description' => "Initial Review",
            'Request_Type' => "ONBOARDING",
        ]);

        $lastRow = DB::table('approval_request')->select('*')->where('ID', '=', $id)->first();

        DB::table('approval_log')->insert([
            'Approver_Id' => $this->userService->getCurrentSessionUserInfo()->id,
            'Notes' => "",
            'Action' => $body['type'],
            'Request_Id' => $id,
            'Level_Number' => $lastRow->Current_Level,
            'Next_Level' => (int)$lastRow->Current_Level + 1
        ]);

        return "submitted";
    }

    public function showProgress(Request $request){
        $params = $request->route()->parameters();


        $partner = DB::table('opportunity_partner')->where('id', $params['id'])->first();

        $pic = [];
        if (Schema::hasTable('users')) {
            $pic = DB::table('users')->select('username', DB::raw("COALESCE(name, username) as Name"))->get();
        }

        $documents = DB::table('opportunity_partner_file')->where('opportunity_id', $params['id'])->get();


        foreach ($documents as $key => $value) {
            $documents[$key]->url = "https://dev.hexamatika.com/geu/hcga/public/filevendor/".$value->file_path;
        };

        return view('approval.progress', compact('partner', 'pic', 'documents'));

    }
} 