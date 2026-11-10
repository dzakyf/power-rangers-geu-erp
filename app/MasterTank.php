<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterTank extends Model
{
    use SoftDeletes;

    protected $table = 'cns_master_tanks';

    protected $fillable = [
        'name',
        'capacity',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'capacity' => 'float',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * Scope only active tanks.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Audit logs relationship.
     */
    public function logs()
    {
        return $this->hasMany(MasterTankLog::class, 'tank_id')->orderBy('id', 'desc');
    }
}
