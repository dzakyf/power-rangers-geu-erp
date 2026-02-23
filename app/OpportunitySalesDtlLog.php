<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpportunitySalesDtlLog extends Model
{
    protected $table = 'opportunity_sales_dtl_log';
    protected $guarded = ['ID'];
    protected $primaryKey = 'ID';

    public $timestamps = false;
}
