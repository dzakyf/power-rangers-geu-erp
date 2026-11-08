<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HcgaNewsAnnouncement extends Model
{
    protected $table = 'hcga_news_announcements';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'is_final'   => 'integer',
        'is_pinned'  => 'integer',
        'view_count' => 'integer',
    ];

    /**
     * Scope for published / final announcements
     */
    public function scopePublished($query)
    {
        return $query->where('is_final', 1);
    }

    /**
     * Scope for audience filtering
     */
    public function scopeForAudience($query, $isHcga = false)
    {
        if (!$isHcga) {
            return $query->where(function ($q) {
                $q->where('target_audience', 'ALL')
                  ->orWhereNull('target_audience');
            });
        }
        return $query;
    }
}

