<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcement';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $guarded = ['ID'];
}
