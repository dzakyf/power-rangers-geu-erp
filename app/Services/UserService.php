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

    public function getMitraById($id)
    {
        $partner = DB::table('users')->select('*')
            // ->where('Position', '=', 'MITRA')
            ->where('id', '=', $id)->where('Status', '=', 'ACTIVE')->first();

        return $partner;
    }

    public function getActiveMitra()
    {
        $partner = DB::table('users as usr')->select('usr.*', 'op.id as Opportunity_Id', 'op.lead_id')
            ->leftJoin('opportunity_partner as op', 'op.user_row_id', '=', 'usr.id')
            ->where('usr.Position', '=', 'MITRA')->where('usr.Status', '=', 'ACTIVE')->get();

        return $partner;
    }

    public function getSales()
    {
        $sales = DB::table('users as usr')->select('usr.*')->where('usr.Status', '=', 'ACTIVE')->where('usr.Position', '=', 'SALES')->get();

        return $sales;
    }
}
