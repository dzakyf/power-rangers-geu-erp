<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShipmentInstruction extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
