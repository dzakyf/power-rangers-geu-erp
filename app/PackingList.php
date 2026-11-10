<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackingList extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
