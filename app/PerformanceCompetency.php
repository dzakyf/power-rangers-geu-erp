<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceCompetency extends Model
{
    protected $table = 'performance_competencies';
    protected $guarded = ['id'];

    protected $casts = [
        'version_id'   => 'integer',
        'target_level' => 'integer',
        'sort_order'   => 'integer',
        'is_active'    => 'boolean',
    ];

    public function version()
    {
        return $this->belongsTo(PerformanceCompetencyVersion::class, 'version_id');
    }

    public function scores()
    {
        return $this->hasMany(PerformanceReviewCompetencyScore::class, 'competency_id');
    }
}

