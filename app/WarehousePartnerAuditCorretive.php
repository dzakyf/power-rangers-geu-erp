<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehousePartnerAuditCorretive extends Model
{
    protected $table = "warehouse_partner_audit_corrective";
    protected $guarded = ['id'];
    public $timestamps = false;
}
