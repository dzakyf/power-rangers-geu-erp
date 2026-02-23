<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpportunityLog extends Model
{
    protected $table = 'opportunity_log';
    protected $guarded = ['ID'];
    protected $primaryKey = 'ID';

    public $timestamps = false;
}
