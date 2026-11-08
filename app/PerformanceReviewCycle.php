<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceReviewCycle extends Model
{
    protected $table = 'performance_review_cycles';
    protected $guarded = ['id'];

    protected $casts = [
        'enable_self_review'    => 'boolean',
        'enable_manager_review' => 'boolean',
        'enable_peer_review'    => 'boolean',
        'weight_kpi'            => 'float',
        'weight_competency'     => 'float',
        'start_date'            => 'date',
        'end_date'              => 'date',
    ];

    public function period()
    {
        return $this->belongsTo(PerformancePeriod::class, 'period_id');
    }

    public function reviews()
    {
        return $this->hasMany(PerformanceReview::class, 'cycle_id');
    }
}

