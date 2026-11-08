<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceGoalProgress extends Model
{
    protected $table = 'performance_goal_progress';
    protected $guarded = ['id'];

    protected $casts = [
        'check_in_date'    => 'date',
        'previous_value'   => 'float',
        'new_value'        => 'float',
        'progress_percent' => 'float',
    ];

    public function item()
    {
        return $this->belongsTo(PerformanceGoalItem::class, 'goal_item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

