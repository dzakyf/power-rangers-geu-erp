<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehousePartnerAuditDtl extends Model
{
    protected $table = "warehouse_partner_audit_dtl";
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
