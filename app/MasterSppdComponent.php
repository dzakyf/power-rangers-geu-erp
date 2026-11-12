<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterSppdComponent extends Model
{
    protected $table = 'master_sppd_components';

    protected $fillable = [
        'code',
        'name',
        'category',
        'type',
        'description',
        'is_editable',
    ];

    protected $casts = [
        'is_editable' => 'boolean',
    ];
}
