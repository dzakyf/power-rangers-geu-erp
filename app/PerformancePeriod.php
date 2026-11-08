<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformancePeriod extends Model
{
    protected $table = 'performance_periods';
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function goals()
    {
        return $this->hasMany(PerformanceGoal::class, 'period_id');
    }
}

