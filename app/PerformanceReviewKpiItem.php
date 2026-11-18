<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceReviewKpiItem extends Model
{
    protected $table = 'performance_review_kpi_items';
    protected $guarded = ['id'];

    protected $casts = [
        'target_value'     => 'float',
        'actual_value'     => 'float',
        'weight_percent'   => 'float',
        'progress_percent' => 'float',
        'score'            => 'float',
    ];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, 'review_id');
    }

    public function goalItem()
    {
        return $this->belongsTo(PerformanceGoalItem::class, 'goal_item_id');
    }
}
