<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterSatisfication extends Model
{
    protected $table = "master_satisfication";
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    public $timestamps = false;
}
