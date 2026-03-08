<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehousePartnerSupplies extends Model
{
    protected $table = "warehouse_partner_supplies";
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
