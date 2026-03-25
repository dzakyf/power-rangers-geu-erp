<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterMaterial extends Model
{
    protected $table = "master_masterial";
    protected $guarded = ['id'];
    public $timestamps = false;
}
