<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterWarehouseAccount extends Model
{
    protected $table = 'master_warehouse_account';
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
