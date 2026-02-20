<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class GeneratorService { 
    public function generateTaskID(){
        $now = Carbon::now()->format('YmdHisv');
        $location = UserService::getUserOfficeLocation();

        return 'BP/'.$location.'/'.$now.'/'.'212';
    }
}