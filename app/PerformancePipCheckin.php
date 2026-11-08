<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformancePipCheckin extends Model
{
    protected $table = 'performance_pip_checkins';
    protected $guarded = ['id'];

    protected $casts = [
        'checkin_date' => 'date',
        'week_number'  => 'integer',
    ];

    public function pip()
    {
        return $this->belongsTo(PerformancePipPlan::class, 'pip_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

