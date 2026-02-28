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
        $getUser = DB::table('users')->get()->pluck('Name', 'username');
        $roomChat = RoomChat::where("Users", "LIKE", "%ADMIN_USER%")->get();

        return view('partner.chat', compact('roomChat', 'getUser'));
    }

    public function getMessages($conversation)
    {
        $roomChat = RoomChat::where("ID", $conversation)->first();
        $messages = Chat::where('room_chat_id', $roomChat->ID)
            ->limit(20)
            ->orderBy('sent_date','DESC')
            ->get();

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
            'message' => $request->message
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
}
