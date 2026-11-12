<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsKemasanSiteLimit extends Model
{
    protected $table = 'cns_kemasan_site_limits';

    protected $fillable = [
        'kemasan_id',
        'site_id',
        'max_stock',
    ];

    protected $casts = [
        'kemasan_id' => 'integer',
        'site_id'    => 'integer',
        'max_stock'  => 'float',
    ];
}
