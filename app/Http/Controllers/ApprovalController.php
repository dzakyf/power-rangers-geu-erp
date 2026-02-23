<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpWord\TemplateProcessor;

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

    public function generateMOU(Request $req){

        $id = $req->query('id');
        $path = public_path('mou.docx');
        $doc = new TemplateProcessor($path);
        $now = Carbon::now()->format('YmdHisv');

        $data = DB::table('opportunity_partner')->select('*')->where('ID', '=', $id)->first();

        $partner_name = $data->register_as == "individual" ? $data->full_name : $data->company_full_name;
        $full_address = "$data->address, $data->pi_rt, $data->pi_rw, $data->pi_ward, $data->pi_subdistrict, $data->pi_city, $data->pi_province, $data->pi_postal_code";

        $doc->setValues([
            'partner_name' => $partner_name ?? '${contoh}',
            'partner_region' => $data->pi_city ?? '${contoh}',
            'signed_at' => Carbon::now(),
            'nama' => $data->pic ?? '${contoh}',
            'ktp' => '$contoh',
            'alamat' => $full_address,
            'pic_name' => $data->pic,
            'npwp' => $data->npwp,
            'membership_term' => '${contoh}',
            'membership_fee' => '${contoh}',
            'warehouse_fee' => '${contoh}',
            'inventory_fee' => '${contoh}',
            'namapaket' => '${contoh}',

        ]);

        
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        $outputPath = storage_path("app/public/mou_$now.docx");

        $doc->saveAs($outputPath);
        
        return response()->download($outputPath)->deleteFileAfterSend(true);
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

        $status = DB::table('approval_request as ar')
        ->select('ar.*', 'al.*') // Select columns you need
        ->join('approval_log as al', 'ar.ID', '=', 'al.Request_Id') // Adjust foreign key
        ->where('ar.Requester_Id', '=', $params['id'])
        ->where('al.Level_Number', '=', '1')
        ->orderBy('al.Action_Date', 'Desc') // Get first occurrence
        ->first();

        return view('approval.progress', compact('partner', 'pic', 'documents', 'status'));

    }
} 