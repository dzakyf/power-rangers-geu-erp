<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketResponseLog extends Model
{
    protected $table = "ticket_response_log";
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];
    
    public $timestamps = false;
}
