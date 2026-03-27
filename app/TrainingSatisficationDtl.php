<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingSatisficationDtl extends Model
{
    protected $table = "training_satisfication_dtl";
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    public $timestamps = false;
}
