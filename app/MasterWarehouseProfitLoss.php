<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterWarehouseProfitLoss extends Model
{
    protected $table = 'master_warehouse_profit_loss';
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
