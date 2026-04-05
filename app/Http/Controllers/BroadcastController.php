<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class BroadcastController extends Controller
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
        return view('broadcast.index');
    }

    public function indexXML()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $partners = DB::table('broadcast')->get();
        foreach ($partners as $key => $partner) {
            echo "<row id=\"$partner->ID\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($partner->Title) . "</cell>";
            echo "<cell>" . htmlspecialchars($partner->Type) . "</cell>";
            echo "<cell>" . htmlspecialchars($partner->Short_Desc) . "</cell>";
            echo "<cell>" . htmlspecialchars($partner->Long_Desc) . "</cell>";
            echo "<cell>" . htmlspecialchars($partner->Effective_Date) . "</cell>";
            echo "<cell>" . htmlspecialchars($partner->Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($partner->Created_At) . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    public function create()
    {
        return view('broadcast.create');
    }

    public function detail(Request $r)
    {
        $id = $r->query('ID');

        $broadcast = DB::table('broadcast as b')->select('b.*')->where('b.ID', '=', $id)->get();
        $files = DB::table('meta_files as mf')->select('*')->where('mf.Map_To', '=', $id)->get();

        foreach ($files as $key => $value) {
            $files[$key]->Url = "https://dev.hexamatika.com/geu/hcga/public/broadcast/" . $value->Path;
        };

        $broadcast[0]->Files = $files;

        return view('broadcast.detail', ['data' => $broadcast]);
    }

    public function submit(Request $r)
    {
        $body = $r->all();
        $doc = $r->file('Doc');
        $path = $this->generatorService->generateUUID();
        $ext = $r->file('Doc')->getClientOriginalExtension();
        $user =  $this->userService->getCurrentSessionUserInfo();

        $filename = "$path.$ext";

        $doc->move(public_path('broadcast'), $filename);

        $bcastId = DB::table('broadcast')->insertGetId([
            'Title' => $body['Title'],
            'Short_Desc' => $body['Short_Desc'],
            'Long_Desc' => $body['Long_Desc'],
            'Type' => $body['Type'],
            'Effective_Date' => $body['Effective_Date'],
            'Status' => $body['Status']
        ]);

        $savePath = DB::table('meta_files')->insert([
            'Filename' => $path,
            'Path' => $filename,
            'Extension' => $ext,
            'Description' => 'Saving broadcast file',
            'Topic' => "broadcast",
            'Map_To' => $bcastId
        ]);

        $notif = $this->notificationService->makeNotification(
            $body['Short_Desc'],
            $body['Long_Desc'],
            'broadcast',
            $user->id,
            $bcastId
        );

        return view('broadcast.index');
    }
}
