<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseStockAdjustment extends Model
{
    protected $table = 'warehouse_stock_adjustments';
    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'Opname_ID',
        'Warehouse_ID',
        'Adjustment_Type',
        'Qty_Before',
        'Qty_After',
        'Difference',
        'Reason',
    ];

    protected $casts = [
        'Qty_Before'  => 'decimal:2',
        'Qty_After'   => 'decimal:2',
        'Difference'  => 'decimal:2',
    ];

    public function opname()
    {
        return $this->belongsTo(WarehouseStockOpname::class, 'Opname_ID', 'ID');
    }
}
