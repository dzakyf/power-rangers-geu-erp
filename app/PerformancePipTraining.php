<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformancePipTraining extends Model
{
    protected $table = 'performance_pip_trainings';
    protected $guarded = ['id'];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function pip()
    {
        return $this->belongsTo(PerformancePipPlan::class, 'pip_id');
    }
}

