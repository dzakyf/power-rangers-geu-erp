<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingSatisfication extends Model
{
    protected $table = "training_satisfication";
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    public $timestamps = false;
}
