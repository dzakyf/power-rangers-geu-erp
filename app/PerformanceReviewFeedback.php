<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceReviewFeedback extends Model
{
    protected $table = 'performance_review_feedbacks';
    protected $guarded = ['id'];

    protected $casts = [
        'score'        => 'float',
        'is_submitted' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, 'review_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function competencyScores()
    {
        return $this->hasMany(PerformanceReviewCompetencyScore::class, 'feedback_id');
    }
}

