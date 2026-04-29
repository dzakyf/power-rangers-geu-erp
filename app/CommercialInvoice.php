<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommercialInvoice extends Model
{
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];
    public $timestamps = false;
}
