<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsInventoryKemasan extends Model
{
    protected $table = 'cns_inventory_kemasan';

    protected $fillable = [
        'transaction_date',
        'site_id',
        'site_name',
        'packaging_id',
        'item_code',
        'item_name',
        'unit',
        'reference_no',
        'type',
        'out_type',
        'qty_in',
        'qty_out',
        'current_balance',
        'qty_balance',
        'excess_balance',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'site_id'          => 'integer',
        'qty_in'           => 'float',
        'qty_out'          => 'float',
        'current_balance'  => 'float',
        'qty_balance'      => 'float',
        'excess_balance'   => 'float',
    ];

    public function packaging()
    {
        return $this->belongsTo(CnsMasterKemasan::class, 'packaging_id', 'Kemasan_ID');
    }
}
