<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceGoal extends Model
{
    protected $table = 'performance_goals';
    protected $guarded = ['id'];

    protected $casts = [
        'total_kpi_weight'         => 'float',
        'overall_progress_percent' => 'float',
        'final_score'              => 'float',
    ];

    public function period()
    {
        return $this->belongsTo(PerformancePeriod::class, 'period_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(PerformanceGoalItem::class, 'goal_id')->orderBy('sort_order', 'asc');
    }
}

