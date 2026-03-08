<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseStockRequest extends Model
{
    protected $table = "warehouse_partner_stock_request";
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
