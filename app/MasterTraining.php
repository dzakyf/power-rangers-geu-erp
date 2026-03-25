<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterTraining extends Model
{
    protected $table = "master_training";
    protected $guarded = ['id'];
    public $timestamps = false;

    public $frequencyMonth = [
        'month',
        // 'quartal',
        // 'semester',
        'year',
    ];
}
