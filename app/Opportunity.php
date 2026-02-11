<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    protected $primaryKey = "ID";
    protected $table = "opportunity";
    protected $guarded = [];
    public $timestamps = false;
}
