<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingApplication extends Model
{
    protected $table = "training_application";
    protected $guarded = ['id'];
    public $timestamps = false;
}
