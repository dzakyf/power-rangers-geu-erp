<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformancePipPlan extends Model
{
    protected $table = 'performance_pip_plans';
    protected $guarded = ['id'];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'duration_days' => 'integer',
        'final_score'   => 'float',
    ];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, 'review_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function actions()
    {
        return $this->hasMany(PerformancePipAction::class, 'pip_id');
    }

    public function checkins()
    {
        return $this->hasMany(PerformancePipCheckin::class, 'pip_id')->orderBy('week_number', 'asc');
    }

    public function trainings()
    {
        return $this->hasMany(PerformancePipTraining::class, 'pip_id');
    }
}

