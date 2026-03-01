<?php

namespace App\Http\Controllers\Partner;

use App\Chat;
use App\Http\Controllers\Controller;
use App\RoomChat;
use App\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function partnerAnnouncement()
    {
        return view('partner.announcement');
    }

    public function partnerAnnouncementXml()
    {
        $announcements = Announcement::orderBy('Created_Date', 'DESC')->get();

        $xml = new \SimpleXMLElement('<rows/>');
        $getUsers = DB::table('users')->pluck('Name', 'username');

        foreach ($announcements as $announcement) {
            $item = $xml->addChild('row');
            $item->addAttribute('id', $announcement->ID);
            $item->addChild('cell', $announcement->ID);
            $item->addChild('cell', $announcement->Created_Date);
            $item->addChild('cell', htmlspecialchars($announcement->Title));
            $item->addChild('cell', htmlspecialchars($announcement->Content));
            $item->addChild('cell', htmlspecialchars($announcement->Is_Final ? 'Final' : 'Draft'));
            $item->addChild('cell', $announcement->Created_Date);
            $item->addChild('cell', $getUsers[$announcement->Created_By] ?? $announcement->Created_By);
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function partnerAnnouncementNew()
    {
        return view('partner.announcement-new');
    }

    public function blastMessage(Request $request, Announcement $announcement)
    {
        if (Auth::user()->Admin_Chat) {
            $getRoomChat = RoomChat::where("Users", "LIKE", "%ADMIN_USER%")->get();
            foreach ($getRoomChat as $key => $value) {
                Chat::create([
                    'room_chat_id' => $value->ID,
                    'created_by' => Auth::user()->username,
                    'message' => $request->input('Content'),
                    'is_sent' => true,
                    'sent_date' => now(),
                    'announcement_id' => $announcement->ID,
                ]);
            }
        }
    }

    public function partnerAnnouncementNewSubmit(Request $request)
    {
        $announcement = new Announcement();
        if ($request->input('Status') == 'Draft') {
            $announcement->Is_Final = 0;
        } else {
            $announcement->Is_Final = 1;
            $announcement->Final_Date = now();
        }
        $announcement->Title = $request->input('Title');
        $announcement->Content = $request->input('Content');
        $announcement->Created_Date = now();
        $announcement->Created_By = auth()->user()->username;
        $announcement->save();

        if ($announcement->Is_Final) {
            $this->blastMessage($request, $announcement);
        }

        echo "success";
        // return response()->json(['success' => true, 'message' => 'Announcement created successfully']);
    }

    public function partnerAnnouncementDetail(Request $request)
    {
        $announcement = Announcement::find($request->Task);
        if (!$announcement) {
            return response()->json(['error' => 'Announcement not found'], 404);
        }

        return view('partner.announcement-detail', compact('announcement'));
    }

    public function partnerAnnouncementDetailSubmit(Request $request)
    {
        $announcement = Announcement::find($request->input('ID'));
        if (!$announcement) {
            return response()->json(['error' => 'Announcement not found'], 404);
        }

        if ($request->input('Status') == 'Draft') {
            $announcement->Is_Final = 0;
        } else {
            $announcement->Is_Final = 1;
            $announcement->Final_Date = now();
        }
        $announcement->Title = $request->input('Title');
        $announcement->Content = $request->input('Content');
        $announcement->save();

        if ($announcement->Is_Final) {
            $this->blastMessage($request, $announcement);
        }

        echo "success";
    }
}
