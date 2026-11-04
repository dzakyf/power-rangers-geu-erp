<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendant_log';

    protected $primaryKey = 'ID';

    protected $guarded = [];

    public $timestamps = false;

    const CREATED_AT = 'Created_Date';
}
