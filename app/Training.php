<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $table = "training";
    protected $guarded = ['id'];
    public $timestamps = false;
}
