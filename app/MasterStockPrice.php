<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterStockPrice extends Model
{
    protected $table = "master_stock_price";
    protected $guarded = ['id'];
    public $timestamps = false;
}
