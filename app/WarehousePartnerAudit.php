<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehousePartnerAudit extends Model
{
    protected $table = "warehouse_partner_audit";
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
