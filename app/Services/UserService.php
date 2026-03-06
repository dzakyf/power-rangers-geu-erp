<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserService
{

    public static function getUserOfficeLocation()
    {
        return Auth::user()->Location;
    }

    public function getCurrentSessionUser()
    {
        return Auth::user()->username;
    }

    public function getCurrentSessionUserInfo()
    {
        $username = $this->getCurrentSessionUser();

        $user = DB::table('users')->select('*')->where('username', '=', $username)->first();

        return $user;
    }

    public function getActiveMitra()
    {
        $partner = DB::table('users')->select('*')->where('Position', '=', 'MITRA')->where('Status', '=', 'ACTIVE')->get();

        return $partner;
    }
}
