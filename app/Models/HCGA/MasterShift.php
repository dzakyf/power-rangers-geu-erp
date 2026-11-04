<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class MasterShift extends Model
{
    protected $table = 'master_shifts';

    protected $guarded = ['id'];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'break_minutes'   => 'integer',
        'working_minutes' => 'integer',
        'is_cross_day'    => 'boolean',
        'is_off'          => 'boolean',
    ];
}

