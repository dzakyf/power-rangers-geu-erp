<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceGoalItem extends Model
{
    protected $table = 'performance_goal_items';
    protected $guarded = ['id'];

    protected $casts = [
        'target_value'     => 'float',
        'actual_value'     => 'float',
        'weight_percent'   => 'float',
        'progress_percent' => 'float',
        'score'            => 'float',
        'due_date'         => 'date',
    ];

    public function goal()
    {
        return $this->belongsTo(PerformanceGoal::class, 'goal_id');
    }

    public function parent()
    {
        return $this->belongsTo(PerformanceGoalItem::class, 'parent_item_id');
    }

    public function keyResults()
    {
        return $this->hasMany(PerformanceGoalItem::class, 'parent_item_id')->orderBy('sort_order', 'asc');
    }

    public function progressLogs()
    {
        return $this->hasMany(PerformanceGoalProgress::class, 'goal_item_id')->orderBy('check_in_date', 'desc');
    }
}

