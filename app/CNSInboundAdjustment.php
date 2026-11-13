<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CNSInboundAdjustment extends Model
{
    use SoftDeletes;

    protected $table = 'cns_inbound_adjustments';

    protected $fillable = [
        'adjustment_code',
        'adjustment_date',
        'movement_id',
        'movement_kode',
        'asal_warehouse',
        'tank_id',
        'tank_name',
        'original_qty',
        'tank_capacity',
        'tank_current_stock',
        'tank_remaining',
        'adjusted_qty',
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
        'adjustment_date'    => 'date',
        'original_qty'       => 'float',
        'tank_capacity'      => 'float',
        'tank_current_stock' => 'float',
        'tank_remaining'     => 'float',
        'adjusted_qty'       => 'float',
        'selisih_qty'        => 'float',
    ];

    protected $dates = [
        'approved_at',
        'deleted_at',
    ];

    /**
     * Target tank relationship.
     */
    public function tank()
    {
        return $this->belongsTo(MasterTank::class, 'tank_id');
    }

    /**
     * Scope for approved records.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'APPROVED');
    }
}
