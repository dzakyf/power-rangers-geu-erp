<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class UserService {
    
    public static function getUserOfficeLocation(){
        return Auth::user()->Location;
    }

    public function getCurrentSessionUser(){
        return Auth::user()->username;
    }
}