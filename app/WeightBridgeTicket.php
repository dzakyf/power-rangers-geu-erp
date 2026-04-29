<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeightBridgeTicket extends Model
{
    protected $table = "weight_bridge_ticket";
    protected $guarded = ['id'];
    public $timestamps = false;
}
