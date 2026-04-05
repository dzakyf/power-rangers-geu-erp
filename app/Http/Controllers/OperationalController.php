<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\NotificationService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationalController extends Controller
{
    protected $generatorService;
    protected $notificationService;
    protected $userService;

    public function __construct(GeneratorService $g, NotificationService $n, UserService $u)
    {
        $this->generatorService = $g;
        $this->notificationService = $n;
        $this->userService = $u;
    }
    public function index()
    {
        return view('panduan.index');
    }

    public function create()
    {
        return view('panduan.create');
    }

    public function detail(Request $r)
    {
        $id = $r->query('ID');

        $guide = DB::table('guide as g')->select('g.*')->where('g.ID', '=', $id)->get();
        $files = DB::table('meta_files as mf')->select('*')->where('mf.Map_To', '=', $id)->get();

        foreach ($files as $key => $value) {
            $files[$key]->Url = "https://dev.hexamatika.com/geu/hcga/public/broadcast/" . $value->Path;
        };

        $guide[0]->Files = $files;

        return view('panduan.detail', ['data' => $guide]);
    }

    public function submit(Request $r)
    {
        $user = $this->userService->getCurrentSessionUserInfo();
        $body = $r->all();

        $titleMapper = [
            1 => "Akses CCTV",
            2 => "ERP",
            3 => "Bagi Hasil",
            4 => "Renewal, Exit, dan Transfer Kemitraan"
        ];

        $title = $titleMapper[$body['Title']];


        $panduanId = DB::table('guide')->insertGetId([
            'Title' => $title,
            'Short_Desc' => $body['Short_Desc'],
            'Version' => $body['Version'],
            'Effective_Date' => $body['Effective_Date'],
            'Changelog' => $body['Changelog']
        ]);

        $doc = $r->file('Doc');
        $path = $this->generatorService->generateUUID();
        $ext = $r->file('Doc')->getClientOriginalExtension();

        $filename = "$path.$ext";

        $doc->move(public_path('guide'), $filename);

        $fileId = DB::table('meta_files')->insertGetId([
            'Filename' => $path,
            'Path' => $filename,
            'Extension' => $ext,
            'Description' => 'Saving Panduan file',
            'Topic' => "guide",
            'Map_To' => $panduanId
        ]);

        $notif = $this->notificationService->makeNotification(
            "Rilis panduan: $title, versi: {$body['Version']}",
            "Administrator merilis: {$body['Short_Desc']}",
            'guide',
            $user->id,
            null,
            'SENT',
            json_encode(['Id' => $panduanId]),
            null
        );

        return view('panduan.create');
    }

    public function loadXML()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $guide = DB::table('guide')->get();
        foreach ($guide as $key => $guide) {
            echo "<row id=\"$guide->ID\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($guide->Title) . "</cell>";
            echo "<cell>" . htmlspecialchars($guide->Short_Desc) . "</cell>";
            echo "<cell>" . htmlspecialchars($guide->Effective_Date) . "</cell>";
            echo "<cell>" . htmlspecialchars($guide->Version) . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }
}
