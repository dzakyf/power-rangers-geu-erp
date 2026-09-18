<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterSppdTripType extends Model
{
    protected $table = 'master_sppd_trip_types';

    protected $fillable = [
        'type_key',
        'name',
        'duration',
        'note',
    ];
}
