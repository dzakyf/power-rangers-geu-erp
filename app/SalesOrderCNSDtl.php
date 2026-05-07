<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesOrderCNSDtl extends Model
{
    protected $table = 'sales_order_cns_dtl';
    protected $guarded = ['id'];
    public $timestamps = false;
}
