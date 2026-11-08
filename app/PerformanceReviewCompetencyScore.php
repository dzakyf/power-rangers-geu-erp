<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceReviewCompetencyScore extends Model
{
    protected $table = 'performance_review_competency_scores';
    protected $guarded = ['id'];

    protected $casts = [
        'score' => 'integer',
    ];

    public function feedback()
    {
        return $this->belongsTo(PerformanceReviewFeedback::class, 'feedback_id');
    }

    public function competency()
    {
        return $this->belongsTo(PerformanceCompetency::class, 'competency_id');
    }
}

