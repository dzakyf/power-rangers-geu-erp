<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterSppdAllowance extends Model
{
    protected $table = 'master_sppd_allowances';

    protected $fillable = [
        'trip_type_key',
        'job_level_key',
        'transport_land',
        'transport_air',
        'ticket_max',
        'reimburse_max_per_night',
        'hotel_star',
        'daily_meal',
        'daily_pocket',
        'kos_per_month',
        'assistance_note',
        'relocation_one_time_payment',
        'relocation_note',
        'mobilization_cost_per_week',
        'mobilization_note',
    ];

    protected $casts = [
        'transport_land' => 'array',
        'ticket_max' => 'float',
        'reimburse_max_per_night' => 'float',
        'hotel_star' => 'integer',
        'daily_meal' => 'float',
        'daily_pocket' => 'float',
        'kos_per_month' => 'float',
        'relocation_one_time_payment' => 'float',
        'mobilization_cost_per_week' => 'float',
    ];
}
