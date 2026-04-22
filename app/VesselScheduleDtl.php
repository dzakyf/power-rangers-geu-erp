<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VesselScheduleDtl extends Model
{
    protected $table = 'vessel_schedule_dtls';
    protected $primaryKey = 'ID';
    public $timestamps = false;
}
