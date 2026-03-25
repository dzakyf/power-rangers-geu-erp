<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingAttendance extends Model
{
    protected $table = "training_attendance";
    protected $guarded = ['id'];
    public $timestamps = false;
}
