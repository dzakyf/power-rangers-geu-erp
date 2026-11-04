<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendanceMain extends Model
{
    protected $table = 'attendant_log_main';

    protected $primaryKey = 'ID';

    protected $guarded = [];

    public $timestamps = false;

    const CREATED_AT = 'Created_Date';

    public function entries()
    {
        return $this->hasMany(Attendance::class, 'Task_ID', 'Task_ID')
            ->orderBy('Scan_Date', 'asc');
    }
}
