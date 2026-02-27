<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketResponse extends Model
{
    protected $table = "ticket_response";
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];
    
    public $timestamps = false;
}
