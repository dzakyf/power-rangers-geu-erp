<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    protected $table = "ticket_log";
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];
    
    public $timestamps = false;
}
