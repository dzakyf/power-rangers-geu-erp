<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpportunitySalesDtl extends Model
{
    protected $table = 'opportunity_sales_dtl';
    protected $guarded = ['ID'];
    protected $primaryKey = 'ID';

    public $timestamps = false;
}
