<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TankDraining extends Model
{
    use SoftDeletes;

    protected $table = 'cns_tank_drainings';

    protected $fillable = [
        'draining_code',
        'draining_date',
        'tank_id',
        'tank_name',
        'qty',
        'target_type',
        'moving_target_type',
        'moving_target_tank_id',
        'moving_target_tank_name',
        'moving_target_external',
        'sales_order_id',
        'sales_order_no',
        'notes',
        'bast_document_path',
        'bast_document_name',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'draining_date' => 'date',
        'qty'           => 'float',
    ];

    protected $dates = [
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
     * Target moving tank relationship (if moving to another master tank).
     */
    public function movingTargetTank()
    {
        return $this->belongsTo(MasterTank::class, 'moving_target_tank_id');
    }

    /**
     * Sales Order reference (if tujuan = PENJUALAN).
     */
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrderCNS::class, 'sales_order_id');
    }
}
