<?php

namespace App\Http\Controllers\Partner;

use App\Chat;
use App\Http\Controllers\Controller;
use App\RoomChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatListController extends Controller
{
    public function chatList(Request $request)
    {
        $userId = Auth::id();
        if (!auth()->user()->Admin_Chat) {
            abort(403, 'Unauthorized access');
        }
        $getUser = DB::table('users')->get()->pluck('Name', 'username');
        $roomChat = RoomChat::where("Users", "LIKE", "%ADMIN_USER%")->get();

        return view('partner.chat', compact('roomChat', 'getUser'));
    }

    public function getMessages($conversation)
    {
        $roomChat = RoomChat::where("ID", $conversation)->first();
        $messages = Chat::where('room_chat_id', $roomChat->ID)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required',
            'message' => 'required'
        ]);

        $message = Chat::create([
            'room_chat_id' => $request->conversation_id,
            'created_by' => Auth::user()->username,
            'message' => $request->message,
            'is_sent' => true,
            'sent_date' => now(),
        ]);

        return response()->json($message);
    }

    public function fetchNewMessages(Request $request)
    {
        $conversationId = $request->conversation_id;
        $lastId = $request->last_id;

        $messages = Chat::where('room_chat_id', $conversationId)
            ->where('id', '>', $lastId)
            ->orderBy('id')
            ->get();

        return response()->json($messages);
    }
    public function loadOlder(Request $request)
    {
        return Chat::where('room_chat_id', $request->conversation_id)
            ->where('id', '<', $request->first_id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();
    }

    public function markRead(Request $request)
    {
        Chat::where('room_chat_id', $request->conversation_id)
            ->where('is_read', false)
            ->where('created_by', '!=', auth()->user()->username)
            ->update([
                'is_read' => true,
                'read_date' => now(),
                ]);

        return response()->json(['success' => true]);
    }

    public function checkRead(Request $request)
    {
        return Chat::where('room_chat_id', $request->conversation_id)
            ->where('created_by', Auth::user()->username) // pesan milik saya
            ->where('is_read', 1)
            // ->whereNull('read_synced') // optional jika mau lebih optimal
            ->select('id')
            ->get();
    }
}
