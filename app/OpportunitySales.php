<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpportunitySales extends Model
{
    protected $table = 'opportunity_sales';
    protected $guarded = ['ID'];
    protected $primaryKey = 'ID';

    public $timestamps = false;
}
