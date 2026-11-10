<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsFlexibagInventory extends Model
{
    protected $table = 'cns_flexibag_inventories';

    protected $fillable = [
        'transaction_date',
        'vessel_schedule_id',
        'reference_no',
        'type',
        'qty_in',
        'qty_out',
        'current_balance',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'qty_in' => 'float',
        'qty_out' => 'float',
        'current_balance' => 'float',
    ];

    public function vesselSchedule()
    {
        return $this->belongsTo(VesselSchedule::class, 'vessel_schedule_id');
    }
}
