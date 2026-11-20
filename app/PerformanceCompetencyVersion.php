<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceCompetencyVersion extends Model
{
    protected $table = 'performance_competency_versions';
    protected $guarded = ['id'];

    protected $casts = [
        'version_no'   => 'integer',
        'scale_min'    => 'integer',
        'scale_max'    => 'integer',
        'scale_labels' => 'array',
        'published_at' => 'datetime',
    ];

    public function competencies()
    {
        return $this->hasMany(PerformanceCompetency::class, 'version_id')->orderBy('sort_order')->orderBy('id');
    }

    public static function getActivePublished(): ?self
    {
        return self::where('status', 'PUBLISHED')
            ->orderBy('version_no', 'desc')
            ->first();
    }
}
