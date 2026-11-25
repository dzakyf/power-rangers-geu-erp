<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CnsProductAllocation extends Model
{
    protected $table = 'cns_product_allocations';

    protected $guarded = ['id'];

    protected $casts = [
        'contract_id'        => 'integer',
        'planned_mt'         => 'float',
        'planned_containers' => 'integer',
    ];

    public function contract()
    {
        return $this->belongsTo(BuyerContract::class, 'contract_id', 'ID');
    }
}
