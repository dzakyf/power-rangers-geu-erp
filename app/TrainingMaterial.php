<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingMaterial extends Model
{
    protected $table = "training_material";
    protected $guarded = ['id'];
    public $timestamps = false;
}
