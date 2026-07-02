<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseStockOpname extends Model
{
    protected $table = 'warehouse_stock_opnames';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'Created_At';
    const UPDATED_AT = 'Updated_At';

    protected $fillable = [
        'Opname_Number',
        'Warehouse_ID',
        'Opname_Date',
        'System_Qty',
        'Physical_Qty',
        'Difference',
        'Status',
        'Notes',
        'Counted_By',
        'Approved_By',
        'Container_ID',
    ];

    protected $casts = [
        'System_Qty'   => 'decimal:2',
        'Physical_Qty' => 'decimal:2',
        'Difference'   => 'decimal:2',
        'Opname_Date'  => 'date',
    ];

    public function adjustments()
    {
        return $this->hasMany(WarehouseStockAdjustment::class, 'Opname_ID', 'ID');
    }
}
