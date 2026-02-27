<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GeneratorService
{
    public function generateTaskID()
    {
        $now = Carbon::now()->format('YmdHisv');
        $location = UserService::getUserOfficeLocation();

        return 'BP/' . $location . '/' . $now . '/' . '212';
    }

    public function generateFilename()
    {
        return Str::uuid()->toString();
    }
}
