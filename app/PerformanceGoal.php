<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceGoal extends Model
{
    protected $table = 'performance_goals';
    protected $guarded = ['id'];

    protected $casts = [
        'is_addon'                 => 'boolean',
        'probation_months'         => 'integer',
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

    public function scopeForPosition($query, int $periodId, int $positionId)
    {
        return $query->where('period_id', $periodId)
            ->where('position_id', $positionId)
            ->where('scope_type', 'POSITION');
    }
}

