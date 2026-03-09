<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehousePartnerJournal extends Model
{
    protected $table = "warehouse_partner_journal";
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
