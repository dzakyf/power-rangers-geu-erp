<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomChat extends Model
{
    protected $table = 'room_chat';
    protected $guarded = [];
    protected $primaryKey = 'id';

    public $timestamps = false;
}
