<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class AttendanceWeeklySummary extends Model
{
    protected $table = 'attendance_weekly_summaries';

    protected $guarded = ['id'];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'is_target_achieved' => 'boolean',
    ];
}

