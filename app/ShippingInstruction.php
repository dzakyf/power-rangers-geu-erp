<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingInstruction extends Model
{
    protected $table = 'shipping_instructions';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
