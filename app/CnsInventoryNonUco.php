<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsInventoryNonUco extends Model
{
    protected $table = 'cns_inventory_non_uco';

    protected $fillable = [
        'transaction_date',
        'category',
        'item_code',
        'item_name',
        'unit',
        'vessel_schedule_id',
        'goods_receipt_id',
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
        'qty_in'           => 'float',
        'qty_out'          => 'float',
        'current_balance'  => 'float',
    ];

    public function vesselSchedule()
    {
        return $this->belongsTo(VesselSchedule::class, 'vessel_schedule_id');
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(CnsGoodsReceipt::class, 'goods_receipt_id');
    }
}
