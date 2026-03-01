<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    protected $userService;


    public function __construct(UserService $u)
    {
        $this->userService = $u;
    }

    public function getAll()
    {
        $user = $this->userService->getCurrentSessionUserInfo();

        $data = DB::table('notification')->select('*')->where('Recipient_Id', '=', $user->id)->orWhereRaw('Recipient_Id IS NULL')->whereRaw('Expires_At BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)')->orderBy('ID', 'desc')->limit(8)->get();

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function getById()
    {

        return response()->json(['data' => []], 200);
    }


    public function markAsRead(Request $r)
    {
        $q = $r->query();
        $id = $q['id'];

        $lastRow = DB::table('notification')->where('ID', '=', $id)->update(['Status' => 'READ', 'Read_At' => Carbon::now()]);

        return response()->json([
            'message' => "success. $lastRow marked notification as read."
        ], 200);
    }
}
