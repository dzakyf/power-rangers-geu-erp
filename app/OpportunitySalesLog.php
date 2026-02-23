<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpportunitySalesLog extends Model
{
    protected $table = 'opportunity_sales_log';
    protected $guarded = ['ID'];
    protected $primaryKey = 'ID';

    public $timestamps = false;
}
