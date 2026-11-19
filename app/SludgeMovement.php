<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SludgeMovement extends Model
{
    use SoftDeletes;

    protected $table = 'cns_sludge_movements';

    protected $fillable = [
        'movement_code',
        'movement_date',
        'draining_id',
        'source_tank_id',
        'source_tank_name',
        'target_warehouse_id',
        'target_warehouse_name',
        'qty',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'qty'           => 'float',
    ];

    public function draining()
    {
        return $this->belongsTo(TankDraining::class, 'draining_id');
    }
}
