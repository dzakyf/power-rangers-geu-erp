<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = "ticket";
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];
    
    public $timestamps = false;
}
