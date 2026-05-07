<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesOrderCNS extends Model
{
    protected $table = "sales_order_cns";
    protected $guarded = ['id'];
    public $timestamps = false;
}
