<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsUpcomingInbound extends Model
{
    protected $table = 'cns_upcoming_inbounds';

    protected $fillable = [
        'inbound_no',
        'supplier_name',
        'origin',
        'eta_date',
        'driver_name',
        'vehicle_plate',
        'estimated_qty',
        'target_tank',
        'product_type',
        'status',
        'notes',
        'api_source',
        'api_payload',
    ];

    protected $casts = [
        'eta_date'      => 'datetime',
        'estimated_qty' => 'float',
        'api_payload'   => 'array',
    ];
}
