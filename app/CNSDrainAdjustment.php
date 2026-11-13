<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CNSDrainAdjustment extends Model
{
    use SoftDeletes;

    protected $table = 'cns_drain_adjustments';

    protected $fillable = [
        'adjustment_code',
        'adjustment_date',
        'tank_id',
        'tank_name',
        'tank_capacity',
        'qty_sistem',
        'qty_timbang',
        'selisih_qty',
        'notes',
        'document_path',
        'document_name',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'tank_capacity'   => 'float',
        'qty_sistem'      => 'float',
        'qty_timbang'     => 'float',
        'selisih_qty'     => 'float',
    ];

    protected $dates = [
        'approved_at',
        'deleted_at',
    ];

    /**
     * Source tank relationship.
     */
    public function tank()
    {
        return $this->belongsTo(MasterTank::class, 'tank_id');
    }

    /**
     * Scope for pending records.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'PENDING');
    }

    /**
     * Scope for approved records.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'APPROVED');
    }
}
