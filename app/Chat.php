<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chat';
    protected $guarded = [];
    protected $primaryKey = 'id';

    public $timestamps = false;
}
