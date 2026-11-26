<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsSalesForecast extends Model
{
    protected $table = 'cns_sales_forecasts';

    protected $guarded = ['id'];

    protected $casts = [
        'planned_mt'    => 'float',
        'planned_value' => 'float',
    ];
}
