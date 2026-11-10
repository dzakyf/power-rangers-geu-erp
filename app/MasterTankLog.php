<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterTankLog extends Model
{
    protected $table = 'cns_master_tank_logs';
    public $timestamps = false;

    protected $fillable = [
        'tank_id',
        'tank_name',
        'action',
        'details',
        'old_values',
        'new_values',
        'performed_by',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function tank()
    {
        return $this->belongsTo(MasterTank::class, 'tank_id')->withTrashed();
    }
}
