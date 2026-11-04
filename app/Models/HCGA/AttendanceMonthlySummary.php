<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class AttendanceMonthlySummary extends Model
{
    protected $table = 'attendance_monthly_summaries';

    protected $guarded = ['id'];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}

