<?php

namespace App\Models\HCGA;

use Illuminate\Database\Eloquent\Model;

class EmployeeSchedule extends Model
{
    protected $table = 'employee_schedules';

    protected $guarded = ['id'];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function shift()
    {
        return $this->belongsTo(MasterShift::class, 'shift_id', 'id');
    }
}

