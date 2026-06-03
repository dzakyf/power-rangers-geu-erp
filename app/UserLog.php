<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $guarded = ['id']; // Allow mass assignment for all fields except 'id'
    public $timestamps = false;
}
