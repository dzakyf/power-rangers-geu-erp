<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    protected $table = 'performance_reviews';
    protected $guarded = ['id'];

    protected $casts = [
        'self_score'       => 'float',
        'manager_score'    => 'float',
        'kpi_score'        => 'float',
        'competency_score' => 'float',
        'final_score'      => 'float',
    ];

    public function cycle()
    {
        return $this->belongsTo(PerformanceReviewCycle::class, 'cycle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function goal()
    {
        return $this->belongsTo(PerformanceGoal::class, 'goal_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(PerformanceReviewFeedback::class, 'review_id');
    }
}

