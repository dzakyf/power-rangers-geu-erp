<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehosuePartner extends Model
{
    protected $table = "warehouse_partner";
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
