<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceCompetency extends Model
{
    protected $table = 'performance_competencies';
    protected $guarded = ['id'];

    protected $casts = [
        'target_level' => 'integer',
        'is_active'    => 'boolean',
    ];

    public function scores()
    {
        return $this->hasMany(PerformanceReviewCompetencyScore::class, 'competency_id');
    }
}

